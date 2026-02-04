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
        Schema::table('consumption_values', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id_add')->nullable()->after('value');
            $table->unsignedBigInteger('user_id_update')->nullable()->after('user_id_add');

            // Foreign keys
            $table->foreign('user_id_add')->references('id')->on('users')->onDelete('set null');
            $table->foreign('user_id_update')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consumption_values', function (Blueprint $table) {
            $table->dropForeign(['user_id_add']);
            $table->dropForeign(['user_id_update']);
            $table->dropColumn(['user_id_add', 'user_id_update']);
        });
    }
};
