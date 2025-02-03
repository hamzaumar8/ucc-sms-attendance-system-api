<?php

namespace App\Http\Resources\V1\Module;

use App\Http\Resources\V1\Attendance\AttendanceCollection;
use App\Http\Resources\V1\Attendance\AttendanceSingle;
use App\Http\Resources\V1\Lecturer\LecturerResource;
use App\Http\Resources\V1\Level\LevelResource;
use App\Http\Resources\V1\ModuleBank\ModuleBankResource;
use App\Http\Resources\V1\Student\StudentResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'coordinator_id' => $this->coordinator_id,
            'module' => ModuleBankResource::make($this->whenLoaded('module_bank')),
            'coordinator' => LecturerResource::make($this->whenLoaded('coordinator')),
            'course_rep' => StudentResource::make($this->whenLoaded('course_rep')),
            'level' => LevelResource::make($this->whenLoaded('level')),
            'lecturers' => LecturerResource::collection($this->whenLoaded('lecturers')),
            'students' => StudentResource::collection($this->whenLoaded('students')),
            'attendance' => new AttendanceCollection($this->whenLoaded('attendances')),
            'att_course_rep' => AttendanceSingle::collection($this->whenLoaded('attendances_course_rep')),
            'att_lect' => $this->whenLoaded('attendances_lecturer'),
            'days' => [
                'total' => $this->total_days,
                'covered' => $this->days_covered,
                'remains' => $this->days_remaining,
                'covered_percentage' => $this->covered_percentage,
            ],
        ];
    }

    public function with($request)
    {
        return ['status' => 'success'];
    }

    public function withResponse($request, $response)
    {
        $response->header('Accept', 'application/json');
    }
}
