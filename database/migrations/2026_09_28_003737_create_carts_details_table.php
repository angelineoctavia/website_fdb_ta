<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts_details', function (Blueprint $table) {
            $table->id('CARTS_DETAILS_ID'); // Auto Increment Integer
            $table->string('CARTS_ID', 10);
            $table->string('PRODUCTS_ID', 8);
            $table->decimal('CARTS_DETAILS_PRICE', 12, 2);
            $table->integer('CARTS_DETAILS_QTY');
            $table->text('CARTS_DETAILS_CUSTOM_SIZE_NOTES')->nullable();
            $table->boolean('CARTS_DETAILS_STATUS_DEL')->default(false);
            $table->timestamps();

            // Foreign Keys
            $table->foreign('CARTS_ID')->references('CARTS_ID')->on('carts')->onDelete('cascade');
            $table->foreign('PRODUCTS_ID')->references('PRODUCTS_ID')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts_details');
    }
};