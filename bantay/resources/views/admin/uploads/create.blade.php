<x-bantay-layout title="Dataset Upload" subtitle="Upload a prepared, de-identified surveillance dataset for validation">

    <div class="max-w-xl">
        <div class="bg-white shadow-sm border rounded-lg p-6">

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.uploads.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Reporting Period</label>
                    <select name="reporting_period_id" class="mt-1 block w-full rounded border-gray-300">
                        <option value="">— Select —</option>
                        @foreach ($periods as $period)
                            <option value="{{ $period->id }}">{{ $period->label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Excel File (.xlsx)</label>
                    <input type="file" name="file" accept=".xlsx,.xls" class="mt-1 block w-full text-sm">
                    <p class="text-xs text-gray-500 mt-1">
                        Expected columns: Disease, ICD10 Code, Barangay, Consultation Date, Age, Sex.
                    </p>
                </div>

                <div class="flex justify-end space-x-2">
                    <a href="{{ route('admin.uploads.index') }}" class="px-4 py-2 text-gray-600">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">Upload</button>
                </div>
            </form>
        </div>
    </div>
</x-bantay-layout>
