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
        Schema::table('users', function (Blueprint $table) {
            // HR fields
            $table->date('joining_date')->nullable()->after('status');
            $table->integer('probation_months')->default(3)->nullable()->after('joining_date');

            // Employee Personal & KYE fields
            $table->date('dob')->nullable()->after('probation_months');
            $table->string('father_name')->nullable()->after('dob');
            $table->string('mother_name')->nullable()->after('father_name');
            $table->string('aadhaar_number')->nullable()->after('mother_name');
            $table->string('pan_number')->nullable()->after('aadhaar_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'joining_date',
                'probation_months',
                'dob',
                'father_name',
                'mother_name',
                'aadhaar_number',
                'pan_number',
            ]);
        });
    }
};
