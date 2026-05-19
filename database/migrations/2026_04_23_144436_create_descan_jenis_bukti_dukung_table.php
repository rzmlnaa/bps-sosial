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
        Schema::create('descan_jenis_bukti_dukung', function (Blueprint $table) {
            $table->id();
            $table->string('nama_bukti');
            $table->boolean('is_wajib')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('descan_jenis_bukti_dukung');
    }
};
