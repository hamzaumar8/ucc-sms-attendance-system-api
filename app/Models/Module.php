<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'semester_id',
        'module_bank_id',
        'cordinator_id',
        'course_rep_id',
        'level_id',
        'start_date',
        'end_date',
        'status',
    ];

    public function cordinator()
    {
        return $this->belongsTo(Lecturer::class, 'cordinator_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function module_bank()
    {
        return $this->belongsTo(ModuleBank::class, 'module_bank_id');
    }

    public function course_rep()
    {
        return $this->belongsTo(Student::class, 'course_rep_id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id');
    }


    public function lecturers()
    {
        return $this->belongsToMany(Lecturer::class, 'lecturer_module', 'module_id', 'lecturer_id');
    }


    public function students()
    {
        return $this->belongsToMany(Student::class, 'module_student', 'module_id', 'student_id');
    }


    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'module_id');
    }


    public function attendances_course_rep()
    {
        return $this->hasMany(Attendance::class, 'module_id');
    }

    public function attendances_lecturer()
    {
        return $this->hasMany(AttendanceLecturer::class, 'module_id');
    }
}
