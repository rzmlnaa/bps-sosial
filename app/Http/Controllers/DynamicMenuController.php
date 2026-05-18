<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DynamicMenu;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
            'type' => [Rule::requiredIf($request->filled('parent_id')), 'nullable', 'string', 'in:youtube,drive,spreadsheet,external,main_menu'],
            'url' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->filled('parent_id') && $request->type !== 'external' && empty($value)) {
                        $fail('URL wajib diisi jika bukan bertipe Link Eksternal.');
                    }
                    if ($request->type === 'external' && empty($value) && (empty($request->links) || count($request->links) === 0)) {
                        $fail('URL utama atau minimal satu link di daftar link wajib diisi untuk tipe External.');
                    }
                }
            ],
            'embed_url' => 'nullable|string',
        ]);

        $validated['parent_id'] = $validated['parent_id'] ?? null;
        $validated['order_number'] = DynamicMenu::where('parent_id', $validated['parent_id'])->max('order_number') + 1;

        // If parent_id is null and type is empty, set type to 'main_menu'
        if (empty($validated['parent_id']) && empty($validated['type'])) {
            $validated['type'] = 'main_menu';
        }

        // Strict YouTube/Spreadsheet/Drive URL Validation (Only if URL is provided)
        if (!empty($validated['url'])) {
            if ($validated['type'] === 'youtube') {
                if (!preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/||.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $validated['url'])) {
                    return back()->withErrors(['url' => 'Format file URL YouTube tidak valid. Harap masukkan link youtube.com atau youtu.be yang benar.'])->withInput();
                }
            } elseif ($validated['type'] === 'spreadsheet') {
                if (!str_contains($validated['url'], 'docs.google.com/spreadsheets')) {
                    return back()->withErrors(['url' => 'Format URL Spreadsheet tidak valid. Harap masukkan link docs.google.com/spreadsheets yang benar.'])->withInput();
                }
            } elseif ($validated['type'] === 'drive') {
                if (!str_contains($validated['url'], 'drive.google.com')) {
                    return back()->withErrors(['url' => 'Format URL Google Drive tidak valid. Harap masukkan link drive.google.com yang benar.'])->withInput();
                }
            }
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['created_by'] = auth()->id();

        // Handle meta based on type
        $meta = [];
        if (!empty($validated['url']) && $validated['type'] === 'spreadsheet') {
            $meta['gid'] = $request->gid;
            $meta['sheet_mode'] = $request->sheet_mode;

            if (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $validated['url'], $matches)) {
                $meta['spreadsheet_id'] = $matches[1];
            }
        }

        if ($validated['type'] === 'external' && $request->has('links')) {
            $meta['links'] = $request->links;
        }

        $validated['meta'] = $meta;

        // Custom Validation for Duplicate URL (Only if URL is provided)
        if (!empty($validated['url'])) {
            $existing = DynamicMenu::where('url', $validated['url'])->get();
            foreach ($existing as $item) {
                if ($validated['type'] !== 'spreadsheet' || $item->type !== 'spreadsheet') {
                    return back()->withErrors(['url' => 'URL ini sudah digunakan oleh menu ' . $item->name . '.'])->withInput();
                }

                // Both are spreadsheets, check GID
                $existingMeta = is_array($item->meta) ? $item->meta : json_decode($item->meta ?? '[]', true);
                if (($existingMeta['gid'] ?? '') == ($meta['gid'] ?? '')) {
                    return back()->withErrors(['url' => 'URL Spreadsheet dengan GID yang sama sudah digunakan oleh menu ' . $item->name . '.'])->withInput();
                }
            }
        }

        // Auto-generate embed_url if empty (Only if URL is provided)
        if (!empty($validated['url']) && empty($validated['embed_url'])) {
            $validated['embed_url'] = $this->generateEmbedUrl($validated['type'], $validated['url'], $meta);
        }

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
            'type' => [Rule::requiredIf($request->filled('parent_id')), 'nullable', 'string', 'in:youtube,drive,spreadsheet,external,main_menu'],
            'url' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->filled('parent_id') && $request->type !== 'external' && empty($value)) {
                        $fail('URL wajib diisi jika bukan bertipe Link Eksternal.');
                    }
                    if ($request->type === 'external' && empty($value) && (empty($request->links) || count($request->links) === 0)) {
                        $fail('URL utama atau minimal satu link di daftar link wajib diisi untuk tipe External.');
                    }
                }
            ],
            'embed_url' => 'nullable|string',
        ]);

        $validated['parent_id'] = $validated['parent_id'] ?? null;
        if ($validated['parent_id'] != $dynamicMenu->parent_id) {
            $validated['order_number'] = DynamicMenu::where('parent_id', $validated['parent_id'])->max('order_number') + 1;
        }

        // If parent_id is null and type is empty, set type to 'main_menu'
        if (empty($validated['parent_id']) && empty($validated['type'])) {
            $validated['type'] = 'main_menu';
        }

        // Strict YouTube/Spreadsheet/Drive URL Validation (Only if URL is provided)
        if (!empty($validated['url'])) {
            if ($validated['type'] === 'youtube') {
                if (!preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $validated['url'])) {
                    return back()->withErrors(['url' => 'Format file URL YouTube tidak valid. Harap masukkan link youtube.com atau youtu.be yang benar.'])->withInput();
                }
            } elseif ($validated['type'] === 'spreadsheet') {
                if (!str_contains($validated['url'], 'docs.google.com/spreadsheets')) {
                    return back()->withErrors(['url' => 'Format URL Spreadsheet tidak valid. Harap masukkan link docs.google.com/spreadsheets yang benar.'])->withInput();
                }
            } elseif ($validated['type'] === 'drive') {
                if (!str_contains($validated['url'], 'drive.google.com')) {
                    return back()->withErrors(['url' => 'Format URL Google Drive tidak valid. Harap masukkan link drive.google.com yang benar.'])->withInput();
                }
            }
        }

        $validated['is_active'] = $request->has('is_active');

        // Handle meta based on type
        $meta = [];
        if (!empty($validated['url']) && $validated['type'] === 'spreadsheet') {
            $meta['gid'] = $request->gid;
            $meta['sheet_mode'] = $request->sheet_mode;

            if (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $validated['url'], $matches)) {
                $meta['spreadsheet_id'] = $matches[1];
            }
        }

        if ($validated['type'] === 'external' && $request->has('links')) {
            $meta['links'] = $request->links;
        }

        $validated['meta'] = $meta;

        // Custom Validation for Duplicate URL (Only if URL is provided)
        if (!empty($validated['url'])) {
            $existing = DynamicMenu::where('url', $validated['url'])
                ->where('id', '!=', $dynamicMenu->id)
                ->get();
            foreach ($existing as $item) {
                if ($validated['type'] !== 'spreadsheet' || $item->type !== 'spreadsheet') {
                    return back()->withErrors(['url' => 'URL ini sudah digunakan oleh menu "' . $item->name . '".'])->withInput();
                }

                // Both are spreadsheets, check GID
                $existingMeta = is_array($item->meta) ? $item->meta : json_decode($item->meta ?? '[]', true);
                if (($existingMeta['gid'] ?? '') == ($meta['gid'] ?? '')) {
                    return back()->withErrors(['url' => 'URL Spreadsheet dengan GID yang sama sudah digunakan oleh menu "' . $item->name . '".'])->withInput();
                }
            }
        }

        // Auto-generate embed_url if empty or if url changed (Only if URL is provided)
        if (!empty($validated['url'])) {
            if (empty($validated['embed_url']) || $validated['url'] !== $dynamicMenu->url || $validated['type'] !== $dynamicMenu->type || $validated['type'] === 'spreadsheet') {
                // For spreadsheet we always regenerate to catch gid/mode changes
                $validated['embed_url'] = $this->generateEmbedUrl($validated['type'], $validated['url'], $meta);
            }
        } else {
            $validated['embed_url'] = null;
        }

        $dynamicMenu->update($validated);

        return redirect()->route('admin.dynamic-menus.index')->with('success', 'Menu berhasil diperbarui.');
    }

    private function generateEmbedUrl($type, $url, $meta = [])
    {
        if ($type === 'youtube') {
            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
                return "https://www.youtube.com/embed/" . $matches[1];
            }
        } elseif ($type === 'spreadsheet') {
            if (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
                $id = $matches[1];
                $embedUrl = "https://docs.google.com/spreadsheets/d/{$id}/htmlembed";
                $queryParams = [];
                if (($meta['sheet_mode'] ?? null) === 'single' && ($meta['gid'] ?? null) !== null) {
                    $queryParams[] = "gid={$meta['gid']}";
                    $queryParams[] = "single=true";
                } else {
                    $queryParams[] = "widget=true";
                    $queryParams[] = "headers=false";
                }
                return $embedUrl . (!empty($queryParams) ? "?" . implode("&", $queryParams) : "");
            }
        } elseif ($type === 'drive') {
            // Google Drive Folders
            if (preg_match('/drive\.google\.com\/drive\/folders\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
                return "https://drive.google.com/embeddedfolderview?id={$matches[1]}#list";
            }

            // Google Drive Files
            if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
                return "https://drive.google.com/file/d/{$matches[1]}/preview";
            }

            if (str_contains($url, 'view?usp=sharing')) {
                return str_replace('view?usp=sharing', 'preview', $url);
            }
            if (str_contains($url, '/view')) {
                return str_replace('/view', '/preview', $url);
            }
        }
        return $url;
    }

    public function destroy(DynamicMenu $dynamicMenu)
    {
        $dynamicMenu->delete();
        return redirect()->route('admin.dynamic-menus.index')->with('success', 'Menu berhasil dihapus.');
    }

    public function reorder(Request $request)
    {

        // Antar Menu Utama (Parent dengan Parent) angka order_number tidak boleh sama
        // Antar Sub-Menu di dalam satu Menu Utama yang SAMA order_number tidak boleh sama

        // Antar Sub-Menu di Menu Utama yang BEDA order_number boleh sama
        // Antara Menu Utama dan Sub-Menu order_number boleh sama
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:dynamic_menus,id',
            'order.*.order_number' => 'required|integer'
        ]);

        foreach ($request->order as $item) {
            DynamicMenu::where('id', $item['id'])->update(['order_number' => $item['order_number']]);
        }

        return response()->json(['success' => true]);
    }
}
