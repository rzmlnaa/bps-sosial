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
        Schema::create('descan_output_desa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_id')->constrained('descan_peserta');
            $table->foreignId('jenis_output_id')->constrained('descan_jenis_output');
            $table->string('link');
            $table->string('status')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['peserta_id', 'jenis_output_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('descan_output_desa');
    }
};
