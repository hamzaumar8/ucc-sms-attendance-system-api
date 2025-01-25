<?php

namespace App\Http\Resources\V1\Attendance;

use App\Http\Resources\V1\Lecturer\LecturerSingle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceSingle extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'semester_id' => $this->semester_id,
            'lecturer_id' => $this->lecturer_id,
            'course_rep_id' => $this->course_rep_id,
            'module_id' => $this->module_id,
            'date' => $this->date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            "created_at" => Carbon::parse($this->created_at),
            'lecturer' => LecturerSingle::make($this->lecturer),
        ];
    }
}
