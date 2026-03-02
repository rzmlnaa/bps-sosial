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
        Schema::table('dynamic_menus', function (Blueprint $table) {
            $table->string('type')->default('spreadsheet')->after('parent_id');
            $table->text('url')->nullable()->after('type');
            $table->text('embed_url')->nullable()->after('url');
            $table->json('meta')->nullable()->after('embed_url');
        });

        // Migrate existing data
        $menus = DB::table('dynamic_menus')->get();
        foreach ($menus as $menu) {
            $url = null;
            $embedUrl = null;
            $meta = [];

            if (!empty($menu->spreadsheet_id)) {
                $url = "https://docs.google.com/spreadsheets/d/{$menu->spreadsheet_id}";
                $embedUrlBase = "https://docs.google.com/spreadsheets/d/{$menu->spreadsheet_id}/htmlembed";
                $queryParams = [];

                if ($menu->sheet_mode === 'single' && $menu->gid !== null) {
                    $queryParams[] = "gid={$menu->gid}";
                    $queryParams[] = "single=true";
                } else {
                    $queryParams[] = "widget=true";
                    $queryParams[] = "headers=false";
                }

                $embedUrl = $embedUrlBase . (!empty($queryParams) ? "?" . implode("&", $queryParams) : "");

                $meta = [
                    'spreadsheet_id' => $menu->spreadsheet_id,
                    'gid' => $menu->gid,
                    'sheet_mode' => $menu->sheet_mode,
                ];
            }

            DB::table('dynamic_menus')->where('id', $menu->id)->update([
                'url' => $url,
                'embed_url' => $embedUrl,
                'meta' => json_encode($meta),
            ]);
        }

        Schema::table('dynamic_menus', function (Blueprint $table) {
            $table->dropColumn(['spreadsheet_id', 'gid', 'sheet_mode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dynamic_menus', function (Blueprint $table) {
            $table->string('spreadsheet_id')->nullable();
            $table->string('gid')->nullable();
            $table->enum('sheet_mode', ['single', 'all'])->nullable();
        });

        // Try to revert data
        $menus = DB::table('dynamic_menus')->get();
        foreach ($menus as $menu) {
            $meta = json_decode($menu->meta, true);
            DB::table('dynamic_menus')->where('id', $menu->id)->update([
                'spreadsheet_id' => $meta['spreadsheet_id'] ?? null,
                'gid' => $meta['gid'] ?? null,
                'sheet_mode' => $meta['sheet_mode'] ?? null,
            ]);
        }

        Schema::table('dynamic_menus', function (Blueprint $table) {
            $table->dropColumn(['type', 'url', 'embed_url', 'meta']);
        });
    }
};
