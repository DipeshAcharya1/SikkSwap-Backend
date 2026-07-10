<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MentorController extends Controller
{
    /**
     * List users who teach at least one skill.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = User::whereHas('userSkills', fn($q) => $q->where('is_teaching', true))
            ->with([
                'userSkills' => fn($q) => $q->where('is_teaching', true)->with('skill.category'),
            ])
            ->withCount([
                'userSkills as teaching_count' => fn($q) => $q->where('is_teaching', true),
            ]);

        if ($request->filled('skill_id')) {
            $query->whereHas('userSkills', fn($q) =>
                $q->where('is_teaching', true)->where('skill_id', $request->skill_id)
            );
        }

        if ($request->filled('category_id')) {
            $query->whereHas('userSkills.skill', fn($q) =>
                $q->where('category_id', $request->category_id)
            );
        }

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        $mentors = $query->orderBy('name')->paginate(12);

        return UserResource::collection($mentors);
    }

    /**
     * Show a single mentor's public profile.
     */
    public function show(User $user): UserResource
    {
        $user->load([
            'userSkills' => fn($q) => $q->where('is_teaching', true)->with('skill.category'),
        ]);
        $user->loadCount([
            'taughtSessions as completed_sessions_count' => fn($q) => $q->where('status', 'completed'),
        ]);

        return new UserResource($user);
    }
}
