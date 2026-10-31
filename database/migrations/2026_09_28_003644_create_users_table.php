<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('USERS_ID', 5)->primary();
            $table->string('USERS_NAME', 50);
            $table->string('USERS_PASSWORD', 255);
            $table->char('USERS_ROLE', 1);
            $table->text('USERS_BIO')->nullable();
            $table->string('USERS_PHONE_NUMBER', 15)->nullable();
            $table->decimal('USERS_BUST', 8, 2)->nullable();
            $table->decimal('USERS_WAIST', 8, 2)->nullable();
            $table->decimal('USERS_HIP', 8, 2)->nullable();
            $table->decimal('USERS_HEIGHT', 8, 2)->nullable();
            $table->decimal('USERS_WEIGHT', 8, 2)->nullable();
            // Seller profile
            $table->string('USERS_INSTAGRAM', 50)->nullable();
            $table->text('USERS_ADDRESS')->nullable();               // alamat toko offline
            $table->string('USERS_PROFILE_PICTURE', 255)->nullable();
            $table->boolean('USERS_STATUS_DEL')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};