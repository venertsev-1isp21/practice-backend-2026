<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        return Booking::with(['user', 'room', 'status'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'status_id' => 'required|exists:booking_statuses,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        return Booking::create($validated);
    }

    public function show(Booking $booking)
    {
        return $booking->load(['user', 'room', 'status']);
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'room_id' => 'sometimes|exists:rooms,id',
            'status_id' => 'sometimes|exists:booking_statuses,id',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
        ]);

        $booking->update($validated);
        return $booking;
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();
        return response()->noContent();
    }
}