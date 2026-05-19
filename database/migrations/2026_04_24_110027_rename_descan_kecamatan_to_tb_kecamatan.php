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
        // 1. Drop constraints from dependent tables first
        Schema::table('descan_desa', function (Blueprint $table) {
            $table->dropForeign(['kecamatan_id']);
        });

        Schema::table('descan_peserta', function (Blueprint $table) {
            $table->dropForeign(['kecamatan_id']);
        });

        // 2. Rename the table
        Schema::rename('descan_kecamatan', 'tb_kecamatan');

        // 3. Re-add the constraints targeting the new table name
        Schema::table('descan_desa', function (Blueprint $table) {
            $table->foreign('kecamatan_id')->references('id')->on('tb_kecamatan');
        });

        Schema::table('descan_peserta', function (Blueprint $table) {
            $table->foreign('kecamatan_id')->references('id')->on('tb_kecamatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop constraints targeting the new table name
        Schema::table('descan_desa', function (Blueprint $table) {
            $table->dropForeign(['kecamatan_id']);
        });

        Schema::table('descan_peserta', function (Blueprint $table) {
            $table->dropForeign(['kecamatan_id']);
        });

        // 2. Revert the table name
        Schema::rename('tb_kecamatan', 'descan_kecamatan');

        // 3. Re-add constraints targeting the old table name
        Schema::table('descan_desa', function (Blueprint $table) {
            $table->foreign('kecamatan_id')->references('id')->on('descan_kecamatan');
        });

        Schema::table('descan_peserta', function (Blueprint $table) {
            $table->foreign('kecamatan_id')->references('id')->on('descan_kecamatan');
        });
    }
};
