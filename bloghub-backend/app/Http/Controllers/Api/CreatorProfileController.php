<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCreatorProfileRequest;
use App\Http\Requests\Api\UpdateCreatorProfileRequest;
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

    public function show(string $slug): CreatorProfileResource|JsonResponse
    {
        $profile = CreatorProfile::query()
            ->where('slug', $slug)
            ->with(['user:id,name,username', 'tags'])
            ->withCount('posts')
            ->first();

        if ($profile === null) {
            return response()->json(['message' => __('Creator profile not found.')], 404);
        }

        return new CreatorProfileResource($profile);
    }

    public function me(Request $request): CreatorProfileResource|JsonResponse
    {
        $profile = $request->user()?->creatorProfile;
        if ($profile === null) {
            return response()->json(['message' => __('You do not have a creator profile.')], 404);
        }

        $profile->load(['user:id,name,username', 'tags'])->loadCount('posts');

        return new CreatorProfileResource($profile);
    }

    public function store(StoreCreatorProfileRequest $request): JsonResponse
    {
        $data = $request->validatedWithSlug();
        $tagIds = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);

        $data['user_id'] = $request->user()->id;
        $profile = CreatorProfile::create($data);
        $profile->tags()->sync($tagIds);

        $profile->load(['user:id,name,username', 'tags'])->loadCount('posts');

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

        $creatorProfile->load(['user:id,name,username', 'tags'])->loadCount('posts');

        return new CreatorProfileResource($creatorProfile);
    }
}
