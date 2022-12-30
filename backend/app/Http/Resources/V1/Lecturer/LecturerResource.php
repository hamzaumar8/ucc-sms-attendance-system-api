<?php

namespace App\Http\Resources\V1\Lecturer;

use App\Http\Resources\V1\Module\ModuleCollection;
use App\Http\Resources\V1\Module\ModuleResource;
use App\Http\Resources\V1\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class LecturerResource extends JsonResource
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
            'staff_id' => $this->staff_id,
            'title' => $this->title,
            'first_name' => $this->first_name,
            'surname' => $this->surname,
            'other_name' => $this->other_name,
            'full_name' => $this->full_name(),
            'gender' => $this->gender,
            'phone' => $this->phone,
            'picture' => $this->picture,
            'picture_url' => $this->picture_url(),
            'created_at' => $this->created_at,
            'user' => UserResource::make($this->user),
            'modules' => ModuleResource::collection($this->whenLoaded('modules')),
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
        // $response->header('Version', '1.0.0');
    }
}