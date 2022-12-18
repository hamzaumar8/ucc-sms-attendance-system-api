<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LectureModule extends Model
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
}