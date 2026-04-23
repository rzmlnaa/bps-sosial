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
        Schema::create('descan_penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_id')->unique()->constrained('descan_peserta');
            $table->boolean('penilaian_mandiri_desa')->default(false);
            $table->boolean('penilaian_mandiri_kab')->default(false);
            $table->boolean('verifikasi_provinsi')->default(false);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('descan_penilaian');
    }
};
