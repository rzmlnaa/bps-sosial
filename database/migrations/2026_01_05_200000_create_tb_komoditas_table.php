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
        Schema::create('tb_komoditas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('tb_kategori_komoditas');
            $table->string('nama_komoditas');
            $table->foreignId('user_id_add')->nullable()->constrained('users');
            $table->foreignId('user_id_update')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_komoditas');
    }
};
