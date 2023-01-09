<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Semester;

class Lecturer extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'staff_id',
        'title',
        'first_name',
        'other_name',
        'surname',
        'gender',
        'phone',
        'picture',
    ];

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
        $semester  = Semester::whereDate('start_date', '<=', Carbon::now()->format('Y-m-d'))->whereDate('end_date', '>=', Carbon::now()->format('Y-m-d'))->first();
        $semester_id = null;
        if ($semester) {
            $semester_id = $semester->id;
        }
        return $this->belongsToMany(Module::class, 'lecturer_module',  'lecturer_id', 'module_id')->where('semester_id', $semester_id);
    }

    public function all_modules()
    {
        return $this->belongsToMany(Module::class, 'lecturer_module',  'lecturer_id', 'module_id');
    }

    public function full_name()
    {
        return $this->other_name ? $this->title . ' ' . $this->first_name . ' ' . $this->other_name . ' ' . $this->surname : $this->first_name . ' ' . $this->surname;
    }

    public function picture_url()
    {
        return $this->picture ? $this->picture : asset('assets/img/lecturers/default.png');
    }


}