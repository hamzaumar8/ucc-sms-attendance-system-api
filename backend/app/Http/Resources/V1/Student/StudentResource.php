<?php

namespace App\Http\Resources\V1\Student;

use App\Http\Resources\V1\Module\ModuleCollection;
use App\Http\Resources\V1\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    // public static $wrap = 'students';
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $picture_url = $this->picture ? asset('assets/img/student/' . $this->picture) : asset('assets/img/lecturers/default.png');
        return [
            'id' => $this->id(),
            'index_number' => $this->index_number,
            'full_name' => $this->full_name(),
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'other_name' => $this->other_name,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'picture' => $this->picture,
            'picture_url' => $picture_url,
            'created_at' => $this->created_at,
            'level' => $this->level,
            'modules' => $this->modules,
            'rel' => [
                'user' => UserResource::make($this->user),
            ],
            'links' => [
                'self' => route('students.show', $this->id()),
            ]
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