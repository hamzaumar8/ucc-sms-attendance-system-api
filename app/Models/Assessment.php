<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'result_id',
        'student_id',
        'score',
        'remarks',
    ];

    public function result()
    {
        return $this->belongsTo(Result::class, 'result_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

     public function students()
    {
        return $this->belongsToMany(Result::class, 'assessments',  'result_id', 'student_id')->withPivot(['score','remarks']);
    }
}
