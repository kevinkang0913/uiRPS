<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpsEvaluation extends Model
{
    protected $guarded = [];

    public function rps() { return $this->belongsTo(Rps::class); }
    public function category() { return $this->belongsTo(RpsAssessmentCategory::class, 'assessment_category_id'); }
}
