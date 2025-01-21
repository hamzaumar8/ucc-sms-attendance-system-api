<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceStudent extends Model
{
    use HasFactory;

    protected $table = 'attendance_student';


    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}