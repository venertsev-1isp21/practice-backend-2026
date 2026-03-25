<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    // список бронирований
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Booking::with(['user', 'room', 'status']);

        // пользователь видит только свои
        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        // фильтр по комнате
        if ($request->room_id) {
            $query->where('room_id', $request->room_id);
        }

        // фильтр по статусу
        if ($request->status_id) {
            $query->where('status_id', $request->status_id);
        }

        // фильтр по дате (один день)
        if ($request->date) {
            $query->whereDate('start_time', $request->date);
        }

        // фильтр по диапазону дат
        if ($request->date_from && $request->date_to) {
            $query->whereBetween('start_time', [$request->date_from, $request->date_to]);
        }

        // безопасная сортировка
        $allowedSorts = ['start_time', 'end_time', 'created_at'];
        $sortBy = in_array($request->get('sort_by'), $allowedSorts)
            ? $request->get('sort_by')
            : 'start_time';

        $sortOrder = $request->get('sort_order') === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($request->get('per_page', 10));
    }


    // мои бронирования (удобный endpoint)
    public function my()
    {
        return Booking::where('user_id', Auth::id())
            ->with(['room', 'status'])
            ->paginate(10);
    }


    // создание бронирования
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'start_time' => 'required|date|after:now',
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
            return response()->json([
                'message' => 'Time slot already booked'
            ], 409);
        }

        $booking = Booking::create([
            'user_id' => $user->id,
            'room_id' => $validated['room_id'],
            'status_id' => 1, // например: pending
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        return response()->json($booking, 201);
    }


    // просмотр
    public function show($id)
    {
        $booking = Booking::with(['user','room','status'])->findOrFail($id);
        $user = Auth::user();

        if ($user->role !== 'admin' && $booking->user_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return $booking;
    }


    // обновление
    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $user = Auth::user();

        if ($user->role !== 'admin' && $booking->user_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        // проверка пересечения (исключая текущую запись)
        $conflict = Booking::where('room_id', $booking->room_id)
            ->where('id', '!=', $booking->id)
            ->where(function ($query) use ($validated) {
                $query->where('start_time', '<', $validated['end_time'])
                      ->where('end_time', '>', $validated['start_time']);
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'Time slot already booked'
            ], 409);
        }

        $booking->update($validated);

        return response()->json($booking);
    }


    // отмена (мягкая логика)
    public function cancel($id)
    {
        $booking = Booking::findOrFail($id);
        $user = Auth::user();

        if ($user->role !== 'admin' && $booking->user_id !== $user->id) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $booking->status_id = 3; // cancelled
        $booking->save();

        return response()->json(['message' => 'Booking cancelled']);
    }


    // подтверждение (например админом)
    public function confirm($id)
    {
        $booking = Booking::findOrFail($id);
        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Only admin can confirm'], 403);
        }

        $booking->status_id = 2; // confirmed
        $booking->save();

        return response()->json(['message' => 'Booking confirmed']);
    }


    // удаление (жёсткое)
    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $user = Auth::user();

        if ($user->role !== 'admin' && $booking->user_id !== $user->id) {
            return response()->json([
                'message' => 'You cannot delete this booking'
            ], 403);
        }

        $booking->delete();

        return response()->json([
            'message' => 'Booking deleted'
        ]);
    }
}