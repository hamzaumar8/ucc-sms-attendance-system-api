<?php

namespace App\Http\Resources\V1\Result;

use App\Http\Controllers\API\V1\ResultController;
use App\Http\Resources\V1\Assesment\AssesmentResource;
use App\Http\Resources\V1\Module\ModuleResource;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'status' => $this->status,
            'created_at' => $this->created_at,
            'module' => ModuleResource::make($this->whenLoaded('module')),
            // 'cordinator' => AssesmentResource::collection($this->whenLoaded('cordinator')),
            // 'students' => $this->students,
            'assessments' => AssesmentResource::collection($this->whenLoaded('assessments')),
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
