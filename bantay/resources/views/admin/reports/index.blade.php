<x-bantay-layout title="Reports" subtitle="Generate and export surveillance reports">

    <div class="grid md:grid-cols-2 gap-4">
        @foreach ($types as $key => $meta)
            <div class="bg-white shadow-sm border rounded-lg p-5 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-800">{{ $meta['label'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Requires: {{ implode(', ', array_map(fn($p) => rtrim($p, '?'), $meta['params'])) }}
                        @if (collect($meta['params'])->contains(fn($p) => str_ends_with($p, '?')))
                            <span class="text-gray-400">(optional where marked)</span>
                        @endif
                    </p>
                </div>
                <a href="{{ route('admin.reports.create', $key) }}"
                   class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium whitespace-nowrap">
                    Generate →
                </a>
            </div>
        @endforeach
    </div>
</x-bantay-layout>
