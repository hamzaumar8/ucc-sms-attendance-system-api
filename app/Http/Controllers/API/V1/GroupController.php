<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\V1\Group\GroupCollection;
use App\Http\Resources\V1\Group\GroupResource;
use App\Models\Level;
use App\Models\Semester;
use App\Helpers\Helper;
use Illuminate\Http\Request;

class GroupController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum', ['only' => ['store', 'update', 'destroy']]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $semester_id = Helper::semester();
        $groups = Group::where('semester_id', $semester_id)->with('level')->get();
        return new GroupCollection($groups);
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
            'name' => 'required|string|unique:groups,name,NULL,id,level_id,' . $request->level,
            'level' => 'required|numeric|exists:levels,id',
            'no_of_group' => 'required|numeric|min:2',
        ]);

        try {
            DB::beginTransaction();

            $level = Level::findOrFail($request->input('level'));
            $no_of_group = $request->input('no_of_group');
            $students = $level->students->shuffle();

            // check if number of group is greater than
            if ($no_of_group > $level->students->count()) {
                return response()->json(['message' => "Number of groups can't be greater then student capacity"])->setStatusCode(500);
            }

            // Generete Groups for students base on Level
            $groups = Helper::groupStudents($no_of_group, $students);

            // create Group
            $group = Group::create([
                'semester_id' => Helper::semester(),
                'level_id' => $level->id,
                'name' => $request->input('name'),
                'groups' => $no_of_group,
            ]);


            foreach ($groups as $key => $grp) {
                // get all student Id from grp array
                $ids = array_column($grp, 'id');
                // module students attachment
                $group->students()->attach($ids, [
                    'group_no' => $key + 1,
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'success'])->setStatusCode(201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occured while generation group!!'
            ])->setStatusCode(500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function show(Group $group)
    {
        return (new GroupResource(
            $group->loadMissing(['level', 'students'])
        ))->response()->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Group $group)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        try {
            DB::beginTransaction();

            $group->delete();

            DB::commit();
            return response()->noContent();
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occured while deleting level!!'
            ])->setStatusCode(500);
        }
    }
}
