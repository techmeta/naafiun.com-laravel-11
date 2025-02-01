<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cart', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->index();
            $table->json('shipping_address')->nullable();
            $table->json('billing_address')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('shipping_method')->nullable();
            $table->smallInteger('shipping_cost')->nullable();
            $table->string('coupon_code')->index()->nullable();
            $table->mediumInteger('coupon_discount')->nullable();
            $table->mediumInteger('use_credit')->nullable();
            $table->enum('status', ['new', 'purchased', 'rejected', 'canceled', 'deleted'])->index()->nullable();
            $table->smallInteger('is_purchase')->index()->nullable();
            $table->unsignedInteger('user_id')->index()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cart_item', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid()->index();
            $table->unsignedBigInteger('cart_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('store_id')->index();
            $table->string('product_link')->nullable();
            $table->string('name', 255)->nullable();
            $table->string('picture', 255)->nullable();
            $table->boolean('is_cart')->index()->nullable();
            $table->boolean('is_selected')->index()->nullable();
            $table->boolean('is_popup_shown')->index()->nullable();
            $table->timestamps();
//            $table->foreign('cart_id')->references('id')->on('cart')->onDelete('cascade');
        });

        Schema::create('cart_item_variation', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->index();
            $table->unsignedBigInteger('cart_id')->index();
            $table->unsignedBigInteger('cart_item_id')->index();
            $table->uuid('config_id')->index();
            $table->json('attributes')->nullable();
            $table->mediumInteger('regular_price')->nullable();
            $table->mediumInteger('sale_price')->nullable();
            $table->mediumInteger('discount_amt')->nullable();
            $table->smallInteger('quantity');
            $table->smallInteger('max_quantity')->nullable();
            $table->timestamps();
//            $table->foreign('cart_item_id')->references('id')->on('cart_item')->onDelete('cascade');
//            $table->foreign('cart_id')->references('id')->on('cart')->onDelete('cascade');
        });

        // php artisan migrate:rollback --path=/database/migrations/2025_02_01_055522_create_cart_table.php
        // php artisan migrate:refresh --path=/database/migrations/2025_02_01_055522_create_cart_table.php
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart');
        Schema::dropIfExists('cart_item');
        Schema::dropIfExists('cart_item_variation');
    }
};
