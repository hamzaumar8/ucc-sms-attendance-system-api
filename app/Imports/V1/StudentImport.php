<?php

namespace App\Imports\V1;

use App\Models\Level;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentImport implements ToModel, WithHeadingRow, WithChunkReading, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsFailures, SkipsErrors;
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $name = $row['other_name'] ? $row['first_name'] . ' ' . $row['other_name'] . ' ' . $row['surname'] : $row['first_name'] . ' ' . $row['surname'];

        $user =   User::create([
            'email' => $row['email'],
            'username' => strtoupper($row['index_number']),
            'name' => $name,
            'email_verified_at' => now(),
            'password' => Hash::make(strtolower(str_replace("/", "", $row['index_number']))),
        ]);

        //Assign a student role
        $user->assignRole('student');

        $level_id = null;
        $level = Level::where('name', 'like', "%{$row['level']}%")->first();
        if ($level) {
            $level_id = $level->id;
        }

        $student = new Student([
            'user_id' => $user->id,
            'index_number' => strtoupper($row['index_number']),
            'first_name' => $row['first_name'],
            'other_name' => $row['other_name'],
            'surname' => $row['surname'],
            'phone' => $row['phone'],
            'level_id' => $level_id,
        ]);

        return $student;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|string|email|max:255|unique:users,email',
            'index_number' => 'required|max:20|unique:students,index_number',
            'first_name' => 'required|string|max:20',
            'other_name' => 'nullable|string|max:255',
            'surname' => 'required|string|max:20',
            'phone' => 'nullable|string|max:15',
            'level' => 'required',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}