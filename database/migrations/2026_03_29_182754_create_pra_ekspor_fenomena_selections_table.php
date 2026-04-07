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
        Schema::create('pra_ekspor_fenomena_selections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kabupaten_id');
            $table->unsignedBigInteger('fenomena_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('tahun');
            $table->integer('bulan');
            $table->string('status')->default('draft');
            $table->timestamps();

            // Composite Index for efficient querying and preventing duplicates
            $table->index(['kabupaten_id', 'fenomena_id', 'tahun', 'bulan'], 'pra_ekspor_selection_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pra_ekspor_fenomena_selections');
    }
};
