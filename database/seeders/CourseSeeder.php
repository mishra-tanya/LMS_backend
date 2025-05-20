<?php

namespace Database\Seeders;

use App\Models\Courses;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        Courses::truncate();
        
        // Add sample courses
        $courses = [
            [
                'course_name' => 'B.A',
                'total_semester' => 6,
            ],
            [
                'course_name' => 'B.Sc',
                'total_semester' => 6,
            ],
            [
                'course_name' => 'B.Com',
                'total_semester' => 6,
            ],
            [
                'course_name' => 'B.CA',
                'total_semester' => 6,
            ],
            [
                'course_name' => 'B.BA',
                'total_semester' => 6,
            ],
        ];

        foreach ($courses as $course) {
            Courses::create($course);
        }
        
        $this->command->info('Sample course data seeded successfully!');
    }
}
