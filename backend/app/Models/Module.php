<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    public function cordinator()
    {
        return $this->belongsTo(Lecturer::class, 'cordinator_id');
    }

    public function lectures()
    {
        return $this->belongsToMany(Lecturer::class, 'lecturer_module', 'module_id', 'lecturer_id');
    }




    // public function emergencycontact()
    // {
    //     return $this->hasMany(EmergencyContact::class);
    // }
}