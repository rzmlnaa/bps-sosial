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
        Schema::create('coicops', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 5); // 01, 02, 03, 20, 40
            $table->string('nama', 255);
            $table->string('seruti', 50); // Makanan / Inti / Inti & Makanan
            $table->boolean('is_total')->default(false); // true untuk subtotal & total
            $table->timestamps();
        });

        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->integer('year'); // 2026
            $table->tinyInteger('quarter'); // 1–4
            $table->timestamps();
        });

        Schema::create('consumption_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('periods')->onDelete('cascade');
            $table->foreignId('coicop_id')->constrained('coicops')->onDelete('cascade');
            $table->bigInteger('value'); // Konsumsi per kapita
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumption_values');
        Schema::dropIfExists('periods');
        Schema::dropIfExists('coicops');
    }
};
