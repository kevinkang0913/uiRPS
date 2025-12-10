<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['rps_id','reviewer_id','comments','status'];

    public function rps() { return $this->belongsTo(Rps::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewer_id'); }
    public function items()
{
    return $this->hasMany(\App\Models\ReviewItem::class, 'review_id');
}

}

