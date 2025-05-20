<?php

namespace Database\Seeders;

use App\Models\User;
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
        
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        
        // Call seeders in the correct order to maintain referential integrity
        $this->call([
            CourseSeeder::class,    // First seed courses
            SubjectSeeder::class,   // Then seed subjects (which reference courses)
            ChapterSeeder::class,   // Finally seed chapters (which reference subjects)
        ]);
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
