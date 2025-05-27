<?php

namespace Database\Seeders;

use App\Models\CourseReview;
use App\Models\SubjectReview;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewsSeeder extends Seeder
{
    public function run()
    {
        // Create 50 users if not enough exist
        if (User::count() < 50) {
            User::factory()->count(50 - User::count())->create();
        }

        // Create 100 course reviews
        CourseReview::factory()->count(100)->create();

        // Create 150 subject reviews
        SubjectReview::factory()->count(150)->create();

        // Create some guaranteed approved reviews
        CourseReview::factory()->approved()->count(20)->create();
        SubjectReview::factory()->approved()->count(30)->create();
    }
}