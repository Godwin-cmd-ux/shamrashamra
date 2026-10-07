<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PlatformController extends Controller
{
    public function __construct()
    {
        Gate::define('manage-platform', fn (User $user) => $user->isAdmin());
    }

    public function index()
    {
        Gate::authorize('manage-platform');

        return view('admin.dashboard', [
            'stats' => [
                'users' => User::count(),
                'active_users' => User::where('status', 'active')->count(),
                'events' => Event::count(),
                'published' => Event::where('status', 'published')->count(),
                'drafts' => Event::where('status', 'draft')->count(),
                'archived' => Event::where('status', 'archived')->count(),
            ],
            'recentEvents' => Event::with('organizer')->latest()->limit(10)->get(),
            'categories' => EventCategory::orderBy('sort_order')->get(),
        ]);
    }

    public function storeCategory(Request $request)
    {
        Gate::authorize('manage-platform');

        $data = $request->validate([
            'name_en' => ['required', 'string', 'max:80'],
            'name_sw' => ['required', 'string', 'max:80'],
            'slug' => ['required', 'string', 'max:80', 'alpha_dash', 'unique:event_categories,slug'],
        ]);

        $category = EventCategory::create($data + ['is_active' => true]);

        AuditService::record($request->user(), null, 'admin.category_created', $category, [], $request->ip());

        return back()->with('status', __('admin.category_created'));
    }

    public function toggleCategory(Request $request, EventCategory $category)
    {
        Gate::authorize('manage-platform');

        $category->update(['is_active' => ! $category->is_active]);

        AuditService::record($request->user(), null, 'admin.category_toggled', $category, [
            'is_active' => $category->is_active,
        ], $request->ip());

        return back()->with('status', __('admin.category_updated'));
    }
}
