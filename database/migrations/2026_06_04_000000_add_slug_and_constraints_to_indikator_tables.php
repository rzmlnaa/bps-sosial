<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Clean up duplicate years in `periode_indikators` if any exist before applying unique index
        $duplicates = DB::table('periode_indikators')
            ->select('tahun', DB::raw('count(*) as count'))
            ->groupBy('tahun')
            ->having('count', '>', 1)
            ->pluck('tahun');

        foreach ($duplicates as $tahun) {
            $keepId = DB::table('periode_indikators')
                ->where('tahun', $tahun)
                ->value('id');
            
            DB::table('periode_indikators')
                ->where('tahun', $tahun)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        // Apply unique constraint to `tahun` on `periode_indikators`
        Schema::table('periode_indikators', function (Blueprint $table) {
            $table->unique('tahun');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('periode_indikators', function (Blueprint $table) {
                $table->dropUnique(['tahun']);
            });
        } catch (\Exception $e) {}


    }
};
