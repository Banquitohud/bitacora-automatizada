<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\CaseStatus;
use App\Models\Group;
use App\Models\GroupFamily;
use App\Models\Priority;
use App\Models\Profile;
use App\Models\ProjectStatus;
use App\Models\RequestType;
use App\Models\SlaConfiguration;
use App\Models\TaskStatus;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // ------- Estados de caso -------
        $statuses = [
            ['name' => 'Recibido', 'color' => '#6b7280', 'sort' => 10, 'initial' => true, 'closed' => false],
            ['name' => 'Pendiente', 'color' => '#f59e0b', 'sort' => 20, 'initial' => false, 'closed' => false],
            ['name' => 'En análisis', 'color' => '#3b82f6', 'sort' => 30, 'initial' => false, 'closed' => false],
            ['name' => 'En gestión', 'color' => '#8b5cf6', 'sort' => 40, 'initial' => false, 'closed' => false],
            ['name' => 'En espera de información', 'color' => '#06b6d4', 'sort' => 50, 'initial' => false, 'closed' => false],
            ['name' => 'Escalado', 'color' => '#ef4444', 'sort' => 60, 'initial' => false, 'closed' => false],
            ['name' => 'Finalizado', 'color' => '#10b981', 'sort' => 100, 'initial' => false, 'closed' => true],
            ['name' => 'No viable', 'color' => '#9ca3af', 'sort' => 110, 'initial' => false, 'closed' => true],
            ['name' => 'Cancelado', 'color' => '#6b7280', 'sort' => 120, 'initial' => false, 'closed' => true],
        ];

        foreach ($statuses as $s) {
            CaseStatus::firstOrCreate(
                ['slug' => str($s['name'])->slug()],
                [
                    'name' => $s['name'],
                    'color' => $s['color'],
                    'sort_order' => $s['sort'],
                    'is_active' => true,
                    'is_initial' => $s['initial'],
                    'is_closed' => $s['closed'],
                ]
            );
        }

        // ------- Tipos de solicitud -------
        foreach (['Creación', 'Modificación', 'Retiro', 'Acceso', 'Consulta', 'Masivo', 'Otro'] as $i => $name) {
            RequestType::firstOrCreate(
                ['slug' => str($name)->slug()],
                ['name' => $name, 'sort_order' => ($i + 1) * 10, 'is_active' => true]
            );
        }

        // ------- Prioridades -------
        $priorities = [
            ['name' => 'Baja', 'color' => '#6b7280'],
            ['name' => 'Media', 'color' => '#3b82f6'],
            ['name' => 'Alta', 'color' => '#f59e0b'],
            ['name' => 'Crítica', 'color' => '#ef4444'],
        ];

        foreach ($priorities as $i => $p) {
            Priority::firstOrCreate(
                ['slug' => str($p['name'])->slug()],
                ['name' => $p['name'], 'color' => $p['color'], 'sort_order' => ($i + 1) * 10, 'is_active' => true]
            );
        }

        // ------- Aplicaciones -------
        $apps = [
            ['name' => 'Gestor Documental', 'code' => 'GD'],
            ['name' => 'SAP', 'code' => 'SAP'],
            ['name' => 'Active Directory', 'code' => 'AD'],
            ['name' => 'Correo Corporativo', 'code' => 'MAIL'],
            ['name' => 'Intranet', 'code' => 'INET'],
            ['name' => 'Sistema de Nómina', 'code' => 'NOM'],
            ['name' => 'CRM', 'code' => 'CRM'],
        ];

        foreach ($apps as $i => $app) {
            Application::firstOrCreate(
                ['name' => $app['name']],
                ['code' => $app['code'], 'sort_order' => ($i + 1) * 10, 'is_active' => true]
            );
        }

        // ------- Familias de grupos / grupos / perfiles -------
        $familyNames = ['Grupos de seguridad', 'Roles de aplicación', 'Grupos de correo'];
        $familyIds = [];

        foreach ($familyNames as $name) {
            $familyIds[$name] = GroupFamily::firstOrCreate(['name' => $name])->id;
        }

        $groups = [
            ['name' => 'GA_GestorDocumental_Lectura', 'family' => 'Grupos de seguridad'],
            ['name' => 'GA_GestorDocumental_Edicion', 'family' => 'Grupos de seguridad'],
            ['name' => 'GA_SAP_Financiero', 'family' => 'Grupos de seguridad'],
            ['name' => 'ROL_Admin_Nómina', 'family' => 'Roles de aplicación'],
            ['name' => 'ROL_Consulta_CRM', 'family' => 'Roles de aplicación'],
            ['name' => 'Correo_Todos_GSI', 'family' => 'Grupos de correo'],
        ];

        foreach ($groups as $g) {
            Group::firstOrCreate(
                ['name' => $g['name']],
                ['group_family_id' => $familyIds[$g['family']] ?? null]
            );
        }

        foreach (['Usuario Básico', 'Supervisor', 'Administrador funcional', 'Administrador técnico'] as $name) {
            Profile::firstOrCreate(['name' => $name]);
        }

        // ------- Estados de proyecto -------
        $projectStatuses = [
            ['name' => 'Planeado', 'color' => '#6b7280', 'sort' => 10],
            ['name' => 'En ejecución', 'color' => '#3b82f6', 'sort' => 20],
            ['name' => 'En pausa', 'color' => '#f59e0b', 'sort' => 30],
            ['name' => 'En revisión', 'color' => '#8b5cf6', 'sort' => 40],
            ['name' => 'Finalizado', 'color' => '#10b981', 'sort' => 100],
            ['name' => 'Cancelado', 'color' => '#9ca3af', 'sort' => 110],
        ];

        foreach ($projectStatuses as $ps) {
            ProjectStatus::firstOrCreate(
                ['slug' => str($ps['name'])->slug()],
                ['name' => $ps['name'], 'color' => $ps['color'], 'sort_order' => $ps['sort'], 'is_active' => true]
            );
        }

        // ------- Estados de tarea -------
        $taskStatuses = [
            ['name' => 'Pendiente', 'color' => '#f59e0b', 'sort' => 10],
            ['name' => 'En progreso', 'color' => '#3b82f6', 'sort' => 20],
            ['name' => 'En pausa', 'color' => '#9ca3af', 'sort' => 30],
            ['name' => 'En revisión', 'color' => '#8b5cf6', 'sort' => 40],
            ['name' => 'Completada', 'color' => '#10b981', 'sort' => 100],
            ['name' => 'Cancelada', 'color' => '#6b7280', 'sort' => 110],
        ];

        foreach ($taskStatuses as $ts) {
            TaskStatus::firstOrCreate(
                ['slug' => str($ts['name'])->slug()],
                ['name' => $ts['name'], 'color' => $ts['color'], 'sort_order' => $ts['sort'], 'is_active' => true]
            );
        }

        // ------- SLA por defecto (configurable) -------
        if (! SlaConfiguration::where('applies_to', 'global')->exists()) {
            SlaConfiguration::create([
                'name' => 'SLA general',
                'unit' => 'days',
                'value' => 3,
                'applies_to' => 'global',
                'is_active' => true,
            ]);
        }
    }
}