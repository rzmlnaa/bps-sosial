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
        Schema::create('fenomena_jenis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fenomena_id')->constrained('fenomenas')->cascadeOnDelete();
            $table->foreignId('jenis_fenomena_id')->constrained('jenis_fenomenas')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fenomena_jenis');
    }
};
