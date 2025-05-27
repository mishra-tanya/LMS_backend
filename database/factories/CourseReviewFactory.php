<?php

namespace Database\Factories;

use App\Models\CourseReview;
use App\Models\Courses;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseReviewFactory extends Factory
{
    protected $model = CourseReview::class;

    public function definition()
    {
        return [
            'course_id' => Courses::inRandomOrder()->first()->course_id ?? 1,
            'user_id' => User::inRandomOrder()->first()->id ?? 1,
            'rating' => $this->faker->numberBetween(1, 5),
            'review_description' => $this->faker->paragraph(2),
            'is_approved' => $this->faker->boolean(70), // 70% chance of being approved
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    // For creating always approved reviews
    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_approved' => true,
            ];
        });
    }
}