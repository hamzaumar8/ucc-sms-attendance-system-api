<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\LecturerModule\LecturerModuleCollection;
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
}