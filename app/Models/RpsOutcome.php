<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpsOutcome extends Model
{
    // 👉 pastikan pakai tabel rps_outcomes
    protected $table = 'rps_outcomes';

    protected $fillable = ['rps_id', 'plo_id', 'clo'];

    public function rps()
    {
        return $this->belongsTo(Rps::class);
    }

    public function plo()
    {
        return $this->belongsTo(RpsPlo::class, 'plo_id');
    }

    public function subClos()
    {
        // 👉 FK di tabel rps_sub_clos adalah outcome_id
        return $this->hasMany(RpsSubClo::class, 'outcome_id');
    }
}
