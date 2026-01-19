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
            $table->foreignId('user_id_add')->nullable()->after('is_active')->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::table('tb_rh_tahun', function (Blueprint $table) {
            $table->dropForeign(['user_id_add']);
            $table->dropColumn('user_id_add');
        });
    }
};
