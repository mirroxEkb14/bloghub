<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $authUser): bool
    {
        return $authUser->can('ViewAny:Payment');
    }

    public function view(User $authUser, Payment $payment): bool
    {
        return $authUser->can('View:Payment');
    }

    public function create(User $authUser): bool
    {
        return $authUser->can('Create:Payment');
    }

    public function update(User $authUser, Payment $payment): bool
    {
        return $authUser->can('Update:Payment');
    }

    public function delete(User $authUser, Payment $payment): bool
    {
        return $authUser->can('Delete:Payment');
    }

    public function restore(User $authUser, Payment $payment): bool
    {
        return $authUser->can('Restore:Payment');
    }

    public function forceDelete(User $authUser, Payment $payment): bool
    {
        return $authUser->can('ForceDelete:Payment');
    }

    public function restoreAny(User $authUser): bool
    {
        return $authUser->can('RestoreAny:Payment');
    }

    public function forceDeleteAny(User $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Payment');
    }

    public function replicate(User $authUser, Payment $payment): bool
    {
        return $authUser->can('Replicate:Payment');
    }

    public function reorder(User $authUser): bool
    {
        return $authUser->can('Reorder:Payment');
    }
}
