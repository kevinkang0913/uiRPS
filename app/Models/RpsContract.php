<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpsContract extends Model
{
    protected $fillable = [
        'rps_id',
        'attendance_policy',
        'participation_policy',
        'late_policy',
        'grading_policy',
        'extra_rules',
    ];

    public function rps()
    {
        return $this->belongsTo(Rps::class);
    }
}
