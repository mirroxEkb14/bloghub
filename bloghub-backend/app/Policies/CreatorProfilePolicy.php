<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CreatorProfile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CreatorProfilePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $authUser): bool
    {
        return $authUser->can('ViewAny:CreatorProfile');
    }

    public function view(User $authUser, CreatorProfile $creatorProfile): bool
    {
        return $authUser->can('View:CreatorProfile');
    }

    public function create(User $authUser): bool
    {
        return $authUser->can('Create:CreatorProfile');
    }

    public function update(User $authUser, CreatorProfile $creatorProfile): bool
    {
        return $authUser->can('Update:CreatorProfile');
    }

    public function delete(User $authUser, CreatorProfile $creatorProfile): bool
    {
        return $authUser->can('Delete:CreatorProfile');
    }

    public function restore(User $authUser, CreatorProfile $creatorProfile): bool
    {
        return $authUser->can('Restore:CreatorProfile');
    }

    public function forceDelete(User $authUser, CreatorProfile $creatorProfile): bool
    {
        return $authUser->can('ForceDelete:CreatorProfile');
    }

    public function restoreAny(User $authUser): bool
    {
        return $authUser->can('RestoreAny:CreatorProfile');
    }

    public function forceDeleteAny(User $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CreatorProfile');
    }

    public function replicate(User $authUser, CreatorProfile $creatorProfile): bool
    {
        return $authUser->can('Replicate:CreatorProfile');
    }

    public function reorder(User $authUser): bool
    {
        return $authUser->can('Reorder:CreatorProfile');
    }
}
