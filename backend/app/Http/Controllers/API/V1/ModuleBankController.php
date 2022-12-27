<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ModuleBank\ModuleBankCollection;
use App\Models\ModuleBank;
use Illuminate\Http\Request;

class ModuleBankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new ModuleBankCollection(ModuleBank::orderBy('id', 'DESC')->get());
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

        ModuleBank::create([
            'title' => $request->input('title'),
            'code' => strtoupper($request->input('code')),
            'credit_hour' => $request->input('credit_hour'),
        ]);

        return response()->json(['status' => 'success'])->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ModuleBank  $moduleBank
     * @return \Illuminate\Http\Response
     */
    public function show(ModuleBank $moduleBank)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ModuleBank  $moduleBank
     * @return \Illuminate\Http\Response
     */
    public function destroy(ModuleBank $moduleBank)
    {
        //
    }
}