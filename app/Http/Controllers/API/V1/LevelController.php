<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Level\LevelCollection;
use App\Http\Resources\V1\Level\LevelResource;
use Illuminate\Support\Facades\DB;
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
        $this->middleware('auth:sanctum', ['only' => ['store', 'update', 'delete', 'student_promotion', 'generate_group']]);
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


    public function groupStudents($num_groups, $students) {
        // Create an empty list called "groups."
        $groups = array();
        for ($i = 0; $i < $num_groups; $i++) {
            $groups[$i] = array();
        }

        // Fetch the list of students from the Laravel database.
        $num_students = count($students);

        // Create a variable "students_per_group" equal to the number of students divided by the number of groups.
        $students_per_group = floor($num_students / $num_groups);

        // Create a variable "remainder" equal to the number of students modulo the number of groups.
        $remainder = $num_students % $num_groups;

        // Iterate through the list of students, adding them to the groups list
        $student_index = 0;
        for ($group_index = 0; $group_index < $num_groups; $group_index++) {
            $students_to_add = ($group_index < $remainder) ? $students_per_group + 1 : $students_per_group;
            for ($i = 0; $i < $students_to_add; $i++) {
                $groups[$group_index][] = $students[$student_index];
                $student_index++;
            }
        }

        // Return the "groups" list as the result.
        return $groups;
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

        try{
            DB::beginTransaction();

            $no_of_group = $request->input('no_of_group');
            $students = $level->students->shuffle();


            if($no_of_group > $level->students->count() ){
                return response()->json(['message' => "Number of groups can't be greater then student capacity"])->setStatusCode(500);
            }
            $groups = $this->groupStudents($no_of_group, $students);

            foreach ($groups as $key => $group){
                foreach($group as $student){
                    $student->group_no = $key+1;
                    $student->save();
                }
            }

            $level->groups = $no_of_group;
            $level->save();

            DB::commit();
            return response()->json(['status' => 'success'])->setStatusCode(201);

         } catch (\Exception $e) {
             DB::rollBack();
            \Log::error($e->getMessage());
            return response()->json([
                'error'=>$e->getMessage(),
                'message'=>'An error occured while generation group!!'
            ])->setStatusCode(500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function destroy(Level $level)
    {
        try{
            DB::beginTransaction();

            $level->delete();

            DB::commit();
            return response()->json(null, 204);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            return response()->json([
                'error'=>$e->getMessage(),
                'message'=>'An error occured while deleting level!!'
            ])->setStatusCode(500);
        }
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

        try{
            DB::beginTransaction();

            $semester_academic = Semester::where('academic_year', $accademicYear)->pluck('id')->toArray();
            $result = Result::whereIn('semester_id', $semester_academic)->pluck('id')->toArray();
            $assessment_failed_student = Assessment::whereIn('result_id', $result)->where('remarks','fail')->pluck('student_id')->toArray();

            $levels = Level::with('students')->get();

            foreach($levels as $level){
                $lev = null;
                if($level->name == "Level 200"){
                    $lvs = Level::where('name', 'like', "Level 300")->first();
                    $lev = $lvs->id;
                }elseif($level->name == "Level 300"){
                    $lvs = Level::where('name', 'like', "Level 400")->first();
                    $lev = $lvs->id;
                }elseif($level->name == "Level 400"){
                    $lvs = Level::where('name', 'like', "Level 500")->first();
                    $lev = $lvs->id;
                }elseif($level->name == "Level 500"){
                    $lvs = Level::where('name', 'like', "Level 600")->first();
                    $lev = $lvs->id;
                }elseif($level->name == "GEM 250"){
                    $lvs = Level::where('name', 'like', "GEM 300")->first();
                    $lev = $lvs->id;
                }elseif($level->name == "GEM 300"){
                    $lvs = Level::where('name', 'like', "Level 400")->first();
                    $lev = $lvs->id;
                }

                foreach($level->students as $student){
                    if(!in_array($student->id, $assessment_failed_student)){
                        $student->level_id = $lev;
                        $student->save();
                    }
                }
            }

            $semester->promotion_status = 'open';
            $semester->save();

            DB::commit();
            return response()->json(['status' => 'success'])->setStatusCode(200);

        }catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            return response()->json([
                'error'=>$e->getMessage(),
                'message'=>'An error occured while runing promotions for student!!'
            ])->setStatusCode(500);
        }
    }
}