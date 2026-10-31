<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Kalau migration pertama versi lama sudah sempat dijalankan, kolom ini dibuang.
        if (Schema::hasColumn('users', 'USERS_SIZE_CHART')) {
            Schema::table('users', fn(Blueprint $t) => $t->dropColumn('USERS_SIZE_CHART'));
        }

        // Banyak size chart per seller (T-Shirt, Jacket, Outer, ...)
        Schema::create('size_charts', function (Blueprint $t) {
            $t->string('SIZE_CHARTS_ID', 8)->primary();
            $t->string('USERS_ID', 5)->index();
            $t->string('SIZE_CHARTS_NAME', 50);
            $t->json('SIZE_CHARTS_DATA');                 // {"columns":[...],"rows":[{"size":"S","values":[..]}]}
            $t->tinyInteger('SIZE_CHARTS_STATUS_DEL')->default(0);
            $t->timestamps();
        });

        // Buku alamat customer
        Schema::create('addresses', function (Blueprint $t) {
            $t->string('ADDRESSES_ID', 8)->primary();
            $t->string('USERS_ID', 5)->index();
            $t->string('ADDRESSES_LABEL', 30);            // Rumah / Kantor / dll
            $t->string('ADDRESSES_RECIPIENT', 50);
            $t->string('ADDRESSES_PHONE', 15);
            $t->text('ADDRESSES_FULL');                   // jalan, no rumah, RT/RW
            $t->string('ADDRESSES_DISTRICT', 50)->nullable();
            $t->string('ADDRESSES_CITY', 50);
            $t->string('ADDRESSES_POSTAL_CODE', 10)->nullable();
            $t->text('ADDRESSES_NOTES')->nullable();
            $t->tinyInteger('ADDRESSES_IS_DEFAULT')->default(0);
            $t->tinyInteger('ADDRESSES_STATUS_DEL')->default(0);
            $t->timestamps();
        });

        // Referensi alamat yang dipilih saat checkout (teksnya tetap disalin ke TRANSACTIONS_SHIPPING_ADDRESS)
        Schema::table('transactions', function (Blueprint $t) {
            $t->string('ADDRESSES_ID', 8)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', fn(Blueprint $t) => $t->dropColumn('ADDRESSES_ID'));
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('size_charts');
    }
};