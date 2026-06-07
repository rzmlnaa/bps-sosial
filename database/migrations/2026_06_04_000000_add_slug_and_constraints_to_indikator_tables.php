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

        // 2. Add `slug` column to `indikator_bidangs`
        Schema::table('indikator_bidangs', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('nama_bidang');
        });

        // Populate existing records with slugs
        $bidangs = DB::table('indikator_bidangs')->get();
        foreach ($bidangs as $b) {
            $slug = Str::slug($b->nama_bidang);
            $originalSlug = $slug;
            $count = 1;
            while (DB::table('indikator_bidangs')->where('slug', $slug)->where('id', '!=', $b->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            DB::table('indikator_bidangs')->where('id', $b->id)->update(['slug' => $slug]);
        }

        // Make slug unique and non-nullable now that all records have slugs
        Schema::table('indikator_bidangs', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('periode_indikators', function (Blueprint $table) {
            $table->dropUnique(['tahun']);
        });

        Schema::table('indikator_bidangs', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
