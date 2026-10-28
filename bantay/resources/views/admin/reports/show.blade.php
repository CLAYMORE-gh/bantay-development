<x-bantay-layout title="{{ $report['title'] }}" subtitle="{{ $report['subtitle'] }}">

    <x-slot name="actions">
        <a href="{{ route('admin.reports.export.pdf', array_merge(['type' => $type], $params)) }}"
           class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700 text-sm font-medium">
            Export PDF
        </a>
        <a href="{{ route('admin.reports.export.excel', array_merge(['type' => $type], $params)) }}"
           class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 text-sm font-medium">
            Export Excel
        </a>
    </x-slot>

    <div class="mb-6 inline-block bg-blue-50 border border-blue-100 rounded-lg px-5 py-3">
        <div class="text-xs text-blue-500 uppercase tracking-wide">Total Cases</div>
        <div class="text-2xl font-semibold text-blue-900">{{ $report['total_cases'] }}</div>
    </div>

    <p class="text-xs text-gray-400 mb-6">Generated {{ $report['generated_at']->format('M d, Y H:i') }}</p>

    <div class="space-y-6">
        @foreach ($report['sections'] as $section)
            <div class="bg-white shadow-sm border rounded-lg overflow-hidden">
                <div class="px-5 py-3 border-b bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700">{{ $section['heading'] }}</h3>
                </div>
                @if (count($section['rows']))
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                @foreach ($section['columns'] as $col)
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ $col }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($section['rows'] as $row)
                                <tr>
                                    @foreach ($row as $cell)
                                        <td class="px-4 py-2 text-sm">{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="px-5 py-4 text-sm text-gray-400">No data available for this section.</p>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        <a href="{{ route('admin.reports.index') }}" class="text-sm text-blue-600 hover:underline">← Back to Reports</a>
    </div>
</x-bantay-layout>
