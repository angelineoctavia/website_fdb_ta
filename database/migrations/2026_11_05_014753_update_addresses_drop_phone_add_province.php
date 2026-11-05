<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'ADDRESSES_PHONE')) {
                $table->dropColumn('ADDRESSES_PHONE');
            }
            // kolom ini tadi kutambah manual lewat ALTER, guard biar tidak dobel
            if (! Schema::hasColumn('addresses', 'ADDRESSES_PROVINCE')) {
                $table->string('ADDRESSES_PROVINCE', 50)->nullable()->after('ADDRESSES_CITY');
            }
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('ADDRESSES_PHONE', 15)->nullable();
        });
    }
};
