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
        Schema::create('tb_nilai_kemiskinan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kabupaten_id')->constrained('tb_kabupaten')->onDelete('cascade');
            $table->foreignId('variabel_kemiskinan_id')->constrained('tb_variabel_kemiskinan')->onDelete('cascade');
            $table->unsignedTinyInteger('persentil');
            $table->decimal('nilai', 14, 2);
            $table->timestamps();

            $table->unique(['kabupaten_id', 'variabel_kemiskinan_id', 'persentil'], 'unique_nilai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_nilai_kemiskinan');
    }
};
