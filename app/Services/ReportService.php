<?php

namespace App\Services;

use App\Models\DailyCase;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;

class ReportService
{
    /**
     * Aplica los filtros del reporte sobre la consulta de casos.
     */
    public function filteredCaseQuery(array $filters)
    {
        return DailyCase::query()
            ->with(['status', 'priority', 'requestType', 'application', 'analyst', 'projects'])
            ->withFilters($filters);
    }

    public function flowReport(array $filters): array
    {
        $query = $this->filteredCaseQuery($filters);

        $all = (clone $query)->get();

        $finalized = $all->filter(fn ($c) => $c->isClosed());
        $pending = $all->filter(fn ($c) => $c->status?->slug === 'pendiente' || $c->status?->slug === 'recibido' || ! $c->status);
        $inProcess = $all->filter(fn ($c) => in_array($c->status?->slug, ['en-analisis', 'en-gestion', 'en-espera-de-informacion', 'escalado']));
        $overdue = $all->filter(fn ($c) => $c->isOverdue());

        return [
            'total_received' => $all->count(),
            'total_finished' => $finalized->count(),
            'total_pending' => $pending->count(),
            'total_in_process' => $inProcess->count(),
            'total_overdue' => $overdue->count(),
            'total_by_analyst' => $all->groupBy(fn ($c) => $c->analyst?->name ?? 'Sin asignar')
                ->map(fn ($g) => $g->count())
                ->sortDesc(),
            'avg_time_hours' => $finalized->map(fn ($c) => $c->totalTimeHours())->filter()->avg(),
        ];
    }

    public function productivityReport(array $filters): \Illuminate\Support\Collection
    {
        $dateFrom = $filters['from'] ?? null;
        $dateTo = $filters['to'] ?? null;

        $users = User::query()->where('is_active', true)->orderBy('name')->get();

        return $users->map(function ($user) use ($dateFrom, $dateTo) {
            $query = DailyCase::query()->where('analyst_id', $user->id);

            if ($dateFrom) {
                $query->whereDate('received_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('received_date', '<=', $dateTo);
            }

            $all = $query->get();
            $finished = $all->filter(fn ($c) => $c->isClosed());

            return [
                'user' => $user,
                'received' => $all->count(),
                'finished' => $finished->count(),
                'pending' => $all->filter(fn ($c) => $c->status?->slug === 'pendiente' || ! $c->status)->count(),
                'avg_hours' => $finished->map(fn ($c) => $c->totalTimeHours())->filter()->avg(),
                'total_hours' => $finished->map(fn ($c) => $c->totalTimeHours())->filter()->sum(),
            ];
        });
    }

    public function applicationReport(array $filters): \Illuminate\Support\Collection
    {
        return $this->filteredCaseQuery($filters)
            ->get()
            ->groupBy(fn ($c) => $c->application?->name ?? 'Sin aplicación')
            ->map(fn ($g) => $g->count())
            ->sortDesc();
    }

    public function requestTypeReport(array $filters): \Illuminate\Support\Collection
    {
        return $this->filteredCaseQuery($filters)
            ->get()
            ->groupBy(fn ($c) => $c->requestType?->name ?? 'Sin tipo')
            ->map(fn ($g) => $g->count())
            ->sortDesc();
    }

    public function projectReport(array $filters = []): array
    {
        $query = Project::query()->with(['status', 'priority', 'responsible', 'tasks'])
            ->withCount('cases');

        $projects = $query->get();

        $active = $projects->filter(fn ($p) => $p->isActive());
        $finished = $projects->filter(fn ($p) => $p->isFinished());

        $pendingTasks = $projects->flatMap(fn ($p) => $p->tasks)
            ->filter(fn ($t) => ! $t->isCompleted());

        $overdueTasks = $pendingTasks->filter(fn ($t) => $t->isOverdue());

        return [
            'projects' => $projects,
            'active_count' => $active->count(),
            'finished_count' => $finished->count(),
            'avg_progress' => $active->avg('progress'),
            'pending_tasks' => $pendingTasks->count(),
            'overdue_tasks' => $overdueTasks->count(),
        ];
    }

    public function monthlySummary(string $yearMonth): array
    {
        $month = Carbon::createFromFormat('Y-m', $yearMonth);
        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();

        $cases = DailyCase::query()
            ->whereBetween('received_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $finished = $cases->filter(fn ($c) => $c->isClosed());
        $pending = $cases->filter(fn ($c) => $c->status?->slug === 'pendiente' || ! $c->status);
        $overdue = $cases->filter(fn ($c) => $c->isOverdue());

        $projects = Project::query()->get();
        $activeProjects = $projects->filter(fn ($p) => $p->isActive());

        return [
            'month' => $month,
            'total_received' => $cases->count(),
            'total_finished' => $finished->count(),
            'total_pending' => $pending->count(),
            'total_overdue' => $overdue->count(),
            'by_analyst' => $cases->groupBy(fn ($c) => $c->analyst?->name ?? 'Sin asignar')->map(fn ($g) => $g->count())->sortDesc(),
            'by_application' => $cases->groupBy(fn ($c) => $c->application?->name ?? 'Sin aplicación')->map(fn ($g) => $g->count())->sortDesc(),
            'by_type' => $cases->groupBy(fn ($c) => $c->requestType?->name ?? 'Sin tipo')->map(fn ($g) => $g->count())->sortDesc(),
            'avg_time_hours' => $finished->map(fn ($c) => $c->totalTimeHours())->filter()->avg(),
            'active_projects' => $activeProjects->count(),
            'finished_projects' => $projects->filter(fn ($p) => $p->isFinished())->count(),
            'avg_project_progress' => $activeProjects->avg('progress'),
            'projects' => $projects,
        ];
    }
}