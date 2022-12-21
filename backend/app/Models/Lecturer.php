<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecturer extends Model
{
    use HasFactory;


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cordinator_modules()
    {
        return $this->hasMany(Module::class, 'cordinator_id');
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'lecturer_module',  'lecturer_id', 'module_id');
    }



    // public function emergencycontact()
    // {
    //     return $this->hasMany(EmergencyContact::class);
    // }

}