<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $students = [
            [
                'user_id' => User::where('email', 'john.doe@deor.edu')->first()->id,
                'reg_no' => 'HS2024001',
                'grade_level' => '12',
                'section' => 'A',
                'clearance_status' => 'in_progress',
                'completed_steps' => 2,
                'total_steps' => 4,
            ],
            [
                'user_id' => User::where('email', 'jane.smith@deor.edu')->first()->id,
                'reg_no' => 'HS2024002',
                'grade_level' => '11',
                'section' => 'B',
                'clearance_status' => 'pending',
                'completed_steps' => 0,
                'total_steps' => 4,
            ],
            [
                'user_id' => User::where('email', 'mike.johnson@deor.edu')->first()->id,
                'reg_no' => 'HS2024003',
                'grade_level' => '10',
                'section' => 'A',
                'clearance_status' => 'in_progress',
                'completed_steps' => 1,
                'total_steps' => 4,
            ],
        ];

        foreach ($students as $student) {
            Student::create($student);
        }
    }
}
