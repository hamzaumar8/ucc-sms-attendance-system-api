<?php

namespace App\Http\Resources\V1\Result;

use App\Http\Controllers\V1\ResultController;
use App\Http\Resources\V1\Assessment\AssessmentResource;
use App\Http\Resources\V1\Module\ModuleResource;
use App\Http\Resources\V1\Semester\SemesterResource;
use App\Http\Resources\V1\Student\StudentResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ResultResource extends JsonResource
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
            'semester_id' => $this->semester_id,
            'module_id' => $this->module_id,
            'coordinator_id' => $this->coordinator_id,
            'status' => $this->status,
            'created_at' => Carbon::parse($this->created_at),
            'score' => $this->whenPivotLoaded('assessments', function () {
                return $this->pivot->score;
            }),
            'remarks' => $this->whenPivotLoaded('assessments', function () {
                return $this->pivot->remarks;
            }),
            'semester' => SemesterResource::make($this->whenLoaded('semester')),
            'module' => ModuleResource::make($this->whenLoaded('module')),
            'assessments' => AssessmentResource::collection($this->whenLoaded('assessments')),
            'students' => StudentResource::collection($this->whenLoaded('students')),
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
