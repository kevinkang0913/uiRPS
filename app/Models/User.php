<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',    // legacy, boleh dibiarkan dulu walau nanti kita pakai tabel roles
        'emplid',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ========== Relasi RPS / Review / Approval ==========
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

    // ========== ROLE SYSTEM (pakai tabel roles + role_user) ==========

    public function roles(): BelongsToMany
    {
        // pivot: role_user (user_id, role_id)
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Cek single role, case-insensitive.
     *
     * Usage:
     *   auth()->user()->hasRole('CTL')
     */
    public function hasRole(string $role): bool
    {
        // pakai collection yg sudah di-load, bukan query ulang ke DB tiap kali
        return $this->roles->contains(function ($r) use ($role) {
            return strcasecmp($r->name, $role) === 0;
        });
    }

    /**
     * Cek apakah user punya salah satu dari beberapa role.
     *
     * Usage:
     *   auth()->user()->hasAnyRole(['CTL', 'Kaprodi'])
     */
    public function hasAnyRole(array $roles): bool
    {
        $lower = array_map('strtolower', $roles);

        return $this->roles->contains(function ($r) use ($lower) {
            return in_array(strtolower($r->name), $lower, true);
        });
    }
    // app/Models/User.php

    public function faculty()
    {
        return $this->belongsTo(\App\Models\Faculty::class);
    }

    public function isFacultyAdmin(): bool
    {
        // admin biasa, punya faculty_id, tapi bukan Super Admin
        return $this->hasRole('Admin')
            && ! $this->hasRole('Super Admin')
            && ! is_null($this->faculty_id);
    }
public function isProgramScoped(): bool
{
    return ! is_null($this->program_id);
}
public function program()
{
    return $this->belongsTo(\App\Models\Program::class);
}
}
