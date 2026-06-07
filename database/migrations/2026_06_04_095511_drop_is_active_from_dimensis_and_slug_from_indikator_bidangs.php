<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Hapus kolom is_active dari tabel dimensis
        Schema::table('dimensis', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        // 2. Hapus unique index dan kolom slug dari tabel indikator_bidangs
        Schema::table('indikator_bidangs', function (Blueprint $table) {
            // Drop unique index dulu sebelum drop kolom
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dimensis', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('nama_dimensi');
        });

        Schema::table('indikator_bidangs', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('nama_bidang');
        });
    }
};
