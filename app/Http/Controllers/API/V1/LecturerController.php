<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Lecturer\LecturerCollection;
use App\Http\Resources\V1\Lecturer\LecturerResource;
use App\Models\Lecturer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

class LecturerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['only' => ['store', 'update', 'delete']]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $lecturers = Lecturer::orderByDesc('id')->with(['modules.module_bank', 'user']);
        return new LecturerCollection($lecturers->paginate(20));
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
            'surname' => 'required|string|max:20',
            // 'gender' => 'required|string',
            'phone' => 'nullable|string|max:15',
            'picture' => 'nullable',
        ]);


        $name = $request->input('other_name') ? $request->input('first_name') . ' ' . $request->input('other_name') . ' ' . $request->input('surname') : $request->input('first_name') . ' ' . $request->input('surname');

        $picture_url = null;
        if ($request->hasFile('picture')) {
            $file = $request->file('picture');
            $file_name = Carbon::now()->timestamp . "." . $file->getClientOriginalExtension();
            $file->move(public_path('assets/img/lcturers'), $file_name);
            $picture_url = URL::to('/') . '/assets/img/lcturers/' . $file_name;
        }

        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $request->input('email'),
            'email_verified_at' => now(),
            'password' => Hash::make($request->input('staff_id')),
        ]);

        Lecturer::create([
            'user_id' => $user->id,
            'staff_id' => $request->input('staff_id'),
            'title' => $request->input('title'),
            'first_name' => $request->input('first_name'),
            'other_name' => $request->input('other_name'),
            'surname' => $request->input('surname'),
            // 'gender' => $request->input('gender'),
            'phone' => $request->input('phone'),
            'picture' => $picture_url,
        ]);

        //TODO: send email (credentials) to student
        return response()->json(['status' => 'success'])->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Lecturer  $lecturer
     * @return \Illuminate\Http\Response
     */
    public function show(Lecturer $lecturer)
    {
        return (new LecturerResource($lecturer->loadMissing(['modules'])))
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

        $this->validate($request, [
            'email' => 'required|string|email|max:255|unique:users,email,' . $lecturer->id,
            'staff_id' => 'required|max:20|unique:lecturers,staff_id,' . $lecturer->id,
            'title' => 'required|string',
            'first_name' => 'required|string|max:20',
            'other_name' => 'nullable|string|max:255',
            'surname' => 'required|string|max:20',
            // 'gender' => 'required|string',
            'phone' => 'nullable|string|max:15',
            'picture' => 'nullable',
        ]);


        $name = $request->input('other_name') ? $request->input('first_name') . ' ' . $request->input('other_name') . ' ' . $request->input('surname') : $request->input('first_name') . ' ' . $request->input('surname');

        // $picture_url = null;
        // if ($request->hasFile('picture')) {
        //     $file = $request->file('picture');
        //     $file_name = Carbon::now()->timestamp . "." . $file->getClientOriginalExtension();
        //     $file->move(public_path('assets/img/lcturers'), $file_name);
        //     $picture_url = URL::to('/') . '/assets/img/lcturers/' . $file_name;
        // }
        $lecturer->update([
            'staff_id' => $request->input('staff_id'),
            'title' => $request->input('title'),
            'first_name' => $request->input('first_name'),
            'other_name' => $request->input('other_name'),
            'surname' => $request->input('surname'),
            // 'gender' => $request->input('gender'),
            'phone' => $request->input('phone'),
        ]);

        //TODO: send email (credentials) to student
        return response()->json(['status' => 'success'])->setStatusCode(201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Lecturer  $lecturer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Lecturer $lecturer)
    {
        $lecturer->delete();
        return response()->json(null, 204);
    }


    public function backend(Request $request)
    {
        $query = Lecturer::query();

        if ($s = $request->input('s')) {
            $query->whereRaw("title Like '%" . $s . "%'")->orWhereRaw("first_name Like '%" . $s . "%'")->orWhereRaw("other_name Like '%" . $s . "%'")->orWhereRaw("surname Like '%" . $s . "%'")->orWhereRaw("staff_id Like '%" . $s . "%'");
        }

        return $query->get();
    }
}