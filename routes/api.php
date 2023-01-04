<?php

use App\Http\Controllers\API\V1\AttendanceController;
use App\Http\Controllers\API\V1\LecturerController;
use App\Http\Controllers\API\V1\LecturerModuleController;
use App\Http\Controllers\API\V1\LevelController;
use App\Http\Controllers\API\V1\ModuleBankController;
use App\Http\Controllers\API\V1\ModuleController;
use App\Http\Controllers\API\V1\ResultController;
use App\Http\Controllers\API\V1\SemesterController;
use App\Http\Controllers\API\V1\StudentController;
use App\Http\Controllers\API\V1\UserController;
use App\Models\Assessment;
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

Route::middleware(['auth:sanctum', 'check-semester'])->get('/v1/user', UserController::class);



Route::group(['prefix' => 'v1'], function () {
    // Semester
    Route::apiResource('/semester', SemesterController::class);


    // Result
    Route::apiResource('/results', ResultController::class);
    Route::get('cordinating/modules', [ResultController::class, 'cordinating_module']);

    // // Assessment
    // Route::apiResource('/semester', Assessment::class);

    // Students
    Route::apiResource('/students', StudentController::class);
    Route::get('/stud/backend', [StudentController::class, 'backend']);

    // Lecturers
    Route::apiResource('/lecturers', LecturerController::class);
    Route::get('/lect/backend', [LecturerController::class, 'backend']);

    // Modules Bank
    Route::apiResource('/module_banks', ModuleBankController::class);
    Route::get('/mod_bank/backend', [ModuleBankController::class, 'backend']);

    // Modules
    Route::apiResource('/modules', ModuleController::class);

    // Levels
    Route::apiResource('/levels', LevelController::class);
    Route::put('generate/level/{level}', [LevelController::class, 'generate_group']);
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

});
