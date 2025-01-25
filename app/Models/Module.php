<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'semester_id',
        'module_bank_id',
        'cordinator_id',
        'course_rep_id',
        'level_id',
        'start_date',
        'end_date',
        'status',
    ];

    public function cordinator()
    {
        return $this->belongsTo(Lecturer::class, 'cordinator_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function module_bank()
    {
        return $this->belongsTo(ModuleBank::class, 'module_bank_id');
    }

    public function course_rep()
    {
        return $this->belongsTo(Student::class, 'course_rep_id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id');
    }


    public function lecturers()
    {
        return $this->belongsToMany(Lecturer::class, 'lecturer_module', 'module_id', 'lecturer_id');
    }


    public function students()
    {
        return $this->belongsToMany(Student::class, 'module_student', 'module_id', 'student_id');
    }


    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'module_id');
    }


    public function attendances_course_rep()
    {
        return $this->hasMany(Attendance::class, 'module_id');
    }

    public function attendances_lecturer()
    {
        return $this->hasMany(AttendanceLecturer::class, 'module_id');
    }




    /**
     * Get the total number of days for the module.
     *
     * @return int
     */
    public function getTotalDaysAttribute()
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);

        return (int)($startDate->diffInDays($endDate));
    }

    /**
     * Get the number of days covered so far.
     *
     * @return int
     */
    public function getDaysCoveredAttribute()
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        $now = Carbon::now();

        if (Carbon::now()->between($startDate, $endDate)) {
            $days_covered = (int)($startDate->diffInDays(Carbon::now()));
        } elseif ($now->gt($endDate)) {
            $days_covered = $this->total_days;
        } else {
            $days_covered = 0;
        }

        return $days_covered;
    }

    /**
     * Get the number of days remaining.
     *
     * @return int
     */
    public function getDaysRemainingAttribute()
    {
        return (int) $this->total_days - $this->days_covered;
    }

    /**
     * Get the percentage of days covered.
     *
     * @return int
     */
    public function getCoveredPercentageAttribute()
    {
        return round(($this->days_covered / ($this->total_days > 0 ? $this->total_days : 1)) * 100);
    }

    /**
     * Update the module's status based on the current date.
     *
     * @return void
     */
    public function updateStatusBasedOnDate()
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        $now = Carbon::now();

        if (Carbon::now()->between($startDate, $endDate)) {
            $this->updateStatus('active');
        } elseif ($now->gt($endDate)) {
            $this->updateStatus('inactive');
        } else {
            $this->updateStatus('upcoming');
        }
    }

    /**
     * Update the module's status if necessary.
     *
     * @param string $newStatus
     * @return void
     */
    protected function updateStatus(string $newStatus)
    {
        if ($this->status !== $newStatus) {
            $this->update(['status' => $newStatus]);

            if ($newStatus === 'inactive') {
                $this->generateResultsAndAssessments();
            }
        }
    }

    /**
     * Generate results and assessments for inactive modules.
     *
     * @return void
     */
    protected function generateResultsAndAssessments()
    {
        if ($this->status !== 'inactive') {
            $result = Result::firstOrCreate([
                'semester_id' => $this->semester_id,
                'module_id' => $this->id,
                'cordinator_id' => $this->cordinator_id,
            ]);

            foreach ($this->students as $student) {
                Assessment::firstOrCreate([
                    'result_id' => $result->id,
                    'student_id' => $student->id,
                ]);
            }
        }
    }
}
