<?php

namespace Tests\Feature;

use App\Models\BudgetCategory;
use App\Models\Event;
use App\Models\EventMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialTest extends TestCase
{
    use RefreshDatabase;

    private User $organizer;
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizer = User::factory()->create();
        $this->event = Event::factory()->create(['organizer_id' => $this->organizer->id]);

        EventMember::create([
            'event_id' => $this->event->id,
            'user_id' => $this->organizer->id,
            'role' => 'owner',
            'status' => 'active',
        ]);
    }

    private function base(): string
    {
        return "/events/{$this->event->public_id}/finance";
    }

    public function test_pledge_recorded_and_contribution_updates_status(): void
    {
        $this->actingAs($this->organizer)->post($this->base().'/pledges', [
            'contributor_name' => 'Juma Kimaro',
            'amount' => '500,000',
            'due_date' => now()->addWeek()->toDateString(),
        ])->assertRedirect();

        $pledge = $this->event->pledges()->first();
        $this->assertSame(50000000, $pledge->amount_minor, 'amount stored in minor units');
        $this->assertSame('open', $pledge->status);

        // Half payment
        $this->actingAs($this->organizer)->post($this->base().'/contributions', [
            'amount' => '200000',
            'received_at' => now()->toDateString(),
            'method' => 'momo',
            'pledge_id' => $pledge->id,
        ])->assertRedirect();

        $pledge->refresh();
        $this->assertSame('partially_paid', $pledge->status);
        $this->assertSame(20000000, $pledge->receivedMinor());
        $this->assertSame(30000000, $pledge->outstandingMinor());

        // Settle
        $this->actingAs($this->organizer)->post($this->base().'/contributions', [
            'amount' => '300000',
            'received_at' => now()->toDateString(),
            'method' => 'bank',
            'pledge_id' => $pledge->id,
        ]);

        $this->assertSame('settled', $pledge->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'contribution.recorded']);
    }

    public function test_reversal_adjusts_totals_without_deleting_original(): void
    {
        $this->actingAs($this->organizer)->post($this->base().'/contributions', [
            'amount' => '100000',
            'received_at' => now()->toDateString(),
            'method' => 'cash',
        ]);

        $contribution = $this->event->contributions()->first();
        $this->assertSame(10000000, (int) $this->event->contributions()->whereNull('reversed_at')->sum('amount_minor'));

        $this->actingAs($this->organizer)
            ->post($this->base()."/contributions/{$contribution->id}/reverse", ['reason' => 'Wrong amount'])
            ->assertRedirect();

        // Original row still exists, marked reversed; net totals drop to zero.
        $this->assertNotNull($contribution->fresh());
        $this->assertNotNull($contribution->fresh()->reversed_at);
        $this->assertSame(0, (int) $this->event->contributions()->whereNull('reversed_at')->sum('amount_minor'));
        $this->assertSame(2, $this->event->contributions()->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'contribution.reversed']);

        // Cannot reverse twice.
        $this->actingAs($this->organizer)
            ->post($this->base()."/contributions/{$contribution->id}/reverse", ['reason' => 'Again'])
            ->assertSessionHasErrors('contribution');
    }

    public function test_expense_reversal_uses_negative_entry_and_budget_delete_is_blocked(): void
    {
        $this->actingAs($this->organizer)->post($this->base().'/budget', [
            'name' => 'Catering',
            'planned_amount' => '1000000',
        ])->assertRedirect();

        $category = $this->event->budgetCategories()->first();
        $this->assertSame(100000000, $category->planned_amount_minor);

        $this->actingAs($this->organizer)->post($this->base().'/expenses', [
            'description' => 'Food for 100',
            'amount' => '250000',
            'incurred_at' => now()->toDateString(),
            'payment_status' => 'paid',
            'budget_category_id' => $category->id,
        ])->assertRedirect();

        $expense = $this->event->expenses()->first();
        $this->assertSame(25000000, $expense->amount_minor);
        $this->assertSame(25000000, (int) $this->event->expenses()->whereNull('reversed_at')->sum('amount_minor'));

        // Budget line with expenses cannot be deleted.
        $this->actingAs($this->organizer)
            ->delete($this->base()."/budget/{$category->id}")
            ->assertSessionHasErrors('category');
        $this->assertNotNull(BudgetCategory::find($category->id));

        // Reverse the expense.
        $this->actingAs($this->organizer)
            ->post($this->base()."/expenses/{$expense->id}/reverse", ['reason' => 'Double entry'])
            ->assertRedirect();

        $this->assertSame(0, (int) $this->event->expenses()->whereNull('reversed_at')->sum('amount_minor'));
        $this->assertSame(2, $this->event->expenses()->count());
    }

    public function test_negative_or_invalid_amounts_rejected(): void
    {
        $this->actingAs($this->organizer)->post($this->base().'/contributions', [
            'amount' => 'abc',
            'received_at' => now()->toDateString(),
            'method' => 'cash',
        ])->assertSessionHasErrors('amount');

        $this->actingAs($this->organizer)->post($this->base().'/contributions', [
            'amount' => '-5000',
            'received_at' => now()->toDateString(),
            'method' => 'cash',
        ])->assertSessionHasErrors('amount');
    }

    public function test_non_member_cannot_access_finance(): void
    {
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->get($this->base().'/pledges')->assertForbidden();
        $this->actingAs($stranger)->post($this->base().'/contributions', [
            'amount' => '1000',
            'received_at' => now()->toDateString(),
            'method' => 'cash',
        ])->assertForbidden();
        $this->assertSame(0, $this->event->contributions()->count());
    }

    public function test_dashboard_metrics_keep_pledges_and_contributions_separate(): void
    {
        $this->actingAs($this->organizer)->post($this->base().'/pledges', [
            'contributor_name' => 'Pledger',
            'amount' => '400000',
        ]);
        $this->actingAs($this->organizer)->post($this->base().'/contributions', [
            'amount' => '150000',
            'received_at' => now()->toDateString(),
            'method' => 'cash',
        ]);

        $metrics = app(\App\Services\MetricsService::class)->forUser($this->organizer);

        $this->assertSame(40000000, $metrics['total_pledged_minor']);
        $this->assertSame(15000000, $metrics['total_contributed_minor']);
        $this->assertNotSame($metrics['total_pledged_minor'], $metrics['total_contributed_minor']);
    }
}
