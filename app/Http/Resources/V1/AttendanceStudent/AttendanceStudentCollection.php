<?php

namespace App\Http\Resources\V1\AttendanceStudent;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AttendanceStudentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request);
        // return [
        //     'data' => $this->collection,
        //     'total' => [
        //         'count' => $this->collection->count(),
        //         'present' => $this->collection->where('status', 1)->count(),
        //         'absent' => $this->collection->where('status', 0)->count(),
        //     ],
        // ];
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