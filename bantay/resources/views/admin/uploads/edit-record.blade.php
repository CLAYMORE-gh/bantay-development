<x-bantay-layout title="Correct Row" subtitle="Upload #{{ $upload->id }}">

    <div class="max-w-xl">
        <div class="bg-white shadow-sm border rounded-lg p-6">

            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm">
                <p class="font-medium text-red-800 mb-1">Current issues:</p>
                @foreach ($record->findings as $finding)
                    <p class="text-red-700">[{{ ucfirst($finding->severity) }}] {{ $finding->message }}</p>
                @endforeach
            </div>

            <form method="POST" action="{{ route('admin.uploads.records.update', [$upload, $record]) }}">
                @csrf @method('PUT')
                @php $raw = $record->raw_data; @endphp

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Disease</label>
                    <input type="text" name="disease" value="{{ old('disease', $raw['disease'] ?? '') }}" class="mt-1 block w-full rounded border-gray-300">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">ICD-10 Code</label>
                    <input type="text" name="icd10_code" value="{{ old('icd10_code', $raw['icd10_code'] ?? '') }}" class="mt-1 block w-full rounded border-gray-300 font-mono">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Barangay</label>
                    <input type="text" name="barangay" value="{{ old('barangay', $raw['barangay'] ?? '') }}" class="mt-1 block w-full rounded border-gray-300">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Consultation Date</label>
                    <input type="text" name="consultation_date" placeholder="YYYY-MM-DD"
                           value="{{ old('consultation_date', $raw['consultation_date'] ?? '') }}" class="mt-1 block w-full rounded border-gray-300">
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Age</label>
                        <input type="text" name="age" value="{{ old('age', $raw['age'] ?? '') }}" class="mt-1 block w-full rounded border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sex</label>
                        <select name="sex" class="mt-1 block w-full rounded border-gray-300">
                            <option value="">—</option>
                            <option value="M" {{ old('sex', $raw['sex'] ?? '') === 'M' ? 'selected' : '' }}>M</option>
                            <option value="F" {{ old('sex', $raw['sex'] ?? '') === 'F' ? 'selected' : '' }}>F</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-2">
                    <a href="{{ route('admin.uploads.show', $upload) }}" class="px-4 py-2 text-gray-600">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">
                        Save &amp; Revalidate
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-bantay-layout>
