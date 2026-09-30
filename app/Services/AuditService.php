<?php

namespace App\Services;

use App\Models\Audit;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    /**
     * Registra una entrada de auditoría y opcionalmente una notificación.
     */
    public function record(
        Model $model,
        string $action,
        ?string $description = null,
        array $oldValues = [],
        array $newValues = [],
        ?int $userId = null
    ): Audit {
        $audit = Audit::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
            'description' => $description,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
        ]);

        return $audit;
    }

    /**
     * Registra la creación de un modelo.
     */
    public function created(Model $model, string $label): Audit
    {
        $description = $this->actorName().' creó '.$label;
        $this->notify($model, $description, 'success');

        return $this->record($model, 'created', $description, [], $model->getAttributes());
    }

    /**
     * Registra una actualización señalando los campos que cambiaron.
     * Retorna la auditoria creada.
     */
    public function updated(Model $model, array $old, array $new): Audit
    {
        $changed = $this->changedFields($old, $new);

        $label = $this->labelFor($model);
        $detail = $changed->isEmpty() ? ' sin cambios de campos' : ': '.$changed->implode(', ');
        $description = $this->actorName().' modificó '.$label.$detail;

        return $this->record($model, 'updated', $description, $old, $new);
    }

    public function statusChanged(Model $model, string $label, mixed $oldStatus, mixed $newStatus): Audit
    {
        $description = $this->actorName().' cambió el estado de '.$label
            .' a "'.(is_string($newStatus) ? $newStatus : ($newStatus?->name ?? 'Sin estado')).'"';

        $this->notify($model, $description, 'info');

        return $this->record($model, 'status_changed', $description, ['status' => $oldStatus], ['status' => $newStatus]);
    }

    public function assigned(Model $model, string $label, ?User $user): Audit
    {
        $name = $user?->name ?? 'Sin asignación';
        $description = $this->actorName().' asignó '.$label.' a '.$name;

        $this->notify($model, $description, 'info', $user?->id);

        return $this->record($model, 'assigned', $description, [], ['assigned_to' => $user?->id]);
    }

    public function closed(Model $model, string $label): Audit
    {
        $description = $this->actorName().' finalizó '.$label;
        $this->notify($model, $description, 'success');

        return $this->record($model, 'closed', $description, [], ['finished_at' => now()]);
    }

    public function took(Model $model, string $label): Audit
    {
        $description = $this->actorName().' tomó '.$label;
        $this->notify($model, $description, 'info');

        return $this->record($model, 'took', $description, [], ['taken_by' => auth()->id()]);
    }

    public function deleted(Model $model, string $label): Audit
    {
        $description = $this->actorName().' eliminó '.$label;

        return $this->record($model, 'deleted', $description, $model->getAttributes(), []);
    }

    public function imported(Model $model, string $label, int $count): Audit
    {
        $description = $this->actorName().' importó '.$count.' registros de '.$label;

        return $this->record($model, 'imported', $description, [], ['count' => $count]);
    }

    /* ------------------------------------------------------------------
     | Helpers
     | ------------------------------------------------------------------ */

    protected function actorName(): string
    {
        return auth()->user()?->name ?? 'Sistema';
    }

    protected function labelFor(Model $model): string
    {
        return match (true) {
            $model instanceof \App\Models\DailyCase => 'el caso #'.($model->case_number ?: $model->id),
            $model instanceof \App\Models\Project => 'el proyecto "'.$model->name.'"',
            $model instanceof \App\Models\ProjectTask => 'la tarea "'.$model->name.'"',
            default => class_basename($model),
        };
    }

    protected function changedFields(array $old, array $new): \Illuminate\Support\Collection
    {
        $labels = [
            'status_id' => 'estado',
            'priority_id' => 'prioridad',
            'analyst_id' => 'analista asignado',
            'progress' => 'porcentaje de avance',
            'concept' => 'concepto GSI',
            'result' => 'resultado',
            'due_date' => 'fecha límite',
            'received_date' => 'fecha de recepción',
        ];

        $changed = collect($old)
            ->filter(fn ($value, $key) => array_key_exists($key, $new) && $value != $new[$key])
            ->keys()
            ->map(fn ($key) => $labels[$key] ?? $key);

        return $changed;
    }

    protected function notify(Model $model, string $message, string $type = 'info', ?int $userId = null): void
    {
        $link = match (true) {
            $model instanceof \App\Models\DailyCase => route('daily-cases.show', $model),
            $model instanceof \App\Models\Project => route('projects.show', $model),
            $model instanceof \App\Models\ProjectTask => route('projects.show', $model->project_id),
            default => null,
        };

        Notification::create([
            'user_id' => $userId,
            'title' => $message,
            'body' => null,
            'link' => $link,
            'type' => $type,
        ]);
    }
}