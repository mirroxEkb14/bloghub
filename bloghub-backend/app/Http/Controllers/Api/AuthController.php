<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptTermsPrivacyRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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
}
