<?php

namespace App\Http\Resources\V1\Attendance;

use App\Http\Resources\V1\AttendanceStudent\AttendanceStudentCollection;
use App\Http\Resources\V1\Student\StudentResource;
use App\Http\Resources\V1\Module\ModuleResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class AttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            "id" => $this->id,
            "lecturer_id" => $this->lecturer_id,
            "module_id" => $this->module_id,
            "date" => $this->date,
            "start_time" => $this->start_time,
            "end_time" => $this->end_time,
            "status" => $this->status,
            "author" => $this->author,
            "semester_id" => $this->semester_id,
            "created_at" => Carbon::parse($this->created_at),
            "module" => ModuleResource::make($this->whenLoaded('module')),
            "students" => new AttendanceStudentCollection($this->whenLoaded('attendance_student')),
            'total'=> $this->total(),
            'weekly' => $this->weekly(),
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