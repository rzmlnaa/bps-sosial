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
        Schema::table('fenomena_indikators', function (Blueprint $table) {
            $table->enum('arah', ['naik', 'turun', 'tetap'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fenomena_indikators', function (Blueprint $table) {
            $table->enum('arah', ['naik', 'turun', 'tetap'])->nullable(false)->change();
        });
    }
};
