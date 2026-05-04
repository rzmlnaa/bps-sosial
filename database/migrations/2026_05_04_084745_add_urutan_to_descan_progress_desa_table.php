<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('descan_progress_desa', function (Blueprint $table) {
            $table->integer('urutan')->nullable()->after('kegiatan_id');
        });

        // Isi data urutan awal dari master kegiatan untuk data yang sudah ada
        $kegiatans = DB::table('descan_kegiatan')->select('id', 'urutan')->get();
        foreach ($kegiatans as $keg) {
            DB::table('descan_progress_desa')
                ->where('kegiatan_id', $keg->id)
                ->whereNull('urutan')
                ->update(['urutan' => $keg->urutan]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('descan_progress_desa', function (Blueprint $table) {
            $table->dropColumn('urutan');
        });
    }
};
