<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventMember;
use App\Services\AuditService;
use App\Services\MetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $events = Event::query()
            ->where('organizer_id', $user->id)
            ->orWhereHas('memberships', fn ($q) => $q->where('user_id', $user->id)->where('status', 'active'))
            ->with(['category', 'organizer'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), fn ($q) => $q->whereRaw('lower(title) like ?', ['%'.strtolower($request->string('q')).'%']))
            ->orderByDesc('starts_at')
            ->paginate(10)
            ->withQueryString();

        return view('events.index', [
            'events' => $events,
            'statuses' => EventStatus::cases(),
        ]);
    }

    public function create()
    {
        return view('events.create', [
            'categories' => EventCategory::where('is_active', true)->orderBy('sort_order')->get(),
            'timezones' => ['Africa/Dar_es_Salaam', 'Africa/Nairobi', 'Africa/Kampala', 'Africa/Kigali', 'Europe/London', 'America/New_York'],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'category_id' => ['nullable', 'integer', Rule::exists('event_categories', 'id')->where('is_active', true)],
            'starts_at' => ['nullable', 'date'],
            'timezone' => ['required', 'string', 'max:64'],
            'venue_name' => ['nullable', 'string', 'max:160'],
            'venue_address' => ['nullable', 'string', 'max:255'],
            'map_link' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'host_names' => ['nullable', 'string', 'max:255'],
            'dress_code' => ['nullable', 'string', 'max:120'],
            'rsvp_deadline' => ['nullable', 'date'],
            'guest_capacity' => ['nullable', 'integer', 'min:1', 'max:1000000'],
        ]);

        $user = Auth::user();

        $event = DB::transaction(function () use ($data, $user) {
            $event = new Event($data);
            $event->organizer_id = $user->id;
            $event->status = EventStatus::Draft->value;
            $event->save();

            EventMember::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'status' => 'active',
            ]);

            return $event;
        });

        AuditService::record($user, $event, 'event.created', $event, [], $request->ip());

        return redirect()->route('events.show', $event)->with('status', __('events.created'));
    }

    public function show(Event $event, MetricsService $metrics)
    {
        $this->authorize('view', $event);

        return view('events.show', [
            'event' => $event->load(['category', 'organizer']),
            'metrics' => $metrics->forEvent($event),
            'recentAudit' => $event->auditLogs()->with('actor')->latest('created_at')->limit(8)->get(),
        ]);
    }

    public function edit(Event $event)
    {
        $this->authorize('update', $event);

        return view('events.edit', [
            'event' => $event,
            'categories' => EventCategory::where('is_active', true)->orderBy('sort_order')->get(),
            'timezones' => ['Africa/Dar_es_Salaam', 'Africa/Nairobi', 'Africa/Kampala', 'Africa/Kigali', 'Europe/London', 'America/New_York'],
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'category_id' => ['nullable', 'integer', Rule::exists('event_categories', 'id')->where('is_active', true)],
            'starts_at' => ['nullable', 'date'],
            'timezone' => ['required', 'string', 'max:64'],
            'venue_name' => ['nullable', 'string', 'max:160'],
            'venue_address' => ['nullable', 'string', 'max:255'],
            'map_link' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'host_names' => ['nullable', 'string', 'max:255'],
            'dress_code' => ['nullable', 'string', 'max:120'],
            'rsvp_deadline' => ['nullable', 'date'],
            'guest_capacity' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'require_expense_approval' => ['sometimes', 'boolean'],
            'guest_categories_text' => ['nullable', 'string', 'max:500'],
        ]);

        $settings = $event->settings ?? [];

        if (array_key_exists('require_expense_approval', $data)) {
            $settings['require_expense_approval'] = (bool) $data['require_expense_approval'];
            unset($data['require_expense_approval']);
        }

        if (array_key_exists('guest_categories_text', $data)) {
            $categories = array_values(array_filter(array_map('trim', explode(',', (string) $data['guest_categories_text']))));
            $settings['guest_categories'] = $categories ?: $event->guestCategories();
            unset($data['guest_categories_text']);
        }

        $data['settings'] = $settings;
        $event->fill($data)->save();

        AuditService::record($request->user(), $event, 'event.updated', $event, [], $request->ip());

        return redirect()->route('events.show', $event)->with('status', __('events.updated'));
    }

    public function updateStatus(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $data = $request->validate([
            'status' => ['required', Rule::in(EventStatus::values())],
        ]);

        $target = EventStatus::from($data['status']);

        try {
            $event->transitionTo($target);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => __('events.invalid_transition')]);
        }

        AuditService::record($request->user(), $event, 'event.status_changed', $event, [
            'status' => $target->value,
        ], $request->ip());

        return back()->with('status', __('events.status_changed'));
    }

    public function destroy(Request $request, Event $event)
    {
        $this->authorize('delete', $event);

        if ($event->status !== EventStatus::Draft->value || $event->invitations()->exists() || $event->guests()->exists()) {
            return back()->withErrors(['event' => __('events.delete_blocked')]);
        }

        AuditService::record($request->user(), null, 'event.deleted', null, [
            'event_id' => $event->id,
            'title' => $event->title,
        ], $request->ip());

        $event->delete();

        return redirect()->route('events.index')->with('status', __('events.deleted'));
    }
}
