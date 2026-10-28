<x-bantay-layout title="{{ $meta['label'] }}" subtitle="Select parameters to generate this report">

    <div class="max-w-lg">
        <div class="bg-white shadow-sm border rounded-lg p-6">
            <form method="GET" action="{{ route('admin.reports.show', $type) }}">

                @if (in_array('period', $meta['params']) || in_array('period?', $meta['params']))
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Reporting Period
                            @if (in_array('period?', $meta['params'])) <span class="text-gray-400 font-normal">(optional — leave blank for all)</span> @endif
                        </label>
                        <select name="period_id" class="mt-1 block w-full rounded border-gray-300" {{ in_array('period', $meta['params']) ? 'required' : '' }}>
                            @if (in_array('period?', $meta['params']))
                                <option value="">— All periods —</option>
                            @else
                                <option value="">— Select —</option>
                            @endif
                            @foreach ($periods as $period)
                                <option value="{{ $period->id }}">{{ $period->label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if (in_array('disease', $meta['params']))
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Disease</label>
                        <select name="disease_id" class="mt-1 block w-full rounded border-gray-300" required>
                            <option value="">— Select —</option>
                            @foreach ($diseases as $disease)
                                <option value="{{ $disease->id }}">{{ $disease->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if (in_array('barangay', $meta['params']))
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Barangay</label>
                        <select name="barangay_id" class="mt-1 block w-full rounded border-gray-300" required>
                            <option value="">— Select —</option>
                            @foreach ($barangays as $barangay)
                                <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex justify-end space-x-2">
                    <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 text-gray-600">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">Generate</button>
                </div>
            </form>
        </div>
    </div>
</x-bantay-layout>
