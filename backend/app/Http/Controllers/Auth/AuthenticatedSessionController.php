<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();
        /**
         * We are authenticating a request from our frontend.
         */
        if (EnsureFrontendRequestsAreStateful::fromFrontend(request())) {
            $request->session()->regenerate();

            return response()->noContent();
        }
        /**
         * We are authenticating a request from a 3rd party.
         */
        else {
            // Use token authentication.
            $user = Auth::user();
            $token = $user->createToken('token')->plainTextToken;
            $cookie = cookie('jwt', $token, 60 * 24); // 1 day
            return response([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'name' => $user->name,
                ],
                'message' => 'User logged in!',
            ])->withCookie($cookie);
        }
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        // if (EnsureFrontendRequestsAreStateful::fromFrontend(request())) {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();

        $cookie = Cookie::forget('jwt');
        return response()->noContent()->withCookie($cookie);
    }
}