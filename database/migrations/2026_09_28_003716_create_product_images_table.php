<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id('PRODUCT_IMAGES_ID'); // Auto Increment Integer
            $table->string('PRODUCTS_ID', 8);
            $table->string('PRODUCT_IMAGES_URL', 255);
            $table->boolean('PRODUCT_IMAGES_IS_PRIMARY')->default(false);
            $table->boolean('PRODUCT_IMAGES_STATUS_DEL')->default(false);
            $table->timestamps();

            // Foreign Key
            $table->foreign('PRODUCTS_ID')->references('PRODUCTS_ID')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};