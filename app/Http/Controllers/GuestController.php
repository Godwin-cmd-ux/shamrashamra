<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Guest;
use App\Services\AuditService;
use App\Services\GuestImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuestController extends Controller
{
    public function index(Request $request, Event $event)
    {
        $this->authorize('viewGuests', $event);

        $guests = $event->guests()
            ->withCount('invitations')
            ->with('latestInvitation')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.strtolower($request->string('q')).'%';
                $q->where(fn ($w) => $w->whereRaw('lower(name) like ?', [$term])
                    ->orWhereRaw("replace(replace(replace(coalesce(phone,''),'-',''),' ',''),'+','') like ?", ['%'.preg_replace('/\D+/', '', (string) $request->string('q')).'%']));
            })
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('guests.index', [
            'event' => $event,
            'guests' => $guests,
            'categories' => $event->guestCategories(),
        ]);
    }

    public function create(Event $event)
    {
        $this->authorize('manageGuests', $event);

        return view('guests.create', [
            'event' => $event,
            'categories' => $event->guestCategories(),
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $this->authorize('manageGuests', $event);

        $data = $this->validated($request);

        $guest = $event->guests()->create($data + ['created_by' => $request->user()->id]);

        AuditService::record($request->user(), $event, 'guest.created', $guest, [], $request->ip());

        return redirect()->route('events.guests.index', $event)->with('status', __('guests.created'));
    }

    public function edit(Event $event, Guest $guest)
    {
        $this->authorize('manageGuests', $event);

        abort_unless($guest->event_id === $event->id, 404);

        return view('guests.edit', [
            'event' => $event,
            'guest' => $guest,
            'categories' => $event->guestCategories(),
        ]);
    }

    public function update(Request $request, Event $event, Guest $guest)
    {
        $this->authorize('manageGuests', $event);

        abort_unless($guest->event_id === $event->id, 404);

        $guest->update($this->validated($request));

        AuditService::record($request->user(), $event, 'guest.updated', $guest, [], $request->ip());

        return redirect()->route('events.guests.index', $event)->with('status', __('guests.updated'));
    }

    public function destroy(Request $request, Event $event, Guest $guest)
    {
        $this->authorize('manageGuests', $event);

        abort_unless($guest->event_id === $event->id, 404);

        $guest->delete();

        AuditService::record($request->user(), $event, 'guest.deleted', null, [
            'guest_id' => $guest->id,
            'name' => $guest->name,
        ], $request->ip());

        return back()->with('status', __('guests.deleted'));
    }

    public function importForm(Event $event)
    {
        $this->authorize('manageGuests', $event);

        return view('guests.import', ['event' => $event]);
    }

    public function import(Request $request, Event $event, GuestImportService $importer)
    {
        $this->authorize('manageGuests', $event);

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $result = $importer->import($event, $request->file('csv_file'), $request->user());

        AuditService::record($request->user(), $event, 'guests.imported', null, [
            'imported' => $result['imported'],
            'duplicates' => $result['duplicates'],
            'invalid' => count($result['invalid']),
        ], $request->ip());

        if ($result['imported'] === 0 && count($result['invalid']) > 0) {
            return back()->withErrors(['csv_file' => __('guests.import.nothing_imported')])->with('importResult', $result);
        }

        return redirect()->route('events.guests.index', $event)->with('importResult', $result);
    }

    public function export(Event $event): StreamedResponse
    {
        $this->authorize('viewGuests', $event);

        $filename = 'guests-'.$event->public_id.'.csv';

        return response()->streamDownload(function () use ($event) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for spreadsheet compatibility
            fputcsv($out, ['name', 'phone', 'email', 'category', 'rsvp', 'invitation_status', 'checked_in', 'notes']);

            $event->guests()->with('latestInvitation')->orderBy('name')->chunk(200, function ($guests) use ($out) {
                foreach ($guests as $guest) {
                    $invitation = $guest->latestInvitation;

                    fputcsv($out, [
                        $this->csvSafe($guest->name),
                        $this->csvSafe((string) $guest->phone),
                        $this->csvSafe((string) $guest->email),
                        $this->csvSafe((string) $guest->category),
                        $invitation?->rsvp_status ?? '',
                        $invitation?->status ?? '',
                        $invitation && $invitation->attendanceRecord()->exists() ? 'yes' : 'no',
                        $this->csvSafe((string) $guest->notes),
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^\+?[0-9\s\-()]{7,20}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'category' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    /** Neutralize spreadsheet formula injection. */
    private function csvSafe(string $value): string
    {
        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
            return "'".$value;
        }

        return $value;
    }
}
