<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | REGISTER
    |--------------------------------------------------------------------------
    */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'language' => $request->validated('language') ?? 'en',
            'onboarding_completed' => false,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | OAUTH REDIRECT
    |--------------------------------------------------------------------------
    */
    public function redirectToProvider(string $provider)
    {
        if (! in_array($provider, ['google', 'apple'], true)) {
            abort(404);
        }

        try {
            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
            $driver = Socialite::driver($provider);
            return $driver->stateless()->redirect();
        } catch (\InvalidArgumentException $e) {
            return redirect('/login?error=' . urlencode("The provider [{$provider}] is not supported or configured on this server."));
        } catch (\Exception $e) {
            return redirect('/login?error=' . urlencode('Could not connect to the authentication provider. Please try again.'));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | OAUTH CALLBACK
    |--------------------------------------------------------------------------
    */
    public function handleProviderCallback(string $provider)
    {
        if (! in_array($provider, ['google', 'apple'], true)) {
            abort(404);
        }

        try {
            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
            $driver = Socialite::driver($provider);
            $socialUser = $driver->stateless()->user();
        } catch (\InvalidArgumentException $e) {
            return redirect('/login?error=' . urlencode("The provider [{$provider}] is not supported or configured on this server."));
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            return redirect('/login?error=' . urlencode('Authentication session expired. Please try again.'));
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return redirect('/login?error=' . urlencode('Authentication failed. Please try again.'));
        } catch (\Exception $e) {
            return redirect('/login?error=' . urlencode('An unexpected error occurred during authentication. Please try again.'));
        }

        if (! $socialUser->getEmail()) {
            return redirect('/login?error=' . urlencode('No email address was returned from your social account.'));
        }

        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name' => $socialUser->getName() ?: Str::before($socialUser->getEmail(), '@'),
                'password' => Hash::make(Str::random(32)),
                'language' => 'en',
                'onboarding_completed' => false,
            ]
        );

        if (!$user->name && $socialUser->getName()) {
            $user->update(['name' => $socialUser->getName()]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        /*
        |--------------------------------------------------------------------------
        | REDIRECT FRONTEND (VUE)
        |--------------------------------------------------------------------------
        */
        return redirect('/login?token=' . $token);
    }
}
