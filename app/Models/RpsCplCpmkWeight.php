<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpsCplCpmkWeight extends Model
{
    protected $guarded = [];

    public function rps()
    {
        return $this->belongsTo(Rps::class);
    }

    public function plo()
    {
        return $this->belongsTo(RpsPlo::class, 'plo_id');
    }

    public function outcome()
    {
        return $this->belongsTo(RpsOutcome::class, 'outcome_id');
    }
}
