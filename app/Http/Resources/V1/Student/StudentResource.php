<?php

namespace App\Http\Resources\V1\Student;

use App\Http\Resources\API\V1\Level\LevelResource;
use App\Http\Resources\API\V1\User\UserResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'index_number' => $this->index_number,
            'full_name' => $this->full_name(),
            'first_name' => $this->first_name,
            'surname' => $this->surname,
            'other_name' => $this->other_name,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'group_no' => $this->group_no,
            'picture' => $this->picture_url(),
            'created_at' => Carbon::parse($this->created_at),
            // eager loading
            'user' => UserResource::make($this->whenLoaded('user')),
            'level' => LevelResource::make($this->whenLoaded('level')),
            // 'modules' => ModuleResource::collection(
            //     $this->whenLoaded('modules')
            // ),
            // 'attendance' => AttendanceStudentResource::collection($this->whenLoaded('attendance')),
            // 'attendance_stats' => [
            //     'total' => $this->attendance_total(),
            //     'present' => $this->attendance_present(),
            //     'absent' => $this->attendance_absent(),
            //     'present_percentage' => $this->attendance_present_percentage(),
            //     'absent_percentage' => $this->attendance_absent_percentage(),
            // ],
            // 'results' => ResultResource::collection($this->whenLoaded('results')),
            // 'groups' => GroupResource::collection($this->whenLoaded('results')),
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
