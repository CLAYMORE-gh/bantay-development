<x-bantay-layout title="System Settings" subtitle="Configuration values affecting system-wide behavior">

    @if (session('status'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('status') }}</div>
    @endif

    <div class="max-w-2xl">
        <div class="bg-white shadow-sm border rounded-lg p-6">
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf @method('PUT')

                @foreach ($settings as $setting)
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700">{{ $setting->label }}</label>
                        @if ($setting->description)
                            <p class="text-xs text-gray-500 mb-1">{{ $setting->description }}</p>
                        @endif
                        <input type="text" name="settings[{{ $setting->key }}]" value="{{ old('settings.' . $setting->key, $setting->value) }}"
                               class="mt-1 block w-full rounded border-gray-300">
                    </div>
                @endforeach

                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</x-bantay-layout>
