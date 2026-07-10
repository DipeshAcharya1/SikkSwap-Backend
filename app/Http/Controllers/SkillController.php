<?php

namespace App\Http\Controllers;

use App\Http\Resources\SkillResource;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SkillController extends Controller
{
    /**
     * List all skills with optional search/filter.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Skill::with('category')
            ->withCount(['userSkills as mentors_count' => function ($q) {
                $q->where('is_teaching', true);
            }]);

        if ($request->has('search') && $request->search) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $skills = $query->orderBy('name')->paginate(12);

        return SkillResource::collection($skills);
    }

    /**
     * Show a single skill with mentors who teach it.
     */
    public function show(Skill $skill): SkillResource
    {
        $skill->load([
            'category',
            'userSkills' => fn($q) => $q->where('is_teaching', true)->with('user'),
        ]);

        return new SkillResource($skill);
    }

    /**
     * Create a new skill (admin only).
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Skill::class);

        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:skills',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $skill = Skill::create($validated);
        $skill->load('category');

        return response()->json(new SkillResource($skill), 201);
    }

    /**
     * Update a skill (admin only).
     */
    public function update(Request $request, Skill $skill): SkillResource
    {
        $this->authorize('update', $skill);

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255|unique:skills,name,' . $skill->id,
            'description' => 'nullable|string',
            'category_id' => 'sometimes|exists:categories,id',
        ]);

        $skill->update($validated);
        $skill->load('category');

        return new SkillResource($skill);
    }

    /**
     * Delete a skill (admin only).
     */
    public function destroy(Skill $skill): JsonResponse
    {
        $this->authorize('delete', $skill);
        $skill->delete();
        return response()->json(['message' => 'Skill deleted successfully.']);
    }
}
