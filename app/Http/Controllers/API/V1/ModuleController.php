<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Module\ModuleCollection;
use App\Http\Resources\V1\Module\ModuleResource;
use Illuminate\Support\Facades\DB;
use App\Models\Lecturer;
use App\Models\Level;
use App\Models\Module;
use App\Models\Semester;
use App\Models\Result;
use App\Models\Student;
use App\Models\Assessment;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['only' => ['store', 'update', 'destroy', 'end_module']]);
    }

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
        $modules = Module::where('semester_id', $this->semester())->orderBy('id', 'DESC')->with(['module_bank', 'lecturers', 'level', 'cordinator', 'course_rep', 'attendances'])->get();
        return new ModuleCollection($modules);
    }

    public function cordinating_modules(Lecturer $lecturer)
    {
        $modules = Module::where('semester_id', $this->semester())->where('cordinator_id', $lecturer->id)->orderBy('id', 'DESC')->with(['module_bank'])->get();
        return new ModuleCollection($modules);
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
            'module' => 'required|exists:module_banks,id',
            'cordinator' => 'required|exists:lecturers,id',
            'course_rep' => 'required|exists:students,id',
            'level' => 'required|exists:levels,id',
            'start_date' => 'required|date',
            'duration' => 'required|numeric',
            'lecturer' => 'required|array|min:1',
        ]);

        try{
            DB::beginTransaction();

            $check = Module::where('semester_id',$this->semester())->where('module_bank_id',$request->input('module'))->where('level_id',$request->input('level'))->first();
            if($check){
                return response()->json([
                    'errors'=>[
                        'msg' => "Module for level already exist!"
                    ]
                ])->setStatusCode(422);
            }
            $start_date = Carbon::parse($request->input('start_date'));
            $end_date = Carbon::parse($request->input('start_date'))->addWeeks($request->input('duration'));

            // create module
            $module = Module::create([
                'semester_id' => $this->semester(),
                'module_bank_id' => $request->input('module'),
                'cordinator_id' => $request->input('cordinator'),
                'course_rep_id' => $request->input('course_rep'),
                'level_id' => $request->input('level'),
                'start_date' => $start_date,
                'end_date' => $end_date,
                'status' => $this->status($start_date, $end_date),
            ]);

            // lecturer module attachment
            $lecturers = Lecturer::find($request->input('lecturer'));
            $module->lecturers()->attach($lecturers);

            // module students attachment
            $module->students()->attach($module->level->students);

            DB::commit();
            return response()->json(['status' => 'success'])
                ->setStatusCode(201);
        }catch(\Exception $e){
            // Rollback & Return Error Message
            DB::rollBack();
            \Log::error($e->getMessage());
            return response()->json([
                'error'=>$e->getMessage(),
                'message'=>'An error occured while mounting module!!'
            ])->setStatusCode(500);
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
        return (new ModuleResource($module->loadMissing(['lecturers', 'module_bank', 'level', 'cordinator', 'course_rep', 'attendances.students', 'students', 'attendances_lecturer'])))
            ->response()
            ->setStatusCode(200);
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
        if (!$this->semester()) {
            return response()->json(['message' => "set-semester"])->setStatusCode(403);
        }
        $prev_module = Module::find($request->id);

        $request->validate([
            'module' => 'required|numeric|exists:module_banks,id',
            'cordinator' => 'required|numeric|exists:lecturers,id',
            'course_rep' => 'required|numeric|exists:students,id',
            'level' => 'required|numeric|exists:levels,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'lecturer' => 'required|array|min:1',
        ]);

        try{
            DB::beginTransaction();

            $start_date = Carbon::parse($request->input('start_date'));
            $end_date = Carbon::parse($request->input('end_date'));

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

            // lecturer module attachment
            if (array_diff($request->input('lecturer'), $prev_module->lecturers->pluck('id')->toArray())) {
                $module->lecturers()->detach($prev_module->lecturers);
                $lecturers = Lecturer::find($request->input('lecturer'));
                $module->lecturers()->attach($lecturers);
            }

            // module students attachment
            if ($prev_module->level_id != $module->level_id) {
                $module->students()->detach($prev_module->level->students);
                $module->students()->attach($module->level->students);
            }

            DB::commit();
            return response()->json(['status' => 'success'])
                ->setStatusCode(201);

        }catch(\Exception $e){
             // Rollback & Return Error Message
            DB::rollBack();
            \Log::error($e->getMessage());
            return response()->json([
                'error'=>$e->getMessage(),
                'message'=>'An error occured while updating module!!'
            ])->setStatusCode(500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function destroy(Module $module)
    {
        // check if semester is set
        if (!$this->semester()) {
            return response()->json(['message' => "set-semester"])->setStatusCode(403);
        }
        try{
            DB::beginTransaction();

            if($module->status == 'upcoming'){
                $module->delete();
            }

            DB::commit();
            return response()->json(null, 204);

        }catch(\Exception $e){
             // Rollback & Return Error Message
            DB::rollBack();
            \Log::error($e->getMessage());
            return response()->json([
                'error'=>$e->getMessage(),
                'message'=>'An error occured while deleting module!!'
            ])->setStatusCode(500);
        }
    }



     public function end_module(Module $module)
    {
        // check if semester is set
        if (!$this->semester()) {
            return response()->json(['message' => "set-semester"])->setStatusCode(403);
        }

        try{
            DB::beginTransaction();

            $end_date = Carbon::parse(now());

            // update module info
            $module->update([
                'end_date' => $end_date,
                // 'status' => "inactive",
            ]);

            // $result = Result::firstOrCreate([
            //     'semester_id' => $this->semester(),
            //     'module_id' => $module->id,
            //     'cordinator_id' => $module->cordinator_id,
            // ]);

            // foreach($this->students as $student){
            //     Assessment::firstOrCreate([
            //         'result_id' => $result->id,
            //         'student_id' => $student->id,
            //     ]);
            // }

            DB::commit();
            return response()->json(['status' => 'success'])
                ->setStatusCode(201);

        }catch(\Exception $e){
             // Rollback & Return Error Message
            DB::rollBack();
            \Log::error($e->getMessage());
            return response()->json([
                'error'=>$e->getMessage(),
                'message'=>'An error occured while ending module!!'
            ])->setStatusCode(500);
        }
    }


    public function add_student(Request $request, Module $module)
    {
        // check if semester is set
        if (!$this->semester()) {
            return response()->json(['message' => "set-semester"])->setStatusCode(403);
        }

        $request->validate([
            'id' => 'required|numeric|exists:modules,id',
            'student' => 'required|array|min:1',
        ]);

        try{
            DB::beginTransaction();

            $students = Student::find($request->input('student'));
            $module->students()->attach($students);

            DB::commit();
            return response()->json(['status' => 'success'])
                ->setStatusCode(201);

        }catch(\Exception $e){
            DB::rollBack();
            \Log::error($e->getMessage());
            return response()->json([
                'error'=>$e->getMessage(),
                'message'=>'An error occured while add student to module!!'
            ])->setStatusCode(500);
        }
    }

}