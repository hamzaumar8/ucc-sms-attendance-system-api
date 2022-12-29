<?php

namespace App\Http\Resources\V1\Student;

use App\Http\Resources\V1\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentSingleResource extends JsonResource
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
            'id' => $this->id(),
            'index_number' => $this->index_number,
            'full_name' => $this->full_name(),
            'first_name' => $this->first_name,
            'surname' => $this->surname,
            'other_name' => $this->other_name,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'picture' => $this->picture,
            'picture_url' => $this->picture_url(),
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