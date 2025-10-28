<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpsLearningMaterial extends Model
{
    protected $fillable = ['rps_id', 'title', 'author', 'publisher', 'year', 'notes'];

    public function rps()
    {
        return $this->belongsTo(Rps::class);
    }

    public function planners()
    {
        return $this->hasMany(RpsPlanner::class, 'learning_material_id');
    }
}
