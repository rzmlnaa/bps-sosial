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
        Schema::table('tb_rh_master_nilai', function (Blueprint $table) {
            $table->text('alasan')->nullable()->after('max_nilai');
        });

        Schema::table('tb_rh_perubahan_detail', function (Blueprint $table) {
            $table->text('alasan')->nullable()->after('max_edit');
        });
    }

    public function down(): void
    {
        Schema::table('tb_rh_master_nilai', function (Blueprint $table) {
            $table->dropColumn('alasan');
        });

        Schema::table('tb_rh_perubahan_detail', function (Blueprint $table) {
            $table->dropColumn('alasan');
        });
    }
};
