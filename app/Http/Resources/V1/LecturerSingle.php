<?php

namespace App\Http\Resources\API\V1\Lecturer;

use Illuminate\Http\Resources\Json\JsonResource;

class LecturerSingle extends JsonResource
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
            'full_name' => $this->full_name(),
            // 'picture' => $this->picture_url(),
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
