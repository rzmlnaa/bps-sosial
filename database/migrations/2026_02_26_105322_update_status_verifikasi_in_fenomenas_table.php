<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change the enum definition first to include P, Y, T, AND N (temporarily)
        Schema::table('fenomenas', function (Blueprint $table) {
            $table->string('status_verifikasi', 1)->default('P')->change();
        });

        // Update existing 'N' to 'P'
        DB::table('fenomenas')->where('status_verifikasi', 'N')->update(['status_verifikasi' => 'P']);

        // Now change it back to the strict enum [P, Y, T]
        Schema::table('fenomenas', function (Blueprint $table) {
            $table->enum('status_verifikasi', ['P', 'Y', 'T'])->default('P')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Change back to N, Y. Note: 'T' will be problematic if not handled.
        // For safety, convert 'T' and 'P' back to 'N'.
        DB::table('fenomenas')->whereIn('status_verifikasi', ['P', 'T'])->update(['status_verifikasi' => 'N']);

        Schema::table('fenomenas', function (Blueprint $table) {
            $table->enum('status_verifikasi', ['N', 'Y'])->default('N')->change();
        });
    }
};
