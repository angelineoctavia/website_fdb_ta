<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions_details', function (Blueprint $table) {
            $table->string('TRANSACTIONS_DETAIL_ID', 15)->primary();
            $table->string('TRANSACTIONS_ID', 14);
            $table->string('PRODUCTS_ID', 8);
            $table->integer('TRANSACTIONS_QTY');
            $table->decimal('TRANSACTIONS_SUBTOTAL', 12, 2);
            $table->text('TRANSACTIONS_DETAILS_CUSTOM_SIZE_NOTES')->nullable();
            $table->string('TRANSACTIONS_DETAILS_SELECTED_SIZE', 6)->nullable();
            $table->date('TRANSACTIONS_DETAILS_EST_COMPLETION_DATE')->nullable();
            $table->boolean('TRANSACTIONS_DETAILS_STATUS_DEL')->default(false);
            $table->timestamps();

            // Foreign Keys
            $table->foreign('TRANSACTIONS_ID')->references('TRANSACTIONS_ID')->on('transactions')->onDelete('cascade');
            $table->foreign('PRODUCTS_ID')->references('PRODUCTS_ID')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions_details');
    }
};