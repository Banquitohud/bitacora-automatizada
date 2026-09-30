<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DailyCaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectTaskController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('daily-cases', DailyCaseController::class)->except(['index', 'show'])->names([
        'create' => 'daily-cases.create',
        'store' => 'daily-cases.store',
        'edit' => 'daily-cases.edit',
        'update' => 'daily-cases.update',
        'destroy' => 'daily-cases.destroy',
    ]);
    Route::get('/daily-cases', [DailyCaseController::class, 'index'])->name('daily-cases.index');
    Route::get('/daily-cases/{daily_case}', [DailyCaseController::class, 'show'])->name('daily-cases.show');
    Route::post('/daily-cases/{daily_case}/take', [DailyCaseController::class, 'take'])->name('daily-cases.take');
    Route::post('/daily-cases/{daily_case}/close', [DailyCaseController::class, 'close'])->name('daily-cases.close');

    Route::resource('projects', ProjectController::class)->names([
        'index' => 'projects.index',
        'create' => 'projects.create',
        'store' => 'projects.store',
        'show' => 'projects.show',
        'edit' => 'projects.edit',
        'update' => 'projects.update',
        'destroy' => 'projects.destroy',
    ]);

    Route::post('/projects/{project}/tasks', [ProjectTaskController::class, 'store'])->name('project-tasks.store');
    Route::put('/projects/{project}/tasks/{task}', [ProjectTaskController::class, 'update'])->name('project-tasks.update');
    Route::delete('/projects/{project}/tasks/{task}', [ProjectTaskController::class, 'destroy'])->name('project-tasks.destroy');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('/reports/export/{format}', [ExportController::class, 'export'])->name('reports.export');
    Route::get('/reports/monthly/pdf', [ExportController::class, 'monthlyPdf'])->name('reports.monthly.pdf');

    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import/preview', [ImportController::class, 'preview'])->name('import.preview');
    Route::post('/import/store', [ImportController::class, 'store'])->name('import.store');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read/{notification}', [NotificationController::class, 'markRead'])->name('notifications.read.one');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/{catalog}', [SettingController::class, 'store'])->name('settings.store');
    Route::put('/settings/{catalog}/{id}', [SettingController::class, 'update'])->name('settings.update');
    Route::delete('/settings/{catalog}/{id}', [SettingController::class, 'destroy'])->name('settings.destroy');
});
