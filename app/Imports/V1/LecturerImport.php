<?php

namespace App\Imports\V1;

use App\Models\Lecturer;
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

class LecturerImport implements ToModel, WithHeadingRow, WithChunkReading, WithValidation, SkipsOnError, SkipsOnFailure
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
            'username' => $row['staff_id'],
            'name' => $name,
            'email_verified_at' => now(),
            'password' => Hash::make($row['staff_id']),
        ]);

        //Assign a lecturer role
        $user->assignRole('lecturer');

        $lecturer = new Lecturer([
            'user_id' => $user->id,
            'staff_id' => $row['staff_id'],
            'title' => $row['title'],
            'first_name' => $row['first_name'],
            'other_name' => $row['other_name'],
            'surname' => $row['surname'],
            'phone' => $row['phone'],
        ]);

        return $lecturer;
    }


    public function rules(): array
    {
        return [
            'email' => 'required|string|email|max:255|unique:users,email',
            'staff_id' => 'required|max:20|unique:lecturers,staff_id',
            'title' => 'required|string|max:20',
            'first_name' => 'required|string|max:20',
            'other_name' => 'nullable|string|max:255',
            'surname' => 'required|string|max:20',
            'phone' => 'nullable|string|max:15',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}