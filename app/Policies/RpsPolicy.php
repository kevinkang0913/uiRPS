<?php

namespace App\Policies;

use App\Models\Rps;
use App\Models\User;

class RpsPolicy
{
    public function viewAny(User $user): bool
    {
        // semua role yang punya akses menu RPS list
        return $user->hasAnyRole(['Super Admin','CTL','Admin','Kaprodi','Dosen']);
    }

    public function view(User $user, Rps $rps): bool
    {
        if ($user->hasAnyRole(['Super Admin','CTL'])) {
            return true;
        }

        // Admin Fakultas: hanya faculty dia
        if ($user->hasRole('Admin')) {
            return $user->faculty_id
                && optional($rps->course?->program)->faculty_id === $user->faculty_id;
        }

        // Kaprodi: hanya program dia
        if ($user->hasRole('Kaprodi')) {
            return $user->program_id
                && $rps->course?->program_id === $user->program_id;
        }

        // Dosen: hanya course yang assigned ke dia
        if ($user->hasRole('Dosen')) {
            return $user->coursesTaught()
                ->where('courses.id', $rps->course_id)
                ->exists();
        }

        return false;
    }

    public function update(User $user, Rps $rps): bool
    {
        // hanya dosen assigned yang can_edit
        if (! $user->hasRole('Dosen')) {
            return false;
        }

        return $user->coursesTaught()
            ->where('courses.id', $rps->course_id)
            ->wherePivot('can_edit', 1)
            ->exists();
    }

    public function submit(User $user, Rps $rps): bool
    {
        // contoh rule submit: hanya dosen editor + status tertentu
        if (! $this->update($user, $rps)) {
            return false;
        }

        return in_array($rps->status, ['draft','need_revision'], true);
    }
    public function clone(User $user, Rps $rps): bool
{
    // CTL tidak usah clone
    if ($user->hasAnyRole(['CTL'])) return false;

    // Admin fakultas boleh clone dalam faculty-nya
    if ($user->hasRole('Admin')) {
        return $user->faculty_id
            && optional($rps->course?->program)->faculty_id === $user->faculty_id;
    }

    // Kaprodi boleh clone dalam prodi-nya (optional)
    if ($user->hasRole('Kaprodi')) {
        return $user->program_id
            && $rps->course?->program_id === $user->program_id;
    }

    // Dosen boleh clone hanya kalau course assigned + can_edit
    if ($user->hasRole('Dosen')) {
        return $user->coursesTaught()
            ->where('courses.id', $rps->course_id)
            ->wherePivot('can_edit', 1)
            ->exists();
    }

    // Super Admin (sementara) — kalau kamu mau restrict nanti, tinggal false
    if ($user->hasRole('Super Admin')) return true;

    return false;
}

}
