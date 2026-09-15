<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Reporting Period</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6">
                <form method="POST" action="{{ route('admin.reporting-periods.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Label</label>
                        <input type="text" name="label" value="{{ old('label') }}" placeholder="e.g. Week 34, 2026"
                               class="mt-1 block w-full rounded border-gray-300">
                        @error('label') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" name="start_date" value="{{ old('start_date') }}"
                                   class="mt-1 block w-full rounded border-gray-300">
                            @error('start_date') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" name="end_date" value="{{ old('end_date') }}"
                                   class="mt-1 block w-full rounded border-gray-300">
                            @error('end_date') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 block w-full rounded border-gray-300">
                            <option value="open" {{ old('status') === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                        @error('status') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4 flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" checked id="is_active">
                        <label for="is_active" class="ml-2 text-sm text-gray-700">Active</label>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <a href="{{ route('admin.reporting-periods.index') }}" class="px-4 py-2 text-gray-600">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
