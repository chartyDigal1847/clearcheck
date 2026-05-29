<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS clearance_summary');

        DB::statement("
            CREATE OR REPLACE VIEW clearance_summary AS
            SELECT
                s.id                                                    AS student_id,
                s.student_name                                          AS student_name,
                s.student_email                                         AS student_email,
                s.reg_no,
                s.grade_level,
                s.section,
                s.clearance_status,
                cr.id                                                   AS clearance_record_id,
                cr.progress_percentage,
                cr.modules_cleared,
                cr.modules_total,
                cr.last_validated_at,
                cr.cleared_at,
                COUNT(mv.id)                                            AS total_validations,
                SUM(CASE WHEN mv.status = 'cleared' THEN 1 ELSE 0 END) AS validations_cleared,
                SUM(CASE WHEN mv.status = 'failed'  THEN 1 ELSE 0 END) AS validations_failed,
                SUM(CASE WHEN mv.status = 'pending' THEN 1 ELSE 0 END) AS validations_pending
            FROM students s
            LEFT JOIN clearance_records cr ON cr.student_id = s.id AND cr.deleted_at IS NULL
            LEFT JOIN module_validations mv ON mv.clearance_record_id = cr.id AND mv.deleted_at IS NULL
            WHERE s.deleted_at IS NULL
            GROUP BY
                s.id, s.student_name, s.student_email, s.reg_no, s.grade_level, s.section,
                s.clearance_status, cr.id, cr.progress_percentage,
                cr.modules_cleared, cr.modules_total,
                cr.last_validated_at, cr.cleared_at
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS clearance_summary');
    }
};
