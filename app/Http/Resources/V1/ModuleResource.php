<?php

namespace App\Http\Resources\API\V1\Module;

use App\Http\Resources\API\V1\Attendance\AttendanceCollection;
use App\Http\Resources\API\V1\Attendance\AttendanceResource;
use App\Http\Resources\API\V1\Attendance\AttendanceSingle;
use App\Http\Resources\API\V1\AttendanceLecturer\AttendanceLecturerResource;
use App\Http\Resources\API\V1\AttendanceLecturer\AttendanceLecturerCollection;
use App\Http\Resources\API\V1\Lecturer\LecturerResource;
use App\Http\Resources\API\V1\Level\LevelResource;
use App\Http\Resources\API\V1\ModuleBank\ModuleBankResource;
use App\Http\Resources\API\V1\Student\StudentResource;
use App\Models\Lecturer;
use App\Models\Result;
use App\Models\Assessment;
use Carbon\Carbon;
use App\Models\Semester;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $startDate = Carbon::parse($this->start_date);
        // if ($startDate->isWeekday()) {
        //     $startDate = $startDate->subDay();
        // }
        $endDate = Carbon::parse($this->end_date);
        $days = (int)($endDate->diffInDays($startDate));
        if (Carbon::now()->between(Carbon::parse($this->start_date), $endDate)) {
            $days_covered = (int)($startDate->diffInDays(Carbon::now()));
            if ($this->status !== 'active') {
                $this->update(['status' => 'active']);
            }
        } elseif (Carbon::now()->gt($endDate)) {
            $days_covered = $days;
            if ($this->status !== 'inactive') {
                $this->update(['status' => 'inactive']);

                $result = Result::firstOrCreate([
                    'semester_id' => $this->semester(),
                    'module_id' => $this->id,
                    'cordinator_id' => $this->cordinator_id,
                ]);

                foreach ($this->students as $student) {
                    Assessment::firstOrCreate([
                        'result_id' => $result->id,
                        'student_id' => $student->id,
                    ]);
                }
            }
        } else {
            $days_covered = 0;
            if ($this->status !== 'upcoming') {
                $this->update(['status' => 'upcoming']);
            }
        }
        $days_remaining = (int)($days - $days_covered);
        $day_s = $days > 0 ? $days : 1;
        $covered_percentage = round(($days_covered / $day_s) * 100);

        return [
            'id' => $this->id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'cordinator_id' => $this->cordinator_id,
            'module' => ModuleBankResource::make($this->whenLoaded('module_bank')),
            'cordinator' => LecturerResource::make($this->whenLoaded('cordinator')),
            'course_rep' => StudentResource::make($this->whenLoaded('course_rep')),
            'level' => LevelResource::make($this->whenLoaded('level')),
            'lecturers' => LecturerResource::collection($this->whenLoaded('lecturers')),
            'students' => StudentResource::collection($this->whenLoaded('students')),
            'attendance' => new AttendanceCollection($this->whenLoaded('attendances')),
            'att_course_rep' => AttendanceSingle::collection($this->whenLoaded('attendances_course_rep')),
            'att_lect' => $this->whenLoaded('attendances_lecturer'),
            'days' => [
                'total' => $days,
                'covered' => $days_covered,
                'remains' => $days_remaining,
                'covered_percentage' => $covered_percentage,
            ]
        ];
    }

    public function semester()
    {
        $semester_id = null;
        $semester =  Semester::whereDate('start_date', '<=', Carbon::now()->format('Y-m-d'))->whereDate('end_date', '>=', Carbon::now()->format('Y-m-d'))->first();
        if ($semester) {
            $semester_id = $semester->id;
        }
        return  $semester_id;
    }

    public function with($request)
    {
        return [
            'status' => 'success',
        ];
    }

    public function withResponse($request, $response)
    {
        $response->header('Accept', 'application/json');
    }
}
