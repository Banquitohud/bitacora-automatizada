<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\CaseStatus;
use App\Models\Priority;
use App\Models\Project;
use App\Models\RequestType;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $report)
    {
    }

    public function index(Request $request): View
    {
        $filters = array_merge([
            'from' => null,
            'to' => null,
            'analyst_id' => null,
            'status_id' => null,
            'request_type_id' => null,
            'application_id' => null,
            'priority_id' => null,
            'project_id' => null,
        ], $request->only(['from', 'to', 'analyst_id', 'status_id', 'request_type_id', 'application_id', 'priority_id', 'project_id']));

        $flow = $this->report->flowReport($filters);
        $productivity = $this->report->productivityReport($filters);
        $applications = $this->report->applicationReport($filters);
        $requestTypes = $this->report->requestTypeReport($filters);
        $projects = $this->report->projectReport();

        return view('reports.index', [
            'filters' => $filters,
            'flow' => $flow,
            'productivity' => $productivity,
            'applications' => $applications,
            'requestTypes' => $requestTypes,
            'projects' => $projects,
            'statuses' => CaseStatus::active()->ordered()->get(),
            'requestTypeList' => RequestType::active()->ordered()->get(),
            'applicationList' => Application::active()->ordered()->get(),
            'priorityList' => Priority::active()->ordered()->get(),
            'analystList' => User::query()->where('is_active', true)->orderBy('name')->get(),
            'projectList' => Project::query()->orderBy('name')->get(),
        ]);
    }

    public function monthly(Request $request): View
    {
        $month = $request->input('month', now()->format('Y-m'));
        $summary = $this->report->monthlySummary($month);

        return view('reports.monthly', [
            'month' => $month,
            'summary' => $summary,
        ]);
    }
}