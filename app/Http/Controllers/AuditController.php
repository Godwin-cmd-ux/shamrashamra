<?php

namespace App\Http\Controllers;

use App\Models\Event;

class AuditController extends Controller
{
    public function index(Event $event)
    {
        $this->authorize('viewAudit', $event);

        $logs = $event->auditLogs()
            ->with('actor')
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('events.audit', [
            'event' => $event,
            'logs' => $logs,
        ]);
    }
}
