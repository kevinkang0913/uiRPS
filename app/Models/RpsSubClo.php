<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpsSubClo extends Model
{
    protected $guarded = [];

    public function outcome(){ return $this->belongsTo(RpsOutcome::class,'outcome_id'); }

    public function scopeOrderByNoNumber($q)
    {
        return $q->orderByRaw("
            CASE WHEN rps_sub_clos.no REGEXP '^[0-9]+$'
                 THEN CAST(rps_sub_clos.no AS UNSIGNED)
                 ELSE 999999 END
        ")->orderBy('rps_sub_clos.no');
    }
}
