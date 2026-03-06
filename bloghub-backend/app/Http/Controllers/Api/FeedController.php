<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FeedController extends Controller
{
    public function publicFeed(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            $query = Post::query()->whereNull('required_tier_id');
        } else {
            $subscribedProfileIds = Subscription::query()
                ->where('user_id', $user->id)
                ->where('sub_status', SubStatus::Active)
                ->where(function ($q) {
                    $q->whereNull('end_date')->orWhere('end_date', '>', now());
                })
                ->with('tier:id,creator_profile_id')
                ->whereHas('tier')
                ->get()
                ->pluck('tier.creator_profile_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (count($subscribedProfileIds) === 0) {
                $query = Post::query()->whereRaw('1 = 0');
            } else {
                $query = Post::query()
                    ->whereIn('creator_profile_id', $subscribedProfileIds)
                    ->whereNull('required_tier_id');
            }
        }

        $query
            ->with('creatorProfile:id,slug,display_name,profile_avatar_path')
            ->withCount('comments')
            ->withCount('likes')
            ->withCount(['postViews as views_count'])
            ->withCount(['postViews as user_has_viewed' => fn ($q) => $q->where('user_id', $user->id)])
            ->withCount(['likes as user_has_liked' => fn ($q) => $q->where('user_id', $user->id)])
            ->orderByDesc('created_at');

        $perPage = min((int) $request->input('per_page', 15), 50);
        $posts = $query->paginate($perPage);

        $request->attributes->set('creator_profile_is_owner', false);
        $request->attributes->set('creator_profile_is_super_admin', $user->hasRole('super_admin'));
        $request->attributes->set('creator_profile_user_tier_level', null);

        return PostResource::collection($posts);
    }
}
