<x-bantay-layout title="Notifications" subtitle="System-generated alerts and events relevant to you">

    <x-slot name="actions">
        <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
            @csrf
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700 text-sm font-medium">
                Mark All Read
            </button>
        </form>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('status') }}</div>
    @endif

    <div class="bg-white shadow-sm border rounded-lg divide-y">
        @forelse ($notifications as $notification)
            <div class="p-4 flex items-start justify-between {{ $notification->read_at ? '' : 'bg-blue-50/50' }}">
                <div>
                    <div class="text-sm {{ $notification->read_at ? 'text-gray-600' : 'text-gray-900 font-medium' }}">
                        {{ $notification->data['message'] ?? 'Notification' }}
                    </div>
                    <div class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                </div>
                <div class="flex items-center gap-3 shrink-0 ml-4">
                    @if (!empty($notification->data['url']))
                        <a href="{{ route('admin.notifications.read', ['id' => $notification->id, 'redirect' => $notification->data['url']]) }}"
                           class="text-sm text-blue-600 hover:underline">View</a>
                    @endif
                    @if (!$notification->read_at)
                        <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="text-sm text-gray-500 hover:underline">Mark read</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-6 text-center text-gray-500 text-sm">No notifications yet.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
</x-bantay-layout>
