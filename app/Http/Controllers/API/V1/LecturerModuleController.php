<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\LecturerModule\LecturerModuleCollection;
use App\Http\Resources\V1\LecturerModule\LecturerModuleResource;
use App\Models\LecturerModule;
use Illuminate\Http\Request;

class LecturerModuleController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new LecturerModuleCollection(LecturerModule::orderBy('id', 'DESC')->get());
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LecturerModule  $lecturerModule
     * @return \Illuminate\Http\Response
     */
    public function show(LecturerModule $lecturerModule)
    {
        return (new LecturerModuleResource($lecturerModule))
            ->response()
            ->setStatusCode(200);
    }
}
