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
        // 1. Add rh_tahun_id to tb_rh_perubahan_detail
        Schema::table('tb_rh_perubahan_detail', function (Blueprint $table) {
            $table->foreignId('rh_tahun_id')->nullable()->after('id')->constrained('tb_rh_tahun')->onDelete('cascade');
        });

        // 2. Make rh_perubahan_header_id nullable
        Schema::table('tb_rh_perubahan_detail', function (Blueprint $table) {
            $table->foreignId('rh_perubahan_header_id')->nullable()->change();
        });

        // 3. Migrate Data: Populate rh_tahun_id for existing revisions
        // We join with header to get the year
        $updates = DB::table('tb_rh_perubahan_detail')
            ->join('tb_rh_perubahan_header', 'tb_rh_perubahan_detail.rh_perubahan_header_id', '=', 'tb_rh_perubahan_header.id')
            ->select('tb_rh_perubahan_detail.id', 'tb_rh_perubahan_header.rh_tahun_id')
            ->get();

        foreach ($updates as $update) {
            DB::table('tb_rh_perubahan_detail')
                ->where('id', $update->id)
                ->update(['rh_tahun_id' => $update->rh_tahun_id]);
        }

        // 4. Migrate Data: Move Masternilai to Perubahan Detail
        $masters = DB::table('tb_rh_master_nilai')->get();
        foreach ($masters as $m) {
            DB::table('tb_rh_perubahan_detail')->insert([
                'rh_tahun_id' => $m->rh_tahun_id,
                'rh_perubahan_header_id' => null, // This marks it as Master
                'kabupaten_id' => $m->kabupaten_id,
                'komoditas_id' => $m->komoditas_id,
                'min_edit' => $m->min_nilai,
                'max_edit' => $m->max_nilai,
                'alasan' => $m->alasan,
                'user_id_add' => $m->user_id_add,
                'verification_status' => $m->verification_status,
                'rejection_reason' => $m->rejection_reason,
                'verified_at' => $m->verified_at,
                'verified_by' => $m->verified_by,
                'created_at' => $m->created_at,
                'updated_at' => $m->updated_at,
            ]);
        }

        // 5. Drop old table
        Schema::dropIfExists('tb_rh_master_nilai');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate Master Table
        Schema::create('tb_rh_master_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rh_tahun_id')->constrained('tb_rh_tahun')->onDelete('cascade');
            $table->foreignId('kabupaten_id')->constrained('tb_kabupaten')->onDelete('cascade');
            $table->foreignId('komoditas_id')->constrained('tb_komoditas')->onDelete('cascade');
            $table->decimal('min_nilai', 15, 2)->nullable();
            $table->decimal('max_nilai', 15, 2)->nullable();
            $table->foreignId('user_id_add')->constrained('users');
            $table->text('alasan')->nullable();
            $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // Move Master data back
        $masters = DB::table('tb_rh_perubahan_detail')->whereNull('rh_perubahan_header_id')->get();
        foreach ($masters as $m) {
            DB::table('tb_rh_master_nilai')->insert([
                'rh_tahun_id' => $m->rh_tahun_id,
                'kabupaten_id' => $m->kabupaten_id,
                'komoditas_id' => $m->komoditas_id,
                'min_nilai' => $m->min_edit,
                'max_nilai' => $m->max_edit,
                'alasan' => $m->alasan,
                'user_id_add' => $m->user_id_add,
                'verification_status' => $m->verification_status,
                'rejection_reason' => $m->rejection_reason,
                'verified_at' => $m->verified_at,
                'verified_by' => $m->verified_by,
                'created_at' => $m->created_at,
                'updated_at' => $m->updated_at,
            ]);
        }

        // Delete Master data from detail
        DB::table('tb_rh_perubahan_detail')->whereNull('rh_perubahan_header_id')->delete();

        // Remove rh_tahun_id column
        Schema::table('tb_rh_perubahan_detail', function (Blueprint $table) {
            $table->dropForeign(['rh_tahun_id']);
            $table->dropColumn('rh_tahun_id');
            // Revert header id to non-nullable (careful if data has nulls? No we deleted them above)
            $table->foreignId('rh_perubahan_header_id')->nullable(false)->change();
        });
    }
};
