<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Module\ModuleCollection;
use App\Models\Lecturer;
use App\Models\LecturerModule;
use App\Models\Level;
use App\Models\Module;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ModuleCollection(Module::orderBy('id', 'DESC')->get());
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

        $lecturers = json_decode($request->input('lecturer'));
        $start_date = Carbon::parse($request->input('start_date'));
        $end_date = Carbon::parse($request->input('start_date'))->addWeeks($request->input('duration'));
        $lects = Lecturer::find($lecturers);
        $students = Level::find($request->input('level'))->students;

        $module = Module::create([
            'cordinator_id' => $request->input('cordinator'),
            'title' => $request->input('title'),
            'code' => strtoupper(
                $request->input('code')
            ),
        ]);

        $status = "upcoming";
        if ($start_date > now()) {
            $status = "upcoming";
        } elseif (Carbon::now()->between($start_date, $end_date)) {
            $status = 'active';
        } elseif (now() > $end_date) {
            $status = "past";
        }

        // lecturer module attachment
        $module->lectures()->attach($lects, [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'status' => $status,
            'course_rep_id' => $request->input('course_rep'),
            'level_id' => $request->input('level'),
        ]);

        // module students attachment
        $module->students()->attach($students);

        return response()->json(['status' => 'module-mounted-succesffully'])
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
        $request->validate([
            'title' => 'required|string|max:225|unique:modules,title,' . $module->id,
            'code' => 'required|string|max:10|unique:modules,code,' . $module->id,
            'level' => 'required|exists:levels,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'lecturer' => 'required|exists:lecturers,id',
            'cordinator' => 'required|exists:lecturers,id',
            'course_rep' => 'required|exists:students,id',
        ]);

        $start_date = Carbon::parse($request->input('start_date'));
        $end_date = Carbon::parse($request->input('end_date'));

        $lects = Lecturer::find($request->input('lecturer'));
        $students = Level::find($request->input('level'))->students;

        // update module info
        $module->update([
            'cordinator_id' => $request->input('cordinator'),
            'title' => $request->input('title'),
            'code' => strtoupper(
                $request->input('code')
            ),
        ]);

        $status = "upcoming";
        if ($start_date > now()) {
            $status = "upcoming";
        } elseif (Carbon::now()->between($start_date, $end_date)) {
            $status = 'active';
        } elseif (now() > $end_date) {
            $status = "past";
        }

        $check = LecturerModule::where('module_id', $module->id)->where('lecturer_id')->first();
        if ($check) {
        }
        // // lecturer module attachment
        // $module->lectures()->attach($lects, [
        //     'start_date' => $start_date,
        //     'end_date' => $end_date,
        //     'status' => $status,
        //     'course_rep_id' => $request->input('course_rep'),
        //     'level_id' => $request->input('level'),
        // ]);

        // // module students attachment
        // $module->students()->attach($students);

        //TODO: send course and lecture rep email
        return response()->json(['status' => 'module-editted'])
            ->setStatusCode(201);
        // "module-editted"
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