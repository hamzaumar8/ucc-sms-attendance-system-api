<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'semester_id',
        'level_id',
        'name',
        'groups',
    ];

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id');
    }


    public function students()
    {
        return $this->belongsToMany(Student::class, 'group_student', 'group_id', 'student_id');
    }
}
