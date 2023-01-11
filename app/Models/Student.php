<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Semester;
use App\Models\AttendanceStudent;
use Carbon\Carbon;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'index_number',
        'first_name',
        'other_name',
        'surname',
        'gender',
        'phone',
        'picture',
        'level_id',
    ];

    // Optional zone open
    public function id(): string
    {
        return $this->id;
    }


    // optional zone close

    public function full_name()
    {
        return $this->other_name ? $this->first_name . ' ' . $this->other_name . ' ' . $this->surname : $this->first_name . ' ' . $this->surname;
    }

    public function picture_url()
    {
        return $this->picture ? $this->picture : asset('assets/img/lecturers/default.png');
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


    public function attendance()
    {
        $semester  = Semester::whereDate('start_date', '<=', Carbon::now()->format('Y-m-d'))->whereDate('end_date', '>=', Carbon::now()->format('Y-m-d'))->first();
        $semester_id = null;
        if ($semester) {
            $semester_id = $semester->id;
        }
        return $this->hasMany(AttendanceStudent::class, 'student_id')->where('semester_id', $semester_id);
    }

    public function attendance_present(){
        return  $this->attendance->where('status', 1)->count();
    }
    public function attendance_absent(){
        return  $this->attendance->where('status', 0)->count();
    }
    public function attendance_total(){
        return  $this->attendance->count();
    }
    public function attendance_present_percentage(){
        $count = $this->attendance->count();
        $present =$this->attendance_present();
        return round($present / ($count > 0 ? $count : 1) * 100);
    }
    public function attendance_absent_percentage(){
       $count = $this->attendance->count();
        $absent =$this->attendance_absent();
        return round($absent / ($count > 0 ? $count : 1) * 100);
    }


    public function result()
    {
        return $this->belongsTo(Result::class, 'result_id');
    }
}