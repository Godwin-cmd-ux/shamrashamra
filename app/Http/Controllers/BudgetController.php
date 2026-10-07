<?php

namespace App\Http\Controllers;

use App\Models\BudgetCategory;
use App\Models\Event;
use App\Services\AuditService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BudgetController extends Controller
{
    public function index(Event $event)
    {
        $this->authorize('viewFinance', $event);

        $categories = $event->budgetCategories()->orderBy('name')->get();

        return view('finance.budget', [
            'event' => $event,
            'categories' => $categories,
            'plannedMinor' => (int) $categories->sum('planned_amount_minor'),
            'spentMinor' => (int) $event->expenses()->whereNull('reversed_at')->sum('amount_minor'),
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $this->authorize('manageBudget', $event);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('budget_categories', 'name')->where('event_id', $event->id)],
            'planned_amount' => ['required', 'string', 'max:32'],
        ]);

        try {
            $planned = Money::parse($data['planned_amount']);
        } catch (\InvalidArgumentException) {
            return back()->withErrors(['planned_amount' => __('finance.amount_invalid')])->withInput();
        }

        if ($planned < 0) {
            return back()->withErrors(['planned_amount' => __('finance.amount_positive')])->withInput();
        }

        $category = $event->budgetCategories()->create([
            'name' => $data['name'],
            'planned_amount_minor' => $planned,
        ]);

        AuditService::record($request->user(), $event, 'budget.created', $category, [
            'planned_minor' => $planned,
        ], $request->ip());

        return back()->with('status', __('finance.budget_created'));
    }

    public function update(Request $request, Event $event, BudgetCategory $category)
    {
        $this->authorize('manageBudget', $event);

        abort_unless($category->event_id === $event->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'planned_amount' => ['required', 'string', 'max:32'],
        ]);

        try {
            $planned = Money::parse($data['planned_amount']);
        } catch (\InvalidArgumentException) {
            return back()->withErrors(['planned_amount' => __('finance.amount_invalid')])->withInput();
        }

        $category->update([
            'name' => $data['name'],
            'planned_amount_minor' => max(0, $planned),
        ]);

        AuditService::record($request->user(), $event, 'budget.updated', $category, [], $request->ip());

        return back()->with('status', __('finance.budget_updated'));
    }

    public function destroy(Request $request, Event $event, BudgetCategory $category)
    {
        $this->authorize('manageBudget', $event);

        abort_unless($category->event_id === $event->id, 404);

        if ($category->expenses()->exists()) {
            return back()->withErrors(['category' => __('finance.budget_has_expenses')]);
        }

        $category->delete();

        AuditService::record($request->user(), $event, 'budget.deleted', null, [
            'category_id' => $category->id,
            'name' => $category->name,
        ], $request->ip());

        return back()->with('status', __('finance.budget_deleted'));
    }
}
