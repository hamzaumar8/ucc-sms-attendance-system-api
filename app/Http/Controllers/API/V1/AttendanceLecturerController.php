<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AttendanceLecturer\AttendanceLecturerResource;
use App\Models\AttendanceLecturer;
use App\Traits\SemesterTrait;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceLecturerController extends Controller
{
    use SemesterTrait;

    private $semesterId;

    public function __construct()
    {
        $this->semesterId = $this->getCurrentSemesterId();
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $attendances = AttendanceLecturer::where('semester_id', $this->semesterId)->orderBy('id', 'DESC')->get();
        return AttendanceLecturerResource::collection($attendances);
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
        if (!$this->semesterId) {
            return response()->json(['message' => "set-semester"])->setStatusCode(403);
        }

        $request->validate([
            'lecturer_id' => 'required|exists:lecturers,id',
            'module_id' => 'required|exists:modules,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $date = Carbon::parse($request->input('date'))->format('Y-m-d');
            $check = AttendanceLecturer::where('semester_id', $this->semesterId)->where('module_id', $request->input('module_id'))->where('lecturer_id', $request->input('lecturer_id'))->where('date', $date)->first();
            if ($check) {
                return response()->json([
                    'errors' => [
                        'msg' => "Attendance for this module has already been taken!"
                    ]
                ])->setStatusCode(422);
            }

            $attendance = AttendanceLecturer::create([
                'lecturer_id' => $request->input('lecturer_id'),
                'module_id' => $request->input('module_id'),
                'semester_id' => $this->semesterId,
                'date' => $date,
                'start_time' => $request->input('start_time'),
                'end_time' => $request->input('end_time'),
                'status' => 'present',
            ]);

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
     * @param  \App\Models\AttendanceLecturer  $attendanceLecturer
     * @return \Illuminate\Http\Response
     */
    public function show(AttendanceLecturer $attendanceLecturer)
    {
        return (new AttendanceLecturerResource($attendanceLecturer->loadMissing(['attendance_student', 'module.module_bank', 'module.students', 'module.students'])))->response()->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AttendanceLecturer  $attendanceLecturer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AttendanceLecturer $attendanceLecturer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AttendanceLecturer  $attendanceLecturer
     * @return \Illuminate\Http\Response
     */
    public function destroy(AttendanceLecturer $attendanceLecturer)
    {
        //
    }


    public function lecturers_attendances()
    {
        $lecturer_id = auth()->user()->lecturer->id;
        $attendances = AttendanceLecturer::where('lecturer_id', $lecturer_id)->where('semester_id', $this->semesterId)->with(['module.module_bank', 'module.level'])->orderBy('id', 'DESC')->get();
        return  AttendanceLecturerResource::collection($attendances);
    }
}
