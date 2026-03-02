<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicMenu extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'type',
        'url',
        'embed_url',
        'meta',
        'order_number',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(DynamicMenu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(DynamicMenu::class, 'parent_id')->orderBy('order_number');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get a safe URL for embedding in an iframe.
     * Automatically converts standard Google Drive/Sheets links to embeddable ones.
     */
    public function getSafeEmbedUrlAttribute()
    {
        $url = $this->embed_url;

        if (empty($url)) {
            return $url;
        }

        // Handle Google Drive Folders
        if (preg_match('/drive\.google\.com\/drive\/folders\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
            return "https://drive.google.com/embeddedfolderview?id={$matches[1]}#list";
        }

        // Handle Google Drive Files (Preview mode)
        if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
            return "https://drive.google.com/file/d/{$matches[1]}/preview";
        }

        // Handle Google Sheets (Standard sharing to embed)
        if (preg_match('/docs\.google\.com\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
            // Check if it already has 'pubhtml' or 'embed'
            if (!str_contains($url, 'pubhtml') && !str_contains($url, 'embed')) {
                return "https://docs.google.com/spreadsheets/d/{$matches[1]}/pubhtml?widget=true&headers=false";
            }
        }

        return $url;
    }
}
