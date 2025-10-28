<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rps extends Model
{
    protected $fillable = [
        'lecturer_id',
        'class_section_id',
        'title',
        'description',
        'status'
    ];

    public function lecturer()
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function classSection()
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function plos()
{
    return $this->hasMany(RpsPlo::class);
}

public function outcomes()
{
    return $this->hasMany(RpsOutcome::class);
}

public function subClos()
{
    return $this->hasManyThrough(
        RpsSubClo::class,
        RpsOutcome::class,
        'rps_id',      // FK di rps_outcomes mengarah ke rps.id
        'outcome_id',  // FK di rps_sub_clos mengarah ke rps_outcomes.id
        'id',          // PK di rps
        'id'           // PK di rps_outcomes
    );
}
public function assessments()
{
    return $this->hasMany(RpsAssessment::class);
}
public function learningMaterials()
{
    return $this->hasMany(RpsLearningMaterial::class);
}

public function planners()
{
    return $this->hasMany(\App\Models\RpsPlanner::class, 'rps_id');
}

public function contract()
{
    return $this->hasOne(RpsContract::class);
}
public function reviews()
{
    return $this->hasMany(Review::class);
}

public function approvals()
{
    return $this->hasMany(Approval::class);
}

public function logs()
{
    return $this->hasMany(ActivityLog::class);
}

    // nanti ada relasi ke outcomes, planner, contract, dll.
}
