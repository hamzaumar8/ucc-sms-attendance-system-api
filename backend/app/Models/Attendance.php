<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

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
}