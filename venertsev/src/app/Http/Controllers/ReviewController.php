<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user', 'room']);

        if ($request->room_id) {
            $query->where('room_id', $request->room_id);
        }

        return $query->paginate($request->get('per_page', 10));
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);


        $exists = Review::where('user_id', Auth::id())
            ->where('room_id', $validated['room_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'You already reviewed this room'
            ], 409);
        }

        $review = Review::create([
            'user_id' => Auth::id(),
            'room_id' => $validated['room_id'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null
        ]);

        return response()->json($review, 201);
    }



    public function show($id)
    {
        return Review::with(['user', 'room'])->findOrFail($id);
    }



    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        if ($review->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Access denied'
            ], 403);
        }

        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $review->update($validated);

        return response()->json($review);
    }



    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        if ($review->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Access denied'
            ], 403);
        }

        $review->delete();

        return response()->json([
            'message' => 'Review deleted'
        ]);
    }



    public function my()
    {
        return Review::where('user_id', Auth::id())
            ->with('room')
            ->paginate(10);
    }
}