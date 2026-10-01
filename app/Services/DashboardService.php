<?php

namespace App\Services;

use App\Models\Audit;
use App\Models\DailyCase;
use App\Models\Project;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Resuelve el rango de fechas según el periodo seleccionado.
     *
     * @return array{from: Carbon, to: Carbon}
     */
    public function resolvePeriod(string $period, ?string $from = null, ?string $to = null): array
    {
        $today = now()->startOfDay();

        return match ($period) {
            'today' => ['from' => $today, 'to' => $today->copy()->endOfDay()],
            'yesterday' => ['from' => $today->copy()->subDay(), 'to' => $today->copy()->subDay()->endOfDay()],
            'last7' => ['from' => $today->copy()->subDays(6), 'to' => $today->copy()->endOfDay()],
            'month' => ['from' => $today->copy()->startOfMonth(), 'to' => $today->copy()->endOfMonth()],
            'lastMonth' => ['from' => $today->copy()->subMonth()->startOfMonth(), 'to' => $today->copy()->subMonth()->endOfMonth()],
            'year' => ['from' => $today->copy()->startOfYear(), 'to' => today()->endOfYear()],
            default => [
                'from' => $from ? Carbon::parse($from)->startOfDay() : $today->copy()->subDays(30),
                'to' => $to ? Carbon::parse($to)->endOfDay() : $today->copy()->endOfDay(),
            ],
        };
    }

    public function baseCaseQuery(array $period, ?int $analystId = null)
    {
        return DailyCase::query()
            ->whereBetween('received_date', [$period['from']->toDateString(), $period['to']->toDateString()])
            ->when($analystId, fn ($q) => $q->where('analyst_id', $analystId));
    }

    /* ------------------------------------------------------------------
     | KPIs
     | ------------------------------------------------------------------ */

    public function kpis(array $period, ?int $analystId = null): array
    {
        $base = $this->baseCaseQuery($period, $analystId);

        $query = DailyCase::query()->when($analystId, fn ($q) => $q->where('analyst_id', $analystId));

        return [
            'received_period' => $base->count(),
            'received_today' => (clone $query)->whereDate('received_date', today())->count(),
            'received_week' => (clone $query)
                ->whereDate('received_date', '>=', now()->startOfWeek())
                ->count(),
            'received_month' => (clone $query)
                ->whereDate('received_date', '>=', now()->startOfMonth())
                ->count(),
            'pending' => (clone $query)->pending()->count(),
            'in_process' => (clone $query)->inProcess()->count(),
            'finished_period' => (clone $query)
                ->whereBetween('received_date', [$period['from']->toDateString(), $period['to']->toDateString()])
                ->closed()
                ->count(),
            'overdue' => (clone $query)->overdue()->count(),
            'near_due' => (clone $query)->flag('yellow')->count(),
            'high_priority' => (clone $base)
                ->whereHas('priority', fn ($p) => $p->whereIn('slug', ['alta', 'critica']))
                ->count(),
            'total_cases' => DailyCase::query()->when($analystId, fn ($q) => $q->where('analyst_id', $analystId))->count(),
        ];
    }

    /* ------------------------------------------------------------------
     | Productividad
     | ------------------------------------------------------------------ */

    public function productivity(array $period, ?int $analystId = null): array
    {
        $dateFrom = $period['from']->toDateString();
        $dateTo = $period['to']->toDateString();

        $users = \App\Models\User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->withCount([
                'assignedCases as received_count' => fn ($q) => $q
                    ->whereBetween('received_date', [$dateFrom, $dateTo]),
                'assignedCases as finished_count' => fn ($q) => $q
                    ->whereBetween('received_date', [$dateFrom, $dateTo])
                    ->whereHas('status', fn ($s) => $s->where('is_closed', true)),
                'assignedCases as pending_count' => fn ($q) => $q->pending(),
            ])
            ->get();

        $closedByDay = DailyCase::query()
            ->when($analystId, fn ($q) => $q->where('analyst_id', $analystId))
            ->whereBetween('received_date', [$dateFrom, $dateTo])
            ->whereNotNull('finished_at')
            ->get(['finished_at'])
            ->groupBy(fn ($case) => Carbon::parse($case->finished_at)->toDateString())
            ->map(fn ($group) => $group->count());

        $avgTime = DailyCase::query()
            ->when($analystId, fn ($q) => $q->where('analyst_id', $analystId))
            ->whereBetween('received_date', [$dateFrom, $dateTo])
            ->closed()
            ->get(['received_date', 'received_time', 'finished_at'])
            ->map(fn ($case) => $case->totalTimeHours())
            ->filter()
            ->avg();

        return [
            'by_analyst' => $users->map(fn ($u) => [
                'user' => $u,
                'received' => $u->received_count,
                'finished' => $u->finished_count,
                'pending' => $u->pending_count,
                'avg_hours' => $this->analystHours($u->id, $dateFrom, $dateTo, 'avg'),
                'total_hours' => $this->analystHours($u->id, $dateFrom, $dateTo, 'sum'),
            ]),
            'avg_time_hours' => $avgTime ? round((float) $avgTime, 2) : null,
            'closed_by_day' => $closedByDay,
            'cases_by_status' => $this->countBy($this->periodBaseQuery($period, $analystId), 'status'),
            'cases_by_type' => $this->countBy($this->periodBaseQuery($period, $analystId), 'requestType'),
            'cases_by_application' => $this->countBy($this->periodBaseQuery($period, $analystId), 'application'),
            'cases_by_analyst' => $this->countBy($this->periodBaseQuery($period, $analystId), 'analyst'),
        ];
    }

    public function projectKpis(?int $analystId = null): array
    {
        $query = Project::query()->with('status');

        if ($analystId) {
            $query->where('responsible_id', $analystId);
        }

        $projects = $query->get();

        $active = $projects->filter(fn ($p) => $p->isActive());
        $finished = $projects->filter(fn ($p) => $p->isFinished());

        return [
            'active' => $active->count(),
            'finished' => $finished->count(),
            'pending' => $projects->filter(fn ($p) => $p->status?->slug === 'planeado')->count(),
            'near_due' => $projects->filter(fn ($p) => $p->isNearDue())->count(),
            'overdue' => $projects->filter(fn ($p) => $p->isOverdue())->count(),
            'avg_progress' => $active->isNotEmpty() ? round($active->avg('progress')) : 0,
        ];
    }

    private function countBy($query, string $relation): \Illuminate\Support\Collection
    {
        return (clone $query)
            ->with($relation)
            ->get()
            ->groupBy(fn ($case) => $case->{$relation}?->name ?? 'Sin asignar')
            ->map(fn ($group) => $group->count())
            ->filter()
            ->sortDesc();
    }

    private function periodBaseQuery(array $period, ?int $analystId)
    {
        return DailyCase::query()
            ->whereBetween('received_date', [$period['from']->toDateString(), $period['to']->toDateString()])
            ->when($analystId, fn ($q) => $q->where('analyst_id', $analystId));
    }

    private function analystHours(int $userId, string $from, string $to, string $mode): ?float
    {
        $hours = DailyCase::query()
            ->where('analyst_id', $userId)
            ->whereBetween('received_date', [$from, $to])
            ->closed()
            ->get()
            ->map(fn ($case) => $case->totalTimeHours())
            ->filter();

        if ($hours->isEmpty()) {
            return null;
        }

        return round((float) ($mode === 'sum' ? $hours->sum() : $hours->avg()), 2);
    }

    /* ------------------------------------------------------------------
     | Gráficas
     | ------------------------------------------------------------------ */

    public function charts(array $period, ?int $analystId = null): array
    {
        $dateFrom = $period['from']->toDateString();
        $dateTo = $period['to']->toDateString();

        // Gráfica 1: casos recibidos por día (últimos 30 días o rango del periodo)
        $days = [];
        $labels = [];
        $cursor = Carbon::parse($dateFrom)->copy();
        $end = Carbon::parse($dateTo)->copy();
        $received = DailyCase::query()
            ->when($analystId, fn ($q) => $q->where('analyst_id', $analystId))
            ->whereBetween('received_date', [$dateFrom, $dateTo])
            ->get(['received_date'])
            ->groupBy(fn ($case) => Carbon::parse($case->received_date)->toDateString())
            ->map(fn ($g) => $g->count());

        while ($cursor->lte($end)) {
            $labels[] = $cursor->format('d/m');
            $days[] = $received->get($cursor->toDateString(), 0);
            $cursor->addDay();
        }

        // Gráfica 7: avance porcentual de proyectos (activos)
        $activeProjects = Project::query()
            ->whereHas('status', fn ($s) => $s->whereNotIn('slug', ['cancelado']))
            ->orderBy('estimated_end_date')
            ->limit(12)
            ->get(['id', 'name', 'progress']);

        return [
            'received_labels' => $labels,
            'received_data' => $days,
            'projects_by_status' => Project::query()
                ->with('status')
                ->get()
                ->groupBy(fn ($p) => $p->status?->name ?? 'Sin estado')
                ->map(fn ($g) => $g->count())
                ->sortDesc(),
            'project_progress' => [
                'labels' => $activeProjects->pluck('name')->map(fn ($n) => \Illuminate\Support\Str::limit($n, 24)),
                'data' => $activeProjects->pluck('progress'),
            ],
            'closed_by_day' => $this->closedByDay($dateFrom, $dateTo, $analystId),
        ];
    }

    private function closedByDay(string $from, string $to, ?int $analystId): array
    {
        $days = [];
        $labels = [];
        $cursor = Carbon::parse($from)->copy();
        $end = Carbon::parse($to)->copy();

        $closed = DailyCase::query()
            ->when($analystId, fn ($q) => $q->where('analyst_id', $analystId))
            ->whereBetween('received_date', [$from, $to])
            ->whereNotNull('finished_at')
            ->get(['finished_at'])
            ->groupBy(fn ($case) => Carbon::parse($case->finished_at)->toDateString())
            ->map(fn ($g) => $g->count());

        while ($cursor->lte($end)) {
            $labels[] = $cursor->format('d/m');
            $days[] = $closed->get($cursor->toDateString(), 0);
            $cursor->addDay();
        }

        return ['labels' => $labels, 'data' => $days];
    }

    /* ------------------------------------------------------------------
     | Actividad reciente y sección personal
     | ------------------------------------------------------------------ */

    public function recentActivities(int $limit = 12): \Illuminate\Support\Collection
    {
        return Audit::query()
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($audit) => [
                'time' => $audit->created_at,
                'user' => $audit->user?->name ?? 'Sistema',
                'description' => $audit->description,
                'link' => $this->linkFor($audit),
            ]);
    }

    public function myCases(int $userId): array
    {
        $query = DailyCase::query()->forAnalyst($userId);

        return [
            'pending' => (clone $query)->pending()->count(),
            'in_analysis' => (clone $query)->whereHas('status', fn ($s) => $s->where('slug', 'en-analisis'))->count(),
            'in_management' => (clone $query)->whereHas('status', fn ($s) => $s->where('slug', 'en-gestion'))->count(),
            'overdue' => (clone $query)->overdue()->count(),
            'finished_recent' => (clone $query)->closed()->whereDate('finished_at', '>=', now()->subDays(7))->count(),
            'list' => (clone $query)->with(['status', 'priority'])->latest()->limit(8)->get(),
        ];
    }

    public function myProjects(int $userId): array
    {
        $query = Project::query()->where('responsible_id', $userId);

        return [
            'active' => (clone $query)->whereHas('status', fn ($s) => $s->whereNotIn('slug', ['finalizado', 'cancelado']))->count(),
            'pending_tasks' => (clone $query)->withCount(['tasks as pending' => fn ($t) => $t->whereHas('status', fn ($s) => $s->whereNotIn('slug', ['completada', 'cancelada']))])->get()->sum('pending'),
            'near_due' => (clone $query)->get()->filter(fn ($p) => $p->isNearDue())->count(),
            'list' => (clone $query)->with('status')->latest()->limit(5)->get(),
        ];
    }

    private function linkFor(Audit $audit): ?string
    {
        if ($audit->auditable_type === \App\Models\DailyCase::class) {
            return route('daily-cases.show', $audit->auditable_id);
        }
        if ($audit->auditable_type === \App\Models\Project::class) {
            return route('projects.show', $audit->auditable_id);
        }
        if ($audit->auditable_type === \App\Models\ProjectTask::class) {
            $task = \App\Models\ProjectTask::withTrashed()->find($audit->auditable_id);

            return $task ? route('projects.show', $task->project_id) : null;
        }

        return null;
    }
}