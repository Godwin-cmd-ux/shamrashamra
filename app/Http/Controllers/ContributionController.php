<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\Event;
use App\Services\AuditService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContributionController extends Controller
{
    public function index(Event $event)
    {
        $this->authorize('viewFinance', $event);

        $contributions = $event->contributions()
            ->with(['pledge', 'recordedBy'])
            ->latest('received_at')
            ->paginate(15)
            ->withQueryString();

        return view('finance.contributions', [
            'event' => $event,
            'contributions' => $contributions,
            'pledges' => $event->pledges()->where('status', '!=', 'cancelled')->orderBy('contributor_name')->get(),
            'totalMinor' => (int) $event->contributions()->whereNull('reversed_at')->sum('amount_minor'),
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $this->authorize('recordContributions', $event);

        $data = $request->validate([
            'amount' => ['required', 'string', 'max:32'],
            'received_at' => ['required', 'date'],
            'method' => ['required', Rule::in(['cash', 'momo', 'bank', 'other'])],
            'reference' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:500'],
            'pledge_id' => ['nullable', 'integer', Rule::exists('pledges', 'id')->where('event_id', $event->id)],
        ]);

        try {
            $amountMinor = Money::parse($data['amount']);
        } catch (\InvalidArgumentException) {
            return back()->withErrors(['amount' => __('finance.amount_invalid')])->withInput();
        }

        if ($amountMinor <= 0) {
            return back()->withErrors(['amount' => __('finance.amount_positive')])->withInput();
        }

        $contribution = $event->contributions()->create([
            'pledge_id' => $data['pledge_id'] ?? null,
            'amount_minor' => $amountMinor,
            'received_at' => $data['received_at'],
            'method' => $data['method'],
            'reference' => $data['reference'] ?? null,
            'note' => $data['note'] ?? null,
            'recorded_by' => $request->user()->id,
        ]);

        if ($contribution->pledge_id) {
            $contribution->pledge?->refreshStatus();
        }

        AuditService::record($request->user(), $event, 'contribution.recorded', $contribution, [
            'amount_minor' => $amountMinor,
            'method' => $data['method'],
        ], $request->ip());

        return back()->with('status', __('finance.contribution_recorded'));
    }

    /** Corrections are reversals — original rows are never deleted or overwritten. */
    public function reverse(Request $request, Event $event, Contribution $contribution)
    {
        $this->authorize('recordContributions', $event);

        abort_unless($contribution->event_id === $event->id, 404);

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:300'],
        ]);

        if ($contribution->isReversed() || $contribution->isReversal()) {
            return back()->withErrors(['contribution' => __('finance.already_reversed')]);
        }

        $originalAmount = $contribution->amount_minor;

        $reversal = $event->contributions()->create([
            'pledge_id' => $contribution->pledge_id,
            'amount_minor' => -$originalAmount,
            'currency' => $contribution->currency,
            'received_at' => now()->toDateString(),
            'method' => $contribution->method,
            'reference' => $contribution->reference,
            'note' => __('finance.reversal_note'),
            'recorded_by' => $request->user()->id,
            'reverses_id' => $contribution->id,
            'reversal_reason' => $data['reason'],
            // Close the correcting row too: a reversal pair never counts
            // toward totals, so whereNull('reversed_at') nets to zero.
            'reversed_at' => now(),
            'reversed_by' => $request->user()->id,
        ]);

        $contribution->update([
            'reversed_at' => now(),
            'reversed_by' => $request->user()->id,
            'reversal_reason' => $data['reason'],
        ]);

        $contribution->pledge?->refreshStatus();

        AuditService::record($request->user(), $event, 'contribution.reversed', $contribution, [
            'amount_minor' => $originalAmount,
            'reversal_id' => $reversal->id,
            'reason' => $data['reason'],
        ], $request->ip());

        return back()->with('status', __('finance.reversed'));
    }
}
