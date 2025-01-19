<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response
    {
        $request->authenticate();

        $request->session()->regenerate();

        return response()->noContent();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }

    /**
     * Handle an incoming authentication request and return a token.
     */
    public function login(Request $request)
    {
        // $credentials = $request->only('email', 'password');

        // if (!Auth::attempt($credentials)) {
        //     return response()->json(['message' => 'Invalid credentials'], 401);
        // }

        // $user = Auth::user();
        // $token = $user->createToken('auth_token')->plainTextToken;

        // return response()->json([
        //     'access_token' => $token,
        //     'token_type' => 'Bearer',
        // ]);

        // $request->validate([
        //     'email' => 'required|string|max:255',
        //     'password' => 'required|string|max:255',
        //     'device_name' => 'required|string|max:255',
        // ]);

        // $user = User::where('email', $request->email)
        //     ->orWhere('username', strtoupper($request->email))
        //     ->first();

        // if (!$user || !Hash::check($request->password, $user->password)) {
        //     throw ValidationException::withMessages([
        //         'email' => ['The provided credentials are incorrect.'],
        //     ]);
        // }

        // if ($user->role === "USR" || $user->role === "REP") {
        //     $userResource = new UserResource($user->loadMissing('student'));
        // } else {
        //     $userResource = new UserResource($user->loadMissing('lecturer'));
        // }

        // return response()->json([
        //     'user' => $userResource,
        //     'token' => $user->createToken($request->device_name)->plainTextToken,
        // ]);
    }
}
