<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Expense;
use App\Services\AuditService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index(Event $event)
    {
        $this->authorize('viewFinance', $event);

        $expenses = $event->expenses()
            ->with(['budgetCategory', 'vendor', 'recordedBy'])
            ->latest('incurred_at')
            ->paginate(15)
            ->withQueryString();

        return view('finance.expenses', [
            'event' => $event,
            'expenses' => $expenses,
            'budgetCategories' => $event->budgetCategories()->orderBy('name')->get(),
            'vendors' => $event->vendors()->orderBy('name')->get(),
            'totalMinor' => (int) $event->expenses()->whereNull('reversed_at')->sum('amount_minor'),
            'requireApproval' => $event->requireExpenseApproval(),
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $this->authorize('recordExpenses', $event);

        $data = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'string', 'max:32'],
            'incurred_at' => ['required', 'date'],
            'payment_status' => ['required', Rule::in(['pending', 'paid'])],
            'budget_category_id' => ['nullable', 'integer', Rule::exists('budget_categories', 'id')->where('event_id', $event->id)],
            'vendor_id' => ['nullable', 'integer', Rule::exists('vendors', 'id')->where('event_id', $event->id)],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        try {
            $amountMinor = Money::parse($data['amount']);
        } catch (\InvalidArgumentException) {
            return back()->withErrors(['amount' => __('finance.amount_invalid')])->withInput();
        }

        if ($amountMinor <= 0) {
            return back()->withErrors(['amount' => __('finance.amount_positive')])->withInput();
        }

        $receiptPath = null;

        if ($request->hasFile('receipt')) {
            // Private disk: receipts are never web-exposed.
            $receiptPath = $request->file('receipt')->store(
                'events/'.$event->public_id.'/receipts',
                'local'
            );
        }

        $expense = $event->expenses()->create([
            'budget_category_id' => $data['budget_category_id'] ?? null,
            'vendor_id' => $data['vendor_id'] ?? null,
            'description' => $data['description'],
            'amount_minor' => $amountMinor,
            'incurred_at' => $data['incurred_at'],
            'payment_status' => $data['payment_status'],
            'approval_status' => $event->requireExpenseApproval() ? 'pending' : 'not_required',
            'receipt_path' => $receiptPath,
            'recorded_by' => $request->user()->id,
        ]);

        AuditService::record($request->user(), $event, 'expense.recorded', $expense, [
            'amount_minor' => $amountMinor,
            'approval_status' => $expense->approval_status,
        ], $request->ip());

        return back()->with('status', __('finance.expense_recorded'));
    }

    public function approve(Request $request, Event $event, Expense $expense)
    {
        $this->authorize('recordExpenses', $event);

        abort_unless($expense->event_id === $event->id, 404);

        if ($expense->approval_status !== 'pending') {
            return back()->withErrors(['expense' => __('finance.not_pending')]);
        }

        $expense->update([
            'approval_status' => 'approved',
            'approved_by' => $request->user()->id,
        ]);

        AuditService::record($request->user(), $event, 'expense.approved', $expense, [], $request->ip());

        return back()->with('status', __('finance.expense_approved'));
    }

    public function reverse(Request $request, Event $event, Expense $expense)
    {
        $this->authorize('recordExpenses', $event);

        abort_unless($expense->event_id === $event->id, 404);

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:300'],
        ]);

        if ($expense->isReversed() || $expense->isReversal()) {
            return back()->withErrors(['expense' => __('finance.already_reversed')]);
        }

        $reversal = $event->expenses()->create([
            'budget_category_id' => $expense->budget_category_id,
            'vendor_id' => $expense->vendor_id,
            'description' => $expense->description.' — '.__('finance.reversal_suffix'),
            'amount_minor' => -$expense->amount_minor,
            'currency' => $expense->currency,
            'incurred_at' => now()->toDateString(),
            'payment_status' => 'paid',
            'approval_status' => 'approved',
            'recorded_by' => $request->user()->id,
            'reverses_id' => $expense->id,
            'reversal_reason' => $data['reason'],
            // Close the correcting row too: a reversal pair never counts
            // toward totals, so whereNull('reversed_at') nets to zero.
            'reversed_at' => now(),
            'reversed_by' => $request->user()->id,
        ]);

        $expense->update([
            'reversed_at' => now(),
            'reversed_by' => $request->user()->id,
            'reversal_reason' => $data['reason'],
        ]);

        AuditService::record($request->user(), $event, 'expense.reversed', $expense, [
            'amount_minor' => $expense->amount_minor,
            'reversal_id' => $reversal->id,
            'reason' => $data['reason'],
        ], $request->ip());

        return back()->with('status', __('finance.reversed'));
    }
}
