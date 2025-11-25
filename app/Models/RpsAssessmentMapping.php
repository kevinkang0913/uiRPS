<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpsAssessmentMapping extends Model {
    protected $fillable = ['rps_id','assessment_category_id','outcome_id','percent'];
    public function rps(){ return $this->belongsTo(Rps::class); }
    public function category(){ return $this->belongsTo(RpsAssessmentCategory::class,'assessment_category_id'); }
    public function outcome(){ return $this->belongsTo(\App\Models\RpsOutcome::class,'outcome_id'); }
}