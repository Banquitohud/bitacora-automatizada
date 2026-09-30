<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyCase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'internal_id',
        'case_number',
        'received_date',
        'received_time',
        'due_date',
        'requester',
        'affected_user',
        'position',
        'request_type_id',
        'application_id',
        'profile_id',
        'permission',
        'group_id',
        'group_family_id',
        'priority_id',
        'status_id',
        'analyst_id',
        'started_at',
        'finished_at',
        'concept',
        'result',
        'observations',
        'comments',
        'created_by',
        'updated_by',
        'taken_by',
        'closed_by',
    ];

    protected function casts(): array
    {
        return [
            'received_date' => 'date',
            'received_time' => 'datetime',
            'due_date' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /* ------------------------------------------------------------------
     | Relaciones
     | ------------------------------------------------------------------ */

    public function status()
    {
        return $this->belongsTo(CaseStatus::class, 'status_id');
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class, 'priority_id');
    }

    public function requestType()
    {
        return $this->belongsTo(RequestType::class, 'request_type_id');
    }

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function groupFamily()
    {
        return $this->belongsTo(GroupFamily::class, 'group_family_id');
    }

    public function analyst()
    {
        return $this->belongsTo(User::class, 'analyst_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function takenBy()
    {
        return $this->belongsTo(User::class, 'taken_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'case_project', 'daily_case_id', 'project_id')
            ->withTimestamps();
    }

    /* ------------------------------------------------------------------
     | Scopes
     | ------------------------------------------------------------------ */

    public function scopeWithFilters($query, array $filters = [])
    {
        $query->when($filters['search'] ?? null, function ($q, $search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('case_number', 'like', "%{$search}%")
                    ->orWhere('internal_id', 'like', "%{$search}%")
                    ->orWhere('affected_user', 'like', "%{$search}%")
                    ->orWhere('requester', 'like', "%{$search}%")
                    ->orWhere('permission', 'like', "%{$search}%");
            });
        })
        ->when($filters['status_id'] ?? null, fn ($q, $v) => $q->where('status_id', $v))
        ->when($filters['request_type_id'] ?? null, fn ($q, $v) => $q->where('request_type_id', $v))
        ->when($filters['application_id'] ?? null, fn ($q, $v) => $q->where('application_id', $v))
        ->when($filters['priority_id'] ?? null, fn ($q, $v) => $q->where('priority_id', $v))
        ->when($filters['project_id'] ?? null, function ($q, $v) {
            $q->whereHas('projects', fn ($pj) => $pj->where('projects.id', $v));
        })
        ->when($filters['analyst_id'] ?? null, fn ($q, $v) => $q->where('analyst_id', $v))
        ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('received_date', '>=', $v))
        ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('received_date', '<=', $v))
        ->when($filters['flag'] ?? null, function ($q, $flag) {
            $this->applyFlagScope($q, $flag);
        });

        return $query;
    }

    public function scopeReceivedBetween($query, $start, $end)
    {
        return $query->whereBetween('received_date', [$start, $end]);
    }

    public function scopeNotClosed($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('status_id')
                ->orWhereDoesntHave('status', function ($s) {
                    $s->where('is_closed', true);
                });
        });
    }

    public function scopeClosed($query)
    {
        return $query->whereHas('status', fn ($s) => $s->where('is_closed', true));
    }

    public function scopeOverdue($query)
    {
        return $query->notClosed()->whereNotNull('due_date')->where('due_date', '<', now());
    }

    public function scopePending($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('status_id')
                ->orWhereHas('status', function ($s) {
                    $s->whereIn('slug', ['pendiente', 'recibido']);
                });
        });
    }

    public function scopeInProcess($query)
    {
        return $query->whereHas('status', function ($q) {
            $q->whereIn('slug', ['en-analisis', 'en-gestion', 'en-espera-de-informacion', 'escalado']);
        });
    }

    public function scopeForAnalyst($query, $userId)
    {
        return $query->where('analyst_id', $userId);
    }

    public function scopeFlag($query, $flag)
    {
        return $this->applyFlagScope($query, $flag);
    }

    private function applyFlagScope($query, $flag)
    {
        $nearDays = (int) config('gsi.near_due_days', 2);

        return match ($flag) {
            'green' => $query->notClosed()
                ->whereNotNull('due_date')
                ->where('due_date', '>', now()->addDays($nearDays)),
            'yellow' => $query->notClosed()
                ->whereNotNull('due_date')
                ->where('due_date', '>', now())
                ->where('due_date', '<=', now()->addDays($nearDays)),
            'red' => $query->overdue(),
            'white' => $query->notClosed()->whereNull('due_date'),
            'closed' => $query->closed(),
            default => $query,
        };
    }

    /* ------------------------------------------------------------------
     | Cálculo de tiempos
     | ------------------------------------------------------------------ */

    public function receivedAt(): ?Carbon
    {
        if (! $this->received_date) {
            return null;
        }
        $date = $this->received_date instanceof Carbon
            ? $this->received_date->copy()
            : Carbon::parse($this->received_date);

        if ($this->received_time) {
            $time = $this->received_time instanceof Carbon
                ? $this->received_time
                : Carbon::parse($this->received_time);
            $date->setTime((int) $time->format('H'), (int) $time->format('i'), (int) $time->format('s'));
        }

        return $date;
    }

    /** Tiempo desde la recepción hasta el inicio (en horas). */
    public function timeToStartHours(): ?float
    {
        $received = $this->receivedAt();
        if (! $received) {
            return null;
        }
        $start = $this->started_at ?: $this->finished_at;
        if (! $start) {
            return null;
        }
        return max(0, round($received->diffInMinutes(Carbon::parse($start)) / 60, 2));
    }

    /** Tiempo de gestión (inicio -> finalización) en horas. */
    public function handlingTimeHours(): ?float
    {
        if (! $this->started_at || ! $this->finished_at) {
            return null;
        }
        return max(0, round(Carbon::parse($this->started_at)->diffInMinutes(Carbon::parse($this->finished_at)) / 60, 2));
    }

    /** Tiempo total (recepción -> finalización) en horas. */
    public function totalTimeHours(): ?float
    {
        $received = $this->receivedAt();
        if (! $received || ! $this->finished_at) {
            return null;
        }
        return max(0, round($received->diffInMinutes(Carbon::parse($this->finished_at)) / 60, 2));
    }

    /** Días abiertos desde la recepción. */
    public function openDays(): int
    {
        $received = $this->receivedAt();
        if (! $received) {
            return 0;
        }
        return (int) $received->diffInDays(now());
    }

    /** Semáforo: closed, green, yellow, red o white. */
    public function flag(): string
    {
        if ($this->isClosed()) {
            return 'closed';
        }
        if (! $this->due_date) {
            return 'white';
        }

        $nearDays = (int) config('gsi.near_due_days', 2);
        $due = Carbon::parse($this->due_date);

        if ($due < now()) {
            return 'red';
        }
        if ($due->lte(now()->addDays($nearDays))) {
            return 'yellow';
        }
        return 'green';
    }

    public function isClosed(): bool
    {
        return $this->status && $this->status->is_closed;
    }

    public function isOverdue(): bool
    {
        return $this->due_date && Carbon::parse($this->due_date) < now() && ! $this->isClosed();
    }

    /** Fecha límite efectiva según SLA configurado si no existe due_date manual. */
    public function effectiveDueDate(): ?Carbon
    {
        if ($this->due_date) {
            return Carbon::parse($this->due_date);
        }

        $sla = SlaConfiguration::resolveFor($this->request_type_id, $this->priority_id);

        if (! $sla) {
            return null;
        }

        $base = $this->receivedAt();
        if (! $base) {
            return null;
        }

        return $sla->unit === 'hours'
            ? $base->copy()->addHours($sla->value)
            : $base->copy()->addDays($sla->value);
    }
}