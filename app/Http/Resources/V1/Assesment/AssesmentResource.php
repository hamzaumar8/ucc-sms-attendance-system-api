<?php

namespace App\Http\Resources\V1\Assesment;

use App\Http\Resources\V1\Student\StudentResource;
use Illuminate\Http\Resources\Json\JsonResource;

class AssesmentResource extends JsonResource
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
            'result_id' => $this->result_id,
            'student_id' => $this->student_id,
            'score' => $this->score,
            'remarks' => $this->remarks,
            'student' => StudentResource::make($this->student),
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