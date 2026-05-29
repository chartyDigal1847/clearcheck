<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('students') && ! Schema::hasColumn('students', 'deoris_user_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->unsignedBigInteger('deoris_user_id')->nullable()->unique()->after('id');
                $table->string('student_name', 200)->nullable()->after('deoris_user_id');
                $table->string('student_email', 150)->nullable()->after('student_name');
            });

            if (Schema::hasTable('users') && Schema::hasColumn('students', 'user_id')) {
                $rows = DB::table('students')
                    ->join('users', 'students.user_id', '=', 'users.id')
                    ->select('students.id', 'users.id as uid', 'users.name', 'users.email')
                    ->get();

                foreach ($rows as $row) {
                    DB::table('students')->where('id', $row->id)->update([
                        'deoris_user_id' => $row->uid,
                        'student_name' => $row->name,
                        'student_email' => $row->email,
                    ]);
                }
            }

            if (Schema::hasColumn('students', 'user_id')) {
                Schema::table('students', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn('user_id');
                });
            }
        }

        if (Schema::hasTable('clearance_checkers') && ! Schema::hasColumn('clearance_checkers', 'deoris_user_id')) {
            Schema::table('clearance_checkers', function (Blueprint $table) {
                $table->unsignedBigInteger('deoris_user_id')->nullable()->unique()->after('id');
                $table->string('checker_name', 200)->nullable()->after('deoris_user_id');
                $table->string('checker_email', 150)->nullable()->after('checker_name');
            });

            if (Schema::hasTable('users') && Schema::hasColumn('clearance_checkers', 'user_id')) {
                $rows = DB::table('clearance_checkers')
                    ->join('users', 'clearance_checkers.user_id', '=', 'users.id')
                    ->select('clearance_checkers.id', 'users.id as uid', 'users.name', 'users.email')
                    ->get();

                foreach ($rows as $row) {
                    DB::table('clearance_checkers')->where('id', $row->id)->update([
                        'deoris_user_id' => $row->uid,
                        'checker_name' => $row->name,
                        'checker_email' => $row->email,
                    ]);
                }
            }

            if (Schema::hasColumn('clearance_checkers', 'user_id')) {
                Schema::table('clearance_checkers', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn('user_id');
                });
            }
        }

        if (Schema::hasTable('clearcheck_notifications') && Schema::hasColumn('clearcheck_notifications', 'user_id')) {
            try {
                Schema::table('clearcheck_notifications', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                });
            } catch (\Throwable) {
                // FK may not exist
            }

            if (! Schema::hasColumn('clearcheck_notifications', 'portal_user_id')) {
                Schema::table('clearcheck_notifications', function (Blueprint $table) {
                    $table->unsignedBigInteger('portal_user_id')->nullable()->after('id');
                });

                DB::table('clearcheck_notifications')
                    ->whereNotNull('user_id')
                    ->update(['portal_user_id' => DB::raw('user_id')]);

                Schema::table('clearcheck_notifications', function (Blueprint $table) {
                    $table->dropColumn('user_id');
                    $table->index(['portal_user_id', 'is_read']);
                });
            }
        }

        if (Schema::hasTable('document_uploads') && Schema::hasColumn('document_uploads', 'reviewed_by')) {
            try {
                Schema::table('document_uploads', function (Blueprint $table) {
                    $table->dropForeign(['reviewed_by']);
                });
            } catch (\Throwable) {
                //
            }

            Schema::table('document_uploads', function (Blueprint $table) {
                if (! Schema::hasColumn('document_uploads', 'reviewed_by_portal_id')) {
                    $table->unsignedBigInteger('reviewed_by_portal_id')->nullable()->after('rejection_reason');
                }
                if (! Schema::hasColumn('document_uploads', 'reviewed_by_name')) {
                    $table->string('reviewed_by_name', 200)->nullable()->after('reviewed_by_portal_id');
                }
            });

            DB::table('document_uploads')
                ->whereNotNull('reviewed_by')
                ->update(['reviewed_by_portal_id' => DB::raw('reviewed_by')]);

            if (Schema::hasColumn('document_uploads', 'reviewed_by')) {
                Schema::table('document_uploads', function (Blueprint $table) {
                    $table->dropColumn('reviewed_by');
                });
            }
        }

        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Identity is owned by DEORIS; local users are not restored.
    }
};
