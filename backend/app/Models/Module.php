<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'cordinator_id',
        'title',
        'code',
        'credit_hour',
    ];

    public function cordinator()
    {
        return $this->belongsTo(Lecturer::class, 'cordinator_id');
    }

    public function lectures()
    {
        return $this->belongsToMany(Lecturer::class, 'lecturer_module', 'module_id', 'lecturer_id');
    }


    public function students()
    {
        return $this->belongsToMany(Student::class, 'module_student', 'module_id', 'student_id');
    }




    // public function emergencycontact()
    // {
    //     return $this->hasMany(EmergencyContact::class);
    // }
}