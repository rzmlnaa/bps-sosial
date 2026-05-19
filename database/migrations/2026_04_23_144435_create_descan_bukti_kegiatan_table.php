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
        Schema::create('descan_bukti_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_desa_id')->constrained('descan_progress_desa');
            $table->foreignId('jenis_bukti_id')->constrained('descan_jenis_bukti_kegiatan');
            $table->string('link_file');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('descan_bukti_kegiatan');
    }
};
