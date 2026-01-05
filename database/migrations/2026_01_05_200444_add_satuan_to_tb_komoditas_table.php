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
            if (!Schema::hasColumn('tb_komoditas', 'satuan')) {
                $table->string('satuan', 50)->nullable()->after('nama_komoditas');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_komoditas', function (Blueprint $table) {
            $table->dropColumn('satuan');
        });
    }
};
