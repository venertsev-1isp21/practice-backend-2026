<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    // список бронирований
    public function index()
    {
        $user = Auth::user();

        // админ видит всё
        if ($user->role === 'admin') {
            return Booking::with(['user', 'room', 'status'])->get();
        }

        // пользователь только свои
        return Booking::with(['room', 'status'])
            ->where('user_id', $user->id)
            ->get();
    }

    // создание бронирования
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        // проверка пересечения
        $conflict = Booking::where('room_id', $validated['room_id'])
            ->where(function ($query) use ($validated) {
                $query->where('start_time', '<', $validated['end_time'])
                      ->where('end_time', '>', $validated['start_time']);
            })
            ->exists();

        if ($conflict) {

            Log::warning('Booking conflict', [
                'user_id' => $user->id,
                'room_id' => $validated['room_id'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time']
            ]);

            return response()->json([
                'message' => 'Time slot already booked'
            ], 409);
        }

        $booking = Booking::create([
            'user_id' => $user->id,
            'room_id' => $validated['room_id'],
            'status_id' => 1,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        Log::info('Booking created', [
            'booking_id' => $booking->id,
            'user_id' => $user->id,
            'room_id' => $validated['room_id']
        ]);

        return response()->json($booking, 201);
    }

    // просмотр бронирования
    public function show($id)
    {
        $booking = Booking::with(['user','room','status'])->findOrFail($id);
        $user = Auth::user();

        if ($user->role !== 'admin' && $booking->user_id !== $user->id) {

            Log::warning('Unauthorized booking access', [
                'user_id' => $user->id,
                'booking_id' => $booking->id
            ]);

            return response()->json([
                'message' => 'Access denied'
            ], 403);
        }

        return $booking;
    }

    // отмена бронирования
    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $user = Auth::user();

        if ($user->role !== 'admin' && $booking->user_id !== $user->id) {

            Log::warning('Unauthorized booking cancel attempt', [
                'user_id' => $user->id,
                'booking_id' => $booking->id
            ]);

            return response()->json([
                'message' => 'You cannot cancel this booking'
            ], 403);
        }

        $booking->delete();

        Log::info('Booking cancelled', [
            'booking_id' => $booking->id,
            'user_id' => $user->id
        ]);

        return response()->json([
            'message' => 'Booking cancelled'
        ]);
    }
}