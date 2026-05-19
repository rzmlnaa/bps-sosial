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
        Schema::create('descan_progress_desa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_id')->constrained('descan_peserta');
            $table->foreignId('kegiatan_id')->constrained('descan_kegiatan');
            $table->date('target_tanggal');
            $table->date('realisasi_tanggal')->nullable();
            $table->enum('status', ['draft', 'menunggu_verifikasi', 'disetujui', 'ditolak'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['peserta_id', 'kegiatan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('descan_progress_desa');
    }
};
