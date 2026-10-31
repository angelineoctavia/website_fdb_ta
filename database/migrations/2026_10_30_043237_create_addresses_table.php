<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->string('ADDRESSES_ID', 8)->primary();
            $table->string('USERS_ID', 5)->index();
            $table->string('ADDRESSES_LABEL', 30);          // Rumah / Kantor
            $table->string('ADDRESSES_RECIPIENT', 50);
            $table->string('ADDRESSES_PHONE', 15);
            $table->text('ADDRESSES_FULL');
            $table->string('ADDRESSES_DISTRICT', 50)->nullable();
            $table->string('ADDRESSES_CITY', 50);
            $table->string('ADDRESSES_POSTAL_CODE', 10)->nullable();
            $table->text('ADDRESSES_NOTES')->nullable();
            $table->tinyInteger('ADDRESSES_IS_DEFAULT')->default(0);
            $table->tinyInteger('ADDRESSES_STATUS_DEL')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
