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
        Schema::table('tb_rh_master_nilai', function (Blueprint $table) {
            $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending')->after('alasan');
            $table->text('rejection_reason')->nullable()->after('verification_status');
            $table->timestamp('verified_at')->nullable()->after('rejection_reason');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users');
            // Assuming 'alasan' was the last column added in previous migrations. 
            // If 'alasan' is not guaranteed to be there, we might need to adjust 'after'.
            // Based on view_file earlier, 'alasan' exists.
        });

        Schema::table('tb_rh_perubahan_detail', function (Blueprint $table) {
            $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending')->after('alasan');
            $table->text('rejection_reason')->nullable()->after('verification_status');
            $table->timestamp('verified_at')->nullable()->after('rejection_reason');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_rh_master_nilai', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['verification_status', 'rejection_reason', 'verified_at', 'verified_by']);
        });

        Schema::table('tb_rh_perubahan_detail', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['verification_status', 'rejection_reason', 'verified_at', 'verified_by']);
        });
    }
};
