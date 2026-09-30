<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Audit;
use App\Models\Priority;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(private readonly AuditService $audit)
    {
        $this->authorizeResource(Project::class, 'project');
    }

    public function index(Request $request): View
    {
        $query = Project::query()
            ->with(['status', 'priority', 'responsible'])
            ->when($request->input('search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"))
            ->when($request->input('project_status_id'), fn ($q, $v) => $q->where('project_status_id', $v))
            ->when($request->input('responsible_id'), fn ($q, $v) => $q->where('responsible_id', $v))
            ->latest();

        return view('projects.index', [
            'projects' => $query->paginate(12)->withQueryString(),
            'filters' => $request->only(['search', 'project_status_id', 'responsible_id']),
            'statuses' => ProjectStatus::active()->ordered()->get(),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return $this->formView(new Project(), 'projects.create');
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = DB::transaction(function () use ($request) {
            $project = Project::create($request->validated() + [
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->audit->created($project, 'el proyecto "'.$project->name.'"');

            return $project;
        });

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Proyecto creado correctamente.');
    }

    public function show(Project $project): View
    {
        $project->load(['status', 'priority', 'responsible', 'tasks.status', 'tasks.priority', 'tasks.responsible', 'cases.status']);

        $history = Audit::query()
            ->where('auditable_type', Project::class)
            ->where('auditable_id', $project->id)
            ->with('user')
            ->latest()
            ->get();

        return view('projects.show', [
            'project' => $project,
            'history' => $history,
            'statuses' => ProjectStatus::active()->ordered()->get(),
            'taskStatuses' => \App\Models\TaskStatus::active()->ordered()->get(),
            'priorities' => Priority::active()->ordered()->get(),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function edit(Project $project): View
    {
        return $this->formView($project, 'projects.edit');
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $old = $project->getAttributes();

        DB::transaction(function () use ($request, $project, $old) {
            $project->update($request->validated() + ['updated_by' => auth()->id()]);

            $this->audit->updated($project, $old, $project->getAttributes());
        });

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Proyecto actualizado correctamente.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->audit->deleted($project, 'el proyecto "'.$project->name.'"');
        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Proyecto eliminado (soft delete).');
    }

    private function formView(Project $project, string $view): View
    {
        return view($view, [
            'project' => $project,
            'statuses' => ProjectStatus::active()->ordered()->get(),
            'priorities' => Priority::active()->ordered()->get(),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}