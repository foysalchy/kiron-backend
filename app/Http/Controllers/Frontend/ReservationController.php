<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\LogHelper;
use App\Models\Reservation;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReservationController extends FrontendController
{
    public function index()
    {
        return $this->view('frontend.reservation');
    }

    public function availability(Request $request)
    {
        $request->validate([
            'reservation_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'guest_count' => 'required|integer|min:1',
        ]);

        $companyId = $this->company_id;
        $date = $request->reservation_date;
        $startTime = $request->start_time;
        $endTime = $request->end_time;

        // Fetch cached tables for the company
        $ttl = now()->addHours(6);
        $tables = Cache::remember("home_tables_{$companyId}", $ttl, function () use ($companyId) {
            return Table::where('company_id', $companyId)
                ->where('is_active', 1)
                ->select('id', 'table_number', 'capacity')
                ->get();
        });

        // Fetch conflicting reservations (any pending/confirmed reservations overlapping the requested time)
        $conflicts = DB::table('reservation_table')
            ->join('reservations', 'reservation_table.reservation_id', '=', 'reservations.id')
            ->where('reservations.company_id', $companyId)
            ->where('reservations.reservation_date', $date)
            ->whereIn('reservations.status', ['pending', 'confirmed'])
            ->where(function($q) use ($startTime, $endTime) {
                $q->where('reservations.start_time', '<', $endTime)
                  ->where('reservations.end_time', '>', $startTime);
            })
            ->pluck('reservation_table.table_id')
            ->toArray();

        // Mark tables as available or not
        $mappedTables = $tables->map(function($table) use ($conflicts) {
            $table->is_available = !in_array($table->id, $conflicts);
            return $table;
        });

        return response()->json([
            'success' => true,
            'tables' => $mappedTables,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reservation_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'table_ids' => 'required|array|min:1',
            'table_ids.*' => 'integer|exists:tables,id',
            'guest_count' => 'required|integer|min:1',
            'guest_name' => 'required|string|max:255',
            'guest_phone' => ['required', 'string', 'regex:/^[0-9]{7,15}$/'],
            'notes' => 'nullable|string',
        ], [
            'guest_phone.regex' => 'Phone number must be between 7 and 15 digits.'
        ]);

        $companyId = $this->company_id;
        $date = $request->reservation_date;
        $startTime = $request->start_time;
        $endTime = $request->end_time;
        $tableIds = $request->table_ids;

        // Re-validate conflict for all requested tables
        $conflicts = DB::table('reservation_table')
            ->join('reservations', 'reservation_table.reservation_id', '=', 'reservations.id')
            ->where('reservations.company_id', $companyId)
            ->whereIn('reservation_table.table_id', $tableIds)
            ->where('reservations.reservation_date', $date)
            ->whereIn('reservations.status', ['pending', 'confirmed'])
            ->where(function($q) use ($startTime, $endTime) {
                $q->where('reservations.start_time', '<', $endTime)
                  ->where('reservations.end_time', '>', $startTime);
            })
            ->pluck('reservation_table.table_id')
            ->toArray();

        if (count($conflicts) > 0) {
            return response()->json([
                'success' => false,
                'message' => 'One or more selected tables are no longer available for this time slot.',
                'conflicting_tables' => $conflicts
            ], 422);
        }

        DB::beginTransaction();
        try {
            $reservation = Reservation::create([
                'company_id' => $companyId,
                'table_id' => $tableIds[0],
                'reservation_date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'guest_count' => $request->guest_count,
                'guest_name' => $request->guest_name,
                'guest_phone' => $request->guest_phone,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            $reservation->tables()->attach($tableIds);
            Reservation::clearHomepageCache($companyId);

            LogHelper::created('reservation', $reservation->id, $companyId, 'Reservation for ' . $reservation->guest_name);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Your reservation request is pending confirmation.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkStatus(Request $request)
    {
        $request->validate([
            'guest_phone' => ['required', 'string', 'regex:/^[0-9]{7,15}$/'],
        ], [
            'guest_phone.regex' => 'Phone number must be between 7 and 15 digits long.'
        ]);

        $companyId = $this->company_id;

        $reservations = Reservation::with(['tables' => function($q) {
                $q->select('tables.id', 'table_number');
            }])
            ->where('company_id', $companyId)
            ->where('guest_phone', $request->guest_phone)
            ->orderBy('reservation_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        $mapped = $reservations->map(function($res) {
            return [
                'id' => $res->id,
                'reservation_date' => date('M d, Y', strtotime($res->reservation_date)),
                'start_time' => date('h:i A', strtotime($res->start_time)),
                'end_time' => date('h:i A', strtotime($res->end_time)),
                'guest_count' => $res->guest_count,
                'status' => ucfirst($res->status),
                'confirmation_token' => $res->confirmation_token,
                'table_numbers' => $res->tables->pluck('table_number')->implode(', ')
            ];
        });

        return response()->json([
            'success' => true,
            'reservations' => $mapped
        ]);
    }
}

