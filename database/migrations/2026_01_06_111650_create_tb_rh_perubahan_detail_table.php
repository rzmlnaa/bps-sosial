<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tb_rh_perubahan_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rh_perubahan_header_id')->constrained('tb_rh_perubahan_header')->onDelete('cascade');
            $table->foreignId('kabupaten_id')->constrained('tb_kabupaten')->onDelete('cascade');
            $table->foreignId('komoditas_id')->constrained('tb_komoditas')->onDelete('cascade');

            $table->decimal('min_edit', 15, 2)->nullable();
            $table->decimal('max_edit', 15, 2)->nullable();

            $table->foreignId('user_id_add')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_rh_perubahan_detail');
    }
};
