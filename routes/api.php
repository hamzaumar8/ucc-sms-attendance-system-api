<?php

use App\Http\Resources\V1\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group(
    ['prefix' => 'v1'],
    function () {

        Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
            return new UserResource($request->user());
        });


        // // Semester
        // Route::apiResource('/semester', SemesterController::class);
        // Route::put('/timetable/semester/{semester}', [SemesterController::class, 'timetable']);
        // Route::get('/timetable/display/{semester}', [SemesterController::class, 'display_timetable']);

        // // Result
        // Route::apiResource('/results', ResultController::class);
        // Route::get('/cordinating/modules/results', [ResultController::class, 'cordinating_module']);
        // Route::get('/promotion/check', [ResultController::class, 'promotion_check']);
        // Route::get('/update_status/result/{result}', [ResultController::class, 'update_status']);
        // Route::get('/lecturer/results', [ResultController::class, 'lecturers_results']);
        // Route::get('/export/results/{result}', [ResultController::class, 'export']);
        // Route::post('/import/results/{result}', [ResultController::class, 'import']);

        // // Assessment
        // Route::apiResource('/assessments', AssessmentController::class);

        // // Students
        // Route::apiResource('/students', StudentController::class);
        // Route::get('/stud/backend', [StudentController::class, 'backend']);
        // Route::get('/stud_module/{module}/backend', [StudentController::class, 'module_backend']);
        // Route::post('/import/students', [StudentController::class, 'import']);
        // Route::get('/result/student', [StudentController::class, 'results']);
        // Route::get('/group/student', [StudentController::class, 'groups']);

        // // Lecturers
        // Route::apiResource('/lecturers', LecturerController::class);
        // Route::get('/lect/backend', [LecturerController::class, 'backend']);
        // Route::post('/import/lecturers', [LecturerController::class, 'import']);
        // Route::get('/all/lecturers', [LecturerController::class, 'all']);
        // Route::get('/lecturer/modules', [LecturerController::class, 'lecturers_modules']);
        // Route::get('/cordinating/modules/lecturer', [LecturerController::class, 'cordinating_modules']);

        // // Modules Bank
        // Route::apiResource('/module_banks', ModuleBankController::class);
        // Route::get('/mod_bank/backend', [ModuleBankController::class, 'backend']);

        // // Modules
        // Route::apiResource('/modules', ModuleController::class);
        // Route::get('/end/module/{module}', [ModuleController::class, 'end_module']);
        // Route::post('/add/student/{module}', [ModuleController::class, 'add_student']);
        // Route::get('/cordinating/modules/{lecturer}', [ModuleController::class, 'cordinating_modules']);
        // Route::get('/student/modules/', [ModuleController::class, 'student_modules']);
        // Route::get('/course_rep/modules', [ModuleController::class, 'course_rep_modules']);

        // // Levels
        // Route::apiResource('/levels', LevelController::class);
        // Route::get('/lev/backend', [LevelController::class, 'backend']);
        // Route::put('student/level/promotion/{semester}', [LevelController::class, 'student_promotion']);

        // // Group
        // Route::apiResource('/groups', GroupController::class);

        // // Attendance
        // Route::apiResource('/attendances', AttendanceController::class);
        // Route::get('/course_rep/attendances', [AttendanceController::class, 'course_rep_attendances']);

        // // Attendance Lecturer
        // Route::apiResource('/attendance_lecturer', AttendanceLecturerController::class);
        // Route::get('/attendance/lecturer', [AttendanceLecturerController::class, 'lecturers_attendances']);

        // // LecturerModule
        // Route::get('/lecture/modules', [LecturerModuleController::class, 'index'])->name('lecture_module.index');
        // Route::get('/lecture/modules/{lecuturermodule}', [LecturerModuleController::class, 'show'])->name('lecture_module.show');
    }
);
