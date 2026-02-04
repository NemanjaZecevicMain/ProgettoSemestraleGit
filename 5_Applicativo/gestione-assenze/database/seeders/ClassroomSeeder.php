<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classroom;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            ['name' => 'INF', 'year' => 1, 'section' => 'A'],
            ['name' => 'INF', 'year' => 1, 'section' => 'B'],
            ['name' => 'INF', 'year' => 2, 'section' => 'A'],
            ['name' => 'INF', 'year' => 2, 'section' => 'B'],
            ['name' => 'INF', 'year' => 3, 'section' => 'A'],
            ['name' => 'INF', 'year' => 3, 'section' => 'B'],
            ['name' => 'INF', 'year' => 4, 'section' => 'A'],
        ];

        foreach ($classes as $class) {
            Classroom::updateOrCreate(
                [
                    'year' => $class['year'],
                    'section' => $class['section'],
                    'name' => $class['name'],
                ],
                $class
            );
        }
    }
}
