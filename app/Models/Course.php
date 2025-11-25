<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    // kalau pakai guarded silakan
    protected $guarded = [];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    // ✅ relasi yang hilang
    public function classSections(): HasMany
    {
        // asumsi: table = class_sections, FK = course_id
        return $this->hasMany(ClassSection::class, 'course_id', 'id');
    }
}
