<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectTaskRequest;
use App\Http\Requests\UpdateProjectTaskRequest;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProjectTaskController extends Controller
{
    public function __construct(private readonly AuditService $audit)
    {
    }

    public function store(StoreProjectTaskRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        DB::transaction(function () use ($request, $project) {
            $task = $project->tasks()->create($request->validated() + [
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->audit->created($task, 'la tarea "'.$task->name.'"');

            $project->syncProgress();
        });

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Tarea agregada correctamente.');
    }

    public function update(UpdateProjectTaskRequest $request, Project $project, ProjectTask $task): RedirectResponse
    {
        $this->authorize('update', $project);

        $old = $task->getAttributes();

        DB::transaction(function () use ($request, $project, $task, $old) {
            $task->update($request->validated() + ['updated_by' => auth()->id()]);

            $this->audit->updated($task, $old, $task->getAttributes());

            $project->syncProgress();
        });

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Tarea actualizada correctamente.');
    }

    public function destroy(Project $project, ProjectTask $task): RedirectResponse
    {
        $this->authorize('update', $project);

        $this->audit->deleted($task, 'la tarea "'.$task->name.'"');
        $task->delete();

        $project->syncProgress();

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Tarea eliminada (soft delete).');
    }
}