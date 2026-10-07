<x-app-layout>
    <x-slot name="header">
        @include('events._tabs', ['event' => $event])
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        @can('manageVendors', $event)
            <div class="card p-6 lg:col-span-1">
                <h2 class="section-title">{{ __('vendors.add') }}</h2>
                <form method="POST" action="{{ route('events.vendors.store', $event) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="label" for="name">{{ __('vendors.fields.name') }} *</label>
                        <input class="input" id="name" name="name" value="{{ old('name') }}" required maxlength="160">
                    </div>
                    <div>
                        <label class="label" for="type">{{ __('vendors.fields.type') }} *</label>
                        <select class="input" id="type" name="type" required>
                            @foreach ($types as $type)
                                <option value="{{ $type }}" @selected(old('type') === $type)>{{ __('vendors.types.'.$type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="contact_name">{{ __('vendors.fields.contact_name') }}</label>
                        <input class="input" id="contact_name" name="contact_name" value="{{ old('contact_name') }}" maxlength="160">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label" for="phone">{{ __('vendors.fields.phone') }}</label>
                            <input class="input" id="phone" name="phone" value="{{ old('phone') }}" maxlength="32">
                        </div>
                        <div>
                            <label class="label" for="email">{{ __('common.email') }}</label>
                            <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" maxlength="255">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label" for="agreed_amount">{{ __('vendors.fields.agreed_amount') }}</label>
                            <input class="input" id="agreed_amount" name="agreed_amount" value="{{ old('agreed_amount') }}" inputmode="decimal">
                        </div>
                        <div>
                            <label class="label" for="deposit_amount">{{ __('vendors.fields.deposit_amount') }}</label>
                            <input class="input" id="deposit_amount" name="deposit_amount" value="{{ old('deposit_amount') }}" inputmode="decimal">
                        </div>
                    </div>
                    <div>
                        <label class="label" for="services">{{ __('vendors.fields.services') }}</label>
                        <textarea class="input" id="services" name="services" rows="2" maxlength="2000">{{ old('services') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">{{ __('common.save') }}</button>
                </form>
            </div>
        @endcan

        <div class="lg:col-span-2">
            @if ($vendors->isEmpty())
                <div class="empty-state">
                    <p class="font-semibold text-zinc-700">{{ __('vendors.empty') }}</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($vendors as $vendor)
                        <div class="card p-5" x-data="{ open: false }">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-zinc-900">{{ $vendor->name }}</p>
                                    <p class="text-sm text-zinc-500">{{ __('vendors.types.'.$vendor->type) }} · {{ $vendor->contact_name ?: '—' }} {{ $vendor->phone ? '· '.$vendor->phone : '' }}</p>
                                    @if ($vendor->services)
                                        <p class="mt-1 text-sm text-zinc-500">{{ \Illuminate\Support\Str::limit($vendor->services, 140) }}</p>
                                    @endif
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <span class="badge {{ match ($vendor->status) {
                                        'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                        'completed' => 'bg-sky-50 text-sky-700 ring-sky-200',
                                        default => 'bg-zinc-100 text-zinc-500 ring-zinc-200',
                                    } }}">{{ __('vendors.status.'.$vendor->status) }}</span>
                                    @can('manageVendors', $event)
                                        <button type="button" class="btn btn-secondary btn-sm" @click="open = !open">{{ __('common.edit') }}</button>
                                    @endcan
                                </div>
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-3 border-t border-zinc-100 pt-3 text-sm sm:grid-cols-4">
                                <div><p class="text-xs text-zinc-400">{{ __('vendors.columns.agreed') }}</p><p class="font-semibold">{{ \App\Support\Money::format($vendor->agreed_amount_minor) }}</p></div>
                                <div><p class="text-xs text-zinc-400">{{ __('vendors.columns.deposit') }}</p><p class="font-semibold">{{ \App\Support\Money::format($vendor->deposit_amount_minor) }}</p></div>
                                <div class="col-span-2"><p class="text-xs text-zinc-400">{{ __('common.email') }}</p><p class="font-semibold">{{ $vendor->email ?: '—' }}</p></div>
                            </div>

                            @can('manageVendors', $event)
                                <form method="POST" action="{{ route('events.vendors.update', [$event, $vendor]) }}" class="mt-4 grid gap-3 border-t border-zinc-100 pt-4 sm:grid-cols-2" x-show="open" x-cloak>
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="label" for="name-{{ $vendor->id }}">{{ __('vendors.fields.name') }}</label>
                                        <input class="input" id="name-{{ $vendor->id }}" name="name" value="{{ old('name', $vendor->name) }}" required maxlength="160">
                                    </div>
                                    <div>
                                        <label class="label" for="type-{{ $vendor->id }}">{{ __('vendors.fields.type') }}</label>
                                        <select class="input" id="type-{{ $vendor->id }}" name="type" required>
                                            @foreach ($types as $type)
                                                <option value="{{ $type }}" @selected($vendor->type === $type)>{{ __('vendors.types.'.$type) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="label" for="status-{{ $vendor->id }}">{{ __('vendors.fields.status') }}</label>
                                        <select class="input" id="status-{{ $vendor->id }}" name="status">
                                            @foreach (['active', 'completed', 'cancelled'] as $status)
                                                <option value="{{ $status }}" @selected($vendor->status === $status)>{{ __('vendors.status.'.$status) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="label" for="contact_name-{{ $vendor->id }}">{{ __('vendors.fields.contact_name') }}</label>
                                        <input class="input" id="contact_name-{{ $vendor->id }}" name="contact_name" value="{{ old('contact_name', $vendor->contact_name) }}" maxlength="160">
                                    </div>
                                    <div>
                                        <label class="label" for="phone-{{ $vendor->id }}">{{ __('vendors.fields.phone') }}</label>
                                        <input class="input" id="phone-{{ $vendor->id }}" name="phone" value="{{ old('phone', $vendor->phone) }}" maxlength="32">
                                    </div>
                                    <div>
                                        <label class="label" for="email-{{ $vendor->id }}">{{ __('common.email') }}</label>
                                        <input class="input" type="email" id="email-{{ $vendor->id }}" name="email" value="{{ old('email', $vendor->email) }}" maxlength="255">
                                    </div>
                                    <div>
                                        <label class="label" for="agreed_amount-{{ $vendor->id }}">{{ __('vendors.fields.agreed_amount') }}</label>
                                        <input class="input" id="agreed_amount-{{ $vendor->id }}" name="agreed_amount" value="{{ old('agreed_amount', number_format($vendor->agreed_amount_minor / 100, 0, '.', '')) }}" inputmode="decimal">
                                    </div>
                                    <div>
                                        <label class="label" for="deposit_amount-{{ $vendor->id }}">{{ __('vendors.fields.deposit_amount') }}</label>
                                        <input class="input" id="deposit_amount-{{ $vendor->id }}" name="deposit_amount" value="{{ old('deposit_amount', number_format($vendor->deposit_amount_minor / 100, 0, '.', '')) }}" inputmode="decimal">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="label" for="services-{{ $vendor->id }}">{{ __('vendors.fields.services') }}</label>
                                        <textarea class="input" id="services-{{ $vendor->id }}" name="services" rows="2" maxlength="2000">{{ old('services', $vendor->services) }}</textarea>
                                    </div>
                                    <div class="sm:col-span-2 flex justify-end gap-2">
                                        <button type="button" class="btn btn-secondary btn-sm" @click="open = false">{{ __('common.cancel') }}</button>
                                        <button type="submit" class="btn btn-primary btn-sm">{{ __('common.save') }}</button>
                                    </div>
                                </form>

                                <form method="POST" action="{{ route('events.vendors.destroy', [$event, $vendor]) }}" class="mt-3 flex justify-end border-t border-zinc-100 pt-3"
                                      onsubmit="return confirm({{ json_encode(__('common.confirm_delete')) }});">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">{{ __('common.delete') }}</button>
                                </form>
                            @endcan
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">{{ $vendors->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
