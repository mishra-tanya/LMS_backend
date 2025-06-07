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
            ['subject_name' => 'History of India', 'course_id' => 1, 'image' => null, 'price' => 300,'discount'=>20],
            ['subject_name' => 'Political Science - Theory of Politics', 'course_id' => 1, 'image' => null, 'price' => 200,'discount'=>20],
            ['subject_name' => 'Sociology - Introduction', 'course_id' => 1, 'image' => null,'price' => 400,'discount'=>20],
            ['subject_name' => 'English Literature', 'course_id' => 1, 'image' => null,'price' => 500,'discount'=>20],

            ['subject_name' => 'Mathematics - Calculus', 'course_id' => 2, 'image' => null,'price' =>600],
            ['subject_name' => 'Physics - Mechanics', 'course_id' => 2, 'image' => null,'price' => 300,'discount'=>20],
            ['subject_name' => 'Chemistry - Organic Chemistry', 'course_id' => 2, 'image' => null,'price' => 300],
            ['subject_name' => 'Environmental Science', 'course_id' => 2, 'image' => null,'price' => 300],

            ['subject_name' => 'Financial Accounting', 'course_id' => 3, 'image' => null,'price' => 300,'discount'=>20],
            ['subject_name' => 'Business Law', 'course_id' => 3, 'image' => null,'price' => 300],
            ['subject_name' => 'Corporate Accounting', 'course_id' => 3, 'image' => null,'price' => 300],
            ['subject_name' => 'Cost Accounting', 'course_id' => 3, 'image' => null,'price' => 300],

            ['subject_name' => 'Computer Programming', 'course_id' => 4, 'image' => null,'price' => 300],
            ['subject_name' => 'Data Structures', 'course_id' => 4, 'image' => null,'price' => 300],
            ['subject_name' => 'Database Management Systems', 'course_id' => 4, 'image' => null,'price' => 300],
            ['subject_name' => 'Operating Systems', 'course_id' => 4, 'image' => null,'price' => 300],
        ];

        foreach ($subjects as $subject) {
            Subjects::create($subject);
        }
        
        $this->command->info('Sample subject data seeded successfully!');
    }
}
