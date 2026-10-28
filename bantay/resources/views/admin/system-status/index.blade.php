<x-bantay-layout title="System Status" subtitle="Live health check — database, queue, storage, and environment">

    <div class="grid md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white shadow-sm border rounded-lg p-5">
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full {{ $dbStatus['ok'] ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                <h3 class="text-sm font-semibold text-gray-700">Database</h3>
            </div>
            <p class="text-sm text-gray-600">{{ $dbStatus['message'] }}</p>
        </div>
        <div class="bg-white shadow-sm border rounded-lg p-5">
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full {{ $queueStatus['ok'] ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                <h3 class="text-sm font-semibold text-gray-700">Queue</h3>
            </div>
            <p class="text-sm text-gray-600">{{ $queueStatus['message'] }}</p>
        </div>
        <div class="bg-white shadow-sm border rounded-lg p-5">
            <div class="flex items-center gap-2 mb-1">
                <span class="w-2.5 h-2.5 rounded-full {{ $storage['ok'] ? 'bg-emerald-500' : ($storage['ok'] === null ? 'bg-gray-300' : 'bg-amber-500') }}"></span>
                <h3 class="text-sm font-semibold text-gray-700">Storage</h3>
            </div>
            @if ($storage['ok'] !== null)
                <p class="text-sm text-gray-600">{{ $storage['used_percent'] }}% used — {{ $storage['free_gb'] }} GB free of {{ $storage['total_gb'] }} GB</p>
            @else
                <p class="text-sm text-gray-600">{{ $storage['message'] }}</p>
            @endif
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white shadow-sm border rounded-lg p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">System Counts</h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><div class="text-gray-500">Users</div><div class="font-semibold text-lg">{{ $stats['total_users'] }}</div></div>
                <div><div class="text-gray-500">Surveillance Records</div><div class="font-semibold text-lg">{{ $stats['total_records'] }}</div></div>
                <div><div class="text-gray-500">Uploads</div><div class="font-semibold text-lg">{{ $stats['total_uploads'] }}</div></div>
                <div><div class="text-gray-500">Open Alerts</div><div class="font-semibold text-lg">{{ $stats['open_alerts'] }}</div></div>
            </div>
            @if ($stats['last_upload'])
                <p class="text-xs text-gray-400 mt-3">
                    Last upload: {{ $stats['last_upload']->original_filename }} — {{ $stats['last_upload']->created_at->diffForHumans() }}
                </p>
            @endif
        </div>

        <div class="bg-white shadow-sm border rounded-lg p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Environment</h3>
            <div class="text-sm space-y-2">
                @foreach ($environment as $key => $value)
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ str_replace('_', ' ', ucfirst($key)) }}</span>
                        <span class="font-medium">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <p class="text-xs text-gray-400">
        This page reflects live system state at the moment it was loaded — refresh to get current values.
    </p>
</x-bantay-layout>
