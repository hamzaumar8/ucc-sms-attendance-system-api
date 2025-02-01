<?php

namespace App\Exports\V1;

use App\Models\Result;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ResultExport implements FromCollection, WithMapping, WithHeadings
{
    use Exportable;

    public $result;

    public function __construct($result)
    {
        $this->result = $result;
    }

    // added for mapping data
    public function map($result): array
    {
        $name = $result->student->other_name ? $result->student->first_name . ' ' . $result->student->other_name . ' ' . $result->student->surname : $result->student->first_name . ' ' . $result->student->surname;
        return [
            strtoupper($result->student->index_number),
            strtoupper($name),
            $result->score,
            // $result->remarks,
        ];
    }


    public function headings(): array
    {
        return [
            // '#',
            'NAME',
            'INDEX NUMBER',
            'SCORE',
            // 'REMARK',
        ];
    }

    public function collection()
    {
        return $this->result;
    }
}
