<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = Notification::query()
            ->forUser(auth()->id())
            ->latest()
            ->paginate(20);

        return view('notifications.index', ['notifications' => $notifications]);
    }

    public function markRead(Request $request, ?Notification $notification = null): RedirectResponse
    {
        if ($notification) {
            $notification->update(['read_at' => now()]);
        } else {
            Notification::query()
                ->forUser(auth()->id())
                ->unread()
                ->update(['read_at' => now()]);
        }

        return back();
    }

    public function destroy(Notification $notification): RedirectResponse
    {
        $notification->delete();

        return back()->with('success', 'Notificación eliminada.');
    }
}