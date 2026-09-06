<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Reservation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReservationService
{
    public function getAllReservations(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Reservation::with(['tables']);

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (isset($filters['table_id'])) {
                // If filtering by a specific table via pivot
                $query->whereHas('tables', function ($q) use ($filters) {
                    $q->where('tables.id', $filters['table_id']);
                });
            }
            if (isset($filters['date'])) {
                $query->where('reservation_date', $filters['date']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('guest_name', 'like', "%{$search}%")
                        ->orWhere('guest_phone', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'reservation_date';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder)->orderBy('start_time', 'asc');

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching reservations: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch reservations');
        }
    }

    public function getReservationById(int $id): Reservation
    {
        $reservation = Reservation::with(['tables'])->find($id);
        if (!$reservation) {
            throw ApiException::notFound('reservation');
        }
        return $reservation;
    }

    protected function checkBookingConflict(array $data, ?int $ignoreId = null): void
    {
        $status = $data['status'] ?? 'pending';
        if (in_array($status, ['completed', 'cancelled'])) {
            return;
        }
        
        $tableIds = $data['table_ids'] ?? [];
        if (empty($tableIds)) {
            return;
        }

        $query = Reservation::whereHas('tables', function($q) use ($tableIds) {
                $q->whereIn('tables.id', $tableIds);
            })
            ->where('reservation_date', $data['reservation_date'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($q) use ($data) {
                $q->where('start_time', '<', $data['end_time'])
                    ->where('end_time', '>', $data['start_time']);
            });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ApiException::validationFailed([
                'table_ids' => ['One or more of the selected tables are already booked for this time range.']
            ]);
        }
    }

    public function createReservation(array $data): Reservation
    {
        $this->checkBookingConflict($data);

        DB::beginTransaction();
        try {
            $reservationData = array_diff_key($data, array_flip(['table_ids']));
            if (!empty($data['table_ids'])) {
                $reservationData['table_id'] = $data['table_ids'][0];
            }
            $reservation = Reservation::create($reservationData);
            
            if (!empty($data['table_ids'])) {
                $reservation->tables()->attach($data['table_ids']);
                // explicit trigger for cache clear if model didn't touch
                Reservation::clearHomepageCache($reservation->company_id);
            }

            LogHelper::created('reservation', $reservation->id, $reservation->company_id, 'Reservation for ' . $reservation->guest_name);
            DB::commit();
            Log::info('Reservation created successfully', ['reservation_id' => $reservation->id]);

            return $reservation->load(['tables']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reservation creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create reservation');
        }
    }

    public function updateReservation(int $id, array $data): Reservation
    {
        $reservation = $this->getReservationById($id);

        // Merge existing data for conflict check
        $checkData = array_merge($reservation->toArray(), $data);
        if (!isset($checkData['table_ids'])) {
            $checkData['table_ids'] = $reservation->tables->pluck('id')->toArray();
        }
        $this->checkBookingConflict($checkData, $id);

        DB::beginTransaction();
        try {
            $updateData = array_diff_key($data, array_flip(['table_ids']));
            $reservation->update($updateData);

            if (isset($data['table_ids'])) {
                $reservation->tables()->sync($data['table_ids']);
                $reservation->touch();
                Reservation::clearHomepageCache($reservation->company_id);
            }

            LogHelper::updated('reservation', $reservation->id, $reservation->company_id, 'Reservation for ' . $reservation->guest_name);
            DB::commit();
            Log::info('Reservation Updated Successfully', ['reservation_id' => $reservation->id]);

            return $reservation->fresh(['tables']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reservation update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update reservation');
        }
    }

    public function deleteReservation(int $id): bool
    {
        DB::beginTransaction();
        try {
            $reservation = $this->getReservationById($id);
            $companyId = $reservation->company_id;

            $reservation->delete();
            Reservation::clearHomepageCache($companyId);

            LogHelper::deleted('reservation', $reservation->id, $companyId, 'Reservation for ' . $reservation->guest_name);
            DB::commit();
            Log::info('Reservation deleted successfully', ['reservation_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reservation deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete reservation');
        }
    }

    public function updateStatus(Reservation $reservation, string $status): Reservation
    {
        if ($reservation->company_id !== auth()->user()->company_id && auth()->user()->role !== 'super_admin') {
            throw ApiException::forbidden('You do not have permission to update this reservation');
        }

        DB::beginTransaction();
        try {
            $updateData = ['status' => $status];

            if ($status === 'confirmed' && empty($reservation->confirmation_token)) {
                $reservation->loadMissing('tables');
                $tableNumbers = $reservation->tables->pluck('table_number')->map(fn($num) => 'T'.$num)->implode('+');
                $lastThreeDigits = substr($reservation->guest_phone, -3);
                $updateData['confirmation_token'] = "{$tableNumbers}-{$lastThreeDigits}";
            }

            $reservation->update($updateData);
            Reservation::clearHomepageCache($reservation->company_id);

            LogHelper::updated('reservation', $reservation->id, $reservation->company_id, 'Reservation status updated to ' . $status . ' for ' . $reservation->guest_name);
            DB::commit();
            Log::info('Reservation Status Updated Successfully', ['reservation_id' => $reservation->id, 'status' => $status]);

            return $reservation->fresh(['tables']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reservation status update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update reservation status');
        }
    }
}
