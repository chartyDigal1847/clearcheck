<?php

namespace Database\Seeders;

use App\Models\ClearanceChecker;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CheckerSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $checkers = [
            [
                'user_id' => User::where('email', 'sarah.williams@deor.edu')->first()->id,
                'department' => 'Library',
                'documents_reviewed' => 42,
                'documents_approved' => 38,
                'documents_rejected' => 4,
            ],
            [
                'user_id' => User::where('email', 'robert.brown@deor.edu')->first()->id,
                'department' => 'Finance',
                'documents_reviewed' => 35,
                'documents_approved' => 32,
                'documents_rejected' => 3,
            ],
            [
                'user_id' => User::where('email', 'emily.davis@deor.edu')->first()->id,
                'department' => 'Exams & Records',
                'documents_reviewed' => 28,
                'documents_approved' => 25,
                'documents_rejected' => 3,
            ],
        ];

        foreach ($checkers as $checker) {
            ClearanceChecker::create($checker);
        }
    }
}
