<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tb_komoditas', function (Blueprint $table) {
            $table->decimal('batas_selisih_harga', 15, 2)->nullable()->after('satuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_komoditas', function (Blueprint $table) {
            $table->dropColumn('batas_selisih_harga');
        });
    }
};
