<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $review = Review::create([
            'user_id' => Auth::id(),
            'room_id' => $validated['room_id'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null
        ]);

        return response()->json($review, 201);
    }
}
