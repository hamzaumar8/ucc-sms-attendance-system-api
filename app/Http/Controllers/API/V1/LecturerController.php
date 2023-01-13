<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Lecturer\LecturerCollection;
use App\Http\Resources\V1\Lecturer\LecturerResource;
use App\Models\Lecturer;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use App\Imports\V1\LecturerImport;
use Maatwebsite\Excel\Facades\Excel;

class LecturerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['only' => ['store', 'update', 'destroy', 'import']]);
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

        $this->validate($request, [
            'email' => 'required|string|email|max:255|unique:users,email',
            'staff_id' => 'required|max:20|unique:lecturers,staff_id',
            'title' => 'required|string',
            'first_name' => 'required|string|max:20',
            'other_name' => 'nullable|string|max:255',
            'surname' => 'required|string|max:20',
            // 'gender' => 'required|string',
            'phone' => 'nullable|string|max:15',
            'picture' => 'nullable|file',
        ]);

        try{
            DB::beginTransaction();

            $name = $request->input('other_name') ? $request->input('first_name') . ' ' . $request->input('other_name') . ' ' . $request->input('surname') : $request->input('first_name') . ' ' . $request->input('surname');

            $picture_url = null;
            if ($request->hasFile('picture')) {
                $file = $request->file('picture');
                $file_name = Carbon::now()->timestamp . "." . $file->getClientOriginalExtension();
                $file->move(public_path('assets/img/lecturers'), $file_name);
                $picture_url = URL::to('/') . '/assets/img/lecturers/' . $file_name;
            }

            // Create user
            $user = User::create([
                'name' => $name,
                'email' => $request->input('email'),
                'email_verified_at' => now(),
                'role' => 'STF',
                'password' => Hash::make($request->input('staff_id')),
            ]);

            Lecturer::create([
                'user_id' => $user->id,
                'staff_id' => $request->input('staff_id'),
                'title' => $request->input('title'),
                'first_name' => $request->input('first_name'),
                'other_name' => $request->input('other_name'),
                'surname' => $request->input('surname'),
                // 'gender' => $request->input('gender'),
                'phone' => $request->input('phone'),
                'picture' => $picture_url,
            ]);

            DB::commit();

            return response()->json(['status' => 'success'])->setStatusCode(201);
        }catch(\Exception $e){
            DB::rollBack();
            \Log::error($e->getMessage());
            return response()->json([
                'error'=>$e->getMessage(),
                'message'=>'An error occured while adding lecturer!!'
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
        return (new LecturerResource($lecturer->loadMissing(['modules.module_bank','user'])))
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
            'email' => 'required', 'string', 'email', 'max:255', 'unique:users,email,' . $lecturer->user->id,
            'staff_id' => 'required|max:20|unique:lecturers,staff_id,' . $lecturer->id,
            'title' => 'required|string',
            'first_name' => 'required|string|max:20',
            'other_name' => 'nullable|string|max:255',
            'surname' => 'required|string|max:20',
            // 'gender' => 'required|string',
            'phone' => 'nullable|string|max:15',
            'picture' => 'nullable|file',
        ]);


        try{
            DB::beginTransaction();

            $picture_url = null;
            if ($request->hasFile('picture')) {
                if ($lecturer->picture) {
                    $lecturerpicture = explode("/", $lecturer->picture);
                    $picture = end($lecturerpicture);
                    $exist = File::exists(public_path("assets/img/lecturers/" . $picture));
                    if ($exist) {
                        File::delete(public_path("assets/img/lecturers/" . $picture));
                    }
                }
                $file = $request->file('picture');
                $file_name = Carbon::now()->timestamp . "." . $file->getClientOriginalExtension();
                $file->move(public_path('assets/img/lecturers'), $file_name);
                $picture_url = URL::to('/') . '/assets/img/lecturers/' . $file_name;
            }

            $lecturer->update([
                'staff_id' => $request->input('staff_id'),
                'title' => $request->input('title'),
                'first_name' => $request->input('first_name'),
                'other_name' => $request->input('other_name'),
                'surname' => $request->input('surname'),
                // 'gender' => $request->input('gender'),
                'phone' => $request->input('phone'),
                'picture' => $picture_url,
            ]);

            $lecturer->user->update([
                'email' => $request->input('email'),
            ]);

            DB::commit();
            return response()->json(['status' => 'success'])->setStatusCode(201);

        }catch(\Exception $e){
            DB::rollBack();
            \Log::error($e->getMessage());
            return response()->json([
                'error'=>$e->getMessage(),
                'message'=>'An error occured while updating lecturer details!!'
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
        try{
            DB::beginTransaction();

            if ($lecturer->picture) {
                $lecturerpicture = explode("/", $lecturer->picture);
                $picture = end($lecturerpicture);
                $exist = File::exists(public_path("assets/img/lecturers/" . $picture));
                if ($exist) {
                    File::delete(public_path("assets/img/lecturers/" . $picture));
                }
            }

            $lecturer->user->delete();

            DB::commit();
            return response()->json(null, 204);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            return response()->json([
                'error'=>$e->getMessage(),
                'message'=>'An error occured while deleting lecturer!!'
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


    public function import(Request $request){

        $request->validate([
            'file' => 'required|mimes:csv,txt',
        ]);

        try {
            Excel::import(new LecturerImport, request()->file('file'));
            return response()->json(['status' => 'success'])->setStatusCode(201);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json([
                'error'=>$e->getMessage(),
                'message'=>'An error occured while importing data!!'
            ])->setStatusCode(500);
        }
    }


    public function all()
    {
        $lecturers = Lecturer::orderByDesc('id')->with(['modules.module_bank', 'user'])->get();
        return new LecturerCollection($lecturers);
    }

}