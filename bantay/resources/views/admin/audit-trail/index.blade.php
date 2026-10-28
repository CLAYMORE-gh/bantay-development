<x-bantay-layout title="Audit Trail" subtitle="System-wide record of create, update, and delete actions">

    <form method="GET" class="mb-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600">Action</label>
            <select name="action" onchange="this.form.submit()" class="mt-1 rounded border-gray-300 text-sm">
                <option value="">All</option>
                @foreach (['created', 'updated', 'deleted'] as $a)
                    <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ ucfirst($a) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600">User</label>
            <select name="user_id" onchange="this.form.submit()" class="mt-1 rounded border-gray-300 text-sm">
                <option value="">All</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600">Model contains</label>
            <input type="text" name="model" value="{{ request('model') }}" placeholder="e.g. Alert" class="mt-1 rounded border-gray-300 text-sm">
        </div>
        <button type="submit" class="px-3 py-2 bg-gray-800 text-white rounded hover:bg-gray-700 text-sm">Filter</button>
        <a href="{{ route('admin.audit-trail.index') }}" class="px-3 py-2 text-sm text-gray-500 hover:underline">Clear</a>
    </form>

    <div class="bg-white shadow-sm border rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">When</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Record</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($logs as $log)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-600">{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                        <td class="px-4 py-2 text-sm">{{ $log->user->name ?? 'System' }}</td>
                        <td class="px-4 py-2">
                            <span @class([
                                'text-xs px-2 py-1 rounded-full font-medium',
                                'bg-emerald-100 text-emerald-800' => $log->action === 'created',
                                'bg-blue-100 text-blue-800' => $log->action === 'updated',
                                'bg-red-100 text-red-800' => $log->action === 'deleted',
                            ])>{{ str_replace('_', ' ', ucfirst($log->action)) }}</span>
                        </td>
                        <td class="px-4 py-2 text-sm font-mono">{{ $log->detailsLabel() }}</td>
                        <td class="px-4 py-2 text-xs text-gray-400">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No audit log entries yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
</x-bantay-layout>
