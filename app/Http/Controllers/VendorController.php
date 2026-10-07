<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Vendor;
use App\Services\AuditService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    public function index(Event $event)
    {
        $this->authorize('manageVendors', $event);

        return view('vendors.index', [
            'event' => $event,
            'vendors' => $event->vendors()->orderBy('name')->paginate(15),
            'types' => ['caterer', 'decorator', 'photographer', 'venue', 'entertainment', 'transport', 'printer', 'other'],
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $this->authorize('manageVendors', $event);

        $data = $this->validated($request);

        $vendor = $event->vendors()->create($data);

        AuditService::record($request->user(), $event, 'vendor.added', $vendor, [], $request->ip());

        return back()->with('status', __('vendors.added'));
    }

    public function update(Request $request, Event $event, Vendor $vendor)
    {
        $this->authorize('manageVendors', $event);

        abort_unless($vendor->event_id === $event->id, 404);

        $vendor->update($this->validated($request));

        AuditService::record($request->user(), $event, 'vendor.updated', $vendor, [], $request->ip());

        return back()->with('status', __('vendors.updated'));
    }

    public function destroy(Request $request, Event $event, Vendor $vendor)
    {
        $this->authorize('manageVendors', $event);

        abort_unless($vendor->event_id === $event->id, 404);

        if ($vendor->expenses()->exists()) {
            return back()->withErrors(['vendor' => __('vendors.delete_blocked')]);
        }

        $vendor->delete();

        AuditService::record($request->user(), $event, 'vendor.deleted', null, [
            'vendor_id' => $vendor->id,
            'name' => $vendor->name,
        ], $request->ip());

        return back()->with('status', __('vendors.deleted'));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'type' => ['required', Rule::in(['caterer', 'decorator', 'photographer', 'venue', 'entertainment', 'transport', 'printer', 'other'])],
            'contact_name' => ['nullable', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'services' => ['nullable', 'string', 'max:2000'],
            'agreed_amount' => ['nullable', 'string', 'max:32'],
            'deposit_amount' => ['nullable', 'string', 'max:32'],
            'status' => ['sometimes', Rule::in(['active', 'completed', 'cancelled'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['agreed_amount_minor'] = ($data['agreed_amount'] ?? '') !== '' ? max(0, Money::parse($data['agreed_amount'])) : 0;
        $data['deposit_amount_minor'] = ($data['deposit_amount'] ?? '') !== '' ? max(0, Money::parse($data['deposit_amount'])) : 0;

        unset($data['agreed_amount'], $data['deposit_amount']);

        return $data;
    }
}
