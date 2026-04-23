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
        Schema::create('descan_kecamatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kabupaten_id')->constrained('tb_kabupaten');
            $table->string('kode_kecamatan');
            $table->string('nama_kecamatan');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('descan_kecamatan');
    }
};
