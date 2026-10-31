<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('USERS_INSTAGRAM', 50)->nullable();
            $t->text('USERS_ADDRESS')->nullable();            // offline store address
            $t->string('USERS_PROFILE_PICTURE', 255)->nullable();
        });

        Schema::table('products', function (Blueprint $t) {
            $t->string('PRODUCTS_GENDER', 10)->nullable();    // Women / Men / Unisex
        });

        Schema::table('transactions', function (Blueprint $t) {
            $t->text('TRANSACTIONS_SHIPPING_ADDRESS')->nullable();
            $t->text('TRANSACTIONS_SHIPPING_NOTES')->nullable();
            $t->string('TRANSACTIONS_COURIER', 20)->nullable();         // JNT / JNE / dll
            $t->string('TRANSACTIONS_TRACKING_NUMBER', 50)->nullable(); // no. resi
        });
    }

    public function down(): void
    {
        Schema::table('users', fn(Blueprint $t) => $t->dropColumn(['USERS_INSTAGRAM', 'USERS_ADDRESS', 'USERS_PROFILE_PICTURE']));
        Schema::table('products', fn(Blueprint $t) => $t->dropColumn('PRODUCTS_GENDER'));
        Schema::table('transactions', fn(Blueprint $t) => $t->dropColumn(['TRANSACTIONS_SHIPPING_ADDRESS', 'TRANSACTIONS_SHIPPING_NOTES', 'TRANSACTIONS_COURIER', 'TRANSACTIONS_TRACKING_NUMBER']));
    }
};