<?php

namespace App\Imports\V1;

use App\Traits\UtilsTrait;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ResultImport implements ToArray, WithHeadingRow
{
    use Importable, UtilsTrait;

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
                $assessment->remarks = $this->remarks($score);
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
