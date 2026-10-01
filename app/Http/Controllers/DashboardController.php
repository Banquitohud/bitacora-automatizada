<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DashboardService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $dashboard, NotificationService $notifications): View
    {
        $period = $request->input('period', 'month');
        $analystId = $request->input('analyst_id');

        // Los analistas solo pueden ver su propia información en el dashboard.
        if (! auth()->user()->isAdmin()) {
            $analystId = auth()->id();
        }

        $analystId = $analystId ? (int) $analystId : null;

        $periodRange = $dashboard->resolvePeriod($period, $request->input('from'), $request->input('to'));

        $kpis = $dashboard->kpis($periodRange, $analystId);
        $projectKpis = $dashboard->projectKpis($analystId);
        $productivity = $dashboard->productivity($periodRange, $analystId);
        $charts = $dashboard->charts($periodRange, $analystId);

        $notifications->generateAlerts(auth()->id());

        return view('dashboard.index', [
            'period' => $period,
            'periodRange' => $periodRange,
            'analystId' => $analystId,
            'analysts' => User::query()->where('is_active', true)->orderBy('name')->get(),
            'kpis' => $kpis,
            'projectKpis' => $projectKpis,
            'productivity' => $productivity,
            'charts' => $charts,
            'recentActivities' => $dashboard->recentActivities(12),
            'myCases' => $dashboard->myCases(auth()->id()),
            'myProjects' => $dashboard->myProjects(auth()->id()),
            'unreadNotifications' => auth()->user()->notifications()->unread()->count(),
        ]);
    }
}