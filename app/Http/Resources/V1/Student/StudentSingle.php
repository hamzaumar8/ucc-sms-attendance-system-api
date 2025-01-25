<?php

namespace App\Http\Resources\V1\Student;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentSingle extends JsonResource
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
            'index_number' => $this->index_number,
            'full_name' => $this->full_name(),
            'picture' => $this->picture_url(),
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
