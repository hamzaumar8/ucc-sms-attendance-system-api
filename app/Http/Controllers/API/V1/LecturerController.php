<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Lecturer;
use App\Http\Resources\V1\Lecturer\LecturerCollection;
use App\Http\Resources\V1\Lecturer\LecturerResource;
use App\Http\Resources\V1\Module\ModuleCollection;
use App\Models\Module;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use App\Imports\V1\LecturerImport;
use App\Traits\SemesterTrait;
use App\Traits\UtilsTrait;
use Maatwebsite\Excel\Facades\Excel;

class LecturerController extends Controller
{
    use UtilsTrait, SemesterTrait;

    private $semesterId;

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
        $lecturers = Lecturer::orderByDesc('id')->with(['modules.module_bank', 'user']);
        return new LecturerCollection($lecturers->paginate(20));
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
            'email' => 'required|string|email|max:255|unique:users,email',
            'staff_id' => 'required|max:20|unique:lecturers,staff_id',
            'title' => 'required|string|max:20',
            'first_name' => 'required|string|max:20',
            'other_name' => 'nullable|string|max:255',
            'surname' => 'required|string|max:20',
            // 'gender' => 'required|string',
            'phone' => 'nullable|string|max:15',
            'picture' => 'nullable|file|mimes:jpeg,png,webp',
        ]);

        try {
            DB::beginTransaction();

            $name = $request->other_name ? $request->input('first_name') . ' ' . $request->other_name . ' ' . $request->input('surname') : $request->input('first_name') . ' ' . $request->input('surname');

            $picture_url = null;
            if ($request->hasFile('picture')) {
                $file = $request->file('picture');
                $file_name = Carbon::now()->timestamp . "." . $file->getClientOriginalExtension();
                $file->move($this->imagePath('lecturers'), $file_name);
                $picture_url = URL::to('/') . '/assets/img/lecturers/' . $file_name;
            }

            // Create user
            $user = User::create([
                'name' => $name,
                'email' => $request->input('email'),
                'username' => $request->input('staff_id'),
                'email_verified_at' => now(),
                'password' => Hash::make($request->input('staff_id')),
            ]);

            //Assign a student role
            $user->assignRole('lecturer');

            Lecturer::create([
                'user_id' => $user->id,
                'staff_id' => $request->input('staff_id'),
                'title' => $request->input('title'),
                'first_name' => $request->input('first_name'),
                'other_name' => $request->other_name,
                'surname' => $request->input('surname'),
                // 'gender' => $request->input('gender'),
                'phone' => $request->phone,
                'picture' => $picture_url,
            ]);

            DB::commit();

            return response()->json(['status' => 'success'])->setStatusCode(201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occurred while adding lecturer!!'
            ])->setStatusCode(500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Lecturer  $lecturer
     * @return \Illuminate\Http\Response
     */
    public function show(Lecturer $lecturer)
    {
        return (new LecturerResource($lecturer->loadMissing(['modules.module_bank', 'modules.level', 'user'])))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Lecturer  $lecturer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Lecturer $lecturer)
    {
        $request->validate([
            'email' => 'required',
            'string',
            'email',
            'max:255',
            'unique:users,email,' . $lecturer->user()->id,
            'staff_id' => 'required|max:20|unique:lecturers,staff_id,' . $lecturer->id,
            'title' => 'required|string|max:20',
            'first_name' => 'required|string|max:20',
            'other_name' => 'nullable|string|max:255',
            'surname' => 'required|string|max:20',
            // 'gender' => 'required|string',
            'phone' => 'nullable|string|max:15',
            'picture' => 'nullable|file|mimes:jpeg,png,webp',
        ]);


        try {
            DB::beginTransaction();

            $picture_url = null;
            if ($request->hasFile('picture')) {
                if ($lecturer->picture) {
                    $lecturer_picture = explode("/", $lecturer->picture);
                    $picture = end($lecturer_picture);
                    $exist = File::exists($this->imagePath("lecturers/" . $picture));
                    if ($exist) {
                        File::delete($this->imagePath("lecturers/" . $picture));
                    }
                }
                $file = $request->file('picture');
                $file_name = Carbon::now()->timestamp . "." . $file->getClientOriginalExtension();
                $file->move($this->imagePath('lecturers'), $file_name);
                $picture_url = URL::to('/') . '/assets/img/lecturers/' . $file_name;
            }

            $lecturer->update([
                'staff_id' => $request->input('staff_id'),
                'title' => $request->input('title'),
                'first_name' => $request->input('first_name'),
                'other_name' => $request->other_name,
                'surname' => $request->input('surname'),
                // 'gender' => $request->input('gender'),
                'phone' => $request->phone,
                'picture' => $picture_url,
            ]);

            $lecturer->user()->update([
                'email' => $request->input('email'),
                'username' => $request->input('staff_id'),
            ]);

            DB::commit();
            return response()->json(['status' => 'success'])->setStatusCode(201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occurred while updating lecturer details!!'
            ])->setStatusCode(500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Lecturer  $lecturer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Lecturer $lecturer)
    {
        try {
            DB::beginTransaction();

            if ($lecturer->picture) {
                $lecturer_picture = explode("/", $lecturer->picture);
                $picture = end($lecturer_picture);
                $exist = File::exists($this->imagePath("lecturers/" . $picture));
                if ($exist) {
                    File::delete($this->imagePath("lecturers/" . $picture));
                }
            }

            $lecturer->user()->delete();

            DB::commit();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occurred while deleting lecturer!!'
            ])->setStatusCode(500);
        }
    }


    public function backend(Request $request)
    {
        $query = Lecturer::query();

        if ($s = $request->input('s')) {
            $query->whereRaw("title Like '%" . $s . "%'")->orWhereRaw("first_name Like '%" . $s . "%'")->orWhereRaw("other_name Like '%" . $s . "%'")->orWhereRaw("surname Like '%" . $s . "%'")->orWhereRaw("staff_id Like '%" . $s . "%'");
        }

        return $query->get();
    }


    public function import(Request $request)
    {

        $request->validate([
            'file' => 'required|mimes:csv,txt',
        ]);

        try {
            Excel::import(new LecturerImport, request()->file('file'));
            return response()->json(['status' => 'success'])->setStatusCode(201);
        } catch (\Exception $e) {

            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'An error occurred while importing data!!'
            ])->setStatusCode(500);
        }
    }


    public function all()
    {
        $lecturers = Lecturer::orderByDesc('id')->with(['modules.module_bank', 'user'])->get();
        return new LecturerCollection($lecturers);
    }


    public function lecturers_modules()
    {
        $lecturerModules = auth()->user()->lecturer->modules->pluck('id')->toArray();
        $modules = Module::whereIn('id', $lecturerModules)
            ->where('semester_id', $this->semesterId)
            ->orderBy('id', 'DESC')
            ->with(['module_bank', 'lecturers', 'level', 'coordinator', 'course_rep', 'attendances'])
            ->get();
        return new ModuleCollection($modules);
    }


    public function coordinating_modules()
    {
        $coordinator_id = auth()->user()->lecturer->id;
        $modules = Module::where('semester_id', $this->semesterId)
            ->where('coordinator_id', $coordinator_id)
            ->orderBy('id', 'DESC')
            ->with(['module_bank', 'lecturers', 'level', 'coordinator', 'course_rep', 'attendances'])
            ->get();
        return new ModuleCollection($modules);
    }
}