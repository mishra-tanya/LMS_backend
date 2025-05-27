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
                'semester' => 6,
                'image' => 'https://amplifilearn.com/Assests/images/ba_6th.jpg',
            ],
            [
                'course_name' => 'B.A',
                'semester' => 1,
                'image' => 'https://amplifilearn.com/Assests/images/ba_1st.jpg',
            ],
            [
                'course_name' => 'B.A',
                'semester' => 2,
                'image' => 'https://amplifilearn.com/Assests/images/ba_2nd.jpg',
            ],
            [
                'course_name' => 'B.A',
                'semester' => 3,
                'image' => 'https://amplifilearn.com/Assests/images/ba_3rd.jpg',
            ],
            [
                'course_name' => 'B.A',
                'semester' => 4,
                'image' => 'https://amplifilearn.com/Assests/images/ba_4th.jpg',
            ],
            [
                'course_name' => 'B.Sc',
                'semester' => 1,
                'image' => 'https://amplifilearn.com/Assests/images/bsc_1st.jpg',
            ],
            [
                'course_name' => 'B.Sc',
                'semester' => 2,
                'image' => 'https://amplifilearn.com/Assests/images/bsc_2nd.jpg',
            ],
            [
                'course_name' => 'B.Sc',
                'semester' => 3,
                'image' => 'https://amplifilearn.com/Assests/images/bsc_3rd.jpg',
            ],
            [
                'course_name' => 'B.Sc',
                'semester' => 4,
                'image' => 'https://amplifilearn.com/Assests/images/bsc_4th.jpg',
            ],
            [
                'course_name' => 'B.Sc',
                'semester' => 5,
                'image' => 'https://amplifilearn.com/Assests/images/bsc_5th.jpg',
            ],
            [
                'course_name' => 'B.Sc',
                'semester' => 6,
                'image' => 'https://amplifilearn.com/Assests/images/bsc_6th.jpg',
            ],
            
            [
                'course_name' => 'B.Com',
                'semester' => 1,
                'image' => 'https://amplifilearn.com/Assests/images/bcom_1st.jpg',
            ]
            ,
            
            [
                'course_name' => 'B.Com',
                'semester' => 2,
                'image' => 'https://amplifilearn.com/Assests/images/bcom_2nd.jpg',
            ]
            ,
            
            [
                'course_name' => 'B.Com',
                'semester' => 3,
                'image' => 'https://amplifilearn.com/Assests/images/bcom_3rd.jpg',
            ],
    
            
            [
                'course_name' => 'B.Com',
                'semester' => 4,
                'image' => 'https://amplifilearn.com/Assests/images/bcom_4th.jpg',
            ]
            ,
            
            [
                'course_name' => 'B.Com',
                'semester' => 5,
                'image' => 'https://amplifilearn.com/Assests/images/bcom_5th.jpg',
            ],
            
            [
                'course_name' => 'B.CA',
                'semester' => 6,
                'image' => 'https://amplifilearn.com/Assests/images/bsc_6th.jpg',
            ]
        ];

        foreach ($courses as $course) {
            Courses::create($course);
        }
        
        $this->command->info('Sample course data seeded successfully!');
    }
}
