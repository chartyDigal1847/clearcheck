<?php

namespace App\Services;

use App\Models\ClearanceChecker;
use App\Models\Student;

class StudentProvisioner
{
    /**
     * Ensure module records exist for the authenticated portal user.
     *
     * @param  array{id: string|int, name?: string, email?: string}  $portalUser
     */
    public function provision(string $role, array $portalUser): void
    {
        $portalId = (string) $portalUser['id'];
        $name = $portalUser['name'] ?? 'User';
        $email = strtolower($portalUser['email'] ?? '');

        if ($role === 'student') {
            Student::updateOrCreate(
                ['deoris_user_id' => $portalId],
                [
                    'student_name' => $name,
                    'student_email' => $email,
                    'reg_no' => $this->regNoFor($portalId),
                    'grade_level' => '12',
                    'clearance_status' => 'pending',
                    'completed_steps' => 0,
                    'total_steps' => 6,
                ]
            );

            return;
        }

        if ($role === 'clearance_checker') {
            ClearanceChecker::updateOrCreate(
                ['deoris_user_id' => $portalId],
                [
                    'checker_name' => $name,
                    'checker_email' => $email,
                    'department' => 'General',
                ]
            );
        }
    }

    private function regNoFor(string $portalId): string
    {
        return 'STU-'.str_pad(substr(preg_replace('/\D/', '', $portalId) ?: '0', -6), 6, '0', STR_PAD_LEFT);
    }
}
