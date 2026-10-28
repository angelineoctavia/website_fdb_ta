<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('TRANSACTIONS_ID', 14)->primary();
            $table->string('USERS_ID', 5);
            $table->decimal('TRANSACTIONS_TOTAL_AMOUNT', 12, 2);
            $table->string('TRANSACTIONS_PAYMENT_METHOD', 20)->nullable();
            $table->string('TRANSACTIONS_PAYMENT_PROOF', 255)->nullable();
            $table->string('TRANSACTIONS_ORDER_STATUS', 25);
            $table->string('TRANSACTIONS_FULFILLMENT_TYPE', 20)->nullable();
            $table->decimal('TRANSACTIONS_SELLER_PAY', 12, 2)->nullable();
            $table->decimal('TRANSACTIONS_PRODI_FEE', 12, 2)->nullable();
            $table->date('TRANSACTIONS_DATE');
            $table->boolean('TRANSACTIONS_STATUS_DEL')->default(false);
            $table->timestamps();

            // Foreign Key
            $table->foreign('USERS_ID')->references('USERS_ID')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};