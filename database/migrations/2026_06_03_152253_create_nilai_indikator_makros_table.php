<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nilai_indikator_makros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_dimensi_id')->constrained('indikator_dimensi')->onDelete('cascade');
            $table->foreignId('kabupaten_id')->constrained('tb_kabupaten')->onDelete('cascade');
            $table->foreignId('periode_indikator_id')->constrained('periode_indikators')->onDelete('cascade');
            $table->decimal('nilai', 20, 4)->nullable();
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
        Schema::dropIfExists('nilai_indikator_makros');
    }
};
