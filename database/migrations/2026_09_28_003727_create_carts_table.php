<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->string('CARTS_ID', 10)->primary();
            $table->string('USERS_ID', 5);
            $table->boolean('CARTS_STATUS_DEL')->default(false);
            $table->timestamps();

            // Foreign Key
            $table->foreign('USERS_ID')->references('USERS_ID')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};