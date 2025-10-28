<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpsPlo extends Model
{
    protected $fillable = ['rps_id', 'description'];

    public function rps()
    {
        return $this->belongsTo(Rps::class);
    }

    // Biarkan nama method 'clos' agar kompatibel dengan controller yang sudah ada
    public function clos()
    {
        // 👉 arahkan ke RpsOutcome (tabel: rps_outcomes)
        return $this->hasMany(RpsOutcome::class, 'plo_id');
    }
}
