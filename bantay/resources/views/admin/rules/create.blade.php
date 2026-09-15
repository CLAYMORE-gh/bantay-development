<x-bantay-layout title="New Surveillance Rule" subtitle="Define a threshold that generates an alert when crossed">

    <div class="max-w-xl">
        <div class="bg-white shadow-sm border rounded-lg p-6">

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                    @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.rules.store') }}">
                @csrf
                @include('admin.rules._form', ['rule' => null])

                <div class="flex justify-end space-x-2 mt-4">
                    <a href="{{ route('admin.rules.index') }}" class="px-4 py-2 text-gray-600">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">Save</button>
                </div>
            </form>
        </div>
    </div>
</x-bantay-layout>
