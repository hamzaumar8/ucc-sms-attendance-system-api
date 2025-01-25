<?php

namespace App\Traits;

use App\Models\Semester;
use Carbon\Carbon;

trait SemesterTrait
{

    /**
     * Get the current semester object.
     *
     * @return Semester|null
     */
    public function getCurrentSemester()
    {
        return Semester::whereDate('start_date', '<=', Carbon::now()->format('Y-m-d'))
            ->whereDate('end_date', '>=', Carbon::now()->format('Y-m-d'))
            ->first();
    }
    /**
     * Get the current semester.
     *
     * @return int|null
     */
    public function getCurrentSemesterId()
    {
        $semester = $this->getCurrentSemester();
        return $semester ? $semester->id : null;
    }
}
