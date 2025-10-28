<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['rps_id','user_id','action','notes'];

    public function rps() { return $this->belongsTo(Rps::class); }
    public function user() { return $this->belongsTo(User::class); }
}

