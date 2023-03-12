<?php

namespace App\Imports\V1;

use APP\Helpers\Helper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;

class ResultImport implements ToArray, WithHeadingRow
{
    use Importable;

    public $assessments;

    public function __construct($assessments)
    {
        $this->assessments = $assessments;
    }

    public function array(array $rows)
    {
        foreach ($this->assessments as $key => $assessment) {
            $score = $rows[$key]['score'];
            $student_id = $rows[$key]['INDEX NUMBER'];
            if ($student_id === $assessment->student_id && $score >= 0 && $score <= 100) {
                $assessment->score = $score;
                $assessment->remarks = Helper::remarks($score);
                $assessment->save();
            }
        }
    }
    // public function rules(): array
    // {
    //     return [
    //         'score' => 'required|numeric|between:0,100',
    //     ];
    // }

    // public function chunkSize(): int
    // {
    //     return 1000;
    // }
}