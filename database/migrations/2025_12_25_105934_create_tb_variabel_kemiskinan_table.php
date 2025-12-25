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
        Schema::create('tb_variabel_kemiskinan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_variabel', 100);
            $table->unsignedTinyInteger('bulan')->nullable();
            $table->year('tahun');
            $table->unsignedBigInteger('user_id_add')->nullable();
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_variabel_kemiskinan');
    }
};
