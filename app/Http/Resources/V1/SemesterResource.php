<?php

namespace App\Http\Resources\API\V1\Semester;

use Illuminate\Http\Resources\Json\JsonResource;

class SemesterResource extends JsonResource
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
            'id' => $this->id,
            'semester' => $this->semester,
            'academic_year' => $this->academic_year,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'promotion_status' => $this->promotion_status,
            'timetable' => $this->timetable,
            'created_at' => $this->created_at,
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
