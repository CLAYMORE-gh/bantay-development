<x-bantay-layout title="Upload #{{ $upload->id }}" subtitle="{{ $upload->original_filename }}">

    @if (session('status'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('status') }}</div>
    @endif

    <div class="bg-white shadow-sm border rounded-lg p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
            <div>
                <div class="text-gray-500">Status</div>
                <div class="font-medium">{{ str_replace('_', ' ', ucfirst($upload->status)) }}</div>
            </div>
            <div>
                <div class="text-gray-500">Reporting Period</div>
                <div class="font-medium">{{ $upload->reportingPeriod->label ?? '—' }}</div>
            </div>
            <div>
                <div class="text-gray-500">Total Rows</div>
                <div class="font-medium">{{ $upload->total_rows }}</div>
            </div>
            <div>
                <div class="text-gray-500">Accepted / Quarantined</div>
                <div class="font-medium">{{ $upload->accepted_rows }} / {{ $upload->quarantined_rows }}</div>
            </div>
            @if ($upload->skipped_rows)
                <div>
                    <div class="text-gray-500">Skipped (not disease cases)</div>
                    <div class="font-medium">{{ $upload->skipped_rows }}</div>
                </div>
            @endif
        </div>

        <div class="mt-4 pt-4 border-t grid grid-cols-3 gap-4 text-sm">
            <div><span class="text-red-700 font-semibold">{{ $summary['blocking'] }}</span> blocking errors</div>
            <div><span class="text-amber-700 font-semibold">{{ $summary['warning'] }}</span> warnings</div>
            <div><span class="text-gray-600 font-semibold">{{ $summary['informational'] }}</span> informational</div>
        </div>

        @if ($upload->failure_reason)
            <div class="mt-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ $upload->failure_reason }}</div>
        @endif

        @if ($upload->notes)
            <div class="mt-4 p-3 bg-blue-50 border border-blue-100 text-blue-800 rounded-lg text-sm">{{ $upload->notes }}</div>
        @endif
    </div>

    @if ($upload->quarantined_rows > 0)
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Quarantined Rows</h3>
        <div class="bg-white shadow-sm border rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Row Data</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Blocking Issues</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($quarantined as $record)
                        <tr>
                            <td class="px-4 py-2 text-xs text-gray-600">
                                {{ collect($record->raw_data)->map(fn($v, $k) => "$k: $v")->implode(', ') }}
                            </td>
                            <td class="px-4 py-2 text-xs text-red-700">
                                @foreach ($record->findings->where('severity', 'blocking') as $finding)
                                    <div>{{ $finding->message }}</div>
                                @endforeach
                            </td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('admin.uploads.records.edit', [$upload, $record]) }}"
                                   class="text-sm text-blue-600 hover:underline">Correct</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $quarantined->links() }}</div>
    @else
        <p class="text-gray-500">No quarantined rows remaining.</p>
    @endif
</x-bantay-layout>