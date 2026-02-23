<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DynamicMenu;
use Illuminate\Support\Str;

class DynamicMenuController extends Controller
{
    public function index()
    {
        $menus = DynamicMenu::with('parent', 'creator')->orderBy('order_number')->get();
        return view('admin.dynamic_menus.index', compact('menus'));
    }

    public function create()
    {
        $parents = DynamicMenu::whereNull('parent_id')->get();
        return view('admin.dynamic_menus.create', compact('parents'));
    }

    public function store(Request $request)
    {
        if ($request->has('slug')) {
            $request->merge(['slug' => Str::slug($request->slug)]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:dynamic_menus',
            'parent_id' => 'nullable|exists:dynamic_menus,id',
            'spreadsheet_id' => 'nullable|string',
            'gid' => 'nullable|string',
            'sheet_mode' => 'nullable|in:single,all',
            'order_number' => 'required|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['created_by'] = auth()->id();

        DynamicMenu::create($validated);

        return redirect()->route('admin.dynamic-menus.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(DynamicMenu $dynamicMenu)
    {
        $parents = DynamicMenu::whereNull('parent_id')->where('id', '!=', $dynamicMenu->id)->get();
        return view('admin.dynamic_menus.edit', compact('dynamicMenu', 'parents'));
    }

    public function update(Request $request, DynamicMenu $dynamicMenu)
    {
        if ($request->has('slug')) {
            $request->merge(['slug' => Str::slug($request->slug)]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:dynamic_menus,slug,' . $dynamicMenu->id,
            'parent_id' => 'nullable|exists:dynamic_menus,id',
            'spreadsheet_id' => 'nullable|string',
            'gid' => 'nullable|string',
            'sheet_mode' => 'nullable|in:single,all',
            'order_number' => 'required|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $dynamicMenu->update($validated);

        return redirect()->route('admin.dynamic-menus.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(DynamicMenu $dynamicMenu)
    {
        $dynamicMenu->delete();
        return redirect()->route('admin.dynamic-menus.index')->with('success', 'Menu berhasil dihapus.');
    }
}
