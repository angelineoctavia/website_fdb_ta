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
                $table->decimal('TRANSACTIONS_SHIPPING_COST', 12, 2)->default(0)->after('TRANSACTIONS_TOTAL_AMOUNT');
            }
            if (! Schema::hasColumn('transactions', 'TRANSACTIONS_SNAP_TOKEN')) {
                $table->string('TRANSACTIONS_SNAP_TOKEN', 100)->nullable();
            }
            if (! Schema::hasColumn('transactions', 'TRANSACTIONS_PAID_AT')) {
                $table->timestamp('TRANSACTIONS_PAID_AT')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            foreach (['TRANSACTIONS_SHIPPING_COST', 'TRANSACTIONS_SNAP_TOKEN', 'TRANSACTIONS_PAID_AT'] as $col) {
                if (Schema::hasColumn('transactions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};