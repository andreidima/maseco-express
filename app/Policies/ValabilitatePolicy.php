<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Valabilitate;

class ValabilitatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('valabilitati');
    }

    public function view(User $user, Valabilitate $valabilitate): bool
    {
        return $user->hasPermission('valabilitati');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('valabilitati');
    }

    public function update(User $user, Valabilitate $valabilitate): bool
    {
        return $user->hasPermission('valabilitati');
    }

    public function delete(User $user, Valabilitate $valabilitate): bool
    {
        return $user->hasPermission('valabilitati');
    }
}
