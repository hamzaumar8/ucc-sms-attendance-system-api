<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'semester_id',
        'module_id',
        'coordinator_id',
        'status',
    ];

    public function coordinator()
    {
        return $this->belongsTo(Lecturer::class, 'coordinator_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'assessments', 'result_id', 'student_id');
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'result_id');
    }
}
