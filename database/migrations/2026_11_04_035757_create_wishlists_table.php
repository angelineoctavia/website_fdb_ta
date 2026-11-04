<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wishlists')) {
            return;
        }

        Schema::create('wishlists', function (Blueprint $table) {
            $table->id('WISHLISTS_ID');
            $table->string('USERS_ID', 5);
            $table->string('PRODUCTS_ID', 8);
            $table->boolean('WISHLISTS_STATUS_DEL')->default(false);
            $table->timestamps();

            $table->unique(['USERS_ID', 'PRODUCTS_ID'], 'uq_wishlists_user_product');
            $table->index('PRODUCTS_ID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};