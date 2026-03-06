<?php

namespace Tests\Feature;

use App\Models\DynamicMenu;
use App\Models\User;
use App\Models\Kabupaten;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicMenuTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin()
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => 'active'
        ]);
    }

    private function createMember()
    {
        $kabupaten = Kabupaten::create([
            'kode_kab' => '6101',
            'nama_kabupaten' => 'Sambas'
        ]);

        return User::factory()->create([
            'kabupaten_id' => $kabupaten->id,
            'role' => 'member',
            'status' => 'active'
        ]);
    }

    public function test_admin_can_access_dynamic_menu_index()
    {
        $admin = $this->createAdmin();
        $response = $this->actingAs($admin)->get(route('admin.dynamic-menus.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_store_dynamic_menu()
    {
        $admin = $this->createAdmin();

        $data = [
            'name' => 'Test Folder Drive',
            'slug' => 'test-folder-drive',
            'type' => 'drive',
            'url' => 'https://drive.google.com/drive/folders/1L_cZu7S8LrDe94QTRDpgZo1FOqzpTaGW?usp=sharing',
            'order_number' => 1,
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)->post(route('admin.dynamic-menus.store'), $data);

        $response->assertRedirect(route('admin.dynamic-menus.index'));
        $this->assertDatabaseHas('dynamic_menus', [
            'name' => 'Test Folder Drive',
            'type' => 'drive'
        ]);

        $menu = DynamicMenu::first();
        // Check automatic embed_url generation in controller
        $this->assertStringContainsString('embeddedfolderview', $menu->embed_url);
        $this->assertStringContainsString('1L_cZu7S8LrDe94QTRDpgZo1FOqzpTaGW', $menu->embed_url);
    }

    public function test_safe_embed_url_accessor_converts_drive_folder()
    {
        $menu = new DynamicMenu([
            'type' => 'drive',
            'embed_url' => 'https://drive.google.com/drive/folders/FOLDER_ID?usp=sharing'
        ]);

        // This tests the accessor in the model
        $this->assertEquals(
            'https://drive.google.com/embeddedfolderview?id=FOLDER_ID#list',
            $menu->safe_embed_url
        );
    }

    public function test_safe_embed_url_accessor_converts_drive_file()
    {
        $menu = new DynamicMenu([
            'type' => 'drive',
            'embed_url' => 'https://drive.google.com/file/d/FILE_ID/view?usp=sharing'
        ]);

        $this->assertEquals(
            'https://drive.google.com/file/d/FILE_ID/preview',
            $menu->safe_embed_url
        );
    }

    public function test_safe_embed_url_accessor_converts_google_sheets()
    {
        $menu = new DynamicMenu([
            'type' => 'spreadsheet',
            'embed_url' => 'https://docs.google.com/spreadsheets/d/SHEET_ID/edit?usp=sharing'
        ]);

        $this->assertStringContainsString('pubhtml', $menu->safe_embed_url);
        $this->assertStringContainsString('SHEET_ID', $menu->safe_embed_url);
    }

    public function test_user_can_view_dynamic_menu_content()
    {
        $user = $this->createMember();
        $menu = DynamicMenu::create([
            'name' => 'Manual Drive',
            'slug' => 'manual-drive',
            'type' => 'drive',
            'url' => 'https://drive.google.com/drive/folders/123',
            'embed_url' => 'https://drive.google.com/drive/folders/123', // purposely raw
            'order_number' => 1,
            'is_active' => true,
            'created_by' => $user->id
        ]);

        $response = $this->actingAs($user)->get(route('dynamic-menu.show', $menu->slug));

        $response->assertStatus(200);
        // Ensure the safe_embed_url is used in the view
        $response->assertSee('https://drive.google.com/embeddedfolderview?id=123#list', false);
    }

    public function test_external_menu_shows_button_not_iframe()
    {
        $user = $this->createMember();
        $menu = DynamicMenu::create([
            'name' => 'External Link',
            'slug' => 'external-link',
            'type' => 'external',
            'url' => 'https://google.com',
            'embed_url' => 'https://google.com', // even if filled
            'order_number' => 1,
            'is_active' => true,
            'created_by' => $user->id
        ]);

        $response = $this->actingAs($user)->get(route('dynamic-menu.show', $menu->slug));

        $response->assertStatus(200);
        $response->assertSee('Buka External Link');
        $response->assertDontSee('<iframe');
    }

    public function test_admin_can_delete_dynamic_menu()
    {
        $admin = $this->createAdmin();
        $menu = DynamicMenu::create([
            'name' => 'To Delete',
            'slug' => 'delete-me',
            'type' => 'external',
            'url' => 'https://example.com',
            'order_number' => 1,
            'created_by' => $admin->id
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.dynamic-menus.destroy', $menu->id));

        $response->assertRedirect(route('admin.dynamic-menus.index'));
        $this->assertDatabaseMissing('dynamic_menus', ['id' => $menu->id]);
    }
}
