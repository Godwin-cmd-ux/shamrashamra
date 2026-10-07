<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\CheckInService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CheckInController extends Controller
{
    /** Mobile-first scanner page (camera + manual fallback). */
    public function index(Event $event)
    {
        $this->authorize('checkIn', $event);

        $recent = $event->attendanceRecords()
            ->with(['invitation', 'attendant'])
            ->latest('checked_in_at')
            ->limit(10)
            ->get();

        $stats = [
            'checked_in' => $event->attendanceRecords()->count(),
            'guests' => (int) $event->attendanceRecords()->sum('guest_count'),
            'issued' => $event->invitations()->where('status', 'issued')->count(),
        ];

        return view('checkin.scanner', [
            'event' => $event,
            'recent' => $recent,
            'stats' => $stats,
        ]);
    }

    /**
     * Server-authoritative verification endpoint (camera scan or manual entry).
     * The server is the only authority for check-in decisions.
     */
    public function verify(Request $request, Event $event, CheckInService $checkIn)
    {
        $this->authorize('checkIn', $event);

        $data = $request->validate([
            'token' => ['required', 'string', 'max:500'],
            'method' => ['sometimes', Rule::in(['qr', 'manual'])],
        ]);

        $result = $checkIn->verify(
            $event,
            $data['token'],
            $request->user(),
            $data['method'] ?? 'qr',
            $request->ip(),
        );

        $invitation = $result['invitation'];
        $attendance = $result['attendance'];

        return response()->json([
            'status' => $result['outcome']->value,
            'message' => __($result['detail']),
            'label' => $invitation?->displayLabel(),
            'entitlement' => $invitation?->entitlement_count,
            'guest_count' => $attendance?->guest_count,
            'checked_in_at' => $attendance?->checked_in_at?->toIso8601String(),
            'checked_in_by' => $attendance?->attendant?->name,
        ]);
    }
}
