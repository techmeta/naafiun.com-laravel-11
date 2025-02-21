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
        Schema::create('user_registration', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->index();
            $table->string('email', 100)->index()->nullable();
            $table->string('phone', 100)->index()->nullable();
            $table->string('otp_code', 10)->index();
            $table->timestamp('otp_expired')->index();
            $table->enum('type', ['register', 'reset_password'])->default('register')->index()->nullable(); // will be flat or percentage
            $table->enum('status', ['new', 'resend', 'registered', 'blocked', 'checked'])->default('new')->index()->nullable(); // will be flat or percentage
            $table->tinyInteger('attempt_count')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // php artisan migrate:refresh --path=/database/migrations/2025_02_21_093337_create_user_registration_table.php
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_registration');
    }
};
