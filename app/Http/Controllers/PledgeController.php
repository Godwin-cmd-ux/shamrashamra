<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Pledge;
use App\Services\AuditService;
use App\Support\Money;
use Illuminate\Http\Request;

class PledgeController extends Controller
{
    public function index(Event $event)
    {
        $this->authorize('viewFinance', $event);

        $pledges = $event->pledges()
            ->withCount('contributions')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $totals = [
            'pledged' => (int) $event->pledges()->where('status', '!=', 'cancelled')->sum('amount_minor'),
            'received' => (int) $event->contributions()->whereNull('reversed_at')->sum('amount_minor'),
        ];

        return view('finance.pledges', [
            'event' => $event,
            'pledges' => $pledges,
            'totals' => $totals,
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $this->authorize('recordContributions', $event);

        $data = $request->validate([
            'contributor_name' => ['required', 'string', 'max:160'],
            'contributor_contact' => ['nullable', 'string', 'max:64'],
            'amount' => ['required', 'string', 'max:32'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $amountMinor = Money::parse($data['amount']);
        } catch (\InvalidArgumentException) {
            return back()->withErrors(['amount' => __('finance.amount_invalid')])->withInput();
        }

        if ($amountMinor <= 0) {
            return back()->withErrors(['amount' => __('finance.amount_positive')])->withInput();
        }

        $pledge = $event->pledges()->create([
            'contributor_name' => $data['contributor_name'],
            'contributor_contact' => $data['contributor_contact'] ?? null,
            'amount_minor' => $amountMinor,
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'recorded_by' => $request->user()->id,
        ]);

        AuditService::record($request->user(), $event, 'pledge.recorded', $pledge, [
            'amount_minor' => $amountMinor,
        ], $request->ip());

        return back()->with('status', __('finance.pledge_recorded'));
    }

    public function cancel(Request $request, Event $event, Pledge $pledge)
    {
        $this->authorize('recordContributions', $event);

        abort_unless($pledge->event_id === $event->id, 404);

        if ($pledge->receivedMinor() > 0) {
            return back()->withErrors(['pledge' => __('finance.pledge_has_payments')]);
        }

        $pledge->update(['status' => 'cancelled']);

        AuditService::record($request->user(), $event, 'pledge.cancelled', $pledge, [], $request->ip());

        return back()->with('status', __('finance.pledge_cancelled'));
    }
}
