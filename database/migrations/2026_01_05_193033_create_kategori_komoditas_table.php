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
        Schema::create('tb_kategori_komoditas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori', 100);
            $table->foreignId('user_id_add')->nullable()->constrained('users');
            $table->foreignId('user_id_update')->nullable()->constrained('users');
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_kategori_komoditas');
    }
};
