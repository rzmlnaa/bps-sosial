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
        $tables = ['sumber_beritas', 'sektor_usahas', 'jenis_fenomenas', 'indikators'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('user_id_add')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('user_id_update')->nullable()->constrained('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['sumber_beritas', 'sektor_usahas', 'jenis_fenomenas', 'indikators'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['user_id_add']);
                $table->dropForeign(['user_id_update']);
                $table->dropColumn(['user_id_add', 'user_id_update']);
            });
        }
    }
};
