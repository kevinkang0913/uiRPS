<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpsPlanner extends Model
{
    protected $fillable = ['rps_id', 'week', 'topic', 'method', 'assessment', 'learning_material_id'];

    public function rps()
    {
        return $this->belongsTo(Rps::class);
    }

    public function material()
    {
        return $this->belongsTo(RpsLearningMaterial::class, 'learning_material_id');
    }
}
