<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /** Report catalogue with per-report authorization. */
    public function index(Request $request, Event $event)
    {
        $this->authorize('viewReports', $event);

        $reports = $this->availableReports($event, $request->user());

        return view('reports.index', [
            'event' => $event,
            'reports' => $reports,
        ]);
    }

    public function export(Request $request, Event $event, string $type)
    {
        $this->authorize('view', $event);

        // Unknown report type is a missing resource (404);
        // a known type the user may not see is a permission problem (403).
        abort_unless(isset($this->catalogue()[$type]), 404);
        abort_unless(isset($this->availableReports($event, $request->user())[$type]), 403);

        return $this->stream($event, $type);
    }

    /** @return array<string, array{label: string, description: string, gate: string}> */
    private function catalogue(): array
    {
        return [
            'guests' => ['label' => __('reports.guests'), 'description' => __('reports.guests_desc'), 'gate' => 'viewGuests'],
            'rsvp' => ['label' => __('reports.rsvp'), 'description' => __('reports.rsvp_desc'), 'gate' => 'viewGuests'],
            'attendance' => ['label' => __('reports.attendance'), 'description' => __('reports.attendance_desc'), 'gate' => 'viewReports'],
            'pledges' => ['label' => __('reports.pledges'), 'description' => __('reports.pledges_desc'), 'gate' => 'viewFinance'],
            'contributions' => ['label' => __('reports.contributions'), 'description' => __('reports.contributions_desc'), 'gate' => 'viewFinance'],
            'expenses' => ['label' => __('reports.expenses'), 'description' => __('reports.expenses_desc'), 'gate' => 'viewFinance'],
            'budget' => ['label' => __('reports.budget'), 'description' => __('reports.budget_desc'), 'gate' => 'viewFinance'],
            'vendors' => ['label' => __('reports.vendors'), 'description' => __('reports.vendors_desc'), 'gate' => 'manageVendors'],
        ];
    }

    /** @return array<string, array{label: string, description: string}> */
    private function availableReports(Event $event, $user): array
    {
        $available = [];

        foreach ($this->catalogue() as $key => $report) {
            if (Gate::check($report['gate'], $event)) {
                $available[$key] = ['label' => $report['label'], 'description' => $report['description']];
            }
        }

        return $available;
    }

    private function stream(Event $event, string $type): StreamedResponse
    {
        $filename = $event->slugForFile().'-'.$type.'.csv';

        return response()->streamDownload(function () use ($event, $type) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            $rows = match ($type) {
                'guests' => $this->guestRows($event),
                'rsvp' => $this->rsvpRows($event),
                'attendance' => $this->attendanceRows($event),
                'pledges' => $this->pledgeRows($event),
                'contributions' => $this->contributionRows($event),
                'expenses' => $this->expenseRows($event),
                'budget' => $this->budgetRows($event),
                'vendors' => $this->vendorRows($event),
                default => [[__('reports.unknown_report')]],
            };

            foreach ($rows as $row) {
                fputcsv($out, array_map([$this, 'safe'], $row));
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return iterable<array<int, mixed>> */
    private function guestRows(Event $event): iterable
    {
        yield ['name', 'phone', 'email', 'category', 'notes'];

        foreach ($event->guests()->orderBy('name')->cursor() as $g) {
            yield [$g->name, $g->phone, $g->email, $g->category, $g->notes];
        }
    }

    /** @return iterable<array<int, mixed>> */
    private function rsvpRows(Event $event): iterable
    {
        yield ['invitation_label', 'guest_name', 'rsvp_status', 'guest_count', 'responded_at'];

        foreach ($event->invitations()->with('guest')->latest()->cursor() as $i) {
            yield [$i->displayLabel(), $i->guest?->name, $i->rsvp_status, $i->rsvp_guest_count, $i->rsvp_responded_at?->toDateTimeString()];
        }
    }

    /** @return iterable<array<int, mixed>> */
    private function attendanceRows(Event $event): iterable
    {
        yield ['invitation_label', 'guest_count', 'method', 'checked_in_at', 'attendant'];

        foreach ($event->attendanceRecords()->with(['invitation', 'attendant'])->orderBy('checked_in_at')->cursor() as $a) {
            yield [$a->invitation?->displayLabel(), $a->guest_count, $a->method, $a->checked_in_at->toDateTimeString(), $a->attendant?->name];
        }
    }

    /** @return iterable<array<int, mixed>> */
    private function pledgeRows(Event $event): iterable
    {
        yield ['contributor', 'contact', 'pledged_amount_minor', 'received_amount_minor', 'outstanding_amount_minor', 'status', 'due_date'];

        foreach ($event->pledges()->orderBy('created_at')->cursor() as $p) {
            yield [$p->contributor_name, $p->contributor_contact, $p->amount_minor, $p->receivedMinor(), $p->outstandingMinor(), $p->status, $p->due_date?->toDateString()];
        }
    }

    /** @return iterable<array<int, mixed>> */
    private function contributionRows(Event $event): iterable
    {
        yield ['received_at', 'amount_minor', 'currency', 'method', 'reference', 'pledge_contributor', 'reversed', 'recorded_by'];

        foreach ($event->contributions()->with(['pledge', 'recordedBy'])->orderBy('received_at')->cursor() as $c) {
            yield [$c->received_at->toDateString(), $c->amount_minor, $c->currency, $c->method, $c->reference, $c->pledge?->contributor_name, $c->reversed_at ? 'yes' : 'no', $c->recordedBy?->name];
        }
    }

    /** @return iterable<array<int, mixed>> */
    private function expenseRows(Event $event): iterable
    {
        yield ['incurred_at', 'description', 'amount_minor', 'currency', 'category', 'vendor', 'payment_status', 'approval_status', 'reversed'];

        foreach ($event->expenses()->with(['budgetCategory', 'vendor'])->orderBy('incurred_at')->cursor() as $e) {
            yield [$e->incurred_at->toDateString(), $e->description, $e->amount_minor, $e->currency, $e->budgetCategory?->name, $e->vendor?->name, $e->payment_status, $e->approval_status, $e->reversed_at ? 'yes' : 'no'];
        }
    }

    /** @return iterable<array<int, mixed>> */
    private function budgetRows(Event $event): iterable
    {
        yield ['category', 'planned_amount_minor', 'spent_amount_minor', 'variance_minor'];

        foreach ($event->budgetCategories()->orderBy('name')->cursor() as $b) {
            $spent = $b->spentMinor();
            yield [$b->name, $b->planned_amount_minor, $spent, $b->planned_amount_minor - $spent];
        }
    }

    /** @return iterable<array<int, mixed>> */
    private function vendorRows(Event $event): iterable
    {
        yield ['name', 'type', 'contact_name', 'phone', 'email', 'agreed_amount_minor', 'deposit_amount_minor', 'status'];

        foreach ($event->vendors()->orderBy('name')->cursor() as $v) {
            yield [$v->name, $v->type, $v->contact_name, $v->phone, $v->email, $v->agreed_amount_minor, $v->deposit_amount_minor, $v->status];
        }
    }

    private function safe(mixed $value): mixed
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
            return "'".$value;
        }

        return $value;
    }
}
