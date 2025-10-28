<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'emplid',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relasi
    public function rps()
    {
        return $this->hasMany(Rps::class, 'lecturer_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'approver_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
    public function roles()
{
    return $this->belongsToMany(Role::class, 'role_user');
}

public function hasRole($role)
{
    return $this->roles()->where('name', $role)->exists();
}

}
