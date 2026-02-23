<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $authUser): bool
    {
        return $authUser->can('ViewAny:Comment');
    }

    public function view(User $authUser, Comment $comment): bool
    {
        return $authUser->can('View:Comment');
    }

    public function create(User $authUser): bool
    {
        return $authUser->can('Create:Comment');
    }

    public function update(User $authUser, Comment $comment): bool
    {
        return $authUser->can('Update:Comment');
    }

    public function delete(User $authUser, Comment $comment): bool
    {
        return $authUser->can('Delete:Comment');
    }

    public function restore(User $authUser, Comment $comment): bool
    {
        return $authUser->can('Restore:Comment');
    }

    public function forceDelete(User $authUser, Comment $comment): bool
    {
        return $authUser->can('ForceDelete:Comment');
    }

    public function restoreAny(User $authUser): bool
    {
        return $authUser->can('RestoreAny:Comment');
    }

    public function forceDeleteAny(User $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Comment');
    }

    public function replicate(User $authUser, Comment $comment): bool
    {
        return $authUser->can('Replicate:Comment');
    }

    public function reorder(User $authUser): bool
    {
        return $authUser->can('Reorder:Comment');
    }
}
