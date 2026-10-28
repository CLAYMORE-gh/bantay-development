<x-bantay-layout title="Backup & Recovery" subtitle="Create, download, and restore database backups">

    <x-slot name="actions">
        <form method="POST" action="{{ route('admin.backup.create') }}" onsubmit="return confirm('Create a new backup now? This may take a moment for large databases.');">
            @csrf
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                Create Backup Now
            </button>
        </form>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
            @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
    @endif

    <div class="mb-4 p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-sm">
        Restoring a backup <strong>replaces all current data</strong> with the backup's contents. This cannot be undone.
        Only restore from a backup you've verified.
    </div>

    <div class="bg-white shadow-sm border rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Backup</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Size</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($files as $file)
                    <tr>
                        <td class="px-4 py-2 font-mono text-sm">{{ $file['name'] }}</td>
                        <td class="px-4 py-2 text-sm">{{ $file['created_at']->format('M d, Y H:i') }}</td>
                        <td class="px-4 py-2 text-sm">{{ $file['size_mb'] }} MB</td>
                        <td class="px-4 py-2 text-right space-x-3">
                            <a href="{{ route('admin.backup.download', $file['name']) }}" class="text-sm text-blue-600 hover:underline">Download</a>
                            <a href="{{ route('admin.backup.restore.show', $file['name']) }}" class="text-sm text-amber-700 hover:underline">Restore</a>
                            <form action="{{ route('admin.backup.destroy', $file['name']) }}" method="POST" class="inline" onsubmit="return confirm('Permanently delete this backup file?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No backups yet. Click "Create Backup Now" above.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-bantay-layout>
