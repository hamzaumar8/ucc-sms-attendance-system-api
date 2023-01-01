<?php

namespace App\Http\Resources\V1\Attendance;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;

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

        $total = $this->collection->count();
        $present = $this->collection->where('status', 'present')->count();
        $absent = $this->collection->where('status', 'absent')->count();
        return [
            // 'data' => $this->collection,
            'weekly' => $this->weekly($this->collection),
            'total' => [
                'count' => $total,
                'present' => $present,
                'absent' => $absent,
                'present_percentage' => round(($present / ($total > 0 ? $total : 1)) * 100),
                'absent_percentage' => round(($absent / ($total > 0 ? $total : 1)) * 100),
                'student_attendance' => [
                    'count' => $this->student_attendance_count($this->collection),
                    'present' => $this->student_attendance_present($this->collection),
                    'absent' => $this->student_attendance_absent($this->collection),
                    'present_percentage' => $this->present_percentage($this->collection)
                ],
            ],
        ];
    }


    public function weekly($collection)
    {
        $groups = $collection->groupBy(function ($row) {
            return
                Carbon::parse($row->date)->format('W');
        });

        $groupwithcount = $groups->map(function ($group) {
            $total = $group->count();
            $present = $group->where('status', 'present')->count();
            $absent = $group->where('status', 'absent')->count();
            return [
                'total' => $total,
                'present' => $present,
                'absent' => $absent,
                'present_percentage' => round(($present / ($total > 0 ? $total : 1)) * 100),
                'absent_percentage' => round(($absent / ($total > 0 ? $total : 1)) * 100),
            ];
        });
        return $groupwithcount;
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

    public function present_percentage($collection)
    {
        $total_present = $this->student_attendance_present($collection);
        $total_count = $this->student_attendance_count($collection);

        return round(($total_present / ($total_count > 0 ? $total_count : 1)) * 100);
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