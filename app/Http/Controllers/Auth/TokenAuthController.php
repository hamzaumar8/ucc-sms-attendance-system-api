<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\V1\User\UserResource;


class TokenAuthController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'device_name' => 'required|string|max:255',
        ]);

        $user = User::where('email', $request->email)->orWhere('username', strtoupper($request->email))->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }


        if ($user->role === "USR" || $user->role === "REP") {
            $userResource = (new UserResource($user->loadMissing('student')));
        } else {
            $userResource = (new UserResource($user->loadMissing('lecturer')));
        }

        return response()->json([
            'user' => $userResource,
            'token' => $user->createToken($request->device_name)->plainTextToken
        ]);
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}