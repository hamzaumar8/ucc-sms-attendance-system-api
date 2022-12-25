<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Student\StudentCollection;
use App\Http\Resources\V1\Student\StudentResource;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new StudentCollection(Student::all());
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'index_number' => ['required', 'max:20', 'unique:students,index_number'],
            'first_name' => ['required', 'string', 'max:20'],
            'other_name' => ['nullable', 'string',],
            'last_name' => ['required', 'string', 'max:20'],
            'gender' => ['required', 'string',],
            'phone' => ['required', 'string', 'max:20'],
            'picture' => ['nullable'],
        ]);

        $name = $request->other_name ? $request->first_name . ' ' . $request->other_name . ' ' . $request->last_name : $request->first_name . ' ' . $request->last_name;
        $user = User::create([
            'name' => $name,
            'email' => $request->email,
            'password' => Hash::make(Str::random(8)),
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'index_number' => $request->input('index_number'),
            'first_name' => $request->input('first_name'),
            'other_name' => $request->input('other_name'),
            'last_name' => $request->input('last_name'),
            'gender' => $request->input('gender'),
            'phone' => $request->input('phone'),
            'picture' => $request->input('picture'),
        ]);

        //TODO: send email (credentials) to student
        return (new StudentResource($student))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function show(Student $student)
    {
        return (new StudentResource($student))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Student $student)
    {
        $this->validate($request, [
            'index_number' => ['required', 'max:20', Rule::unique('students')->ignore($student->index_number(), 'index_number')],
            'first_name' => ['required', 'string', 'max:20'],
            'other_name' => ['nullable', 'string',],
            'last_name' => ['required', 'string', 'max:20'],
            'gender' => ['required', 'string',],
            'phone' => ['required', 'string', 'max:20'],
            'picture' => ['nullable'],
        ]);

        $student->update([
            'index_number' => $request->input('index_number'),
            'first_name' => $request->input('first_name'),
            'other_name' => $request->input('other_name'),
            'last_name' => $request->input('last_name'),
            'gender' => $request->input('gender'),
            'phone' => $request->input('phone'),
            'picture' => $request->input('picture'),
        ]);

        return (new StudentResource($student))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function destroy(Student $student)
    {
        $student->user->delete();
        return response()->json(null, 204);
    }


    public function backend(Request $request)
    {
        $query = Student::query();

        if ($s = $request->input('s')) {
            $query->whereRaw("first_name Like '%" . $s . "%'")->orWhereRaw("other_name Like '%" . $s . "%'")->orWhereRaw("last_name Like '%" . $s . "%'")->orWhereRaw("index_number Like '%" . $s . "%'");
        }

        return $query->get();
    }
}