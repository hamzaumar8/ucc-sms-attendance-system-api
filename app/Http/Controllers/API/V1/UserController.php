<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $user = User::find(auth()->user()->id);
        // $this->getRoleNames()->first()
        // new UserResource($request->user()
        // if ($user->role === "USR" || $user->role === "REP") {
        //     return (new UserResource($user->loadMissing('student')))->response()->setStatusCode(200);
        // } else {
        return (new UserResource($user->loadMissing(['lecturer', 'student'])))->response()->setStatusCode(200);
    }
}
