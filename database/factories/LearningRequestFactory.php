<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Skill;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LearningRequest>
 */
class LearningRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory(),
            'mentor_id' => User::factory(),
            'skill_id' => Skill::factory(),
            'status' => fake()->randomElement(['pending', 'accepted', 'rejected']),
            'message' => fake()->paragraph(),
        ];
    }
}
