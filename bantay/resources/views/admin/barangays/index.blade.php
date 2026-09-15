<x-bantay-layout title="Reference Data" subtitle="Barangays">

    <x-slot name="actions">
        <a href="{{ route('admin.barangays.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
            + Add Barangay
        </a>
    </x-slot>

    <div class="mb-4 flex gap-4 text-sm">
        <a href="{{ route('admin.barangays.index') }}" class="font-medium text-blue-700 border-b-2 border-blue-700 pb-1">Barangays</a>
        <a href="{{ route('admin.diseases.index') }}" class="text-gray-500 hover:text-gray-700 pb-1">Diseases</a>
        <a href="{{ route('admin.icd10-codes.index') }}" class="text-gray-500 hover:text-gray-700 pb-1">ICD-10 Codes</a>
        <a href="{{ route('admin.reporting-periods.index') }}" class="text-gray-500 hover:text-gray-700 pb-1">Reporting Periods</a>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('status') }}</div>
    @endif

    <div class="bg-white shadow-sm border rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($barangays as $barangay)
                    <tr>
                        <td class="px-4 py-2">{{ $barangay->name }}</td>
                        <td class="px-4 py-2">{{ $barangay->code ?? '—' }}</td>
                        <td class="px-4 py-2">
                            @if ($barangay->is_active)
                                <span class="text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-800 font-medium">Active</span>
                            @else
                                <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-500 font-medium">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right space-x-2">
                            <a href="{{ route('admin.barangays.edit', $barangay) }}" class="text-sm text-blue-600 hover:underline">Edit</a>
                            @if ($barangay->is_active)
                                <form action="{{ route('admin.barangays.destroy', $barangay) }}" method="POST" class="inline" onsubmit="return confirm('Deactivate this barangay?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:underline">Deactivate</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No barangays yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $barangays->links() }}</div>
</x-bantay-layout>
