<?php

namespace App\Http\Resources\V1\Module;

use App\Http\Resources\V1\Attendance\AttendanceCollection;
use App\Http\Resources\V1\Lecturer\LecturerResource;
use App\Http\Resources\V1\Lecturer\LecturerSingleResource;
use App\Http\Resources\V1\Student\StudentSingleResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $startDate = Carbon::parse($this->start_date);
        if ($startDate->isWeekday()) {
            $startDate = $startDate->subDay();
        }
        $endDate = Carbon::parse($this->end_date);
        $days = (int)($endDate->diffInDays($startDate));
        if (Carbon::now()->between(Carbon::parse($this->start_date), $endDate)) {
            $days_covered = (int)(Carbon::parse($this->start_date)->diffInDays(Carbon::now()));
            $this->status === 'active' ? $this->status : $this->update(['status' => 'active']);
        } elseif (Carbon::now()->gt($endDate)) {
            $days_covered = $days;
            $this->status === 'inactive' ? $this->status : $this->update(['status' => 'inactive']);
        } else {
            $days_covered = 0;
            $this->status === 'upcoming' ? $this->status : $this->update(['status' => 'upcoming']);
        }
        $days_remaining = (int)($days - $days_covered);
        $covered_percentage = round(($days_covered * 100) / $days);

        return [
            'id' => $this->id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            // 'students' => $this->students,
            'lecturers' => $this->lectures,
            'module' => $this->module_bank,
            'cordinator' => LecturerSingleResource::make($this->cordinator),
            'level' => $this->level,
            'course_rep' => StudentSingleResource::make($this->course_rep),
            'attendance' => AttendanceCollection::make($this->attendances),
            'days' => [
                'total' => $days,
                'covered' => $days_covered,
                'remains' => $days_remaining,
                'covered_percentage' => $covered_percentage,
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
    }
}