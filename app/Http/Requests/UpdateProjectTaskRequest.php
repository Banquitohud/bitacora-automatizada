<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'responsible_id' => ['nullable', 'integer', 'exists:users,id'],
            'task_status_id' => ['nullable', 'integer', 'exists:task_statuses,id'],
            'priority_id' => ['nullable', 'integer', 'exists:priorities,id'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'completed_date' => ['nullable', 'date'],
            'progress' => ['nullable', 'integer', 'between:0,100'],
            'comments' => ['nullable', 'string'],
        ];
    }
}