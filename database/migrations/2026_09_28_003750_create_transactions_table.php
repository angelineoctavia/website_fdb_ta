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
            $table->string('TRANSACTIONS_ORDER_STATUS', 25);   // pending, paid, packing, shipping, completed, cancelled
            $table->string('TRANSACTIONS_FULFILLMENT_TYPE', 20)->nullable();
            $table->decimal('TRANSACTIONS_SELLER_PAY', 12, 2)->nullable();
            $table->decimal('TRANSACTIONS_PRODI_FEE', 12, 2)->nullable();
            // Pengiriman (alamat disalin dari tabel addresses saat checkout)
            $table->string('ADDRESSES_ID', 8)->nullable();
            $table->text('TRANSACTIONS_SHIPPING_ADDRESS')->nullable();
            $table->text('TRANSACTIONS_SHIPPING_NOTES')->nullable();
            $table->string('TRANSACTIONS_COURIER', 20)->nullable();          // JNT / JNE / dll
            $table->string('TRANSACTIONS_TRACKING_NUMBER', 50)->nullable();  // no. resi
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