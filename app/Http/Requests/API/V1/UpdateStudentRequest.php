<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $student = $this->route('student'); // Assuming the student is passed as a route parameter

        return [
            'email' => 'required|string|email|max:255|unique:users,email,' . $student->user->id,
            'index_number' => 'required|max:20|unique:students,index_number,' . $student->id,
            'first_name' => 'required|string|max:20',
            'other_name' => 'nullable|string|max:255',
            'surname' => 'required|string|max:20',
            'phone' => 'nullable|string|max:15',
            'picture' => 'nullable|file|mimes:jpeg,png,webp',
            'level' => 'required|numeric|exists:levels,id',
        ];
    }
}
