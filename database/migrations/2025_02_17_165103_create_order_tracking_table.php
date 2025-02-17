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
        Schema::create('order_tracking', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->index();
            $table->unsignedInteger('order_id')->index();
            $table->enum('status', ['new', 'in-progress', 'cancel', 'dispatch', 'return', 'delivered'])->index();
            $table->string('tracking_status', 255)->nullable();
            $table->smallInteger('sorting')->nullable();
            $table->timestamp('updated_time')->index()->nullable();
            $table->text('comment')->nullable();
            $table->unsignedInteger('user_id')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // php artisan migrate:refresh --path=/database/migrations/2025_02_17_165103_create_order_tracking_table.php
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_tracking');
    }
};
