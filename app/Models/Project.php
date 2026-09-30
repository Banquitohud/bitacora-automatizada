<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'start_date',
        'estimated_end_date',
        'actual_end_date',
        'responsible_id',
        'project_status_id',
        'priority_id',
        'progress',
        'observations',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'estimated_end_date' => 'date',
            'actual_end_date' => 'date',
            'progress' => 'integer',
        ];
    }

    public function status()
    {
        return $this->belongsTo(ProjectStatus::class, 'project_status_id');
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class, 'priority_id');
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class)->orderBy('due_date')->orderBy('name');
    }

    public function cases()
    {
        return $this->belongsToMany(DailyCase::class, 'case_project', 'project_id', 'daily_case_id')
            ->withTimestamps();
    }

    public function isFinished(): bool
    {
        return $this->status && in_array($this->status->slug, ['finalizado', 'cancelado']);
    }

    public function isActive(): bool
    {
        return ! $this->isFinished();
    }

    public function isOverdue(): bool
    {
        return $this->estimated_end_date
            && Carbon::parse($this->estimated_end_date)->lt(now())
            && ! $this->isFinished();
    }

    public function isNearDue(): bool
    {
        if ($this->isFinished() || ! $this->estimated_end_date) {
            return false;
        }
        $nearDays = (int) config('gsi.near_due_days', 2);

        return Carbon::parse($this->estimated_end_date)->between(now(), now()->addDays($nearDays));
    }

    public function hasOverdueTasks(): bool
    {
        return $this->tasks()->whereNull('completed_date')->where('due_date', '<', now())->exists();
    }

    /** Promedio de avance de las tareas (0-100). */
    public function computedProgress(): int
    {
        $tasks = $this->tasks()->get(['progress']);

        if ($tasks->isEmpty()) {
            return $this->progress;
        }

        return (int) round($tasks->avg('progress'));
    }

    public function syncProgress(): void
    {
        $this->forceFill(['progress' => $this->computedProgress()])->saveQuietly();
    }
}