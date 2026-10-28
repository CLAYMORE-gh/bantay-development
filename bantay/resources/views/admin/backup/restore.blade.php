<x-bantay-layout title="Restore Database" subtitle="Verified backup selection, authorization, and confirmation">

    <div class="max-w-xl">
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
            Restoration is permitted only after explicit confirmation. This will
            <strong>overwrite all current data</strong> with the contents of this backup.
            Unsaved work since this backup was created will be lost.
        </div>

        <div class="bg-white shadow-sm border rounded-lg p-6">
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                    @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
                </div>
            @endif

            <div class="mb-4 text-sm">
                <div class="text-gray-500">Selected Backup</div>
                <div class="font-mono font-medium">{{ $filename }}</div>
            </div>

            <form method="POST" action="{{ route('admin.backup.restore', $filename) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">
                        Type the exact backup filename to confirm
                    </label>
                    <input type="text" name="confirm_filename" placeholder="{{ $filename }}"
                           class="mt-1 block w-full rounded border-gray-300 font-mono text-sm">
                </div>

                <div class="flex justify-end space-x-2">
                    <a href="{{ route('admin.backup.index') }}" class="px-4 py-2 text-gray-600">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 font-medium">
                        Restore Database
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-bantay-layout>
