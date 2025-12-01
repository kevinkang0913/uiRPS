<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $guarded = [];

    public function users(): BelongsToMany
    {
        // pivot: role_user (user_id, role_id)
        return $this->belongsToMany(User::class);
    }
}
