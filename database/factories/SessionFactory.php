<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Skill;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Session>
 */
class SessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = fake()->dateTimeBetween('-1 month', '+1 month');
        $endTime = (clone $startTime)->modify('+1 hour');

        return [
            'mentor_id' => User::factory(),
            'student_id' => User::factory(),
            'skill_id' => Skill::factory(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => fake()->randomElement(['scheduled', 'completed', 'cancelled']),
            'meeting_link' => fake()->url(),
        ];
    }
}
