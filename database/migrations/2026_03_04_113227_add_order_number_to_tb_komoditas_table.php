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
        Schema::table('tb_komoditas', function (Blueprint $table) {
            $table->integer('order_number')->nullable()->after('kategori_id');
        });

        // Sinkronisasi data lama: set order_number = id
        DB::statement('UPDATE tb_komoditas SET order_number = id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_komoditas', function (Blueprint $table) {
            $table->dropColumn('order_number');
        });
    }
};
