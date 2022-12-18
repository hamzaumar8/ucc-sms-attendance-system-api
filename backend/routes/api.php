<?php

use App\Http\Controllers\API\V1\LecturerController;
use App\Http\Controllers\API\V1\LecturerModuleController;
use App\Http\Controllers\API\V1\ModuleController;
use App\Http\Controllers\API\V1\StudentController;
use App\Http\Controllers\API\V1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});



Route::group([
    'prefix' => 'v1',
    // 'middleware' => 'auth:sanctum'
], function () {
    // Students
    Route::apiResource('/students', StudentController::class);


    // Lectures
    Route::apiResource('/lecturers', LecturerController::class);

    // Modules
    Route::apiResource('/modules', ModuleController::class);

    // LectureModule
    Route::get('/lecture/modules', [LecturerModuleController::class, 'index'])->name('lecture_module.index');

    // Users
    Route::get('users/{user}', [UserController::class, 'show'])->name('users');

    // User
    // Route::get('/user', Usercontroller::class);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});