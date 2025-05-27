<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'), // Default password for all test users
            'created_at' => $this->faker->dateTimeBetween('-2 years', '-6 months'),
            'updated_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    // Create a student user
    public function student()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'student',
            ];
        });
    }

    // Create a teacher user
    public function teacher()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'teacher',
            ];
        });
    }

    // Create a verified user
    public function verified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => now(),
            ];
        });
    }
}
