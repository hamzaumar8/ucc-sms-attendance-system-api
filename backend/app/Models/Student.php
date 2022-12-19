<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'index_number',
        'first_name',
        'other_name',
        'last_name',
        'gender',
        'phone',
        'picture',
    ];

    // Optional zone open
    public function id(): string
    {
        return $this->id;
    }


    // optional zone close

    public function full_name()
    {
        return $this->other_name ? $this->first_name . ' ' . $this->other_name . ' ' . $this->last_name : $this->first_name . ' ' . $this->last_name;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id');
    }


    public function modules()
    {
        return $this->belongsToMany(Module::class, 'module_student',  'student_id', 'module_id');
    }
}