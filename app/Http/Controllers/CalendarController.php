<?php

namespace App\Http\Controllers;

use App\Models\DailyCase;
use App\Models\Project;
use App\Models\ProjectTask;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(): View
    {
        return view('calendar.index');
    }

    public function events(Request $request): JsonResponse
    {
        $start = $request->input('start', now()->startOfMonth()->toDateString());
        $end = $request->input('end', now()->endOfMonth()->toDateString());

        $events = [];

        // Casos con fecha límite
        DailyCase::query()
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$start, $end])
            ->with(['status', 'priority'])
            ->get()
            ->each(function ($case) use (&$events) {
                $flag = $case->flag();
                $color = match ($flag) {
                    'red' => '#ef4444',
                    'yellow' => '#f59e0b',
                    'green' => '#10b981',
                    default => '#6b7280',
                };

                $events[] = [
                    'id' => 'case-'.$case->id,
                    'title' => 'Caso '.($case->case_number ?: '#'.$case->id),
                    'start' => $case->due_date->toDateString(),
                    'color' => $color,
                    'url' => route('daily-cases.show', $case),
                    'type' => 'case',
                ];
            });

        // Proyectos
        Project::query()
            ->whereNotNull('estimated_end_date')
            ->whereBetween('estimated_end_date', [$start, $end])
            ->with('status')
            ->get()
            ->each(function ($project) use (&$events) {
                $events[] = [
                    'id' => 'project-'.$project->id,
                    'title' => 'Proyecto: '.$project->name,
                    'start' => $project->estimated_end_date->toDateString(),
                    'color' => '#2563eb',
                    'url' => route('projects.show', $project),
                    'type' => 'project',
                ];
            });

        // Tareas de proyectos
        ProjectTask::query()
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$start, $end])
            ->with('project', 'status')
            ->get()
            ->each(function ($task) use (&$events) {
                $color = $task->isCompleted() ? '#10b981' : ($task->isOverdue() ? '#ef4444' : '#8b5cf6');

                $events[] = [
                    'id' => 'task-'.$task->id,
                    'title' => 'Tarea: '.$task->name,
                    'start' => $task->due_date->toDateString(),
                    'color' => $color,
                    'url' => $task->project ? route('projects.show', $task->project_id) : null,
                    'type' => 'task',
                ];
            });

        return response()->json($events);
    }
}