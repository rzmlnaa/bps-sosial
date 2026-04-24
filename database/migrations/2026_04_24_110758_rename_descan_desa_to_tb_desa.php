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
        Schema::table('descan_peserta', function (Blueprint $table) {
            $table->dropForeign(['desa_id']);
        });

        // 2. Rename the table
        Schema::rename('descan_desa', 'tb_desa');

        // 3. Re-add the constraints targeting the new table name
        Schema::table('descan_peserta', function (Blueprint $table) {
            $table->foreign('desa_id')->references('id')->on('tb_desa')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop constraints targeting the new table name
        Schema::table('descan_peserta', function (Blueprint $table) {
            $table->dropForeign(['desa_id']);
        });

        // 2. Revert the table name
        Schema::rename('tb_desa', 'descan_desa');

        // 3. Re-add constraints targeting the old table name
        Schema::table('descan_peserta', function (Blueprint $table) {
            $table->foreign('desa_id')->references('id')->on('descan_desa');
        });
    }
};
