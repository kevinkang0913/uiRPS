<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpsWeeklyActivity extends Model
{
    protected $table = 'rps_weekly_activities';

    protected $fillable = [
        'weekly_plan_id',
        'mode',        // 'in' atau 'online'
        'type',        // KM / PB / PT
        'duration',    // teks bebas, mis. "2×50'"
        'description', // teks aktivitas
        'order_no',
    ];

    public function weeklyPlan()
    {
        return $this->belongsTo(RpsWeeklyPlan::class, 'weekly_plan_id');
    }
}
