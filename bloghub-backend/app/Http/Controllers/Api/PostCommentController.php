<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubStatus;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CreatorProfile;
use App\Models\Post;
use App\Models\Subscription;
use App\Support\CommentResourceSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostCommentController extends Controller
{
    private function resolvePostWithAccess(Request $request, string $slug, string $postSlug): array
    {
        $profile = CreatorProfile::query()->where('slug', $slug)->first();

        if ($profile === null) {
            return [null, response()->json(['message' => __('Creator profile not found')], 404)];
        }

        $post = $profile->posts()->where('slug', $postSlug)->with('requiredTier:id,creator_profile_id,level,tier_name')->first();

        if ($post === null) {
            return [null, response()->json(['message' => __('Post not found')], 404)];
        }

        if ($post->required_tier_id !== null) {
            $user = $request->user();
            if (! $user) {
                return [
                    null,
                    response()->json([
                        'message' => __('This post is for subscribers only'),
                        'requires_subscription' => true,
                        'required_tier' => [
                            'id' => $post->requiredTier->id,
                            'tier_name' => $post->requiredTier->tier_name,
                            'level' => $post->requiredTier->level,
                        ],
                    ], 403),
                ];
            }
            $hasAccess = Subscription::query()
                ->where('user_id', $user->id)
                ->where('sub_status', SubStatus::Active)
                ->where('end_date', '>', now())
                ->whereHas('tier', function ($q) use ($post) {
                    $q->where('creator_profile_id', $post->creator_profile_id)
                        ->where('level', '>=', $post->requiredTier->level);
                })
                ->exists();
            if (! $hasAccess) {
                return [
                    null,
                    response()->json([
                        'message' => __('This post is for subscribers only'),
                        'requires_subscription' => true,
                        'required_tier' => [
                            'id' => $post->requiredTier->id,
                            'tier_name' => $post->requiredTier->tier_name,
                            'level' => $post->requiredTier->level,
                        ],
                    ], 403),
                ];
            }
        }

        return [$post, null];
    }

    public function index(Request $request, string $slug, string $postSlug): JsonResponse
    {
        [$post, $errorResponse] = $this->resolvePostWithAccess($request, $slug, $postSlug);
        if ($errorResponse !== null) {
            return $errorResponse;
        }

        $comments = $post->comments()
            ->with(['user:id,name,username', 'user.creatorProfile:id,user_id,profile_avatar_path'])
            ->orderBy('created_at')
            ->get();

        $data = [];
        foreach ($comments as $comment) {
            $data[] = $this->commentToArray($comment);
        }

        return response()->json(['data' => $data]);
    }

    public function store(Request $request, string $slug, string $postSlug): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        [$post, $errorResponse] = $this->resolvePostWithAccess($request, $slug, $postSlug);
        if ($errorResponse !== null) {
            return $errorResponse;
        }

        $contentText = $request->input('content_text');
        if (! is_string($contentText)) {
            return response()->json([
                'message' => __('The content text field is required'),
                'errors' => ['content_text' => [__('The content text field is required')]],
            ], 422);
        }
        $contentText = trim($contentText);
        if ($contentText === '') {
            return response()->json([
                'message' => __('The content text field is required'),
                'errors' => ['content_text' => [__('The content text field is required')]],
            ], 422);
        }
        if (strlen($contentText) > CommentResourceSupport::CONTENT_TEXT_MAX_LENGTH) {
            return response()->json([
                'message' => __('The content text field must not exceed :max characters', ['max' => CommentResourceSupport::CONTENT_TEXT_MAX_LENGTH]),
                'errors' => ['content_text' => [__('The content text field must not exceed :max characters', ['max' => CommentResourceSupport::CONTENT_TEXT_MAX_LENGTH])]],
            ], 422);
        }

        $comment = $post->comments()->create([
            'user_id' => $user->id,
            'content_text' => $contentText,
        ]);

        $comment->load(['user:id,name,username', 'user.creatorProfile:id,user_id,profile_avatar_path']);

        return response()->json(['data' => $this->commentToArray($comment)]);
    }

    private function commentToArray(Comment $comment): array
    {
        $user = $comment->relationLoaded('user') ? $comment->user : null;

        return [
            'id' => $comment->id,
            'content_text' => $comment->content_text,
            'created_at' => $comment->created_at?->toIso8601String(),
            'updated_at' => $comment->updated_at?->toIso8601String(),
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'avatar_url' => $user->relationLoaded('creatorProfile') && $user->creatorProfile?->profile_avatar_path
                    ? $user->creatorProfile->profile_avatar_url
                    : null,
            ] : null,
        ];
    }
}
