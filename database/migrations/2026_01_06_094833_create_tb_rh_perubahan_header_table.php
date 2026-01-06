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
        Schema::create('tb_rh_perubahan_header', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rh_tahun_id')->constrained('tb_rh_tahun');
            $table->date('tanggal_perubahan');
            $table->string('label', 100); // Perubahan RH 3 Maret 2025
            $table->foreignId('user_id_add')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_rh_perubahan_header');
    }
};
