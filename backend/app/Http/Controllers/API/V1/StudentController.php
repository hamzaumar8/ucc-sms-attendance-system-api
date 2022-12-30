<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Student\StudentCollection;
use App\Http\Resources\V1\Student\StudentResource;
use App\Models\Level;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
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
        return new StudentCollection(Student::orderByDesc('id')->paginate(2));
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
            'email' => 'required|string|email|max:255|unique:users',
            'index_number' => 'required|max:20|unique:students,index_number',
            'first_name' => 'required|string|max:20',
            'other_name' => 'nullable|string|max:255',
            'surname' => 'required|string|max:20',
            // 'gender' => 'required|string',
            'phone' => 'nullable|string|max:15',
            'picture' => 'nullable',
            'level' => 'required|numeric|exists:levels,id',
        ]);

        $name = $request->input('other_name') ? $request->input('first_name') . ' ' . $request->input('other_name') . ' ' . $request->input('surname') : $request->input('first_name') . ' ' . $request->input('surname');

        $picture_url = null;
        if ($request->hasFile('picture')) {
            $file = $request->file('picture');
            Log::debug($file);
            $file_name = Carbon::now()->timestamp . "." . $file->getClientOriginalExtension();
            $file->move(public_path('assets/img/students'), $file_name);
            $picture_url = URL::to('/') . '/assets/img/students/' . $file_name;
        }


        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $request->input('email'),
            'password' => Hash::make(Str::random(8)),
        ]);


        $student = Student::create([
            'user_id' => $user->id,
            'index_number' => $request->input('index_number'),
            'first_name' => $request->input('first_name'),
            'other_name' => $request->input('other_name'),
            'surname' => $request->input('surname'),
            // 'gender' => $request->input('gender'),
            'phone' => $request->input('phone'),
            'picture' => $picture_url,
            'level_id' => $request->input('level'),
        ]);

        //TODO: send email (credentials) to student
        return response()->json(['status' => 'student-added-succesffully'])->setStatusCode(201);
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
        dd($student);
        $this->validate($request, [
            'index_number' => ['required', 'max:20', Rule::unique('students')->ignore($student->index_number, 'index_number')],
            'first_name' => ['required', 'string', 'max:20'],
            'other_name' => ['nullable', 'string',],
            'surname' => ['required', 'string', 'max:20'],
            'gender' => ['required', 'string',],
            'phone' => ['required', 'string', 'max:20'],
            'picture' => ['nullable'],
        ]);

        $student->update([
            'index_number' => $request->input('index_number'),
            'first_name' => $request->input('first_name'),
            'other_name' => $request->input('other_name'),
            'surname' => $request->input('surname'),
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
            $query->whereRaw("first_name Like '%" . $s . "%'")->orWhereRaw("other_name Like '%" . $s . "%'")->orWhereRaw("surname Like '%" . $s . "%'")->orWhereRaw("index_number Like '%" . $s . "%'");
        }

        return $query->get();
    }
}