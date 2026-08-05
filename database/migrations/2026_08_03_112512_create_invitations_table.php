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
        Schema::create('invitations', function (Blueprint $table) {

            $table->id();

            $table->foreignId('organization_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('role_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('invited_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('email')->index();

            $table->char('token', 64)->unique();

            $table->enum('status', [
                'PENDING',
                'ACCEPTED',
                'EXPIRED',
                'CANCELLED'
            ])->default('PENDING');

            $table->timestamp('expires_at');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};