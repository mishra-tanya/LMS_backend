<?php

namespace Database\Seeders;

use App\Models\Chapters;
use Illuminate\Database\Seeder;

class ChapterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        Chapters::truncate();
        $chapters = [
            // Subject ID 1 - History of India
            ['chapter_name' => 'Introduction to Ancient India', 'subject_id' => 1, 'resource_link' => null],
            ['chapter_name' => 'Mauryan Empire', 'subject_id' => 1, 'resource_link' => null],
            ['chapter_name' => 'Gupta Dynasty and Achievements', 'subject_id' => 1, 'resource_link' => null],

            // Subject ID 2 - Political Science
            ['chapter_name' => 'What is Politics?', 'subject_id' => 2, 'resource_link' => null],
            ['chapter_name' => 'Key Political Ideologies', 'subject_id' => 2, 'resource_link' => null],
            ['chapter_name' => 'Constitutional Framework', 'subject_id' => 2, 'resource_link' => null],

            // Subject ID 3 - Sociology
            ['chapter_name' => 'Nature and Scope of Sociology', 'subject_id' => 3, 'resource_link' => null],
            ['chapter_name' => 'Social Groups and Institutions', 'subject_id' => 3, 'resource_link' => null],

            // Subject ID 4 - English Literature
            ['chapter_name' => 'Introduction to Poetry', 'subject_id' => 4, 'resource_link' => null],
            ['chapter_name' => 'Prose and Fiction', 'subject_id' => 4, 'resource_link' => null],

            // Subject ID 7 - Geography (BA)
            ['chapter_name' => 'Introduction to Physical Geography', 'subject_id' => 7, 'resource_link' => null],
            ['chapter_name' => 'Climatology and Weather Patterns', 'subject_id' => 7, 'resource_link' => null],

            // Subject ID 8 - Math (Calculus)
            ['chapter_name' => 'Limits and Continuity', 'subject_id' => 8, 'resource_link' => null],
            ['chapter_name' => 'Differentiation Techniques', 'subject_id' => 8, 'resource_link' => null],

            // Subject ID 10 - Chemistry
            ['chapter_name' => 'Structure of Organic Molecules', 'subject_id' => 10, 'resource_link' => null],
            ['chapter_name' => 'Isomerism in Organic Chemistry', 'subject_id' => 10, 'resource_link' => null],

            // Subject ID 13 - Financial Accounting (B.Com)
            ['chapter_name' => 'Basics of Accounting', 'subject_id' => 13, 'resource_link' => null],
            ['chapter_name' => 'Journal and Ledger Entries', 'subject_id' => 13, 'resource_link' => null],

            // Subject ID 17 - Programming in C (BCA)
            ['chapter_name' => 'Introduction to C Programming', 'subject_id' => 17, 'resource_link' => null],
            ['chapter_name' => 'Control Structures and Loops', 'subject_id' => 17, 'resource_link' => null],
            ['chapter_name' => 'Functions in C', 'subject_id' => 17, 'resource_link' => null],

            // Subject ID 23 - Principles of Management (BBA)
            ['chapter_name' => 'Introduction to Management', 'subject_id' => 23, 'resource_link' => null],
            ['chapter_name' => 'Functions of Management', 'subject_id' => 23, 'resource_link' => null],
        ];

        foreach ($chapters as $chapter) {
           Chapters::create($chapter);
        }
       }
}
