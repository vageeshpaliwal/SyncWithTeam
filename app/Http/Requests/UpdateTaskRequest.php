<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'user_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'string', 'in:todo,in_progress,review,done,reopened'],
            'priority' => ['required', 'string', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
