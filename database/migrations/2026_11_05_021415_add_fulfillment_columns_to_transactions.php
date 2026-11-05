<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'TRANSACTIONS_SHIPPING_COST')) {
                $table->decimal('TRANSACTIONS_SHIPPING_COST', 12, 2)->default(0);
            }
            if (! Schema::hasColumn('transactions', 'TRANSACTIONS_FULFILLMENT_TYPE')) {
                $table->string('TRANSACTIONS_FULFILLMENT_TYPE', 10)->default('ready'); // ready | preorder
            }
            if (! Schema::hasColumn('transactions', 'ADDRESSES_ID')) {
                $table->string('ADDRESSES_ID', 8)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            foreach (['TRANSACTIONS_SHIPPING_COST', 'TRANSACTIONS_FULFILLMENT_TYPE', 'ADDRESSES_ID'] as $col) {
                if (Schema::hasColumn('transactions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
