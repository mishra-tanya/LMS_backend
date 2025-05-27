<?php

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\Courses;
use App\Models\Subjects;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'course_id' => $this->faker->boolean ? Courses::inRandomOrder()->first()->course_id : null,
            'subject_id' => $this->faker->boolean ? Subjects::inRandomOrder()->first()->subject_id : null,
            'purchased_at' => $this->faker->dateTimeThisYear,
        ];
    }
}