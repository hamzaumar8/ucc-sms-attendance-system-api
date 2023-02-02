<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\V1\Attendance\AttendanceResource;
use App\Http\Resources\V1\Attendance\AttendanceCollection;
use App\Models\Attendance;
use App\Models\Semester;
use App\Models\Module;
use App\Models\LecturerModule;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['only' => ['store', 'update', 'destroy', 'course_rep_attendances']]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $attendances = Attendance::where('semester_id', Helper::semester())->orderBy('id', 'DESC')->get();
        return AttendanceResource::collection($attendances);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // check if semester is set
        if (!Helper::semester()) {
            return response()->json(['message' => "set-semester"])->setStatusCode(403);
        }

        $request->validate([
            'lecturer_id' => 'required|exists:lecturers,id',
            'module_id' => 'required|exists:modules,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'status' => 'required:string',
            'students' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();
            $course_rep_id = auth()->user()->student->id;
            $students = $request->input('students');
            $date = Carbon::parse($request->input('date'))->format('Y-m-d');
            $check = Attendance::where('semester_id', Helper::semester())->where('module_id', $request->input('module_id'))->where('lecturer_id', $request->input('lecturer_id'))->where('date', $date)->first();
            if ($check) {
                return response()->json([
                    'errors' => [
                        'msg' => "Attendance for this module has already been taken!"
                    ]
                ])->setStatusCode(422);
            }

            $attendance = Attendance::create([
                'semester_id' => Helper::semester(),
                'lecturer_id' => $request->input('lecturer_id'),
                'course_rep_id' => $course_rep_id,
                'module_id' => $request->input('module_id'),
                'date' => $date,
                'start_time' => $request->input('start_time'),
                'end_time' => $request->input('end_time'),
                'status' => $request->input('status'),
            ]);


            $module = Module::find($request->input('module_id'));
            $attendance->students()->attach($module->students, ['semester_id' => Helper::semester()]);

            if (count($students) !== 0) {
                $attendance->students()->updateExistingPivot($students, ['status' => 1]);
            }

            DB::commit();
            return response()->json(['status' => 'success'])
                ->setStatusCode(201);
        } catch (\Exception $e) {
            // Rollback & Return Error Message
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occurred while checking in attendance!!'
            ])->setStatusCode(500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function show(Attendance $attendance)
    {
        return (new AttendanceResource($attendance->loadMissing(['attendance_student', 'module.module_bank', 'module.students', 'module.students'])))->response()->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Attendance $attendance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function destroy(Attendance $attendance)
    {
        //
    }

    public function course_rep_attendances()
    {
        $course_rep_id = auth()->user()->student->id;
        $attendances = Attendance::where('course_rep_id', $course_rep_id)->where('semester_id', Helper::semester())->with(['module.module_bank'])->orderBy('id', 'DESC')->get();
        return  AttendanceResource::collection($attendances);
    }
}
