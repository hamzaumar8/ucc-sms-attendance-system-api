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
        $user = auth()->user();
        $role = $user->getFirstRoleName();
        if ($role === 'admin') {
            $loadMissing = ['lecturer', 'student'];
        } elseif ($role === 'lecturer') {
            $loadMissing = ['lecturer'];
        } elseif ($role === 'student') {
            $loadMissing = ['student'];
        } else {
            $loadMissing = [];
        }
        return (new UserResource($user->loadMissing($loadMissing)))->response()->setStatusCode(200);
    }
}