<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Lecturer\LecturerCollection;
use App\Http\Resources\V1\Lecturer\LecturerResource;
use App\Models\Lecturer;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LecturerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return Lecturer::all();
        return new LecturerCollection(Lecturer::orderBy('id', 'DESC')->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate($request, [
            'email' => 'required|string|email|max:255|unique:users,email',
            'staff_id' => 'required|max:20|unique:lecturers,staff_id',
            'title' => 'required|string',
            'first_name' => 'required|string|max:20',
            'other_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:20',
            // 'gender' => 'required|string',
            'phone' => 'nullable|string|max:15|unique:lecturers,phone',
            'picture' => 'nullable',
        ]);


        $name = $request->input('other_name') ? $request->input('first_name') . ' ' . $request->input('other_name') . ' ' . $request->input('last_name') : $request->input('first_name') . ' ' . $request->input('last_name');

        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $request->input('email'),
            'password' => Hash::make(Str::random(8)),
        ]);


        Lecturer::create([
            'user_id' => $user->id,
            'staff_id' => $request->input('staff_id'),
            'title' => $request->input('title'),
            'first_name' => $request->input('first_name'),
            'other_name' => $request->input('other_name'),
            'last_name' => $request->input('last_name'),
            // 'gender' => $request->input('gender'),
            'phone' => $request->input('phone'),
            // 'picture' => $request->input('picture'),
        ]);

        //TODO: send email (credentials) to student
        return response()->json(['status' => 'lecturer-added'])->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Lecturer  $lecturer
     * @return \Illuminate\Http\Response
     */
    public function show(Lecturer $lecturer)
    {
        return (new LecturerResource($lecturer))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Lecturer  $lecturer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Lecturer $lecturer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Lecturer  $lecturer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Lecturer $lecturer)
    {
        //
    }


    public function backend(Request $request)
    {
        $query = Lecturer::query();

        if ($s = $request->input('s')) {
            $query->whereRaw("title Like '%" . $s . "%'")->orWhereRaw("first_name Like '%" . $s . "%'")->orWhereRaw("other_name Like '%" . $s . "%'")->orWhereRaw("last_name Like '%" . $s . "%'")->orWhereRaw("staff_id Like '%" . $s . "%'");
        }

        return $query->get();
    }
}