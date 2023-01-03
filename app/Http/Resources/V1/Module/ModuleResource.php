<?php

namespace App\Http\Resources\V1\Module;

use App\Http\Resources\V1\Attendance\AttendanceCollection;
use App\Http\Resources\V1\Attendance\AttendanceResource;
use App\Http\Resources\V1\Lecturer\LecturerResource;
use App\Http\Resources\V1\Level\LevelResource;
use App\Http\Resources\V1\ModuleBank\ModuleBankResource;
use App\Http\Resources\V1\Student\StudentResource;
use App\Models\Lecturer;
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
        // if ($startDate->isWeekday()) {
        //     $startDate = $startDate->subDay();
        // }
        $endDate = Carbon::parse($this->end_date);
        $days = (int)($endDate->diffInDays($startDate));
        if (Carbon::now()->between(Carbon::parse($this->start_date), $endDate)) {
            $days_covered = (int)($startDate->diffInDays(Carbon::now()));
            if ($this->status !== 'active') {
                $this->update(['status' => 'active']);
            }
        } elseif (Carbon::now()->gt($endDate)) {
            $days_covered = $days;
            if ($this->status !== 'inactive') {
                $this->update(['status' => 'inactive']);
            }
        } else {
            $days_covered = 0;
            if ($this->status !== 'upcoming') {
                $this->update(['status' => 'upcoming']);
            }
        }
        $days_remaining = (int)($days - $days_covered);
        $day_s = $days > 0 ? $days : 1;
        $covered_percentage = round(($days_covered / $day_s) * 100);

        return [
            'id' => $this->id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'cordinator_id' => $this->cordinator_id,
            'module' => ModuleBankResource::make($this->whenLoaded('module_bank')),
            'cordinator' => LecturerResource::make($this->whenLoaded('cordinator')),
            'course_rep' => StudentResource::make($this->whenLoaded('course_rep')),
            'level' => LevelResource::make($this->whenLoaded('level')),
            'lecturers' => LecturerResource::collection($this->whenLoaded('lecturers')),
            'attendance' => new AttendanceCollection($this->whenLoaded('attendances')),
            'students' => StudentResource::collection($this->whenLoaded('students')),
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