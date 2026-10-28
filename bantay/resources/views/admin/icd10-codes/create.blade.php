<x-bantay-layout title="Add Reporting Period" subtitle="Reference Data">
    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6">
                <form method="POST" action="{{ route('admin.icd10-codes.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Code</label>
                        <input type="text" name="code" value="{{ old('code') }}" placeholder="e.g. A09"
                               class="mt-1 block w-full rounded border-gray-300 font-mono">
                        @error('code') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <input type="text" name="description" value="{{ old('description') }}"
                               class="mt-1 block w-full rounded border-gray-300">
                        @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Linked Disease (optional)</label>
                        <select name="disease_id" class="mt-1 block w-full rounded border-gray-300">
                            <option value="">— None —</option>
                            @foreach ($diseases as $disease)
                                <option value="{{ $disease->id }}" {{ old('disease_id') == $disease->id ? 'selected' : '' }}>
                                    {{ $disease->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('disease_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4 flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" checked id="is_active">
                        <label for="is_active" class="ml-2 text-sm text-gray-700">Active</label>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <a href="{{ route('admin.icd10-codes.index') }}" class="px-4 py-2 text-gray-600">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-bantay-layout>