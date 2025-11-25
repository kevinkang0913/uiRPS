<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpsPlo extends Model
{
    protected $guarded = [];

    public function rps(){ return $this->belongsTo(Rps::class,'rps_id'); }
    public function outcomes(){ return $this->hasMany(RpsOutcome::class,'plo_id')->orderByNoNumber(); }

    // Urut CPL berdasarkan angka dalam code (mis. CPL-1, CPL-2)
    public function scopeOrderByCodeNumber($q)
    {
        return $q->orderByRaw("CAST(REGEXP_SUBSTR(code, '[0-9]+') AS UNSIGNED)")->orderBy('code');
    }
}
