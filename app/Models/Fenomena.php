<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fenomena extends Model
{
    use HasFactory;
    protected $fillable = [
        'tanggal_berita',
        'bulan',
        'tahun',
        'judul',
        'penjelasan',
        'link_berita',
        'sumber_berita_id',
        'status_verifikasi',
        'created_by',
        'verified_by',
        'verified_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->tanggal_berita) {
                $tanggal = \Carbon\Carbon::parse($model->tanggal_berita);
                $model->bulan = $tanggal->month;
                $model->tahun = $tanggal->year;
            }
        });
    }

    public function sumberBerita()
    {
        return $this->belongsTo(SumberBerita::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function sektors()
    {
        return $this->belongsToMany(SektorUsaha::class, 'fenomena_sektors')->withTimestamps();
    }

    public function jenisFenomenas()
    {
        return $this->belongsToMany(JenisFenomena::class, 'fenomena_jenis')->withTimestamps();
    }

    public function indikators()
    {
        return $this->belongsToMany(Indikator::class, 'fenomena_indikators')
            ->withPivot(['arah', 'ditetapkan_oleh', 'ditetapkan_at'])
            ->withTimestamps();
    }
}
