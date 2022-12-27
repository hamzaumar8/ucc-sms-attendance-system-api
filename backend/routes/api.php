<?php

use App\Http\Controllers\API\V1\AttendanceController;
use App\Http\Controllers\API\V1\LecturerController;
use App\Http\Controllers\API\V1\LecturerModuleController;
use App\Http\Controllers\API\V1\LevelController;
use App\Http\Controllers\API\V1\ModuleBankController;
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

Route::get('v1/user', UserController::class)->middleware(['auth:sanctum']);


Route::group([
    'prefix' => 'v1',
    // 'middleware' => 'auth:sanctum'
], function () {
    // Students
    Route::apiResource('/students', StudentController::class);
    Route::get('/stud/backend', [StudentController::class, 'backend']);


    // Lectures
    Route::apiResource('/lecturers', LecturerController::class);
    Route::get('/lect/backend', [LecturerController::class, 'backend']);

    // Modules Bank
    Route::apiResource('/module/bank', ModuleBankController::class);

    // Modules
    Route::apiResource('/modules', ModuleController::class);

    // Levels
    Route::apiResource('/levels', LevelController::class);
    Route::get('/lev/backend', [LevelController::class, 'backend']);

    // Levels
    Route::apiResource('/attendances', AttendanceController::class);
    Route::get('/attandance/lecturer', [AttendanceController::class, 'lecturers_attendances']);

    // LecturerModule
    Route::get('/lecture/modules', [LecturerModuleController::class, 'index'])->name('lecture_module.index');
    Route::get('/lecture/modules/{lecuturermodule}', [LecturerModuleController::class, 'show'])->name('lecture_module.show');

    // Users
    Route::get('users/{user}', [UserController::class, 'show'])->name('users');

    // User
    // Route::get('/user', Usercontroller::class);
    // Route::get('/user', function (Request $request) {
    //     return $request->user();
    // });
});