<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Module\ModuleCollection;
use App\Models\Lecturer;
use App\Models\LecturerModule;
use App\Models\Level;
use App\Models\Module;
use App\Models\Semester;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
{
    public function status($start_date, $end_date)
    {
        $status = "upcoming";
        if ($start_date > Carbon::now()) {
            $status = "upcoming";
        } elseif (Carbon::now()->between($start_date, $end_date)) {
            $status = 'active';
        } elseif (Carbon::now() > $end_date) {
            $status = "inactive";
        }
        return $status;
    }

    public function semester()
    {
        return Semester::whereDate('start_date', '<=', Carbon::now()->format('Y-m-d'))->whereDate('end_date', '>=', Carbon::now()->format('Y-m-d'))->first();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ModuleCollection(Module::where('semester_id', $this->semester()->id)->orderBy('id', 'DESC')->get());
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
        if (!$this->semester()->id) {
            return response()->json(['message' => "set-semester"])->setStatusCode(403);
        }

        $request->validate([
            'module' => 'required|exists:module_banks,id',
            'cordinator' => 'required|exists:lecturers,id',
            'course_rep' => 'required|exists:students,id',
            'level' => 'required|exists:levels,id',
            'start_date' => 'required|date',
            'duration' => 'required|numeric',
            'lecturer' => 'required',
        ]);
        $start_date = Carbon::parse($request->input('start_date'));
        $end_date = Carbon::parse($request->input('start_date'))->addWeeks($request->input('duration'));

        // create module
        $module = Module::create([
            'semester_id' => $this->semester()->id,
            'module_bank_id' => $request->input('module'),
            'cordinator_id' => $request->input('cordinator'),
            'course_rep_id' => $request->input('course_rep'),
            'level_id' => $request->input('level'),
            'start_date' => $start_date,
            'end_date' => $end_date,
            'status' => $this->status($start_date, $end_date),
        ]);

        // lecturer module attachment
        $lecturers = Lecturer::find(json_decode($request->input('lecturer')));
        $module->lectures()->attach($lecturers);

        // module students attachment
        $module->students()->attach($module->level->students);

        return response()->json(['status' => 'success'])
            ->setStatusCode(201);
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
        // check if semester is set
        if (!$this->semester()->id) {
            return response()->json(['message' => "set-semester"])->setStatusCode(403);
        }

        $request->validate([
            'module' => 'required|exists:module_banks,id',
            'cordinator' => 'required|exists:lecturers,id',
            'course_rep' => 'required|exists:students,id',
            'level' => 'required|exists:levels,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'lecturer' => 'required',
        ]);

        $start_date = Carbon::parse($request->input('start_date'));
        $end_date = Carbon::parse($request->input('start_date'));

        // update module info
        $module->update([
            'module_bank_id' => $request->input('module'),
            'cordinator_id' => $request->input('cordinator'),
            'course_rep_id' => $request->input('course_rep'),
            'level_id' => $request->input('level'),
            'start_date' => $start_date,
            'end_date' => $end_date,
            'status' => $this->status($start_date, $end_date),
        ]);

        $check = LecturerModule::where('module_id', $module->id)->where('lecturer_id')->first();
        if ($check) {
        }
        // lecturer module attachment
        $lecturers = Lecturer::find(json_decode($request->input('lecturer')));
        $module->lectures()->attach($lecturers);

        // module students attachment
        $module->students()->attach($module->level->students);

        return response()->json(['status' => 'success'])
            ->setStatusCode(201);
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