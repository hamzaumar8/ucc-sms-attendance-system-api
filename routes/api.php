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
use App\Http\Controllers\API\V1\AttendanceLecturerController;
use App\Http\Controllers\Auth\TokenAuthController;
use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum', 'check-semester'])->get('/v1/user', UserController::class);

// Token Logout
Route::post('/auth/token', [TokenAuthController::class, 'store']);
Route::post('/auth/logout', [TokenAuthController::class, 'destroy'])->middleware('auth:sanctum');


Route::group(['prefix' => 'v1'], function () {
    // Semester
    Route::apiResource('/semester', SemesterController::class);


    // Result
    Route::apiResource('/results', ResultController::class);
    Route::get('cordinating/modules', [ResultController::class, 'cordinating_module']);
    Route::get('promotion/check', [ResultController::class, 'promotion_check']);

    // // Assessment
    // Route::apiResource('/semester', Assessment::class);

    // Students
    Route::apiResource('/students', StudentController::class);
    Route::get('/stud/backend', [StudentController::class, 'backend']);
    Route::get('/stud_module/{module}/backend', [StudentController::class, 'module_backend']);
    Route::post('/import/students', [StudentController::class, 'import']);

    // Lecturers
    Route::apiResource('/lecturers', LecturerController::class);
    Route::get('/lect/backend', [LecturerController::class, 'backend']);
    Route::post('/import/lecturers', [LecturerController::class, 'import']);

    // Modules Bank
    Route::apiResource('/module_banks', ModuleBankController::class);
    Route::get('/mod_bank/backend', [ModuleBankController::class, 'backend']);

    // Modules
    Route::apiResource('/modules', ModuleController::class);
    Route::get('/end/module/{module}', [ModuleController::class, 'end_module']);
    Route::post('/add/student/{module}', [ModuleController::class, 'add_student']);

    // Levels
    Route::apiResource('/levels', LevelController::class);
    Route::put('generate/level/{level}', [LevelController::class, 'generate_group']);
    Route::get('/lev/backend', [LevelController::class, 'backend']);
    Route::put('student/level/promotion/{semester}', [LevelController::class, 'student_promotion']);

    // Attendance
    Route::apiResource('/attendances', AttendanceController::class);
    Route::put('/accept/attendance/{attendance}', [AttendanceController::class, 'accecpt']);

    // Attendance Lecturer
    Route::apiResource('/attendance_lecturer', AttendanceLecturerController::class);
    Route::get('/attendance/lecturer', [AttendanceLecturerController::class, 'lecturers_attendances']);

    // LecturerModule
    Route::get('/lecture/modules', [LecturerModuleController::class, 'index'])->name('lecture_module.index');
    Route::get('/lecture/modules/{lecuturermodule}', [LecturerModuleController::class, 'show'])->name('lecture_module.show');

});
