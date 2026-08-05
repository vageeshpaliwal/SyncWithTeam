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
        Schema::create('organizations', function (Blueprint $table) {

            $table->id();

            $table->string('name', 150);

            $table->string('slug', 150)->unique();

            $table->string('logo')->nullable();

            $table->string('website')->nullable();

            $table->string('email')->nullable();

            $table->string('phone', 20)->nullable();

            $table->string('timezone', 100)->default('Asia/Kolkata');

            $table->string('currency', 10)->default('INR');

            $table->string('country', 100)->default('India');

            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};