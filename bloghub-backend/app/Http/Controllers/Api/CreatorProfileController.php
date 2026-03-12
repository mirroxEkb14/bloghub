<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCreatorProfileRequest;
use App\Http\Requests\Api\UpdateCreatorProfileRequest;
use App\Http\Requests\Api\UpdateMyCreatorProfileRequest;
use App\Http\Resources\CreatorProfileResource;
use App\Models\CreatorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CreatorProfileController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = CreatorProfile::query()
            ->withCount('posts')
            ->with(['user:id,name,username', 'tags']);

        if ($request->filled('tag')) {
            $tag = $request->input('tag');
            $query->whereHas('tags', function ($q) use ($tag) {
                if (is_numeric($tag)) {
                    $q->where('tags.id', (int) $tag);
                } else {
                    $q->where('tags.slug', $tag);
                }
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('display_name', 'like', '%' . $search . '%')
                    ->orWhereHas('user', fn ($uq) => $uq->where('username', 'like', '%' . $search . '%'));
            });
        }

        $perPage = min((int) $request->input('per_page', 15), 50);
        $profiles = $query->orderBy('display_name')->paginate($perPage);

        return CreatorProfileResource::collection($profiles);
    }

    public function show(Request $request, string $slug): CreatorProfileResource|JsonResponse
    {
        $profile = CreatorProfile::query()
            ->where('slug', $slug)
            ->with(['user:id,name,username', 'tags'])
            ->withCount(['posts', 'followers'])
            ->withCount(['subscriptions as subscribers_count' => function ($q) {
                $q->where('sub_status', SubStatus::Active)
                    ->where(function ($q2) {
                        $q2->whereNull('end_date')->orWhere('end_date', '>', now());
                    })
                    ->selectRaw('count(distinct user_id)');
            }])
            ->first();

        if ($profile === null) {
            return response()->json(['message' => __('Creator profile not found')], 404);
        }

        $user = $request->user();
        if ($user) {
            $profile->is_following = $profile->followers()->where('users.id', $user->id)->exists();
        }

        return new CreatorProfileResource($profile);
    }

    public function me(Request $request): CreatorProfileResource|JsonResponse
    {
        $profile = $request->user()?->creatorProfile;
        if ($profile === null) {
            return response()->json(['message' => __('You do not have a creator profile')], 404);
        }

        $profile->load(['user:id,name,username', 'tags'])
            ->loadCount(['posts', 'followers'])
            ->loadCount(['subscriptions as subscribers_count' => function ($q) {
                $q->where('sub_status', SubStatus::Active)
                    ->where(function ($q2) {
                        $q2->whereNull('end_date')->orWhere('end_date', '>', now());
                    })
                    ->selectRaw('count(distinct user_id)');
            }]);
        $profile->is_following = false;

        return new CreatorProfileResource($profile);
    }

    public function store(StoreCreatorProfileRequest $request): JsonResponse
    {
        if (! $request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'You must verify your email before creating a creator profile'], 403);
        }

        $data = $request->validatedWithSlug();
        $tagIds = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);

        $data['user_id'] = $request->user()->id;
        $profile = CreatorProfile::create($data);
        $profile->tags()->sync($tagIds);

        $profile->load(['user:id,name,username', 'tags'])->loadCount(['posts', 'subscriptions']);

        return response()->json(new CreatorProfileResource($profile), 201);
    }

    public function update(UpdateCreatorProfileRequest $request, CreatorProfile $creatorProfile): CreatorProfileResource
    {
        $data = $request->validated();
        $tagIds = $data['tag_ids'] ?? null;
        unset($data['tag_ids']);

        $creatorProfile->update($data);
        if ($tagIds !== null) {
            $creatorProfile->tags()->sync($tagIds);
        }

        $creatorProfile->load(['user:id,name,username', 'tags'])->loadCount(['posts', 'subscriptions']);

        return new CreatorProfileResource($creatorProfile);
    }

    public function updateMe(UpdateMyCreatorProfileRequest $request): CreatorProfileResource
    {
        $profile = $request->user()->creatorProfile;
        $data = $request->validated();
        $tagIds = $data['tag_ids'] ?? null;
        unset($data['tag_ids']);

        $profile->update($data);
        if ($tagIds !== null) {
            $profile->tags()->sync($tagIds);
        }

        $profile->load(['user:id,name,username', 'tags'])->loadCount(['posts', 'subscriptions']);

        return new CreatorProfileResource($profile);
    }
}
