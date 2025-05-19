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
            // B.A - course_id = 1
            ['subject_name' => 'History of India', 'course_id' => 1, 'semester' => 1, 'resource_link' => null],
            ['subject_name' => 'Political Science - Theory of Politics', 'course_id' => 1, 'semester' => 1, 'resource_link' => null],
            ['subject_name' => 'Sociology - Introduction', 'course_id' => 1, 'semester' => 2, 'resource_link' => null],
            ['subject_name' => 'English Literature', 'course_id' => 1, 'semester' => 3, 'resource_link' => null],
            ['subject_name' => 'Public Administration', 'course_id' => 1, 'semester' => 4, 'resource_link' => null],
            ['subject_name' => 'Philosophy - Ethics', 'course_id' => 1, 'semester' => 5, 'resource_link' => null],
            ['subject_name' => 'Geography - Fundamentals', 'course_id' => 1, 'semester' => 6, 'resource_link' => null],

            // B.Sc - course_id = 2
            ['subject_name' => 'Mathematics - Calculus', 'course_id' => 2, 'semester' => 1, 'resource_link' => null],
            ['subject_name' => 'Physics - Mechanics', 'course_id' => 2, 'semester' => 1, 'resource_link' => null],
            ['subject_name' => 'Chemistry - Organic Chemistry', 'course_id' => 2, 'semester' => 2, 'resource_link' => null],
            ['subject_name' => 'Mathematics - Algebra', 'course_id' => 2, 'semester' => 3, 'resource_link' => null],
            ['subject_name' => 'Physics - Thermodynamics', 'course_id' => 2, 'semester' => 4, 'resource_link' => null],
            ['subject_name' => 'Chemistry - Physical Chemistry', 'course_id' => 2, 'semester' => 5, 'resource_link' => null],
            ['subject_name' => 'Environmental Science', 'course_id' => 2, 'semester' => 6, 'resource_link' => null],

            // B.Com - course_id = 3
            ['subject_name' => 'Financial Accounting', 'course_id' => 3, 'semester' => 1, 'resource_link' => null],
            ['subject_name' => 'Business Law', 'course_id' => 3, 'semester' => 2, 'resource_link' => null],
            ['subject_name' => 'Corporate Accounting', 'course_id' => 3, 'semester' => 3, 'resource_link' => null],
            ['subject_name' => 'Cost Accounting', 'course_id' => 3, 'semester' => 4, 'resource_link' => null],
            ['subject_name' => 'Income Tax Law and Practice', 'course_id' => 3, 'semester' => 5, 'resource_link' => null],
            ['subject_name' => 'Auditing', 'course_id' => 3, 'semester' => 6, 'resource_link' => null],

            // B.CA - course_id = 4
            ['subject_name' => 'Programming in C', 'course_id' => 4, 'semester' => 1, 'resource_link' => null],
            ['subject_name' => 'Data Structures', 'course_id' => 4, 'semester' => 2, 'resource_link' => null],
            ['subject_name' => 'Database Management Systems', 'course_id' => 4, 'semester' => 3, 'resource_link' => null],
            ['subject_name' => 'Operating Systems', 'course_id' => 4, 'semester' => 4, 'resource_link' => null],
            ['subject_name' => 'Web Technologies', 'course_id' => 4, 'semester' => 5, 'resource_link' => null],
            ['subject_name' => 'Software Engineering', 'course_id' => 4, 'semester' => 6, 'resource_link' => null],

            // B.BA - course_id = 5
            ['subject_name' => 'Principles of Management', 'course_id' => 5, 'semester' => 1, 'resource_link' => null],
            ['subject_name' => 'Business Economics', 'course_id' => 5, 'semester' => 2, 'resource_link' => null],
            ['subject_name' => 'Marketing Management', 'course_id' => 5, 'semester' => 3, 'resource_link' => null],
            ['subject_name' => 'Human Resource Management', 'course_id' => 5, 'semester' => 4, 'resource_link' => null],
            ['subject_name' => 'Operations Management', 'course_id' => 5, 'semester' => 5, 'resource_link' => null],
            ['subject_name' => 'Strategic Management', 'course_id' => 5, 'semester' => 6, 'resource_link' => null],
        ];

        foreach ($subjects as $subject) {
            Subjects::create($subject);
        }
        
        
        $this->command->info('Sample subject data seeded successfully!');
    }
}
