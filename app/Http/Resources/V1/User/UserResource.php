<?php

namespace App\Http\Resources\V1\User;

use App\Http\Resources\V1\Lecturer\LecturerResource;
use App\Http\Resources\V1\Student\StudentResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'role' => $this->role,
            'lecturer' => LecturerResource::make($this->whenLoaded('lecturer')),
            'student' => StudentResource::make($this->whenLoaded('student')),
        ];
    }
}