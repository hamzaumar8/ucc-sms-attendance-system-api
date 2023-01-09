<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'semester_id',
        'lecturer_id',
        'module_id',
        'date',
        'start_time',
        'end_time',
        'status',
    ];


    public function attendance_student()
    {
        return $this->hasMany(AttendanceStudent::class, 'attendance_id');
    }


    public function students()
    {
        return $this->belongsToMany(Student::class, 'attendance_student', 'attendance_id', 'student_id');
    }
}