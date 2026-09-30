<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', Rule::unique('projects', 'code')->ignore($project?->id)],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'estimated_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'actual_end_date' => ['nullable', 'date'],
            'responsible_id' => ['nullable', 'integer', 'exists:users,id'],
            'project_status_id' => ['nullable', 'integer', 'exists:project_statuses,id'],
            'priority_id' => ['nullable', 'integer', 'exists:priorities,id'],
            'progress' => ['nullable', 'integer', 'between:0,100'],
            'observations' => ['nullable', 'string'],
        ];
    }
}