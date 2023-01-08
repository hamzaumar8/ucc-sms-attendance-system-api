<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Result\ResultCollection;
use App\Http\Resources\V1\Result\ResultResource;
use App\Models\Result;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ResultController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum', ['only' => ['store', 'update', 'destroy', 'cordinating_module',]]);
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
        $results = Result::where('semester_id', $this->semester())->orderBy('id', 'DESC')->with(['module.cordinator', 'module.module_bank']);
        return new ResultCollection($results->get());
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
     * @param  \App\Models\Result  $result
     * @return \Illuminate\Http\Response
     */
    public function show(Result $result)
    {
        return (new ResultResource($result->loadMissing(['module.cordinator', 'module.module_bank', 'assessments'])))
            ->response()
            ->setStatusCode(200);
    }


    public function remarks($score){
        $score = (int)$score;
        if ($score === 0 || $score === 0.00) {
            $remark = 'ic';
        } elseif ($score >= 79.5) {
            $remark = 'honour';
        } elseif ($score >= 49.5) {
            $remark = 'pass';
        } else {
            $remark = 'fail';
        }
        return $remark;
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Result  $result
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Result $result)
    {
         $this->validate($request, [
            'assessments.*.score' => 'required|numeric|between:0,100',
        ]);

        try{
            $assessments = $result->assessments;
            foreach ($assessments as $key => $assessment) {
                $score = $request->assessments[$key]['score'];
                $assessment->score = $score;
                $assessment->remarks = $this->remarks($score);
                $assessment->save();
            }
            $result->status = $request->status;
            $result->save();

            return response()->json(['status' => 'success'])
                ->setStatusCode(201);

        }catch(\Exception $e){
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>'An error occured while updating a results!!'
            ])->setStatusCode(500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Result  $result
     * @return \Illuminate\Http\Response
     */
    public function destroy(Result $result)
    {
        //
    }


    /**
     * Cordinating Module the specified resource from storage.
     *
     * @param  \App\Models\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function cordinating_module()
    {
        $lecturer_id = auth()->user()->lecturer->id;
        $results = Result::where('semester_id', $this->semester())->where('cordinator_id', $lecturer_id)->orderBy('id', 'DESC')->with(['module.module_bank']);
        return new ResultCollection($results->get());
    }

    /**
     * Cordinating Module the specified resource from storage.
     *
     * @param  \App\Models\Module  $module
     * @return \Illuminate\Http\Response
     */
    public function promotion_check()
    {
        $results = Result::where('semester_id', $this->semester())->pluck('status')->toArray();
        $data = "unset";
        if(!in_array('save',$results)){
            $data = "set";
        }
        return response()->json(['data' => $data])->setStatusCode(200);
    }
}