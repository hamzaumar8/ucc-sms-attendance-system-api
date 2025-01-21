<?php

namespace App\Http\Resources\API\V1\LecturerModule;

use App\Http\Resources\API\V1\Attendance\AttendanceCollection;
use App\Http\Resources\API\V1\Attendance\AttendanceResource;
use App\Http\Resources\API\V1\Lecturer\LecturerResource;
use App\Http\Resources\API\V1\Module\ModuleResource;
use App\Http\Resources\API\V1\Student\StudentResource;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class LecturerModuleResource extends JsonResource
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
            "id" => $this->id,
            "lecturer_id" => $this->lecturer_id,
            "module_id" => $this->module_id,
            "level_id" => $this->level_id,
            "start_date" => $this->start_date,
            "end_date" => $this->end_date,
            "status" => $this->status,
            "course_rep_id" => $this->course_rep_id,
            "created_at" => Carbon::parse($this->created_at),
            'module' => ModuleResource::make($this->module),
            'lecturer' => LecturerResource::make($this->lecturer),
            'attendance' => AttendanceCollection::make($this->attendances),
            'course_rep' => StudentResource::make($this->course_rep),
            'level' => $this->level,
        ];
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
