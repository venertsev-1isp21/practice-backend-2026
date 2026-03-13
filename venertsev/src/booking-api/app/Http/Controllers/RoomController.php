<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\Review;

class RoomController extends Controller
{
    public function index()
    {
        return Room::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'location' => 'required|string|max:255',
        ]);

        return Room::create($validated);
    }

    public function show(Room $room)
    {
        return $room;
    }

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

    public function destroy(Room $room)
    {
        $room->delete();
        return response()->noContent();
    }

    public function schedule($id)
    {
        $room = Room::findOrFail($id);

        $bookings = Booking::where('room_id', $id)
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'room' => $room,
            'schedule' => $bookings
        ]);
    }
    public function available(Request $request)
    {
        $start = $request->start_time;
        $end = $request->end_time;
        $capacity = $request->capacity;

        $rooms = Room::where('capacity', '>=', $capacity)
            ->whereDoesntHave('bookings', function ($query) use ($start, $end) {
                $query->where(function ($q) use ($start, $end) {
                    $q->whereBetween('start_time', [$start, $end])
                    ->orWhereBetween('end_time', [$start, $end]);
                });
            })
            ->get();

        return response()->json($rooms);
    }
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