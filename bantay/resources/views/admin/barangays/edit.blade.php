<x-bantay-layout title="Edit Barangay" subtitle="Reference Data">

    <div class="max-w-xl">
        <div class="bg-white shadow-sm border rounded-lg p-6">
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                    @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.barangays.update', $barangay) }}">
                @csrf @method('PUT')

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" value="{{ old('name', $barangay->name) }}"
                           class="mt-1 block w-full rounded border-gray-300">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Code (optional)</label>
                    <input type="text" name="code" value="{{ old('code', $barangay->code) }}"
                           class="mt-1 block w-full rounded border-gray-300">
                </div>

                <div class="grid grid-cols-2 gap-4 mb-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Latitude <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="number" step="any" name="latitude" value="{{ old('latitude', $barangay->latitude) }}"
                               placeholder="e.g. 13.4115"
                               class="mt-1 block w-full rounded border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Longitude <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="number" step="any" name="longitude" value="{{ old('longitude', $barangay->longitude) }}"
                               placeholder="e.g. 121.1803"
                               class="mt-1 block w-full rounded border-gray-300">
                    </div>
                </div>
                <p class="text-xs text-gray-500 mb-4">
                    Used for the Geographic Monitoring map. Right-click the barangay's location on
                    <a href="https://www.google.com/maps" target="_blank" class="text-blue-600 hover:underline">Google Maps</a>
                    and copy the coordinates shown at the top of the menu.
                </p>

                <div class="mb-4 flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active"
                           {{ $barangay->is_active ? 'checked' : '' }}>
                    <label for="is_active" class="ml-2 text-sm text-gray-700">Active</label>
                </div>

                <div class="flex justify-end space-x-2">
                    <a href="{{ route('admin.barangays.index') }}" class="px-4 py-2 text-gray-600">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">Update</button>
                </div>
            </form>
        </div>
    </div>
</x-bantay-layout>
