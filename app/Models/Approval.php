<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = ['rps_id','approver_id','status','notes'];

    public function rps() { return $this->belongsTo(Rps::class); }
    public function approver() { return $this->belongsTo(User::class, 'approver_id'); }
}

