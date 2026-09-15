<x-bantay-layout title="Data Management" subtitle="Dataset uploads, validation status, and quarantine review">

    <x-slot name="actions">
        <a href="{{ route('admin.uploads.create') }}"
           class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
            + New Upload
        </a>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('status') }}</div>
    @endif

    <div class="bg-white shadow-sm border rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">File</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rows</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Uploaded</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($uploads as $upload)
                    <tr>
                        <td class="px-4 py-2">{{ $upload->original_filename }}</td>
                        <td class="px-4 py-2 text-sm">{{ $upload->reportingPeriod->label ?? '—' }}</td>
                        <td class="px-4 py-2">
                            <span @class([
                                'text-xs px-2 py-1 rounded-full font-medium',
                                'bg-emerald-100 text-emerald-800' => $upload->status === 'processed',
                                'bg-amber-100 text-amber-800' => in_array($upload->status, ['partially_accepted', 'validating', 'checking']),
                                'bg-red-100 text-red-800' => in_array($upload->status, ['quarantined', 'failed']),
                                'bg-gray-100 text-gray-600' => in_array($upload->status, ['registered', 'archived']),
                            ])>
                                {{ str_replace('_', ' ', ucfirst($upload->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-sm">
                            {{ $upload->accepted_rows }} accepted / {{ $upload->quarantined_rows }} quarantined
                            ({{ $upload->total_rows }} total)
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-500">{{ $upload->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('admin.uploads.show', $upload) }}" class="text-sm text-blue-600 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No uploads yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $uploads->links() }}</div>
</x-bantay-layout>
