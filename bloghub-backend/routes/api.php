<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CreatorProfileController;
use App\Http\Controllers\Api\CreatorProfilePostController;
use App\Http\Controllers\Api\CreatorProfileTierController;
use App\Http\Controllers\Api\CreatorProfileUploadController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\SubscriptionCheckoutController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/stripe', StripeWebhookController::class);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/tags', [TagController::class, 'index']);
Route::get('/creator-profiles', [CreatorProfileController::class, 'index']);
Route::get('/creator-profiles/{slug}/posts', [CreatorProfilePostController::class, 'index']);
Route::get('/creator-profiles/{slug}/posts/{postSlug}', [CreatorProfilePostController::class, 'show']);
Route::get('/creator-profiles/{slug}/tiers', [CreatorProfileTierController::class, 'index']);
Route::get('/creator-profiles/{slug}', [CreatorProfileController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/me/creator-profile', [CreatorProfileController::class, 'me']);
    Route::post('/creator-profiles', [CreatorProfileController::class, 'store']);
    Route::post('/creator-profiles/upload-avatar', [CreatorProfileUploadController::class, 'avatar']);
    Route::post('/creator-profiles/upload-cover', [CreatorProfileUploadController::class, 'cover']);
    Route::put('/creator-profiles/{creatorProfile}', [CreatorProfileController::class, 'update']);

    Route::get('/me/subscriptions', [SubscriptionController::class, 'index']);
    Route::post('/subscriptions', [SubscriptionController::class, 'store']);
    Route::post('/subscriptions/create-checkout-session', [SubscriptionCheckoutController::class, 'createCheckoutSession']);
    Route::get('/creator-profiles/{slug}/subscription-status', [SubscriptionController::class, 'statusByCreator']);
    Route::patch('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel']);
});
