<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSection extends Model
{
    use HasFactory;

    protected $fillable = ['course_id','class_section','semester'];

    public function course() {
        return $this->belongsTo(Course::class);
    }
}
