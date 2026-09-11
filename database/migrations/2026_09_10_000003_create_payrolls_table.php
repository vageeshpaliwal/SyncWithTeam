<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('salary_month', 7); // e.g. '2026-09'
            $table->unsignedSmallInteger('month'); // 1-12
            $table->unsignedSmallInteger('year'); // e.g. 2026
            $table->string('payslip_number')->unique(); // e.g. PAY-202609-0001
            
            // Attendance & Working Days
            $table->unsignedTinyInteger('working_days')->default(30);
            $table->decimal('present_days', 4, 1)->default(0);
            $table->decimal('paid_leaves', 4, 1)->default(0);
            $table->decimal('unpaid_leaves', 4, 1)->default(0);
            $table->decimal('total_hours_worked', 8, 2)->default(0);
            $table->decimal('overtime_hours', 6, 2)->default(0);

            // Salary breakdown
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->json('allowances')->nullable();
            $table->decimal('total_allowances', 12, 2)->default(0);
            
            $table->json('deductions')->nullable();
            $table->decimal('total_deductions', 12, 2)->default(0);

            $table->decimal('gross_salary', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);

            // Payment status & details
            $table->enum('payment_status', ['pending', 'processing', 'paid', 'cancelled'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->text('remarks')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'user_id', 'salary_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};