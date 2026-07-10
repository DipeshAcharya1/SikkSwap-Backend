<?php

namespace App\Http\Controllers;

use App\Http\Resources\LearningRequestResource;
use App\Models\LearningRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LearningRequestController extends Controller
{
    /**
     * List learning requests for the authenticated user (as student or mentor).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $role = $request->query('role', 'student'); // 'student' or 'mentor'

        $query = LearningRequest::with(['student', 'mentor', 'skill']);

        if ($role === 'mentor') {
            $query->where('mentor_id', $user->id);
        } else {
            $query->where('student_id', $user->id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return LearningRequestResource::collection($query->latest()->get());
    }

    /**
     * Create a new learning request.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mentor_id' => 'required|exists:users,id|different:' . $request->user()->id,
            'skill_id'  => 'required|exists:skills,id',
            'message'   => 'nullable|string|max:1000',
        ]);

        // Prevent duplicate pending requests
        $existing = LearningRequest::where('student_id', $request->user()->id)
            ->where('mentor_id', $validated['mentor_id'])
            ->where('skill_id', $validated['skill_id'])
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json(['message' => 'You already have a pending request for this skill with this mentor.'], 422);
        }

        $learningRequest = LearningRequest::create([
            ...$validated,
            'student_id' => $request->user()->id,
            'status'     => 'pending',
        ]);

        $learningRequest->load(['student', 'mentor', 'skill']);

        return response()->json(new LearningRequestResource($learningRequest), 201);
    }

    /**
     * Show a single learning request.
     */
    public function show(LearningRequest $learningRequest): LearningRequestResource
    {
        $this->authorize('view', $learningRequest);
        $learningRequest->load(['student', 'mentor', 'skill']);
        return new LearningRequestResource($learningRequest);
    }

    /**
     * Accept or reject a learning request (mentor only).
     */
    public function update(Request $request, LearningRequest $learningRequest): JsonResponse
    {
        $this->authorize('update', $learningRequest);

        $validated = $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        $learningRequest->update($validated);
        $learningRequest->load(['student', 'mentor', 'skill']);

        return response()->json(new LearningRequestResource($learningRequest));
    }

    /**
     * Cancel a learning request (student only).
     */
    public function destroy(LearningRequest $learningRequest): JsonResponse
    {
        $this->authorize('delete', $learningRequest);
        $learningRequest->delete();
        return response()->json(['message' => 'Learning request cancelled.']);
    }
}
