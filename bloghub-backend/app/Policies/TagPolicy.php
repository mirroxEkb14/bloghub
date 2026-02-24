<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TagPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $authUser): bool
    {
        return $authUser->can('ViewAny:Tag');
    }

    public function view(User $authUser, Tag $tag): bool
    {
        return $authUser->can('View:Tag');
    }

    public function create(User $authUser): bool
    {
        return $authUser->can('Create:Tag');
    }

    public function update(User $authUser, Tag $tag): bool
    {
        return $authUser->can('Update:Tag');
    }

    public function delete(User $authUser, Tag $tag): bool
    {
        return $authUser->can('Delete:Tag');
    }

    public function restore(User $authUser, Tag $tag): bool
    {
        return $authUser->can('Restore:Tag');
    }

    public function forceDelete(User $authUser, Tag $tag): bool
    {
        return $authUser->can('ForceDelete:Tag');
    }

    public function restoreAny(User $authUser): bool
    {
        return $authUser->can('RestoreAny:Tag');
    }

    public function forceDeleteAny(User $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Tag');
    }

    public function replicate(User $authUser, Tag $tag): bool
    {
        return $authUser->can('Replicate:Tag');
    }

    public function reorder(User $authUser): bool
    {
        return $authUser->can('Reorder:Tag');
    }
}
