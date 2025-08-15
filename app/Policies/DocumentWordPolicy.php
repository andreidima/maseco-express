<?php

namespace App\Policies;

use App\Models\DocumentWord;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DocumentWordPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DocumentWord $documentWord): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DocumentWord $documentWord): Response
    {
        // Check if the document has admin-only rights and the user isn't an admin
        if ($user->role !== 1 && $documentWord->nivel_acces === 1) {
            return Response::deny('Nu ai drepturi să modifici acest document.');
        }

        // Check if the record is locked by someone else
        if ($documentWord->locked_by !== null && $documentWord->locked_by !== $user->id) {
            // dd($documentWord->locked_by, $user->id);
            return Response::deny('Acest document este în lucru în acest moment de către alt operator.');
        }

        // If the record is either not locked or locked by the current user, allow the update
        return Response::allow();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DocumentWord $documentWord): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DocumentWord $documentWord): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DocumentWord $documentWord): bool
    {
        //
    }

    /**
     * User who can unlock the recods
     */
    public function unlock(User $user, DocumentWord $documentWord): Response
    {
        // List of user IDs allowed to unlock the record
        // 1 - Andrei Dima
        // 4 - Ciobanu Ionut
        $allowedUserIds = [1,4];

        if (! in_array($user->id, $allowedUserIds)) {
            return Response::deny('Nu ai dreptul de a debloca acest document');
        }

        return Response::allow();
    }
}
