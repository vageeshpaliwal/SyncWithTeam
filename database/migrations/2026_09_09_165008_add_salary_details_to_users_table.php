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
            $table->decimal('basic_salary', 12, 2)->nullable()->after('pan_number');
            $table->string('pay_frequency')->default('monthly')->nullable()->after('basic_salary');
            $table->string('bank_name')->nullable()->after('pay_frequency');
            $table->string('account_number')->nullable()->after('bank_name');
            $table->string('ifsc_code')->nullable()->after('account_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'basic_salary',
                'pay_frequency',
                'bank_name',
                'account_number',
                'ifsc_code',
            ]);
        });
    }
};
