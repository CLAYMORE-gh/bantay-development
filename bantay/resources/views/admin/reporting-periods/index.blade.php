<x-bantay-layout title="Reference Data" subtitle="Reporting Periods">

    <x-slot name="actions">
        <a href="{{ route('admin.reporting-periods.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
            + Add Reporting Period
        </a>
    </x-slot>

    <div class="mb-4 flex gap-4 text-sm">
        <a href="{{ route('admin.barangays.index') }}" class="text-gray-500 hover:text-gray-700 pb-1">Barangays</a>
        <a href="{{ route('admin.diseases.index') }}" class="text-gray-500 hover:text-gray-700 pb-1">Diseases</a>
        <a href="{{ route('admin.icd10-codes.index') }}" class="text-gray-500 hover:text-gray-700 pb-1">ICD-10 Codes</a>
        <a href="{{ route('admin.reporting-periods.index') }}" class="font-medium text-blue-700 border-b-2 border-blue-700 pb-1">Reporting Periods</a>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('status') }}</div>
    @endif

    <div class="bg-white shadow-sm border rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Label</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Start</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">End</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($periods as $period)
                    <tr>
                        <td class="px-4 py-2">{{ $period->label }}</td>
                        <td class="px-4 py-2 text-sm">{{ $period->start_date->format('M d, Y') }}</td>
                        <td class="px-4 py-2 text-sm">{{ $period->end_date->format('M d, Y') }}</td>
                        <td class="px-4 py-2">
                            <span class="text-xs px-2 py-1 rounded-full font-medium {{ $period->status === 'open' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                                {{ ucfirst($period->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-right space-x-2">
                            <a href="{{ route('admin.reporting-periods.edit', $period) }}" class="text-sm text-blue-600 hover:underline">Edit</a>
                            @if ($period->is_active)
                                <form action="{{ route('admin.reporting-periods.destroy', $period) }}" method="POST" class="inline" onsubmit="return confirm('Deactivate this period?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:underline">Deactivate</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No reporting periods yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $periods->links() }}</div>
</x-bantay-layout>
