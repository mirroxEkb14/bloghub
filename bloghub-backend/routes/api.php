<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CreatorProfileController;
use App\Http\Controllers\Api\CreatorProfileFollowController;
use App\Http\Controllers\Api\UserUploadController;
use App\Http\Controllers\Api\CreatorProfilePostController;
use App\Http\Controllers\Api\CreatorProfileTierController;
use App\Http\Controllers\Api\MeCreatorPostController;
use App\Http\Controllers\Api\PostMediaUploadController;
use App\Http\Controllers\Api\TierCoverUploadController;
use App\Http\Controllers\Api\CreatorProfileUploadController;
use App\Http\Controllers\Api\PostCommentController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\ExploreController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\MeFollowingController;
use App\Http\Controllers\Api\MePaymentController;
use App\Http\Controllers\Api\SubscriptionCheckoutController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/stripe', StripeWebhookController::class);

Route::middleware('throttle:api')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');

    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware('signed')
        ->name('verification.verify');

    Route::get('/tags', [TagController::class, 'index']);
    Route::get('/creator-profiles', [CreatorProfileController::class, 'index']);
    Route::get('/creator-profiles/{slug}/posts', [CreatorProfilePostController::class, 'index'])
        ->middleware('auth.sanctum.optional');
    Route::get('/creator-profiles/{slug}/posts/{postSlug}', [CreatorProfilePostController::class, 'show'])
        ->middleware('auth.sanctum.optional');
    Route::get('/creator-profiles/{slug}/posts/{postSlug}/comments', [PostCommentController::class, 'index'])
        ->middleware('auth.sanctum.optional');
    Route::get('/creator-profiles/{slug}/tiers', [CreatorProfileTierController::class, 'index']);
    Route::get('/creator-profiles/{slug}', [CreatorProfileController::class, 'show'])
        ->middleware('auth.sanctum.optional');

    Route::get('/explore/popular-creators', [ExploreController::class, 'popularCreators']);
    Route::get('/explore/trending-posts', [ExploreController::class, 'trendingPosts'])
        ->middleware('auth.sanctum.optional');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::patch('/user', [AuthController::class, 'updateProfile']);
        Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail']);
        Route::patch('/user/accept-terms-privacy', [AuthController::class, 'acceptTermsAndPrivacy']);
        Route::post('/user/upload-avatar', [UserUploadController::class, 'avatar']);

        Route::get('/me/creator-profile', [CreatorProfileController::class, 'me']);
        Route::put('/me/creator-profile', [CreatorProfileController::class, 'updateMe']);
        Route::post('/creator-profiles', [CreatorProfileController::class, 'store']);
        Route::post('/creator-profiles/upload-avatar', [CreatorProfileUploadController::class, 'avatar']);
        Route::post('/creator-profiles/upload-cover', [CreatorProfileUploadController::class, 'cover']);
        Route::put('/creator-profiles/{creatorProfile}', [CreatorProfileController::class, 'update']);

        Route::get('/me/creator-profile/tiers', [CreatorProfileTierController::class, 'indexMy']);
        Route::post('/me/creator-profile/tiers/upload-cover', [TierCoverUploadController::class, 'cover']);
        Route::post('/me/creator-profile/tiers', [CreatorProfileTierController::class, 'store']);
        Route::put('/me/creator-profile/tiers/{tier}', [CreatorProfileTierController::class, 'update']);
        Route::delete('/me/creator-profile/tiers/{tier}', [CreatorProfileTierController::class, 'destroy']);

        Route::post('/me/creator-profile/posts/upload-media', [PostMediaUploadController::class, 'upload']);
        Route::post('/me/creator-profile/posts', [MeCreatorPostController::class, 'store']);
        Route::put('/me/creator-profile/posts/{postSlug}', [MeCreatorPostController::class, 'update']);
        Route::delete('/me/creator-profile/posts/{postSlug}', [MeCreatorPostController::class, 'destroy']);

        Route::get('/me/feed', [FeedController::class, 'homeFeed']);
        Route::get('/me/feed/public', [FeedController::class, 'publicFeed']);
        Route::get('/me/feed/tier', [FeedController::class, 'tierFeed']);
        Route::get('/me/subscriptions', [SubscriptionController::class, 'index']);
        Route::get('/me/following', [MeFollowingController::class, 'index']);
        Route::get('/me/payments', [MePaymentController::class, 'index']);
        Route::post('/subscriptions', [SubscriptionController::class, 'store']);
        Route::post('/subscriptions/create-checkout-session', [SubscriptionCheckoutController::class, 'createCheckoutSession']);
        Route::post('/subscriptions/confirm-checkout', [SubscriptionCheckoutController::class, 'confirmCheckout']);
        Route::get('/creator-profiles/{slug}/subscription-status', [SubscriptionController::class, 'statusByCreator']);
        Route::patch('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel']);
        Route::get('/me/notifications', [NotificationController::class, 'index']);
        Route::get('/me/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::patch('/me/notifications/read', [NotificationController::class, 'markAllRead']);
        Route::patch('/me/notifications/{notification}/read', [NotificationController::class, 'markRead']);
        Route::post('/creator-profiles/{slug}/follow', [CreatorProfileFollowController::class, 'follow']);
        Route::delete('/creator-profiles/{slug}/follow', [CreatorProfileFollowController::class, 'unfollow']);

        Route::post('/creator-profiles/{slug}/posts/{postSlug}/comments', [PostCommentController::class, 'store']);
        Route::post('/creator-profiles/{slug}/posts/{postSlug}/view', [CreatorProfilePostController::class, 'recordView']);
        Route::post('/creator-profiles/{slug}/posts/{postSlug}/like', [CreatorProfilePostController::class, 'like']);
        Route::delete('/creator-profiles/{slug}/posts/{postSlug}/like', [CreatorProfilePostController::class, 'unlike']);
    });
});
