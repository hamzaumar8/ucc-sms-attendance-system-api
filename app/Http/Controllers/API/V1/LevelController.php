<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Level\LevelCollection;
use App\Http\Resources\V1\Level\LevelResource;
use App\Models\Level;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['only' => ['store', 'update', 'delete']]);
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
}