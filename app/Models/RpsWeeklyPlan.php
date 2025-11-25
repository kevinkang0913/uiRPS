<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpsWeeklyPlan extends Model {
    protected $guarded = [];
    public function rps(){ return $this->belongsTo(Rps::class); }
    public function outcomes(){ return $this->belongsToMany(RpsOutcome::class, 'rps_weekly_plan_outcomes', 'weekly_plan_id', 'outcome_id')->withPivot('percent'); }
}
