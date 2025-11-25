<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpsOutcome extends Model
{
    protected $guarded = [];

    public function plo(){ return $this->belongsTo(RpsPlo::class,'plo_id'); }
    public function subClos(){ return $this->hasMany(RpsSubClo::class,'outcome_id')->orderByNoNumber(); }

    public function scopeOrderByNoNumber($q)
    {
        return $q->orderByRaw("
            CASE WHEN rps_outcomes.no REGEXP '^[0-9]+$'
                 THEN CAST(rps_outcomes.no AS UNSIGNED)
                 ELSE 999999 END
        ")->orderBy('rps_outcomes.no');
    }
}
