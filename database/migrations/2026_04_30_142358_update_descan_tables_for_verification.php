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
        // 1. descan_output_desa
        Schema::table('descan_output_desa', function (Blueprint $table) {
            $table->enum('status_new', ['draf', 'menunggu_verifikasi', 'disetujui', 'ditolak'])->default('draf')->after('link');
            $table->text('alasan_penolakan')->nullable()->after('status_new');
        });

        // Copy data if needed, but since it's likely fresh, we can just drop and rename
        // However, for safety in migration, we'll just drop the old and keep the new if we don't care about data.
        // If we want to be clean:
        Schema::table('descan_output_desa', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        Schema::table('descan_output_desa', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });

        // 2. descan_progress_desa
        Schema::table('descan_progress_desa', function (Blueprint $table) {
            $table->text('alasan_penolakan')->nullable()->after('status');
            // Change draft to draf for consistency
            $table->enum('status', ['draf', 'menunggu_verifikasi', 'disetujui', 'ditolak'])->default('draf')->change();
        });

        // 3. descan_bukti_dukung_desa
        Schema::table('descan_bukti_dukung_desa', function (Blueprint $table) {
            $table->dropColumn('keterangan');
            $table->enum('status', ['draf', 'menunggu_verifikasi', 'disetujui', 'ditolak'])->default('draf')->after('link_file');
            $table->text('alasan_penolakan')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('descan_output_desa', function (Blueprint $table) {
            $table->dropColumn(['status', 'alasan_penolakan']);
        });
        Schema::table('descan_output_desa', function (Blueprint $table) {
            $table->string('status')->nullable()->after('link');
        });

        Schema::table('descan_progress_desa', function (Blueprint $table) {
            $table->dropColumn('alasan_penolakan');
            $table->enum('status', ['draft', 'menunggu_verifikasi', 'disetujui', 'ditolak'])->default('draft')->change();
        });

        Schema::table('descan_bukti_dukung_desa', function (Blueprint $table) {
            $table->text('keterangan')->nullable()->after('link_file');
            $table->dropColumn(['status', 'alasan_penolakan']);
        });
    }
};
