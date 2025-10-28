<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $fillable = ['code','name'];

    public function programs()
    {
        return $this->hasMany(Program::class);
    }
}

