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
        Schema::create('tb_rh_tahun', function (Blueprint $table) {
            $table->id();
            $table->string('tahun', 4);
            $table->boolean('is_active')->default(false);
            $table->foreignId('user_id_add')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_rh_tahun');
    }
};
