<?php

namespace App\Http\Resources\V1\Group;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V1\Level\LevelResource;
use App\Http\Resources\V1\Student\StudentResource;

class GroupResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'groups' => $this->groups,
            'semester_id' => $this->semester_id,
            'level_id' => $this->level_id,
            'level'=> LevelResource::make($this->whenLoaded('level')),
            'students'=> StudentResource::collection($this->whenLoaded('students')),
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