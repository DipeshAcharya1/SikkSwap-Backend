<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\UserSkillController;
use App\Http\Controllers\LearningRequestController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\MessageController;

/*
|--------------------------------------------------------------------------
| Public Routes (no auth required)
|--------------------------------------------------------------------------
*/

// Auth
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Platform stats (public - for landing page)
Route::get('/stats', function () {
    return response()->json([
        'users'    => \App\Models\User::count(),
        'mentors'  => \App\Models\User::whereHas('userSkills', fn($q) => $q->where('is_teaching', true))->count(),
        'skills'   => \App\Models\Skill::count(),
        'sessions' => \App\Models\Session::where('status', 'completed')->count(),
    ]);
});

// Browse skills & categories (public)
Route::get('/categories',       [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/skills',           [SkillController::class, 'index']);
Route::get('/skills/{skill}',   [SkillController::class, 'show']);

// Public reviews for a user
Route::get('/reviews',          [ReviewController::class, 'index']);

// Browse mentors (public)
Route::get('/mentors',          [\App\Http\Controllers\MentorController::class, 'index']);
Route::get('/mentors/{user}',   [\App\Http\Controllers\MentorController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user',    [AuthController::class, 'user']);
    Route::patch('/user',  [AuthController::class, 'updateProfile']);

    // Skills management (admin only via policy)
    Route::post('/skills',           [SkillController::class, 'store']);
    Route::put('/skills/{skill}',    [SkillController::class, 'update']);
    Route::delete('/skills/{skill}', [SkillController::class, 'destroy']);

    // User's own skills
    Route::get('/my-skills',                [UserSkillController::class, 'index']);
    Route::post('/my-skills',               [UserSkillController::class, 'store']);
    Route::put('/my-skills/{userSkill}',    [UserSkillController::class, 'update']);
    Route::delete('/my-skills/{userSkill}', [UserSkillController::class, 'destroy']);

    // Learning Requests
    Route::get('/learning-requests',                      [LearningRequestController::class, 'index']);
    Route::post('/learning-requests',                     [LearningRequestController::class, 'store']);
    Route::get('/learning-requests/{learningRequest}',    [LearningRequestController::class, 'show']);
    Route::patch('/learning-requests/{learningRequest}',  [LearningRequestController::class, 'update']);
    Route::delete('/learning-requests/{learningRequest}', [LearningRequestController::class, 'destroy']);

    // Sessions
    Route::get('/sessions',              [SessionController::class, 'index']);
    Route::post('/sessions',             [SessionController::class, 'store']);
    Route::get('/sessions/{session}',    [SessionController::class, 'show']);
    Route::patch('/sessions/{session}',  [SessionController::class, 'update']);
    Route::delete('/sessions/{session}', [SessionController::class, 'destroy']);

    // Reviews
    Route::post('/reviews',           [ReviewController::class, 'store']);
    Route::get('/reviews/{review}',   [ReviewController::class, 'show']);
    Route::delete('/reviews/{review}',[ReviewController::class, 'destroy']);

    // Messages
    Route::get('/messages/conversations', [MessageController::class, 'conversations']);
    Route::get('/messages/unread',        [MessageController::class, 'unreadCount']);
    Route::get('/messages',               [MessageController::class, 'index']);
    Route::post('/messages',              [MessageController::class, 'store']);

    // ─── Admin Routes ──────────────────────────────────────────
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/stats',                    [\App\Http\Controllers\AdminController::class, 'stats']);
        Route::get('/activity',                 [\App\Http\Controllers\AdminController::class, 'activity']);

        // Users
        Route::get('/users',                    [\App\Http\Controllers\AdminController::class, 'users']);
        Route::patch('/users/{user}',           [\App\Http\Controllers\AdminController::class, 'updateUser']);
        Route::delete('/users/{user}',          [\App\Http\Controllers\AdminController::class, 'deleteUser']);

        // Skills
        Route::get('/skills',                   [\App\Http\Controllers\AdminController::class, 'skills']);
        Route::post('/skills',                  [\App\Http\Controllers\AdminController::class, 'createSkill']);
        Route::patch('/skills/{skill}',         [\App\Http\Controllers\AdminController::class, 'updateSkill']);
        Route::delete('/skills/{skill}',        [\App\Http\Controllers\AdminController::class, 'deleteSkill']);

        // Categories
        Route::get('/categories',               [\App\Http\Controllers\AdminController::class, 'categories']);
        Route::post('/categories',              [\App\Http\Controllers\AdminController::class, 'createCategory']);
        Route::delete('/categories/{category}', [\App\Http\Controllers\AdminController::class, 'deleteCategory']);
    });
});
