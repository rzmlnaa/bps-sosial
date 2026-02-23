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
        Schema::create('fenomena_indikators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fenomena_id')->constrained('fenomenas')->cascadeOnDelete();
            $table->foreignId('indikator_id')->constrained('indikators')->cascadeOnDelete();
            $table->enum('arah', ['naik', 'turun', 'tetap']);
            $table->foreignId('ditetapkan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('ditetapkan_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fenomena_indikators');
    }
};
