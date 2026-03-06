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
        Schema::create('fenomenas', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_berita');
            $table->tinyInteger('bulan');
            $table->smallInteger('tahun');
            $table->string('judul');
            $table->text('penjelasan');
            $table->text('link_berita')->nullable();
            $table->foreignId('sumber_berita_id')->constrained('sumber_beritas')->cascadeOnDelete();
            $table->enum('status_verifikasi', ['N', 'Y'])->default('N');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fenomenas');
    }
};
