<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\DynamicMenu;

class FrontendMenuController extends Controller
{
    public function show($slug)
    {
        $menu = DynamicMenu::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $title = "Administrator belum menyetel Spreadsheet untuk menu ini";
        $response = Http::get("https://docs.google.com/spreadsheets/d/{$menu->spreadsheet_id}");

        if ($response->successful()) {
            preg_match('/<title>(.*?)<\/title>/', $response->body(), $matches);
            $title = $matches[1] ?? null;
        }

        return view('dynamic_menus.show', compact('menu', 'title'));
    }
}
