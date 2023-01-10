<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\V1\Attendance\AttendanceResource;
use App\Http\Resources\V1\Attendance\AttendanceCollection;
use App\Http\Resources\V1\Attendance\AttendanceSingleCollection;
use App\Models\Attendance;
use App\Models\Semester;
use App\Models\Module;
use App\Models\LecturerModule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['only' => ['store', 'update', 'destroy']]);
    }


    public function semester()
    {
        $semester_id = null;
        $semester =  Semester::whereDate('start_date', '<=', Carbon::now()->format('Y-m-d'))->whereDate('end_date', '>=', Carbon::now()->format('Y-m-d'))->first();
        if ($semester) {
            $semester_id = $semester->id;
        }
        return  $semester_id;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $attendances = Attendance::where('semester_id', $this->semester())->orderBy('id', 'DESC')->get();
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
        if (!$this->semester()) {
            return response()->json(['message' => "set-semester"])->setStatusCode(403);
        }

        $request->validate([
            'lecturer_id' => 'required|exists:lecturers,id',
            'module_id' => 'required|exists:modules,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        try{
            DB::beginTransaction();

            $date = Carbon::parse($request->input('date'))->format('Y-m-d');
            $check = Attendance::where('semester_id',$this->semester())->where('module_id',$request->input('module_id'))->where('lecturer_id',$request->input('lecturer_id'))->where('date',$date)->first();
            if($check){
                return response()->json([
                    'errors'=>[
                        'msg' => "Attendance for this module has already been taken!"
                    ]
                ])->setStatusCode(422);
            }

            $attendance = Attendance::create([
                'lecturer_id' => $request->input('lecturer_id'),
                'module_id' => $request->input('module_id'),
                'semester_id' => $this->semester(),
                'date' => $date,
                'start_time' => $request->input('start_time'),
                'end_time' => $request->input('end_time'),
                'status' => 'present',
            ]);


            $module = Module::find($request->input('module_id'));
            $attendance->students()->attach($module->students, ['semester_id' => $this->semester()]);

            DB::commit();
            return response()->json(['status' => 'success'])
                ->setStatusCode(201);

        }catch(\Exception $e){
            // Rollback & Return Error Message
            DB::rollBack();
            \Log::error($e->getMessage());
            return response()->json([
                'error'=>$e->getMessage(),
                'message'=>'An error occured while checking in attendance!!'
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
        return (new AttendanceResource($attendance->loadMissing(['attendance_student'])))->response()->setStatusCode(200);
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

    public function lecturers_attendances()
    {
        $lecturer_id = auth()->user()->lecturer->id;
        $attendances = Attendance::where('lecturer_id', $lecturer_id)->where('semester_id', $this->semester())->with(['module.module_bank'])->orderBy('id', 'DESC')->get();
        return  AttendanceResource::collection($attendances);
    }
}