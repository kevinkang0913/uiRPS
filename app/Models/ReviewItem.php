<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewItem extends Model
{
    protected $fillable = [
        'review_id','criterion_key','criterion_label','weight',
        'level_index','level_label','level_score','weighted_score','notes'
    ];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }
}
