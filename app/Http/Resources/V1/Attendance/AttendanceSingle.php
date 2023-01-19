<?php

namespace App\Http\Resources\V1\Attendance;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Http\Resources\V1\Lecturer\LecturerSingle;

class AttendanceSingle extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
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
