<?php

namespace App\Http\Resources\V1\AttendanceStudent;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceStudentResource extends JsonResource
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
            "attendance_id" => $this->attendance_id,
            "student_id" => $this->student_id,
            "status" => $this->status,
            "created_at" => $this->created_at,
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
