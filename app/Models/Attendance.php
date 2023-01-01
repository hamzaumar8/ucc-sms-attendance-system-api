<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'lecturer_module_id',
        'date',
        'start_time',
        'end_time',
        'status',
    ];

    // protected $casts = [
    //     'date' => 'datetime',
    //     'start_time' => 'datetime',
    //     'end_time' => 'datetime',
    // ];

    public function lecturerModel()
    {
        return $this->belongsTo(LecturerModule::class, 'lecturer_module_id');
    }

    public function presentLecturerModel()
    {
        return $this->lecturerModel->where('status', 'present')->count();
    }

    public function attendance_student()
    {
        return $this->hasMany(AttendanceStudent::class, 'attendance_id');
    }


    public function students()
    {
        return $this->belongsToMany(Student::class, 'attendance_student', 'attendance_id', 'student_id');
    }
}