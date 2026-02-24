<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Tier;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TierPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $authUser): bool
    {
        return $authUser->can('ViewAny:Tier');
    }

    public function view(User $authUser, Tier $tier): bool
    {
        return $authUser->can('View:Tier');
    }

    public function create(User $authUser): bool
    {
        return $authUser->can('Create:Tier');
    }

    public function update(User $authUser, Tier $tier): bool
    {
        return $authUser->can('Update:Tier');
    }

    public function delete(User $authUser, Tier $tier): bool
    {
        return $authUser->can('Delete:Tier');
    }

    public function restore(User $authUser, Tier $tier): bool
    {
        return $authUser->can('Restore:Tier');
    }

    public function forceDelete(User $authUser, Tier $tier): bool
    {
        return $authUser->can('ForceDelete:Tier');
    }

    public function restoreAny(User $authUser): bool
    {
        return $authUser->can('RestoreAny:Tier');
    }

    public function forceDeleteAny(User $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Tier');
    }

    public function replicate(User $authUser, Tier $tier): bool
    {
        return $authUser->can('Replicate:Tier');
    }

    public function reorder(User $authUser): bool
    {
        return $authUser->can('Reorder:Tier');
    }
}
