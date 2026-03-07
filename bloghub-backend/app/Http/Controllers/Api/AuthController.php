<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptTermsPrivacyRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\Api\UpdateUserProfileRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Verified;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'terms_accepted_at' => ! empty($data['terms_accepted']) ? now() : null,
            'privacy_accepted_at' => ! empty($data['privacy_accepted']) ? now() : null,
        ]);

        $user->sendEmailVerificationNotification();

        $token = $user->createToken('auth')->plainTextToken;
        $user->load('creatorProfile:id,user_id,slug');

        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => [__('auth.email_not_found')],
            ]);
        }

        if (! Auth::guard('web')->attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'password' => [__('auth.wrong_password')],
            ]);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth')->plainTextToken;
        $user->load('creatorProfile:id,user_id,slug');

        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('creatorProfile:id,user_id,slug');

        return response()->json([
            'user' => $user,
        ]);
    }

    public function acceptTermsAndPrivacy(AcceptTermsPrivacyRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'terms_accepted_at' => now(),
            'privacy_accepted_at' => now(),
        ]);

        return response()->json([
            'user' => $user->fresh(),
        ]);
    }

    public function updateProfile(UpdateUserProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());
        $user->load('creatorProfile:id,user_id,slug');

        return response()->json([
            'user' => $user,
        ]);
    }

    public function verifyEmail(Request $request, string $id, string $hash): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $user = User::findOrFail((int) $id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Invalid verification link'], 403);
            }
            return redirect()->to(config('services.frontend_url', '/').'?email_verified=0&error=invalid');
        }

        if ($user->hasVerifiedEmail()) {
            $url = config('services.frontend_url').'/?email_verified=1';
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Email already verified', 'verified' => true, 'redirect_url' => $url]);
            }
            return redirect()->to($url);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        $url = config('services.frontend_url').'/?email_verified=1';
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Email verified successfully',
                'verified' => true,
                'redirect_url' => $url,
            ]);
        }
        return redirect()->to($url);
    }

    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 422);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent']);
    }
}
