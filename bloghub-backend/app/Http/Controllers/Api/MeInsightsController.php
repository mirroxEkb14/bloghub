<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentStatus;
use App\Enums\SubStatus;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Payment;
use App\Models\PostLike;
use App\Models\PostView;
use App\Models\Subscription;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeInsightsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $profile = $request->user()?->creatorProfile;
        if ($profile === null) {
            return response()->json(['message' => __('You do not have a creator profile')], 404);
        }

        $tierIds = $profile->tiers()->pluck('id');
        $postIds = $profile->posts()->pluck('id');

        $members = $this->members($profile, $tierIds);
        $earnings = $this->earningsByPeriod($tierIds);
        $engagement = $this->engagementByPeriod($postIds);
        $growth30d = $this->growth30d($tierIds);

        return response()->json([
            'members' => $members,
            'earnings' => $earnings,
            'engagement' => $engagement,
            'growth_30d' => $growth30d,
        ]);
    }

    private function members($profile, $tierIds): array
    {
        $followerIds = $profile->followers()->pluck('users.id');
        $activeSubscriberIds = Subscription::query()
            ->whereIn('tier_id', $tierIds)
            ->where('sub_status', SubStatus::Active)
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>', now());
            })
            ->distinct()
            ->pluck('user_id');

        $totalUnique = $followerIds->merge($activeSubscriberIds)->unique()->count();
        $paid = $activeSubscriberIds->count();
        $free = $followerIds->count();

        return [
            'total' => $totalUnique,
            'paid' => $paid,
            'free' => $free,
        ];
    }

    private function parsePeriod(string $period): ?CarbonInterface
    {
        return match ($period) {
            'overall' => null,
            'year' => now()->subYear(),
            '6m' => now()->subMonths(6),
            '3m' => now()->subMonths(3),
            '1m' => now()->subMonth(),
            default => now()->subMonth(),
        };
    }

    private function earningsByPeriod($tierIds): array
    {
        $periods = ['overall', 'year', '6m', '3m', '1m'];
        $result = [];
        foreach ($periods as $period) {
            $since = $this->parsePeriod($period);
            $query = Payment::query()
                ->where('payment_status', PaymentStatus::Completed)
                ->whereHas('subscription', fn ($q) => $q->whereIn('tier_id', $tierIds));
            if ($since !== null) {
                $query->where('checkout_date', '>=', $since);
            }
            $result[$period] = ['amount' => (int) $query->sum('amount')];
        }
        return $result;
    }

    private function engagementByPeriod($postIds): array
    {
        $periods = ['overall', 'year', '6m', '3m', '1m'];
        $result = [];
        foreach ($periods as $period) {
            $since = $this->parsePeriod($period);
            $viewsQuery = PostView::query()->whereIn('post_id', $postIds);
            $likesQuery = PostLike::query()->whereIn('post_id', $postIds);
            $commentsQuery = Comment::query()->whereIn('post_id', $postIds);
            if ($since !== null) {
                $viewsQuery->where('created_at', '>=', $since);
                $likesQuery->where('created_at', '>=', $since);
                $commentsQuery->where('created_at', '>=', $since);
            }
            $result[$period] = [
                'post_views' => $viewsQuery->count(),
                'likes' => $likesQuery->count(),
                'comments' => $commentsQuery->count(),
            ];
        }
        return $result;
    }

    private function growth30d($tierIds): array
    {
        $since = now()->subDays(30);

        $newPaid = Subscription::query()
            ->whereIn('tier_id', $tierIds)
            ->where('sub_status', SubStatus::Active)
            ->where('start_date', '>=', $since)
            ->pluck('user_id')
            ->unique()
            ->count();

        $cancellations = Subscription::query()
            ->whereIn('tier_id', $tierIds)
            ->where('sub_status', SubStatus::Canceled)
            ->where('end_date', '>=', $since)
            ->count();

        return [
            'new_paid' => $newPaid,
            'cancellations' => $cancellations,
        ];
    }
}
