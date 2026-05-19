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
        Schema::table('descan_periode', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('tahun');
        });

        Schema::table('descan_kegiatan', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('urutan');
        });

        Schema::table('descan_jenis_bukti_kegiatan', function (Blueprint $table) {
            $table->boolean('is_wajib')->default(false)->after('nama_bukti');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('descan_periode', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('descan_kegiatan', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('descan_jenis_bukti_kegiatan', function (Blueprint $table) {
            $table->dropColumn('is_wajib');
        });
    }
};
