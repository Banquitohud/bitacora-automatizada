<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDailyCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $case = $this->route('daily_case');

        return [
            'case_number' => ['nullable', 'string', 'max:255', Rule::unique('daily_cases', 'case_number')->ignore($case?->id)],
            'received_date' => ['required', 'date'],
            'received_time' => ['nullable', 'date_format:H:i'],
            'due_date' => ['nullable', 'date'],
            'requester' => ['nullable', 'string', 'max:255'],
            'affected_user' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'request_type_id' => ['nullable', 'integer', 'exists:request_types,id'],
            'application_id' => ['nullable', 'integer', 'exists:applications,id'],
            'profile_id' => ['nullable', 'integer', 'exists:profiles,id'],
            'permission' => ['nullable', 'string', 'max:255'],
            'group_id' => ['nullable', 'integer', 'exists:groups,id'],
            'group_family_id' => ['nullable', 'integer', 'exists:group_families,id'],
            'priority_id' => ['nullable', 'integer', 'exists:priorities,id'],
            'status_id' => ['nullable', 'integer', 'exists:case_statuses,id'],
            'analyst_id' => ['nullable', 'integer', 'exists:users,id'],
            'started_at' => ['nullable', 'date'],
            'finished_at' => ['nullable', 'date'],
            'concept' => ['nullable', 'string'],
            'result' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'comments' => ['nullable', 'string'],
            'project_ids' => ['nullable', 'array'],
            'project_ids.*' => ['integer', 'exists:projects,id'],
        ];
    }
}