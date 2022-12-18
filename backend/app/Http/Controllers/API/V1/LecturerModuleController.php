<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\LectureModule\LectureModuleCollection;
use App\Models\LectureModule;
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
        return  new LectureModuleCollection(LectureModule::all());
    }
}