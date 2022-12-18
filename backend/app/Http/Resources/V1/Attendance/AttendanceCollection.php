<?php

namespace App\Http\Resources\V1\Attendance;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AttendanceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        return [
            'data' => $this->collection,
            'total' => [
                'count' => $this->collection->count(),
                'present' => $this->collection->where('status', 'present')->count(),
                'absent' => $this->collection->where('status', 'absent')->count(),
                'student_attendance' => [
                    'count' => $this->student_attendance_count($this->collection),
                    'present' => $this->student_attendance_present($this->collection),
                    'absent' => $this->student_attendance_absent($this->collection),
                ],
            ],
        ];
    }

    public function student_attendance_present($collection)
    {
        $total = 0;
        foreach ($collection as $col) {
            $total += $col->attendance_student->where('status', 1)->count();
        }

        return $total;
    }
    public function student_attendance_absent($collection)
    {
        $total = 0;
        foreach ($collection as $col) {
            $total += $col->attendance_student->where('status', 0)->count();
        }

        return $total;
    }
    public function student_attendance_count($collection)
    {
        $total = 0;
        foreach ($collection as $col) {
            $total += $col->attendance_student->count();
        }

        return $total;
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