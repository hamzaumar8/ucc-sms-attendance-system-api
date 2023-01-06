<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Student\StudentCollection;
use App\Http\Resources\V1\Student\StudentResource;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use App\Imports\V1\StudentsImport;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['only' => ['store', 'update', 'destroy', 'import']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $students = Student::orderByDesc('id')->with(['level', 'user'])->get();
        return new StudentCollection($students);
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
            'index_number' => 'required|max:20|unique:students,index_number',
            'first_name' => 'required|string|max:20',
            'other_name' => 'nullable|string|max:255',
            'surname' => 'required|string|max:20',
            // 'gender' => 'required|string',
            'phone' => 'nullable|string|max:15',
            'picture' => 'nullable|file',
            'level' => 'required|numeric|exists:levels,id',
        ]);

        try{

            $name = $request->input('other_name') ? $request->input('first_name') . ' ' . $request->input('other_name') . ' ' . $request->input('surname') : $request->input('first_name') . ' ' . $request->input('surname');

            $picture_url = null;
            if ($request->hasFile('picture')) {
                $file = $request->file('picture');
                $file_name = Carbon::now()->timestamp . "." . $file->getClientOriginalExtension();
                $file->move(public_path('assets/img/students'), $file_name);
                $picture_url = URL::to('/') . '/assets/img/students/' . $file_name;
            }

            // Create user
            $user = User::create([
                'name' => $name,
                'email' => $request->input('email'),
                'email_verified_at' => now(),
                'password' => Hash::make(str_replace("/", "", $request->input('index_number'))),
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
            return response()->json(['status' => 'success'])->setStatusCode(201);

        }catch(\Exception $e){
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>'An error occured while adding a student!!'
            ])->setStatusCode(500);
        }
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
            'email' => 'required|string|email|max:255|unique:users,email,' . $student->user->id,
            'index_number' => 'required|max:20|unique:students,index_number,' . $student->id,
            'first_name' => 'required|string|max:20',
            'other_name' => 'nullable|string|max:255',
            'surname' => 'required|string|max:20',
            // 'gender' => 'required|string',
            'phone' => 'nullable|string|max:15',
            'picture' => 'nullable|file',
            'level' => 'required|numeric|exists:levels,id',
        ]);

        try{

            $picture_url = null;
            if ($request->hasFile('picture')) {
                if ($student->picture) {
                    $studentpicture = explode("/", $student->picture);
                    $picture = end($studentpicture);
                    $exist = File::exists(public_path("assets/img/students/" . $picture));
                    if ($exist) {
                        File::delete(public_path("assets/img/students/" . $picture));
                    }
                }
                $file = $request->file('picture');
                $file_name = Carbon::now()->timestamp . "." . $file->getClientOriginalExtension();
                $file->move(public_path('assets/img/lecturers'), $file_name);
                $picture_url = URL::to('/') . '/assets/img/lecturers/' . $file_name;
            }

            $student->update([
                'index_number' => $request->input('index_number'),
                'first_name' => $request->input('first_name'),
                'other_name' => $request->input('other_name'),
                'surname' => $request->input('surname'),
                // 'gender' => $request->input('gender'),
                'phone' => $request->input('phone'),
                'picture' => $picture_url,
                'level_id' => $request->input('level'),
            ]);

            $student->user->update([
                'email' => $request->input('email'),
            ]);

            return response()->json(['status' => 'success'])->setStatusCode(201);

        }catch(\Exception $e){
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>'An error occured while updating a student details!!'
            ])->setStatusCode(500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function destroy(Student $student)
    {
        try{
            if ($student->picture) {
                $studentpicture = explode("/", $student->picture);
                $picture = end($studentpicture);
                $exist = File::exists(public_path("assets/img/students/" . $picture));
                if ($exist) {
                    File::delete(public_path("assets/img/students/" . $picture));
                }
            }

            $student->user->delete();

            return response()->json(null, 204);

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>'An error occured while deleting student!!'
            ])->setStatusCode(500);
        }
    }


    public function backend(Request $request)
    {
        $query = Student::query();

        if ($s = $request->input('s')) {
            $query->whereRaw("first_name Like '%" . $s . "%'")->orWhereRaw("other_name Like '%" . $s . "%'")->orWhereRaw("surname Like '%" . $s . "%'")->orWhereRaw("index_number Like '%" . $s . "%'");
        }

        return $query->get();
    }

     public function import(Request $request){

        $request->validate([
            'file' => 'required|mimes:csv,txt',
        ]);

        try {

            Excel::import(new StudentsImport, request()->file('file'));
            return response()->json(['status' => 'success'])->setStatusCode(201);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>'An error occured while importing data!!'
            ])->setStatusCode(500);
        }
    }
}
