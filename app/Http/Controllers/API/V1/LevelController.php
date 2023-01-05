<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Level\LevelCollection;
use App\Http\Resources\V1\Level\LevelResource;
use App\Models\Level;
use App\Models\Assessment;
use App\Models\Semester;
use App\Models\Result;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['only' => ['store', 'update', 'delete', 'student_promotion']]);
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
        return new LevelCollection(Level::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function show(Level $level)
    {
        return (new LevelResource(
            $level->loadMissing(['students'])
        ))->response()->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Level $level)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function generate_group(Request $request, Level $level)
    {
         $request->validate([
            'level' => 'required|numeric|exists:levels,id',
            'no_of_group' => 'required|numeric|min:2',
        ]);

        $no_of_group = $request->input('no_of_group');
        $student_capacity = $level->students->count();
        $chunk = intdiv($student_capacity, $no_of_group);

        if($no_of_group > $student_capacity ){
            return response()->json(['message' => "Number of groups can't be greater then student capacity"])->setStatusCode(500);
        }
        $students = $level->students->shuffle();
        $chunks = $students->chunk($chunk);
        foreach ($chunks as $key => $ck){
            foreach($ck as $student){
                $student->group_no = $key+1;
                $student->save();
            }
        }

        $level->groups = $no_of_group;
        $level->save();

        return response()->json(['status' => 'success'])->setStatusCode(201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function destroy(Level $level)
    {
        //
    }

    public function backend(Request $request)
    {
        $query = Level::query();

        if ($s = $request->input('s')) {
            $query->whereRaw("name Like '%" . $s . "%'");
        }

        return $query->get();
    }


    public function student_promotion(Request $request, Semester $semester)
    {

        $currentYear = Carbon::now()->format('Y');
        $lastYear = Carbon::now()->subYear()->format('Y');
        $accademicYear = $lastYear."-".$currentYear;

        $semester_academic = Semester::where('academic_year', $accademicYear)->pluck('id')->toArray();
        $result = Result::whereIn('semester_id', $semester_academic)->pluck('id')->toArray();
        $assessment_failed_student = Assessment::whereIn('result_id', $result)->where('remarks','fail')->pluck('student_id')->toArray();

        $levels = Level::with('students')->get();

        foreach($levels as $level){
            $lev = null;
            if($level->name == "Level 200"){
                $lvs = Level::where('name', 'like', "Level 300")->first();
                $lev = $level->id;
            }elseif($level->name == "Level 300"){
                $lvs = Level::where('name', 'like', "Level 400")->first();
                $lev = $level->id;
            }elseif($level->name == "Level 400"){
                $lvs = Level::where('name', 'like', "Level 500")->first();
                $lev = $level->id;
            }elseif($level->name == "Level 500"){
                $lvs = Level::where('name', 'like', "Level 600")->first();
                $lev = $level->id;
            }elseif($level->name == "Level 600"){
                $lev = null;
            }elseif($level->name == "GEM 250"){
                $lvs = Level::where('name', 'like', "GEM 300")->first();
                $lev = $level->id;
            }elseif($level->name == "GEM 300"){
                $lvs = Level::where('name', 'like', "Level 400")->first();
                $lev = $level->id;
            }

            foreach($level->students as $student){
                if(!in_array($student->id, $assessment_failed_student)){
                    $student->update(['level_id'=>$lev]);
                }
            }
        }

        $semester->promotion_status = 'open';
        $semester->save();

        return response()->json(['status' => 'success'])->setStatusCode(200);
    }
}