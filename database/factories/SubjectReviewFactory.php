<?php

namespace Database\Factories;

use App\Models\SubjectReview;
use App\Models\Subjects;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectReviewFactory extends Factory
{
    protected $model = SubjectReview::class;

    public function definition()
    {
        return [
            'subject_id' => Subjects::inRandomOrder()->first()->subject_id,
            'user_id' => User::inRandomOrder()->first()->id,
            'rating' => $this->faker->numberBetween(1, 5),
            'review_description' => $this->faker->sentence,
            'is_approved' => $this->faker->boolean,
        ];
    }
}