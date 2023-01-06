<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class Level extends Model
{
    use HasFactory;

    public function students()
    {
        return $this->hasMany(Student::class, 'level_id');
    }

    public function students_count($id){
        $count = Student::where('level_id', $id)->count();
        return $count;
    }
}
