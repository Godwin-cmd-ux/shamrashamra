<?php

namespace App\Http\Controllers;

use App\Enums\EventPermission;
use App\Enums\MemberRole;
use App\Models\Event;
use App\Models\EventMember;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EventMemberController extends Controller
{
    public function index(Event $event)
    {
        $this->authorize('manageMembers', $event);

        $members = $event->memberships()
            ->with(['user', 'permissions'])
            ->orderBy('role')
            ->get();

        return view('events.members', [
            'event' => $event,
            'members' => $members,
            'permissions' => EventPermission::grantable(),
            'roles' => [MemberRole::Committee, MemberRole::Attendant],
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $this->authorize('manageMembers', $event);

        $data = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', Rule::in([MemberRole::Committee->value, MemberRole::Attendant->value])],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => [Rule::in(EventPermission::grantable())],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return back()->withErrors(['email' => __('events.member_must_register')]);
        }

        if ($user->id === $event->organizer_id) {
            return back()->withErrors(['email' => __('events.member_is_owner')]);
        }

        $existing = EventMember::where('event_id', $event->id)->where('user_id', $user->id)->first();

        if ($existing && $existing->status !== 'revoked') {
            return back()->withErrors(['email' => __('events.member_exists')]);
        }

        $permissions = $data['role'] === MemberRole::Attendant->value
            ? [EventPermission::CheckinUse->value]
            : ($data['permissions'] ?? []);

        DB::transaction(function () use ($event, $user, $data, $permissions, $request, $existing) {
            $member = $existing ?: new EventMember([
                'event_id' => $event->id,
                'user_id' => $user->id,
            ]);

            $member->fill([
                'role' => $data['role'],
                'status' => 'active',
                'invited_by' => $request->user()->id,
            ])->save();

            $member->permissions()->delete();

            foreach ($permissions as $permission) {
                $member->permissions()->create(['permission' => $permission]);
            }

            AuditService::record($request->user(), $event, 'member.added', $member, [
                'user_id' => $user->id,
                'role' => $data['role'],
                'permissions' => $permissions,
            ], $request->ip());
        });

        return back()->with('status', __('events.member_added'));
    }

    public function update(Request $request, Event $event, EventMember $member)
    {
        $this->authorize('manageMembers', $event);

        abort_unless($member->event_id === $event->id, 404);

        if ($member->user_id === $event->organizer_id) {
            return back()->withErrors(['member' => __('events.member_is_owner')]);
        }

        $data = $request->validate([
            'role' => ['required', Rule::in([MemberRole::Committee->value, MemberRole::Attendant->value])],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => [Rule::in(EventPermission::grantable())],
        ]);

        $permissions = $data['role'] === MemberRole::Attendant->value
            ? [EventPermission::CheckinUse->value]
            : ($data['permissions'] ?? []);

        DB::transaction(function () use ($member, $data, $permissions) {
            $member->update(['role' => $data['role'], 'status' => 'active']);
            $member->permissions()->delete();

            foreach ($permissions as $permission) {
                $member->permissions()->create(['permission' => $permission]);
            }
        });

        AuditService::record($request->user(), $event, 'member.updated', $member, [
            'role' => $data['role'],
            'permissions' => $permissions,
        ], $request->ip());

        return back()->with('status', __('events.member_updated'));
    }

    public function destroy(Request $request, Event $event, EventMember $member)
    {
        $this->authorize('manageMembers', $event);

        abort_unless($member->event_id === $event->id, 404);

        if ($member->user_id === $event->organizer_id) {
            return back()->withErrors(['member' => __('events.member_is_owner')]);
        }

        $member->update(['status' => 'revoked']);
        $member->permissions()->delete();

        AuditService::record($request->user(), $event, 'member.revoked', $member, [
            'user_id' => $member->user_id,
        ], $request->ip());

        return back()->with('status', __('events.member_revoked'));
    }
}
