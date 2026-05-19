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
        Schema::dropIfExists('descan_kuota');

        Schema::table('descan_kegiatan', function (Blueprint $table) {
            $table->boolean('is_wajib')->default(false)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('descan_kegiatan', function (Blueprint $table) {
            $table->dropColumn('is_wajib');
        });

        Schema::create('descan_kuota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->unique()->constrained('descan_periode');
            $table->integer('max_kecamatan');
            $table->integer('max_desa');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }
};
