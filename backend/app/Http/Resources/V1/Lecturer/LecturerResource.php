<?php

namespace App\Http\Resources\V1\Lecturer;

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
        // return parent::toArray($request);
        $picture_url = $this->picture ? asset('assets/img/lecturers/' . $this->picture) : asset('assets/img/lecturers/default.png');
        return [
            'id' => $this->id,
            'attributes' => [
                'title' => $this->title,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'other_name' => $this->other_name,
                'gender' => $this->gender,
                'phone1' => $this->phone1,
                'picture' => $this->picture,
                'picture_url' => $picture_url,
                'created_at' => $this->created_at,
            ],
            'relationships' => [
                'user' => UserResource::make($this->user),
            ],
            'links' => [
                'self' => route('lecturers.show', $this->id),
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