<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CourseReview;
use App\Models\SubjectReview;
use App\Models\Purchase;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key checks to avoid constraint errors during truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Seed data for users
        User::factory()->count(10)->create();

        // Seed data for courses
        $this->call(CourseSeeder::class);

        // Seed data for subjects
        $this->call(SubjectSeeder::class);

        // Seed data for chapters
        $this->call(ChapterSeeder::class);

        // Seed data for course_reviews
        CourseReview::factory()->count(10)->create();

        // Seed data for subject_reviews
        SubjectReview::factory()->count(15)->create();

        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
