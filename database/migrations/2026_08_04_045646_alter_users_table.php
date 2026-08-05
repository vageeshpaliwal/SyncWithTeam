<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->foreignId('organization_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('role_id')
                ->nullable()
                ->after('organization_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('first_name', 100)->after('role_id');
            $table->string('last_name', 100)->nullable();
            $table->string('username', 100)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('avatar')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropSoftDeletes();

            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');

            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');

            $table->dropColumn([
                'first_name',
                'last_name',
                'username',
                'phone',
                'avatar',
                'status',
                'last_login_at',
            ]);
        });
    }
};