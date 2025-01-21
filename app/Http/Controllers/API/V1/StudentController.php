<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\API\V1\Student\StudentCollection;
use App\Http\Resources\API\V1\Student\StudentResource;
use App\Http\Resources\API\V1\Result\ResultCollection;
use App\Http\Resources\API\V1\Group\GroupCollection;
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
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

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
        try {
            $students = Student::orderByDesc('id')->with(['level', 'user'])->get();
            return new StudentCollection($students);
        } catch (Exception $e) {
            Log::error('Error fetching students: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching students'], 500);
        }
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
            'phone' => 'nullable|string|max:15',
            'picture' => 'nullable|file|mimes:jpeg,png,webp',
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
                'username' => strtoupper($request->input('index_number')),
                'email_verified_at' => now(),
                'password' => Hash::make(strtolower(str_replace("/", "", $request->input('index_number')))),
            ]);

            Student::create([
                'user_id' => $user->id,
                'index_number' => strtoupper($request->input('index_number')),
                'first_name' => $request->input('first_name'),
                'other_name' => $request->other_name,
                'surname' => $request->input('surname'),
                'phone' => $request->phone,
                'picture' => $picture_url,
                'level_id' => $request->input('level'),
            ]);

            DB::commit();
            return response()->json(['status' => 'success'])->setStatusCode(201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error storing student: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occurred while adding a student!!'
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
        try {
            return (new StudentResource($student))
                ->response()
                ->setStatusCode(200);
        } catch (Exception $e) {
            Log::error('Error showing student: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching student details'], 500);
        }
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
            'phone' => 'nullable|string|max:15',
            'picture' => 'nullable|file|mimes:jpeg,png,webp',
            'level' => 'required|numeric|exists:levels,id',
        ]);

        try {
            DB::beginTransaction();

            $picture_url = null;
            if ($request->hasFile('picture')) {
                if ($student->picture) {
                    $student_picture = explode("/", $student->picture);
                    $picture = end($student_picture);
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
                'phone' => $request->phone,
                'picture' => $picture_url,
                'level_id' => $request->input('level'),
            ]);

            $student->user->update([
                'email' => $request->input('email'),
                'username' => strtoupper($request->input('index_number')),
            ]);

            DB::commit();
            return response()->json(['status' => 'success'])->setStatusCode(201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating student: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occurred while updating a student details!!'
            ])->setStatusCode(500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
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

            // Check if the student has a picture and delete it from storage
            if ($student->picture) {
                $student_picture = explode("/", $student->picture);
                $picture = end($student_picture);
                $exist = File::exists(Helper::imagePath('students/' . $picture));
                if ($exist) {
                    File::delete(Helper::imagePath('students/' . $picture));
                }
            }

            // Delete the associated user
            $student->user->delete();

            DB::commit();
            Log::info('Student deleted successfully: ' . $student->id);
            return response()->json(null, 204);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting student: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occurred while deleting student!!'
            ])->setStatusCode(500);
        }
    }

    /**
     * Backend search for students.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function backend(Request $request)
    {
        try {
            $query = Student::query();

            if ($s = $request->input('s')) {
                $query->whereRaw("first_name Like '%" . $s . "%'")->orWhereRaw("other_name Like '%" . $s . "%'")->orWhereRaw("surname Like '%" . $s . "%'")->orWhereRaw("index_number Like '%" . $s . "%'");
            }

            return $query->get();
        } catch (Exception $e) {
            Log::error('Error in backend search: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while searching for students'], 500);
        }
    }

    /**
     * Backend search for students not in a module.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function module_backend(Request $request, Module $module)
    {
        try {
            $module_student = $module->students->pluck('id')->toArray();
            $query = Student::query();
            if ($s = $request->input('s')) {
                $query->whereRaw("first_name Like '%" . $s . "%'")->orWhereRaw("other_name Like '%" . $s . "%'")->orWhereRaw("surname Like '%" . $s . "%'")->orWhereRaw("index_number Like '%" . $s . "%'");
            }
            return $query->whereNotIn('id', $module_student)->get();
        } catch (Exception $e) {
            Log::error('Error in module backend search: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while searching for students not in the module'], 500);
        }
    }

    /**
     * Import students from a file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt',
        ]);

        try {
            Excel::import(new StudentsImport, request()->file('file'));
            return response()->json(['status' => 'success'])->setStatusCode(201);
        } catch (Exception $e) {
            Log::error('Error importing students: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occurred while importing data!!'
            ])->setStatusCode(500);
        }
    }

    /**
     * Get results for the authenticated student.
     *
     * @return \Illuminate\Http\Response
     */
    public function results()
    {
        try {
            $id = auth()->user()->student->id;
            $student = Student::with(['results', 'results.module.module_bank', 'results.semester'])->find($id);
            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }
            return (new ResultCollection($student->results))->collection->groupBy('semester_id');
        } catch (Exception $e) {
            Log::error('Error fetching student results: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching student results'], 500);
        }
    }

    /**
     * Get groups for the authenticated student.
     *
     * @return \Illuminate\Http\Response
     */
    public function groups()
    {
        try {
            $id = auth()->user()->student->id;
            $student = Student::with(['groups'])->find($id);
            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }
            return new GroupCollection($student->groups);
        } catch (Exception $e) {
            Log::error('Error fetching student groups: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching student groups'], 500);
        }
    }
}
