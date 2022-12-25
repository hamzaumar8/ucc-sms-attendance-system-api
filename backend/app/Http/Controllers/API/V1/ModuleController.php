<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Module\ModuleCollection;
use App\Models\Lecturer;
use App\Models\Level;
use App\Models\Module;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ModuleCollection(Module::all());
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
            'title' => 'required|string|max:225|unique:modules',
            'code' => 'required|string|max:10|unique:modules',
            'level' => 'required|exists:levels,id',
            'start_date' => 'required|date',
            'duration' => 'required|numeric',
            'lecturer' => 'required',
            'cordinator' => 'required|exists:lecturers,id',
            'course_rep' => 'required|exists:students,id',
        ]);
        try {
            $lecturers = json_decode($request->lecturer);
            $start_date = Carbon::parse($request->start_date);
            $end_date = Carbon::parse($request->start_date)->addWeeks($request->duration);
            $lects = Lecturer::find($lecturers);
            $students = Level::find($request->level)->students;

            $module = Module::create([
                'cordinator_id' => $request->cordinator,
                'title' => $request->title,
                'code' => $request->code,
            ]);

            $status = "upcoming";
            if ($start_date > now()) {
                $status = "upcoming";
            } elseif ($start_date <= now() || $end_date >= now()) {
                $status = 'active';
            } elseif (now() > $end_date) {
                $status = "past";
            }

            // lecturer module attachment
            $module->lectures()->attach($lects, [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'status' => $status,
                'course_rep_id' => $request->course_rep,
            ]);

            // module students attachment
            $module->students()->attach($students);

            //TODO: send course and lecture rep email
            return response()->json(['status' => 'module-mounted-succesffully'])
                ->setStatusCode(201);
        } catch (Exception $ex) {
            return response()->json(['error' => 'Exception Message: ' . $ex->getMessage()])->setStatusCode(500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function show(Module $module)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Module $module)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function destroy(Module $module)
    {
        //
    }
}