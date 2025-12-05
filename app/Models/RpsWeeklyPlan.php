<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpsWeeklyPlan extends Model
{
    protected $table = 'rps_weekly_plans';
    protected $guarded = [];

    public function rps()
    {
        return $this->belongsTo(Rps::class);
    }

    // relasi ke Sub-CPMK
    public function subClo()
    {
        return $this->belongsTo(RpsSubClo::class, 'sub_clo_id');
    }

    // relasi ke referensi (Step 4)
    public function reference()
    {
        return $this->belongsTo(RpsReference::class, 'reference_id');
    }

    /**
     * Legacy: relasi lama ke CPMK via pivot rps_weekly_plan_outcomes.
     * Biarin saja supaya tidak merusak fitur lain yg mungkin masih pakai.
     */
    public function outcomes()
    {
        return $this->belongsToMany(
                RpsOutcome::class,
                'rps_weekly_plan_outcomes',
                'weekly_plan_id',
                'outcome_id'
            )
            ->withPivot('percent')
            ->withTimestamps();
    }
}
