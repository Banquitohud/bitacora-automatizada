<?php

namespace Tests\Concerns;

use App\Models\Application;
use App\Models\CaseStatus;
use App\Models\Priority;
use App\Models\ProjectStatus;
use App\Models\RequestType;
use App\Models\TaskStatus;

trait SeedsCatalogs
{
    protected function seedCatalogs(): void
    {
        // Estados de caso
        $this->statusPendiente = CaseStatus::create([
            'name' => 'Pendiente', 'slug' => 'pendiente', 'color' => '#f59e0b',
            'sort_order' => 20, 'is_active' => true, 'is_initial' => false, 'is_closed' => false,
        ]);
        $this->statusEnAnalisis = CaseStatus::create([
            'name' => 'En análisis', 'slug' => 'en-analisis', 'color' => '#3b82f6',
            'sort_order' => 30, 'is_active' => true, 'is_initial' => false, 'is_closed' => false,
        ]);
        $this->statusFinalizado = CaseStatus::create([
            'name' => 'Finalizado', 'slug' => 'finalizado', 'color' => '#10b981',
            'sort_order' => 100, 'is_active' => true, 'is_initial' => false, 'is_closed' => true,
        ]);

        // Tipos / aplicaciones / prioridades
        $this->requestType = RequestType::create(['name' => 'Creación', 'slug' => 'creacion', 'sort_order' => 10, 'is_active' => true]);
        $this->application = Application::create(['name' => 'SAP', 'code' => 'SAP', 'sort_order' => 10, 'is_active' => true]);
        $this->priorityMedia = Priority::create(['name' => 'Media', 'slug' => 'media', 'color' => '#3b82f6', 'sort_order' => 20, 'is_active' => true]);
        $this->priorityAlta = Priority::create(['name' => 'Alta', 'slug' => 'alta', 'color' => '#f59e0b', 'sort_order' => 30, 'is_active' => true]);

        // Estados de proyecto / tarea
        $this->projectStatusEjecucion = ProjectStatus::create(['name' => 'En ejecución', 'slug' => 'en-ejecucion', 'color' => '#3b82f6', 'sort_order' => 20, 'is_active' => true]);
        $this->projectStatusFinalizado = ProjectStatus::create(['name' => 'Finalizado', 'slug' => 'finalizado', 'color' => '#10b981', 'sort_order' => 100, 'is_active' => true]);

        $this->taskStatusPendiente = TaskStatus::create(['name' => 'Pendiente', 'slug' => 'pendiente', 'color' => '#f59e0b', 'sort_order' => 10, 'is_active' => true]);
        $this->taskStatusCompletada = TaskStatus::create(['name' => 'Completada', 'slug' => 'completada', 'color' => '#10b981', 'sort_order' => 100, 'is_active' => true]);
    }
}