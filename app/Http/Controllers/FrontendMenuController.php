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

        $title = null;

        if ($menu->type === 'spreadsheet' && !empty($menu->url)) {
            $response = Http::get($menu->url);

            if ($response->successful()) {
                if (preg_match('/<title>(.*?)<\/title>/', $response->body(), $matches)) {
                    $title = $matches[1] ?? null;

                    if ($title) {
                        $title = str_replace([' - Google Sheets', ' - Google Spreadshet'], '', $title);
                    }
                } else {
                    $title = null;
                }
            }
        }
        return view('dynamic_menus.show', compact('menu', 'title'));
    }
}
