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
        Schema::create('descan_bukti_dukung_desa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_id')->constrained('descan_peserta');
            $table->foreignId('jenis_bukti_id')->constrained('descan_jenis_bukti_dukung');
            $table->string('link_file');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['peserta_id', 'jenis_bukti_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('descan_bukti_dukung_desa');
    }
};
