<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Models\Session;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReviewController extends Controller
{
    /**
     * List reviews for a user (as reviewee).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $userId = $request->query('user_id', $request->user()->id);

        $reviews = Review::with(['reviewer', 'reviewee'])
            ->where('reviewee_id', $userId)
            ->latest()
            ->paginate(10);

        return ReviewResource::collection($reviews);
    }

    /**
     * Create a review for a completed session.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id'  => 'required|exists:learning_sessions,id',
            'reviewee_id' => 'required|exists:users,id',
            'rating'      => 'required|integer|min:1|max:5',
            'comment'     => 'nullable|string|max:1000',
        ]);

        // Ensure session is completed and user is a participant
        $session = Session::findOrFail($validated['session_id']);

        if ($session->status !== 'completed') {
            return response()->json(['message' => 'You can only review completed sessions.'], 422);
        }

        if (!in_array($request->user()->id, [$session->mentor_id, $session->student_id])) {
            return response()->json(['message' => 'You are not a participant in this session.'], 403);
        }

        // Prevent duplicate reviews
        $existing = Review::where('session_id', $validated['session_id'])
            ->where('reviewer_id', $request->user()->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'You have already reviewed this session.'], 422);
        }

        $review = Review::create([
            ...$validated,
            'reviewer_id' => $request->user()->id,
        ]);

        $review->load(['reviewer', 'reviewee']);

        return response()->json(new ReviewResource($review), 201);
    }

    /**
     * Show a single review.
     */
    public function show(Review $review): ReviewResource
    {
        $review->load(['reviewer', 'reviewee']);
        return new ReviewResource($review);
    }

    /**
     * Delete a review (admin only).
     */
    public function destroy(Review $review): JsonResponse
    {
        $this->authorize('delete', $review);
        $review->delete();
        return response()->json(['message' => 'Review deleted.']);
    }
}
