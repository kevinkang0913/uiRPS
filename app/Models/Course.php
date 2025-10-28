<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $guarded = []; 
    // atau: protected $fillable = ['program_id','code','course_id','catalog_nbr','name'];

    public function program() { 
        return $this->belongsTo(\App\Models\Program::class); 
    }
}
