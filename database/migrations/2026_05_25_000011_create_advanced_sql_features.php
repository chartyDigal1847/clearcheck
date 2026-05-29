<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── VIEW: clearance_summary ───────────────────────────────────────
        // Aggregates per-student clearance progress for dashboards/reports
        DB::statement("
            CREATE OR REPLACE VIEW clearance_summary AS
            SELECT
                s.id                                                    AS student_id,
                u.name                                                  AS student_name,
                u.email                                                 AS student_email,
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
            JOIN users u ON u.id = s.user_id
            LEFT JOIN clearance_records cr ON cr.student_id = s.id AND cr.deleted_at IS NULL
            LEFT JOIN module_validations mv ON mv.clearance_record_id = cr.id AND mv.deleted_at IS NULL
            WHERE s.deleted_at IS NULL
            GROUP BY
                s.id, u.name, u.email, s.reg_no, s.grade_level, s.section,
                s.clearance_status, cr.id, cr.progress_percentage,
                cr.modules_cleared, cr.modules_total,
                cr.last_validated_at, cr.cleared_at
        ");

        // ── VIEW: module_validation_summary ──────────────────────────────
        // Per-module aggregate stats for analytics dashboard
        DB::statement("
            CREATE OR REPLACE VIEW module_validation_summary AS
            SELECT
                mv.module_key,
                mv.module_name,
                COUNT(mv.id)                                            AS total_validations,
                SUM(CASE WHEN mv.status = 'cleared'  THEN 1 ELSE 0 END) AS cleared_count,
                SUM(CASE WHEN mv.status = 'failed'   THEN 1 ELSE 0 END) AS failed_count,
                SUM(CASE WHEN mv.status = 'pending'  THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN mv.status = 'error'    THEN 1 ELSE 0 END) AS error_count,
                SUM(CASE WHEN mv.status = 'timeout'  THEN 1 ELSE 0 END) AS timeout_count,
                ROUND(
                    SUM(CASE WHEN mv.status = 'cleared' THEN 1 ELSE 0 END) * 100.0
                    / NULLIF(COUNT(mv.id), 0), 2
                )                                                        AS clearance_rate,
                AVG(mv.response_time_ms)                                 AS avg_response_time_ms
            FROM module_validations mv
            WHERE mv.deleted_at IS NULL
            GROUP BY mv.module_key, mv.module_name
        ");

        // ── VIEW: pending_clearance_stats ─────────────────────────────────
        // Quick stats for admin/checker dashboards
        DB::statement("
            CREATE OR REPLACE VIEW pending_clearance_stats AS
            SELECT
                s.grade_level,
                COUNT(DISTINCT s.id)                                                AS total_students,
                SUM(CASE WHEN s.clearance_status = 'cleared'           THEN 1 ELSE 0 END) AS cleared,
                SUM(CASE WHEN s.clearance_status = 'pending'           THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN s.clearance_status = 'partially_cleared' THEN 1 ELSE 0 END) AS partially_cleared,
                SUM(CASE WHEN s.clearance_status = 'validating'        THEN 1 ELSE 0 END) AS validating,
                SUM(CASE WHEN s.clearance_status = 'disputed'          THEN 1 ELSE 0 END) AS disputed,
                ROUND(
                    SUM(CASE WHEN s.clearance_status = 'cleared' THEN 1 ELSE 0 END) * 100.0
                    / NULLIF(COUNT(DISTINCT s.id), 0), 2
                )                                                                   AS clearance_rate
            FROM students s
            WHERE s.deleted_at IS NULL
            GROUP BY s.grade_level
        ");

        // ── STORED PROCEDURE: recalculate_clearance ───────────────────────
        // Recomputes a student's clearance_record and students.clearance_status
        // based on current module_validations. Called after each module response.
        DB::unprepared("
            DROP PROCEDURE IF EXISTS recalculate_clearance;
        ");
        DB::unprepared("
            CREATE PROCEDURE recalculate_clearance(IN p_student_id BIGINT UNSIGNED)
            BEGIN
                DECLARE v_record_id     BIGINT UNSIGNED DEFAULT NULL;
                DECLARE v_total         INT DEFAULT 4;
                DECLARE v_cleared       INT DEFAULT 0;
                DECLARE v_failed        INT DEFAULT 0;
                DECLARE v_pending       INT DEFAULT 0;
                DECLARE v_new_status    VARCHAR(30) DEFAULT 'pending';
                DECLARE v_progress      TINYINT UNSIGNED DEFAULT 0;

                -- Get the active clearance record
                SELECT id INTO v_record_id
                FROM clearance_records
                WHERE student_id = p_student_id AND deleted_at IS NULL
                ORDER BY created_at DESC
                LIMIT 1;

                IF v_record_id IS NOT NULL THEN
                    -- Count module statuses
                    SELECT
                        SUM(CASE WHEN status = 'cleared' THEN 1 ELSE 0 END),
                        SUM(CASE WHEN status = 'failed'  THEN 1 ELSE 0 END),
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END)
                    INTO v_cleared, v_failed, v_pending
                    FROM module_validations
                    WHERE clearance_record_id = v_record_id AND deleted_at IS NULL;

                    SET v_progress = ROUND(v_cleared * 100 / v_total);

                    -- Determine new status
                    IF v_cleared = v_total THEN
                        SET v_new_status = 'cleared';
                    ELSEIF v_cleared > 0 THEN
                        SET v_new_status = 'partially_cleared';
                    ELSEIF v_failed > 0 THEN
                        SET v_new_status = 'pending';
                    ELSE
                        SET v_new_status = 'validating';
                    END IF;

                    -- Update clearance_record
                    UPDATE clearance_records
                    SET
                        status              = v_new_status,
                        progress_percentage = v_progress,
                        modules_cleared     = v_cleared,
                        last_validated_at   = NOW(),
                        cleared_at          = IF(v_new_status = 'cleared', NOW(), cleared_at)
                    WHERE id = v_record_id;

                    -- Mirror status onto students table
                    UPDATE students
                    SET clearance_status = v_new_status
                    WHERE id = p_student_id;
                END IF;
            END
        ");

        // ── TRIGGER: after_module_validation_update ───────────────────────
        // Auto-recalculates clearance whenever a module_validation row changes
        DB::unprepared("
            DROP TRIGGER IF EXISTS after_module_validation_update;
        ");
        DB::unprepared("
            CREATE TRIGGER after_module_validation_update
            AFTER UPDATE ON module_validations
            FOR EACH ROW
            BEGIN
                IF OLD.status <> NEW.status THEN
                    CALL recalculate_clearance(NEW.student_id);
                END IF;
            END
        ");

        // ── TRIGGER: after_module_validation_insert ───────────────────────
        DB::unprepared("
            DROP TRIGGER IF EXISTS after_module_validation_insert;
        ");
        DB::unprepared("
            CREATE TRIGGER after_module_validation_insert
            AFTER INSERT ON module_validations
            FOR EACH ROW
            BEGIN
                CALL recalculate_clearance(NEW.student_id);
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS after_module_validation_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS after_module_validation_update');
        DB::unprepared('DROP PROCEDURE IF EXISTS recalculate_clearance');
        DB::statement('DROP VIEW IF EXISTS pending_clearance_stats');
        DB::statement('DROP VIEW IF EXISTS module_validation_summary');
        DB::statement('DROP VIEW IF EXISTS clearance_summary');
    }
};
