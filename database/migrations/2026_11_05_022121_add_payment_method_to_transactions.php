<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'TRANSACTIONS_PAYMENT_METHOD')) {
                $table->string('TRANSACTIONS_PAYMENT_METHOD', 30)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'TRANSACTIONS_PAYMENT_METHOD')) {
                $table->dropColumn('TRANSACTIONS_PAYMENT_METHOD');
            }
        });
    }
};