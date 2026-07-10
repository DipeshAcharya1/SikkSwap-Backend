<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserSkillResource;
use App\Models\UserSkill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserSkillController extends Controller
{
    /**
     * List the authenticated user's skills.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $skills = $request->user()->userSkills()->with('skill.category')->get();
        return UserSkillResource::collection($skills);
    }

    /**
     * Add a skill to the authenticated user's profile.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'skill_id'          => 'required|exists:skills,id',
            'proficiency_level' => 'required|in:beginner,intermediate,expert',
            'is_teaching'       => 'sometimes|boolean',
        ]);

        $userSkill = $request->user()->userSkills()->updateOrCreate(
            ['skill_id' => $validated['skill_id']],
            $validated
        );

        $userSkill->load('skill.category');

        return response()->json(new UserSkillResource($userSkill), 201);
    }

    /**
     * Update a user skill.
     */
    public function update(Request $request, UserSkill $userSkill): UserSkillResource
    {
        $this->authorize('update', $userSkill);

        $validated = $request->validate([
            'proficiency_level' => 'sometimes|in:beginner,intermediate,expert',
            'is_teaching'       => 'sometimes|boolean',
        ]);

        $userSkill->update($validated);
        $userSkill->load('skill.category');

        return new UserSkillResource($userSkill);
    }

    /**
     * Remove a skill from the authenticated user's profile.
     */
    public function destroy(Request $request, UserSkill $userSkill): JsonResponse
    {
        $this->authorize('delete', $userSkill);
        $userSkill->delete();
        return response()->json(['message' => 'Skill removed from profile.']);
    }
}
