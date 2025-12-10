<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'rps_id',
        'approver_id',
        'status',    // 'approved' atau 'rejected' (di DB kamu nanti kita mapping ke 'not_approved' di RPS)
        'notes',
    ];

    public function rps()
    {
        return $this->belongsTo(Rps::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
