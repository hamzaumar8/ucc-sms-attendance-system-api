<?php

namespace App\Http\Resources\V1\LecturerModule;

use App\Http\Resources\V1\Attendance\AttendanceCollection;
use App\Http\Resources\V1\Attendance\AttendanceResource;
use App\Http\Resources\V1\Lecturer\LecturerResource;
use App\Http\Resources\V1\Module\ModuleResource;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class LecturerModuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $toDate = Carbon::parse($this->start_date);
        $fromDate = Carbon::parse($this->end_date);

        $days = $toDate->diffInDays($fromDate);
        $days_remaining = now()->diffInDays($fromDate);
        $days_covered = $toDate->diffInDays(now());
        $weekdays = $toDate->diffInWeekdays($fromDate);
        $months = $toDate->diffInMonths($fromDate);
        $years = $toDate->diffInYears($fromDate);



        return [
            "id" => $this->id,
            "lecturer_id" => $this->lecturer_id,
            "module_id" => $this->module_id,
            "start_date" => $this->start_date,
            "end_date" => $this->end_date,
            "status" => $this->status,
            "course_rep_id" => $this->course_rep_id,
            "created_at" => $this->created_at,
            'module' => ModuleResource::make($this->module),
            'lecturer' => LecturerResource::make($this->lecturer),
            'attendance' => AttendanceCollection::make($this->attendances),
            'days' => [
                'total' => (int)$days + 1,
                'covered' => (int)$days_covered + 1,
                'remains' => (int)$days_remaining + 1,
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