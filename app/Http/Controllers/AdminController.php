<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Category;
use App\Models\LearningRequest;
use App\Models\Review;
use App\Models\Session;
use App\Models\Skill;
use App\Models\User;
use App\Models\UserSkill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminController extends Controller
{
    /**
     * Platform-wide stats.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'users'             => User::count(),
            'mentors'           => User::whereHas('userSkills', fn($q) => $q->where('is_teaching', true))->count(),
            'skills'            => Skill::count(),
            'categories'        => Category::count(),
            'sessions_total'    => Session::count(),
            'sessions_completed'=> Session::where('status', 'completed')->count(),
            'reviews'           => Review::count(),
            'avg_rating'        => round(Review::avg('rating') ?? 0, 1),
            'learning_requests' => LearningRequest::count(),
            'pending_requests'  => LearningRequest::where('status', 'pending')->count(),
        ]);
    }

    /**
     * List all users with pagination and search.
     */
    public function users(Request $request): AnonymousResourceCollection
    {
        $users = User::withCount([
            'userSkills as teaching_count' => fn($q) => $q->where('is_teaching', true),
            'taughtSessions as sessions_count',
        ])
        ->when($request->filled('search'), fn($q) =>
            $q->where('name', 'ilike', '%' . $request->search . '%')
              ->orWhere('email', 'ilike', '%' . $request->search . '%')
        )
        ->when($request->filled('role'), fn($q) => $q->where('role', $request->role))
        ->latest()
        ->paginate(20);

        return UserResource::collection($users);
    }

    /**
     * Update user role.
     */
    public function updateUser(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role' => 'required|in:user,admin',
        ]);

        $user->update($validated);

        return response()->json(new UserResource($user));
    }

    /**
     * Delete a user (admin only).
     */
    public function deleteUser(User $user): JsonResponse
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }

    /**
     * List all skills with category info.
     */
    public function skills(Request $request): JsonResponse
    {
        $skills = Skill::with('category')
            ->withCount(['userSkills as mentors_count' => fn($q) => $q->where('is_teaching', true)])
            ->when($request->filled('search'), fn($q) =>
                $q->where('name', 'ilike', '%' . $request->search . '%')
            )
            ->orderBy('name')
            ->paginate(20);

        return response()->json($skills);
    }

    /**
     * Create a skill.
     */
    public function createSkill(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:skills',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $skill = Skill::create($validated);
        $skill->load('category');

        return response()->json($skill, 201);
    }

    /**
     * Update a skill.
     */
    public function updateSkill(Request $request, Skill $skill): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255|unique:skills,name,' . $skill->id,
            'description' => 'nullable|string',
            'category_id' => 'sometimes|exists:categories,id',
        ]);

        $skill->update($validated);
        $skill->load('category');

        return response()->json($skill);
    }

    /**
     * Delete a skill.
     */
    public function deleteSkill(Skill $skill): JsonResponse
    {
        $skill->delete();
        return response()->json(['message' => 'Skill deleted.']);
    }

    /**
     * List categories.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::withCount('skills')->orderBy('name')->get();
        return response()->json(['data' => $categories]);
    }

    /**
     * Create a category.
     */
    public function createCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'slug' => 'required|string|max:255|unique:categories|regex:/^[a-z0-9-]+$/',
        ]);

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    /**
     * Delete a category.
     */
    public function deleteCategory(Category $category): JsonResponse
    {
        $category->delete();
        return response()->json(['message' => 'Category deleted.']);
    }

    /**
     * Recent activity (last 10 sessions + requests).
     */
    public function activity(): JsonResponse
    {
        $sessions = Session::with(['mentor', 'student', 'skill'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($s) => [
                'type'    => 'session',
                'text'    => "{$s->student?->name} booked {$s->skill?->name} with {$s->mentor?->name}",
                'status'  => $s->status,
                'date'    => $s->created_at,
            ]);

        $requests = LearningRequest::with(['student', 'mentor', 'skill'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($r) => [
                'type'    => 'request',
                'text'    => "{$r->student?->name} requested {$r->skill?->name} from {$r->mentor?->name}",
                'status'  => $r->status,
                'date'    => $r->created_at,
            ]);

        $activity = $sessions->concat($requests)->sortByDesc('date')->take(10)->values();

        return response()->json(['data' => $activity]);
    }
}
