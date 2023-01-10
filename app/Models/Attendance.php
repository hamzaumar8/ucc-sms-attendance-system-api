<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'semester_id',
        'lecturer_id',
        'module_id',
        'date',
        'start_time',
        'end_time',
        'status',
    ];


    public function attendance_student()
    {
        return $this->hasMany(AttendanceStudent::class, 'attendance_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'attendance_student', 'attendance_id', 'student_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function lecatt_count(){

        $count = Attendance::where('module_id', $this->module_id)->where('lecturer_id', $this->lecturer_id)->where('semester_id', $this->semester_id)->count();
        return $count;
    }

    public function lecatt_present(){
        $count = Attendance::where('module_id', $this->module_id)->where('lecturer_id', $this->lecturer_id)->where('semester_id', $this->semester_id)->where('status', 'present')->count();
        return $count;
    }

    public function lecatt_absent(){
        $count = Attendance::where('module_id', $this->module_id)->where('lecturer_id', $this->lecturer_id)->where('semester_id', $this->semester_id)->where('status', 'absent')->count();
        return $count;
    }


    public function total(){
       return [
            'count' => $this->lecatt_count(),
            'present' => $this->lecatt_present(),
            'absent' => $this->lecatt_absent(),
            'student'=>[
                'count'=> $this->attendance_student->count(),
                'present' => $this->attendance_student->where('status', 1)->count(),
                'absent' => $this->attendance_student->where('status', 0)->count(),
            ]
       ];
    }

    public function lecturer_weekly()
    {
        $collection = Attendance::where('module_id', $this->module_id)->where('lecturer_id', $this->lecturer_id)->where('semester_id', $this->semester_id)->get();

        $groups = $collection->groupBy(function ($row) {
            return
                Carbon::parse($row->date)->format('W');
        });

        $groupwithcount = $groups->map(function ($group) {
            $total = $group->count();
            $present = $group->where('status', 'present')->count();
            $absent = $group->where('status', 'absent')->count();
            return [
                'total' => $total,
                'present' => $present,
                'absent' => $absent,
                'present_percentage' => round(($present / ($total > 0 ? $total : 1)) * 100),
                'absent_percentage' => round(($absent / ($total > 0 ? $total : 1)) * 100),
            ];
        });
        return $groupwithcount;
    }


    public function student_weekly()
    {
        $collection = Attendance::where('module_id', $this->module_id)->where('lecturer_id', $this->lecturer_id)->where('semester_id', $this->semester_id)->get();

        $groups = $collection->groupBy(function ($row) {
            return
                Carbon::parse($row->date)->format('W');
        });

        $groupwithcount = $groups->map(function ($group) {
            $total = 0;
            $present = 0;
            $absent = 0;
            foreach ($group as $col) {
                $total += $col->attendance_student->count();
                $present += $col->attendance_student->where('status', 1)->count();
                $absent += $col->attendance_student->where('status', 0)->count();
            }
            return [
                'total' => $total,
                'present' => $present,
                'absent' => $absent,
                'present_percentage' => round(($present / ($total > 0 ? $total : 1)) * 100),
                'absent_percentage' => round(($absent / ($total > 0 ? $total : 1)) * 100),
            ];
        });
        return $groupwithcount;
    }


    public function weekly(){
        return [
            'lecturer' => $this->lecturer_weekly(),
            'students' => $this->student_weekly(),
        ];
    }
}