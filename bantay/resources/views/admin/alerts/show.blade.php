<x-bantay-layout title="Alert #{{ $alert->id }}" subtitle="{{ $alert->disease->name ?? '—' }} — {{ $alert->barangay->name ?? 'City-wide' }}">

    @if (session('status'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
            @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
    @endif

    <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">

            <div class="bg-white shadow-sm border rounded-lg p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span @class([
                        'text-sm px-3 py-1 rounded-full font-medium',
                        'bg-gray-100 text-gray-700' => $alert->severity === 'watch',
                        'bg-amber-100 text-amber-800' => $alert->severity === 'alert',
                        'bg-red-100 text-red-800' => $alert->severity === 'critical',
                    ])>{{ ucfirst($alert->severity) }}</span>
                    <span class="text-sm px-3 py-1 rounded-full bg-blue-50 text-blue-700 font-medium">
                        {{ str_replace('_', ' ', ucfirst($alert->status)) }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><div class="text-gray-500">Rule</div><div class="font-medium">{{ $alert->rule->name ?? '—' }}</div></div>
                    <div><div class="text-gray-500">Reporting Period</div><div class="font-medium">{{ $alert->reportingPeriod->label ?? '—' }}</div></div>
                    <div><div class="text-gray-500">Triggered Value</div><div class="font-medium">{{ $alert->triggered_value }}</div></div>
                    <div><div class="text-gray-500">Threshold</div><div class="font-medium">{{ $alert->threshold_value }}</div></div>
                </div>

                @if ($alert->notes)
                    <div class="mt-4 pt-4 border-t text-sm">
                        <div class="text-gray-500 mb-1">Notes</div>
                        <div>{{ $alert->notes }}</div>
                    </div>
                @endif
            </div>

            @if (count($availableTransitions))
                <div class="bg-white shadow-sm border rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Update Status</h3>
                    <form method="POST" action="{{ route('admin.alerts.transition', $alert) }}" class="space-y-3">
                        @csrf
                        <div class="flex flex-wrap gap-2">
                            @foreach ($availableTransitions as $transition)
                                <button type="submit" name="to" value="{{ $transition }}"
                                        onclick="return document.getElementById('notes-{{ $transition }}') ? true : true"
                                        class="px-4 py-2 rounded text-sm font-medium border
                                               {{ $transition === 'dismissed' ? 'border-gray-300 text-gray-600 hover:bg-gray-50' : 'bg-blue-600 text-white border-blue-600 hover:bg-blue-700' }}">
                                    {{ str_replace('_', ' ', ucfirst($transition)) }}
                                </button>
                            @endforeach
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600">Notes (optional)</label>
                            <textarea name="notes" rows="2" class="mt-1 block w-full rounded border-gray-300 text-sm"
                                      placeholder="Professional remarks or justification for this decision"></textarea>
                        </div>
                    </form>
                </div>
            @else
                <div class="bg-gray-50 border rounded-lg p-4 text-sm text-gray-500">
                    This alert is closed — no further transitions available.
                </div>
            @endif
        </div>

        <div>
            <div class="bg-white shadow-sm border rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">History</h3>
                <div class="space-y-3">
                    @foreach ($alert->history->sortByDesc('created_at') as $entry)
                        <div class="text-xs border-l-2 border-gray-200 pl-3">
                            <div class="font-medium text-gray-800">
                                {{ $entry->from_status ? str_replace('_',' ',ucfirst($entry->from_status)) . ' → ' : '' }}{{ str_replace('_',' ',ucfirst($entry->to_status)) }}
                            </div>
                            <div class="text-gray-500">
                                {{ $entry->changedBy->name ?? 'System' }} · {{ $entry->created_at->format('M d, Y H:i') }}
                            </div>
                            @if ($entry->notes)
                                <div class="text-gray-600 mt-1">{{ $entry->notes }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.alerts.index') }}" class="text-sm text-blue-600 hover:underline">← Back to Alerts</a>
    </div>
</x-bantay-layout>
