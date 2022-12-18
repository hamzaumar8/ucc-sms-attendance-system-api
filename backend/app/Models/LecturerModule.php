<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LecturerModule extends Model
{
    use HasFactory;

    protected $table = 'lecturer_module';

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
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