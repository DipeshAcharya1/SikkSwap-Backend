<?php

namespace App\Http\Controllers;

use App\Http\Resources\SessionResource;
use App\Models\Session;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SessionController extends Controller
{
    /**
     * List sessions for the authenticated user.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $sessions = Session::with(['mentor', 'student', 'skill', 'review'])
            ->where(fn($q) => $q->where('mentor_id', $user->id)->orWhere('student_id', $user->id))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest('start_time')
            ->get();

        return SessionResource::collection($sessions);
    }

    /**
     * Book / create a new session.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mentor_id'    => 'required|exists:users,id|different:' . $request->user()->id,
            'skill_id'     => 'required|exists:skills,id',
            'start_time'   => 'required|date|after:now',
            'end_time'     => 'required|date|after:start_time',
            'meeting_link' => 'nullable|url',
        ]);

        $session = Session::create([
            ...$validated,
            'student_id' => $request->user()->id,
            'status'     => 'scheduled',
        ]);

        $session->load(['mentor', 'student', 'skill']);

        return response()->json(new SessionResource($session), 201);
    }

    /**
     * Show a single session.
     */
    public function show(Session $session): SessionResource
    {
        $this->authorize('view', $session);
        $session->load(['mentor', 'student', 'skill', 'review']);
        return new SessionResource($session);
    }

    /**
     * Update session status or meeting link.
     */
    public function update(Request $request, Session $session): SessionResource
    {
        $this->authorize('update', $session);

        $validated = $request->validate([
            'status'       => 'sometimes|in:scheduled,completed,cancelled',
            'meeting_link' => 'sometimes|nullable|url',
        ]);

        $session->update($validated);
        $session->load(['mentor', 'student', 'skill', 'review']);

        return new SessionResource($session);
    }

    /**
     * Cancel a session.
     */
    public function destroy(Session $session): JsonResponse
    {
        $this->authorize('delete', $session);
        $session->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Session cancelled.']);
    }
}
