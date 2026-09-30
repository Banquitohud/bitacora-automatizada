<?php

namespace App\Services;

use App\Models\DailyCase;
use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Genera (o renueva) las alertas internas de un usuario:
     * casos próximos a vencer, casos vencidos, fechas de proyectos.
     */
    public function generateAlerts(?int $userId = null): void
    {
        $users = $userId
            ? User::where('id', $userId)->active()->get()
            : User::active()->get();

        foreach ($users as $user) {
            $this->markOldAlertsRead($user);

            $near = DailyCase::query()->forAnalyst($user->id)->flag('yellow')->count();
            $overdue = DailyCase::query()->forAnalyst($user->id)->flag('red')->count();
            $newAssigned = DailyCase::query()
                ->forAnalyst($user->id)
                ->where('taken_by', null)
                ->where('started_at', null)
                ->whereDate('created_at', today())
                ->count();

            if ($overdue > 0) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => "Tienes {$overdue} caso(s) vencido(s)",
                    'body' => 'Revisa tus casos con fecha límite vencida.',
                    'link' => route('daily-cases.index', ['flag' => 'red', 'analyst_id' => $user->id]),
                    'type' => 'danger',
                ]);
            }

            if ($near > 0) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => "Tienes {$near} caso(s) próximos a vencer",
                    'body' => 'Atiende antes de la fecha límite.',
                    'link' => route('daily-cases.index', ['flag' => 'yellow', 'analyst_id' => $user->id]),
                    'type' => 'warning',
                ]);
            }

            if ($newAssigned > 0 && $user->isAnalyst()) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => "Se te asignaron {$newAssigned} caso(s) nuevo(s)",
                    'body' => 'Revisa tu bandeja de flujo diario.',
                    'link' => route('daily-cases.index', ['analyst_id' => $user->id]),
                    'type' => 'info',
                ]);
            }
        }
    }

    protected function markOldAlertsRead(User $user): void
    {
        Notification::query()
            ->forUser($user->id)
            ->whereIn('type', ['danger', 'warning', 'info'])
            ->unread()
            ->update(['read_at' => now()]);
    }
}