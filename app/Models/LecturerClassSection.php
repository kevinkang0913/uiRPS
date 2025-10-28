<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LecturerClassSection extends Model
{
    use HasFactory;

    protected $table = 'lecturer_class_section';

    protected $fillable = ['lecturer_id', 'class_section_id'];
}
