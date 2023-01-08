<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Support\Facades\Auth;

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
            $user = $user = auth()->user();
            return response()->json([
                // 'status'=>200,
                'username' => $user->name,
                'token' => $user->createToken($user->email.'_Token')->plainTextToken,
                'token_type' => 'Bearer',
                'message' => 'logined In successfully',
            ])->setStatusCode(200);

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
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
