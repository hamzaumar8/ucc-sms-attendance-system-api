<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Semester\SemesterResource;
use App\Models\Semester;
use App\Traits\SemesterTrait;
use App\Traits\UtilsTrait;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;

class SemesterController extends Controller
{
    use SemesterTrait, UtilsTrait;

    protected $semesterId;

    public function __construct()
    {
        $this->semesterId = $this->getCurrentSemesterId();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $semester = $this->getCurrentSemester();
        if ($semester != null) {
            return (new SemesterResource($semester))->response()->setStatusCode(200);
        }
        return response()->json(['data' => null]);
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
            'semester_name' => 'required|string',
            'academic_year' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $start_date = Carbon::parse($request->input('start_date'));
            $end_date = Carbon::parse($request->input('end_date'));

            // $promation
            // create module
            $semester = Semester::create([
                'semester' => $request->input('semester_name'),
                'academic_year' => $request->input('academic_year'),
                'start_date' => $start_date,
                'end_date' => $end_date,
            ]);

            DB::commit();
            return response()->json(['status' => 'success'])
                ->setStatusCode(201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occurred while setting semester!!'
            ])->setStatusCode(500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Semester  $semester
     * @return \Illuminate\Http\Response
     */
    public function show(Semester $semester)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Semester  $semester
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Semester $semester)
    {
        $request->validate([
            'semester_name' => 'required|string',
            'academic_year' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $start_date = Carbon::parse($request->input('start_date'));
            $end_date = Carbon::parse($request->input('end_date'));

            // create module
            $semester->update([
                'semester' => $request->input('semester_name'),
                'academic_year' => $request->input('academic_year'),
                'start_date' => $start_date,
                'end_date' => $end_date,
            ]);

            DB::commit();
            return response()->json(['status' => 'success'])
                ->setStatusCode(201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occurred while updation semester!!'
            ])->setStatusCode(500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Semester  $semester
     * @return \Illuminate\Http\Response
     */
    public function destroy(Semester $semester)
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function timetable(Request $request, Semester $semester)
    {
        $request->validate([
            'timetable' => 'required|file',
        ]);

        try {
            DB::beginTransaction();

            $timetable_url = null;
            if ($request->hasFile('timetable')) {
                if ($semester->timetable) {
                    $semester_timetable = explode("/", $semester->timetable);
                    $timetable = end($semester_timetable);
                    $exist = File::exists($this->pdfPath('semesters/' . $timetable));
                    if ($exist) {
                        File::delete($this->pdfPath('semesters/' . $timetable));
                    }
                }
                $file = $request->file('timetable');
                $file_name = 'timetable-' . Carbon::now()->timestamp . "." . $file->getClientOriginalExtension();
                $file->move($this->pdfPath('semesters'), $file_name);
                $timetable_url = URL::to('/') . '/assets/pdf/semesters/' . $file_name;
            }

            $semester->update([
                'timetable' => $timetable_url,
            ]);

            DB::commit();
            return response()->json(['status' => 'success'])->setStatusCode(201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occurred while setting timetable!!'
            ])->setStatusCode(500);
        }
    }


    public function display_timetable(Semester $semester)
    {
        if ($semester->timetable) {
            $semester_timetable = explode("/", $semester->timetable);
            $timetable = end($semester_timetable);
            return Response::make(file_get_contents($this->pdfPath('semesters/' . $timetable)), 200, [
                'content-type' => 'application/pdf',
            ]);
        }
        return response()->json([
            'error' => 'timetable is null'
        ])->setStatusCode(500);
    }
}
