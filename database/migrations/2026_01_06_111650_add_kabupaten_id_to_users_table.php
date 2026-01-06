<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('kabupaten_id')->nullable()->after('email')->constrained('tb_kabupaten')->onDelete('set null');
            $table->string('role')->default('admin')->after('kabupaten_id'); // e.g. admin, kab_id
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['kabupaten_id']);
            $table->dropColumn(['kabupaten_id', 'role']);
        });
    }
};
