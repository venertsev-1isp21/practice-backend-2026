<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\Review;

class RoomController extends Controller
{
    // список комнат (с фильтрацией + пагинацией)
    public function index(Request $request)
    {
        $query = Room::query();

        // фильтр по вместимости
        if ($request->capacity) {
            $query->where('capacity', '>=', $request->capacity);
        }

        // поиск по названию
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // сортировка
        $allowedSorts = ['capacity', 'name', 'created_at'];
        $sortBy = in_array($request->get('sort_by'), $allowedSorts)
            ? $request->get('sort_by')
            : 'created_at';

        $sortOrder = $request->get('sort_order') === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($request->get('per_page', 10));
    }


    // создание
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'location' => 'required|string|max:255',
        ]);

        return Room::create($validated);
    }


    // просмотр
    public function show(Room $room)
    {
        return $room;
    }


    // обновление
    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'capacity' => 'sometimes|integer|min:1',
            'location' => 'sometimes|string|max:255',
        ]);

        $room->update($validated);

        return $room;
    }


    // удаление
    public function destroy(Room $room)
    {
        $room->delete();

        return response()->json([
            'message' => 'Room deleted'
        ]);
    }


    // 📅 расписание комнаты
    public function schedule(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $query = Booking::where('room_id', $id)
            ->orderBy('start_time');

        // фильтр по дате
        if ($request->date) {
            $query->whereDate('start_time', $request->date);
        }

        return response()->json([
            'room' => $room,
            'schedule' => $query->get()
        ]);
    }


    // 🔍 поиск свободных комнат (УЛУЧШЕННЫЙ)
    public function available(Request $request)
    {
        $validated = $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'capacity' => 'nullable|integer|min:1'
        ]);

        $start = $validated['start_time'];
        $end = $validated['end_time'];
        $capacity = $validated['capacity'] ?? 0;

        $rooms = Room::where('capacity', '>=', $capacity)
            ->whereDoesntHave('bookings', function ($query) use ($start, $end) {

                // ПРАВИЛЬНАЯ проверка пересечения
                $query->where(function ($q) use ($start, $end) {
                    $q->where('start_time', '<', $end)
                      ->where('end_time', '>', $start);
                });

            })
            ->get();

        return response()->json($rooms);
    }


    // ⭐ рейтинг комнаты
    public function rating($roomId)
    {
        $reviews = Review::where('room_id', $roomId)->get();

        if ($reviews->isEmpty()) {
            return response()->json([
                'room_id' => $roomId,
                'average_rating' => null,
                'reviews_count' => 0
            ]);
        }

        $average = $reviews->avg('rating');

        return response()->json([
            'room_id' => $roomId,
            'average_rating' => round($average, 2),
            'reviews_count' => $reviews->count()
        ]);
    }
}