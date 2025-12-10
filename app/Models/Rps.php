<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rps extends Model
{
    protected $guarded = [];
    protected $casts = ['lecturers' => 'array'];

    // Master
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function program(): BelongsTo { return $this->belongsTo(Program::class); }

    // Hierarki outcome
    public function plos(): HasMany
    {
        return $this->hasMany(RpsPlo::class, 'rps_id')->orderByCodeNumber();
    }

    public function outcomes(): HasMany
    {
        return $this->hasMany(RpsOutcome::class, 'rps_id')->orderByNoNumber();
    }

    public function outcomesFlat()
    {
        return $this->hasManyThrough(
            RpsOutcome::class,
            RpsPlo::class,
            'rps_id',   // FK di rps_plos -> rps.id
            'plo_id',   // FK di rps_outcomes -> rps_plos.id
            'id',
            'id'
        )->orderByNoNumber();
    }

    // Assessment
    public function assessmentMappings(): HasMany
    {
        return $this->hasMany(RpsAssessmentMapping::class, 'rps_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(RpsAssessment::class, 'rps_id')->orderBy('order_no');
    }
    public function references()
{
    return $this->hasMany(\App\Models\RpsReference::class, 'rps_id')
                ->orderBy('type')
                ->orderBy('order_no');
}
public function weeklyPlans()
{ 
    return $this->hasMany(\App\Models\RpsWeeklyPlan::class)->orderBy('order_no'); 
}
public function evaluations()
{
    return $this->hasMany(\App\Models\RpsEvaluation::class)->orderBy('order_no');
}
public function contract()
{
    return $this->hasOne(RpsContract::class);
}
public function getStatusLabelAttribute()
{
    if ($this->status === 'approved') {
        return $this->is_reviewed_by_ctl
            ? 'Approved (sudah review CTL)'
            : 'Approved (tanpa review CTL)';
    }

    if ($this->status === 'submitted') {
        return $this->is_reviewed_by_ctl
            ? 'Menunggu approval Kaprodi (sudah review CTL)'
            : 'Menunggu proses (belum review CTL)';
    }

    if ($this->status === 'revision_required') {
        return 'Perlu revisi (menunggu perbaikan dosen)';
    }

    if ($this->status === 'revision_submitted') {
        return 'Revisi dikirim, menunggu proses berikutnya';
    }

    return ucfirst($this->status);
}
public function approvals()
{
    return $this->hasMany(Approval::class);
}

public function latestApproval()
{
    return $this->hasOne(Approval::class)->latestOfMany();
}
public function reviews()
{
    return $this->hasMany(Review::class);
}

}
