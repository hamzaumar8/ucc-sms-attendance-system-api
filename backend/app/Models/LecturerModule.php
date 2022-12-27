<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LecturerModule extends Model
{
    use HasFactory;
    protected $fillable = [
        'lecturer_id',
        'module_id',
        'start_date',
        'end_date',
        'status',
        'course_rep_id',
    ];

    protected $table = 'lecturer_module';

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }
    public function course_rep()
    {
        return $this->belongsTo(Student::class, 'course_rep_id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'lecturer_module_id');
    }
}