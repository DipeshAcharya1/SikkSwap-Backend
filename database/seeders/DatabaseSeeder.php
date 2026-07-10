<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\LearningRequest;
use App\Models\Message;
use App\Models\Review;
use App\Models\Session;
use App\Models\Skill;
use App\Models\User;
use App\Models\UserSkill;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Users
        $users = User::factory(10)->create();

        // Admin User
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@skillswap.local',
            'role' => 'admin',
        ]);

        // Regular Test User
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@skillswap.local',
        ]);

        $users->push($testUser);

        // 2. Create Categories & Skills
        $categories = Category::factory(5)->create();
        
        $skills = collect();
        foreach ($categories as $category) {
            $skills = $skills->merge(Skill::factory(4)->create([
                'category_id' => $category->id,
            ]));
        }

        // 3. Assign Skills to Users
        foreach ($users as $user) {
            $randomSkills = $skills->random(rand(2, 5));
            foreach ($randomSkills as $skill) {
                UserSkill::factory()->create([
                    'user_id' => $user->id,
                    'skill_id' => $skill->id,
                ]);
            }
        }

        // 4. Create Learning Requests
        for ($i = 0; $i < 15; $i++) {
            $student = $users->random();
            $mentor = $users->where('id', '!=', $student->id)->random();
            $skill = $skills->random();
            
            LearningRequest::factory()->create([
                'student_id' => $student->id,
                'mentor_id' => $mentor->id,
                'skill_id' => $skill->id,
            ]);
        }

        // 5. Create Sessions
        $sessions = collect();
        for ($i = 0; $i < 20; $i++) {
            $mentor = $users->random();
            $student = $users->where('id', '!=', $mentor->id)->random();
            $skill = $skills->random();
            
            $sessions->push(Session::factory()->create([
                'mentor_id' => $mentor->id,
                'student_id' => $student->id,
                'skill_id' => $skill->id,
            ]));
        }

        // 6. Create Reviews for Completed Sessions
        foreach ($sessions->where('status', 'completed') as $session) {
            Review::factory()->create([
                'reviewer_id' => $session->student_id,
                'reviewee_id' => $session->mentor_id,
                'session_id' => $session->id,
            ]);
        }

        // 7. Create Messages between random users
        for ($i = 0; $i < 30; $i++) {
            $sender = $users->random();
            $receiver = $users->where('id', '!=', $sender->id)->random();
            
            Message::factory()->create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
            ]);
        }
    }
}
