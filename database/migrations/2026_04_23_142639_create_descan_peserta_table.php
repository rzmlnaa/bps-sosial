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
        Schema::create('descan_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained('descan_desa');
            $table->foreignId('kecamatan_id')->constrained('descan_kecamatan');
            $table->foreignId('kabupaten_id')->constrained('tb_kabupaten');
            $table->foreignId('periode_id')->constrained('descan_periode');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('descan_peserta');
    }
};
