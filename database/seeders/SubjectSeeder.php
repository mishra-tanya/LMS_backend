<?php

namespace Database\Seeders;

use App\Models\Subjects;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        Subjects::truncate();
        
     
        // Insert all subjects
        $subjects = [
            // B.A Subjects
            ['subject_name' => 'History of India', 'course_id' => 1, 'image' => null],
            ['subject_name' => 'Political Science - Theory of Politics', 'course_id' => 1, 'image' => null],
            ['subject_name' => 'Sociology - Introduction', 'course_id' => 1, 'image' => null],
            ['subject_name' => 'English Literature', 'course_id' => 1, 'image' => null],

            ['subject_name' => 'Mathematics - Calculus', 'course_id' => 2, 'image' => null],
            ['subject_name' => 'Physics - Mechanics', 'course_id' => 2, 'image' => null],
            ['subject_name' => 'Chemistry - Organic Chemistry', 'course_id' => 2, 'image' => null],
            ['subject_name' => 'Environmental Science', 'course_id' => 2, 'image' => null],

            ['subject_name' => 'Financial Accounting', 'course_id' => 3, 'image' => null],
            ['subject_name' => 'Business Law', 'course_id' => 3, 'image' => null],
            ['subject_name' => 'Corporate Accounting', 'course_id' => 3, 'image' => null],
            ['subject_name' => 'Cost Accounting', 'course_id' => 3, 'image' => null],

            ['subject_name' => 'Computer Programming', 'course_id' => 4, 'image' => null],
            ['subject_name' => 'Data Structures', 'course_id' => 4, 'image' => null],
            ['subject_name' => 'Database Management Systems', 'course_id' => 4, 'image' => null],
            ['subject_name' => 'Operating Systems', 'course_id' => 4, 'image' => null],
        ];

        foreach ($subjects as $subject) {
            Subjects::create($subject);
        }
        
        $this->command->info('Sample subject data seeded successfully!');
    }
}
