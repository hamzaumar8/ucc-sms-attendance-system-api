<?php

namespace App\Http\Resources\V1\Assessment;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V1\Student\StudentSingle;

class AssessmentResource extends JsonResource
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
            'student' => new StudentSingle($this->whenLoaded('student')),
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
