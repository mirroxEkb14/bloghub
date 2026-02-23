<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $authUser): bool
    {
        return $authUser->can('ViewAny:Post');
    }

    public function view(User $authUser, Post $post): bool
    {
        return $authUser->can('View:Post');
    }

    public function create(User $authUser): bool
    {
        return $authUser->can('Create:Post');
    }

    public function update(User $authUser, Post $post): bool
    {
        return $authUser->can('Update:Post');
    }

    public function delete(User $authUser, Post $post): bool
    {
        return $authUser->can('Delete:Post');
    }

    public function restore(User $authUser, Post $post): bool
    {
        return $authUser->can('Restore:Post');
    }

    public function forceDelete(User $authUser, Post $post): bool
    {
        return $authUser->can('ForceDelete:Post');
    }

    public function restoreAny(User $authUser): bool
    {
        return $authUser->can('RestoreAny:Post');
    }

    public function forceDeleteAny(User $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Post');
    }

    public function replicate(User $authUser, Post $post): bool
    {
        return $authUser->can('Replicate:Post');
    }

    public function reorder(User $authUser): bool
    {
        return $authUser->can('Reorder:Post');
    }
}
