<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpsReference extends Model
{
    protected $table = 'rps_references';  // pastikan sesuai dengan nama tabel
    protected $guarded = [];              // biar mass assignment bisa langsung

    // relasi ke RPS induk
    public function rps()
    {
        return $this->belongsTo(Rps::class);
    }

    // helper opsional kalau nanti mau grouping
    public function scopeUtama($q) { return $q->where('type', 'utama'); }
    public function scopePendukung($q) { return $q->where('type', 'pendukung'); }
}
