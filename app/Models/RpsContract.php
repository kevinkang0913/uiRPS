<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpsContract extends Model
{
    protected $table = 'rps_contracts';
    protected $guarded = [];

    public function rps()
    {
        return $this->belongsTo(Rps::class);
    }
}
