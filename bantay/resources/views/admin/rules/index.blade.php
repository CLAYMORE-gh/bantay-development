<x-bantay-layout title="Rules Engine" subtitle="Configurable thresholds that generate surveillance alerts">

    <x-slot name="actions">
        <a href="{{ route('admin.rules.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
            + New Rule
        </a>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('status') }}</div>
    @endif

    <div class="bg-white shadow-sm border rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Disease</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Condition</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Severity</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($rules as $rule)
                    <tr>
                        <td class="px-4 py-2">{{ $rule->name }}</td>
                        <td class="px-4 py-2 text-sm">{{ $rule->disease->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-sm text-gray-600">{{ $rule->thresholdTypeLabel() }}</td>
                        <td class="px-4 py-2">
                            <span @class([
                                'text-xs px-2 py-1 rounded-full font-medium',
                                'bg-gray-100 text-gray-700' => $rule->severity === 'watch',
                                'bg-amber-100 text-amber-800' => $rule->severity === 'alert',
                                'bg-red-100 text-red-800' => $rule->severity === 'critical',
                            ])>{{ ucfirst($rule->severity) }}</span>
                        </td>
                        <td class="px-4 py-2 text-sm">{{ $rule->priority }}</td>
                        <td class="px-4 py-2">
                            @if ($rule->is_active)
                                <span class="text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-800 font-medium">Active</span>
                            @else
                                <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-500 font-medium">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right space-x-2">
                            <a href="{{ route('admin.rules.edit', $rule) }}" class="text-sm text-blue-600 hover:underline">Edit</a>
                            @if ($rule->is_active)
                                <form action="{{ route('admin.rules.destroy', $rule) }}" method="POST" class="inline" onsubmit="return confirm('Deactivate this rule?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:underline">Deactivate</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">No rules configured yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $rules->links() }}</div>
</x-bantay-layout>
