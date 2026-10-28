<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->string('PRODUCTS_ID', 8)->primary();
            $table->string('CATEGORIES_ID', 4);
            $table->string('USERS_ID', 5);
            $table->string('PRODUCTS_NAME', 100);
            $table->decimal('PRODUCTS_PRICE', 12, 2);
            $table->integer('PRODUCTS_STOCK');
            $table->text('PRODUCTS_DESCRIPTION')->nullable();
            $table->string('PRODUCTS_MATERIAL', 100)->nullable();
            $table->text('PRODUCTS_SIZE_CHART')->nullable();
            $table->smallInteger('PRODUCTS_READY_TYPE');
            $table->integer('PRODUCTS_PREORDER_DURATION')->nullable();
            $table->boolean('PRODUCTS_STATUS_DEL')->default(false);
            $table->timestamps();

            // Foreign Keys
            $table->foreign('CATEGORIES_ID')->references('CATEGORIES_ID')->on('categories')->onDelete('cascade');
            $table->foreign('USERS_ID')->references('USERS_ID')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};