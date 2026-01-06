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
        Schema::table('tb_rh_tahun', function (Blueprint $table) {
            $table->decimal('batas_selisih_harga', 10, 2)->after('is_active')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_rh_tahun', function (Blueprint $table) {
            $table->dropColumn('batas_selisih_harga');
        });
    }
};
