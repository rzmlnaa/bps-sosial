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
        Schema::table('tb_rh_tahun', function (Blueprint $table) {
            $table->dropForeign(['user_id_add']);
            $table->dropColumn(['batas_selisih_harga', 'user_id_add']);
        });
    }

    public function down(): void
    {
        Schema::table('tb_rh_tahun', function (Blueprint $table) {
            $table->decimal('batas_selisih_harga', 15, 2)->default(0);
            $table->unsignedBigInteger('user_id_add')->nullable();
        });
    }
};
