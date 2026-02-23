<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubscriptionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $authUser): bool
    {
        return $authUser->can('ViewAny:Subscription');
    }

    public function view(User $authUser, Subscription $subscription): bool
    {
        return $authUser->can('View:Subscription');
    }

    public function create(User $authUser): bool
    {
        return $authUser->can('Create:Subscription');
    }

    public function update(User $authUser, Subscription $subscription): bool
    {
        return $authUser->can('Update:Subscription');
    }

    public function delete(User $authUser, Subscription $subscription): bool
    {
        return $authUser->can('Delete:Subscription');
    }

    public function restore(User $authUser, Subscription $subscription): bool
    {
        return $authUser->can('Restore:Subscription');
    }

    public function forceDelete(User $authUser, Subscription $subscription): bool
    {
        return $authUser->can('ForceDelete:Subscription');
    }

    public function restoreAny(User $authUser): bool
    {
        return $authUser->can('RestoreAny:Subscription');
    }

    public function forceDeleteAny(User $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Subscription');
    }

    public function replicate(User $authUser, Subscription $subscription): bool
    {
        return $authUser->can('Replicate:Subscription');
    }

    public function reorder(User $authUser): bool
    {
        return $authUser->can('Reorder:Subscription');
    }
}
