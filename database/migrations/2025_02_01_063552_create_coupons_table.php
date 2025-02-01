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
        Schema::create('coupons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->index();
            $table->timestamp('active')->useCurrent()->nullable();
            $table->enum('coupon_type', ['flat', 'percentage', 'free_shipping'])->index()->nullable(); // will be flat or percentage
            $table->string('coupon_code')->index()->unique();
            $table->double('coupon_amount')->nullable();
            $table->double('minimum_spend')->nullable();
            $table->double('maximum_spend')->nullable();
            $table->integer('limit_per_coupon')->index()->nullable();
            $table->integer('limit_per_user')->index()->nullable();
            $table->timestamp('expiry_date')->index()->nullable();
            $table->unsignedBigInteger('user_id')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('coupon_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('coupon_id');
            $table->string('coupon_code');
            $table->string('coupon_details', 255)->nullable();
            $table->integer('win_amount')->nullable();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('user_id'); // will be customer id
            $table->timestamps();
            $table->softDeletes();
        });
        // php artisan migrate:refresh --path=/database/migrations/2025_02_01_063552_create_coupons_table.php
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('coupon_user');
    }
};
