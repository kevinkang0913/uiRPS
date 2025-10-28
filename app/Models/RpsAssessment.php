<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpsAssessment extends Model
{
    protected $fillable = ['rps_id', 'sub_clo_id', 'type', 'weight'];

    public function rps()
    {
        return $this->belongsTo(Rps::class);
    }

    public function subClo()
    {
        return $this->belongsTo(RpsSubClo::class, 'sub_clo_id');
    }
}
