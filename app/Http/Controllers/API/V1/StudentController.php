<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\V1\Student\StudentCollection;
use App\Http\Resources\V1\Student\StudentResource;
use App\Http\Resources\V1\Result\ResultCollection;
use App\Http\Resources\V1\Group\GroupCollection;
use App\Models\Student;
use App\Models\User;
use App\Helpers\Helper;
use App\Models\Module;
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
        $this->middleware('auth:sanctum', ['only' => ['store', 'update', 'destroy', 'import', 'results', 'groups']]);
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

        try {
            DB::beginTransaction();

            $name = $request->other_name ? $request->input('first_name') . ' ' . $request->other_name . ' ' . $request->input('surname') : $request->input('first_name') . ' ' . $request->input('surname');

            $picture_url = null;
            if ($request->hasFile('picture')) {
                $file = $request->file('picture');
                $file_name = Carbon::now()->timestamp . "." . $file->getClientOriginalExtension();
                $file->move(Helper::imagePath('students'), $file_name);
                $picture_url = URL::to('/') . '/assets/img/students/' . $file_name;
            }

            // Create user
            $user = User::create([
                'name' => $name,
                'email' => $request->input('email'),
                'email_verified_at' => now(),
                'password' => Hash::make(strtolower(str_replace("/", "", $request->input('index_number')))),
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'index_number' => strtoupper($request->input('index_number')),
                'first_name' => $request->input('first_name'),
                'other_name' => $request->other_name,
                'surname' => $request->input('surname'),
                // 'gender' => $request->input('gender'),
                'phone' => $request->phone,
                'picture' => $picture_url,
                'level_id' => $request->input('level'),
            ]);

            DB::commit();
            return response()->json(['status' => 'success'])->setStatusCode(201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occured while adding a student!!'
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

        try {
            DB::beginTransaction();

            $picture_url = null;
            if ($request->hasFile('picture')) {
                if ($student->picture) {
                    $studentpicture = explode("/", $student->picture);
                    $picture = end($studentpicture);
                    $exist = File::exists(Helper::imagePath('students/' . $picture));
                    if ($exist) {
                        File::delete(Helper::imagePath('students/' . $picture));
                    }
                }
                $file = $request->file('picture');
                $file_name = Carbon::now()->timestamp . "." . $file->getClientOriginalExtension();
                $file->move(Helper::imagePath('students'), $file_name);
                $picture_url = URL::to('/') . '/assets/img/students/' . $file_name;
            }

            $student->update([
                'index_number' => strtoupper($request->input('index_number')),
                'first_name' => $request->input('first_name'),
                'other_name' => $request->other_name,
                'surname' => $request->input('surname'),
                // 'gender' => $request->input('gender'),
                'phone' => $request->phone,
                'picture' => $picture_url,
                'level_id' => $request->input('level'),
            ]);

            $student->user->update([
                'email' => $request->input('email'),
            ]);

            DB::commit();
            return response()->json(['status' => 'success'])->setStatusCode(201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occured while updating a student details!!'
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
        try {
            DB::beginTransaction();

            if ($student->picture) {
                $studentpicture = explode("/", $student->picture);
                $picture = end($studentpicture);
                $exist = File::exists(Helper::imagePath('students/' . $picture));
                if ($exist) {
                    File::delete(Helper::imagePath('students/' . $picture));
                }
            }

            $student->user->delete();

            DB::commit();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occured while deleting student!!'
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

    public function module_backend(Request $request, Module $module)
    {
        $modulestudent = $module->students->pluck('id')->toArray();
        $query = Student::query();
        if ($s = $request->input('s')) {
            $query->whereRaw("first_name Like '%" . $s . "%'")->orWhereRaw("other_name Like '%" . $s . "%'")->orWhereRaw("surname Like '%" . $s . "%'")->orWhereRaw("index_number Like '%" . $s . "%'");
        }
        return $query->whereNotIn('id', $modulestudent)->get();
    }

    public function import(Request $request)
    {

        $request->validate([
            'file' => 'required|mimes:csv,txt',
        ]);

        try {

            Excel::import(new StudentsImport, request()->file('file'));
            return response()->json(['status' => 'success'])->setStatusCode(201);
        } catch (\Exception $e) {

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occured while importing data!!'
            ])->setStatusCode(500);
        }
    }


    public function results()
    {
        $id = auth()->user()->student->id;
        $student = Student::with(['results', 'results.module.module_bank', 'results.semester'])->find($id);
        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }
        return (new ResultCollection($student->results))->collection->groupBy('semester_id');
    }

    public function groups()
    {
        $id = auth()->user()->student->id;
        $student = Student::with(['groups'])->find($id);
        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }
        return new GroupCollection($student->groups);
    }
}
