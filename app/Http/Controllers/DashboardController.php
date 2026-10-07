<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\User;
use App\Services\MetricsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index(MetricsService $metrics)
    {
        $user = Auth::user();

        $events = Event::query()
            ->where('organizer_id', $user->id)
            ->orWhereHas('memberships', fn ($q) => $q->where('user_id', $user->id)->where('status', 'active'))
            ->with(['category', 'organizer'])
            ->orderByDesc('starts_at')
            ->paginate(8)
            ->withQueryString();

        $adminStats = null;

        if ($user->isAdmin()) {
            $adminStats = [
                'users' => User::count(),
                'events' => Event::count(),
                'published_events' => Event::where('status', 'published')->count(),
                'categories' => EventCategory::count(),
            ];
        }

        return view('dashboard', [
            'metrics' => $metrics->forUser($user),
            'events' => $events,
            'adminStats' => $adminStats,
        ]);
    }
}
