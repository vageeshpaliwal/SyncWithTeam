<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->after('project_id')->constrained()->cascadeOnDelete();
            $table->dropColumn('work_log');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->dropColumn('task_id');
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
            $table->text('work_log')->nullable()->after('clock_out_at');
        });
    }
};
