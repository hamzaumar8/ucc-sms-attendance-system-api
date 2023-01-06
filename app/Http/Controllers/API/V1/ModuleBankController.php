<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ModuleBank\ModuleBankCollection;
use App\Http\Resources\V1\Student\StudentResource;
use App\Models\ModuleBank;
use Illuminate\Http\Request;

class ModuleBankController extends Controller
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
        return new ModuleBankCollection(ModuleBank::orderByDesc('id')->paginate(15));
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
            'title' => 'required|string|max:225|unique:module_banks',
            'code' => 'required|string|max:10|unique:module_banks',
            'credit_hour' => 'required|numeric',
        ]);

        try{
            ModuleBank::create([
                'title' => $request->input('title'),
                'code' => strtoupper($request->input('code')),
                'credit_hour' => $request->input('credit_hour'),
            ]);

            return response()->json(['status' => 'success'])->setStatusCode(201);

        }catch(\Exception $e){
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>'An error occured while adding a module!!'
            ])->setStatusCode(500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ModuleBank  $moduleBank
     * @return \Illuminate\Http\Response
     */
    public function show(ModuleBank $moduleBank)
    {
        return (new StudentResource($moduleBank))->response()->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ModuleBank  $moduleBank
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ModuleBank $moduleBank)
    {
        $request->validate([
            'title' => 'required|string|max:225|unique:module_banks,title,' . $request->input('id'),
            'code' => 'required|string|max:10|unique:module_banks,code,' . $request->input('id'),
            'credit_hour' => 'required|numeric',
        ]);

        try{

        $moduleBank->update([
            'title' => $request->input('title'),
            'code' => strtoupper($request->input('code')),
            'credit_hour' => $request->input('credit_hour'),
        ]);

        return response()->json(['status' => 'success'])->setStatusCode(201);

        }catch(\Exception $e){
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>'An error occured while updating module!!'
            ])->setStatusCode(500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ModuleBank  $moduleBank
     * @return \Illuminate\Http\Response
     */
    public function destroy(ModuleBank $moduleBank)
    {
        try{
            $moduleBank->delete();
            return response()->json(null, 204);

        }catch(\Exception $e){
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>'An error occured while deleting module!!'
            ])->setStatusCode(500);
        }
    }

    public function backend(Request $request)
    {
        $query = ModuleBank::query();

        if ($s = $request->input('s')) {
            $query->whereRaw("title Like '%" . $s . "%'")->orWhereRaw("code Like '%" . $s . "%'");
        }

        return $query->get();
    }
}
