<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Attendance\AttendanceResource;
use App\Models\Attendance;
use App\Models\LecturerModule;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'lecturer_id' => 'required|exists:lecturers,id',
            'module_id' => 'required|exists:modules,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $lecturer_module = LecturerModule::where('lecturer_id', $request->lecturer_id)->where('module_id', $request->module_id)->first();
        $attendance = Attendance::create([
            'lecturer_module_id' => $lecturer_module->id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'present',
        ]);

        return response()->json(['status' => 'attendance-checked-in'])
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Attendance  $attendance
     * @return \Illuminate\Http\Response
     */
    public function show(Attendance $attendance)
    {
        //
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
        $lecturrer_module = LecturerModule::where('lecturer_id', $lecturer_id)->get();
        dd($lecturer_id);
    }
}