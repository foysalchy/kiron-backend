<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;

class ReservationDashboardController extends Controller
{
    /**
     * Show the authenticated customer's reservations.
     */
    public function myReservations()
    {
        $customer = auth()->guard('customer')->user();
        $reservations = Reservation::where('guest_phone', $customer->phone ?? null)
            ->orderBy('reservation_date', 'desc')
            ->paginate(10);

        return view('template5.frontend.user.my_reservations', compact('reservations'));
    }
}

