<x-bantay-layout title="Geographic Monitoring" subtitle="Barangay-level case distribution">

    <div class="mb-4 flex justify-between items-center">
        <form method="GET">
            <select name="period" onchange="this.form.submit()" class="rounded border-gray-300 text-sm">
                <option value="">All periods</option>
                @foreach ($periods as $period)
                    <option value="{{ $period->id }}" {{ $periodId == $period->id ? 'selected' : '' }}>
                        {{ $period->label }}
                    </option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-blue-600 hover:underline">← Back to Dashboard</a>
    </div>

    @php
        $missingCoords = collect($barangayCounts)->filter(fn ($b) => is_null($b['latitude']) || is_null($b['longitude']))->count();
    @endphp

    @if ($missingCoords > 0)
        <div class="mb-4 p-3 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-lg">
            {{ $missingCoords }} barangay(s) have no coordinates set yet, so they won't appear on the map below.
        </div>
    @endif

    <div id="map" class="rounded-lg shadow-sm border" style="height: 500px;"></div>

    <div class="mt-6 bg-white shadow-sm border rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Barangay</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Case Count</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach (collect($barangayCounts)->sortByDesc('count') as $b)
                    <tr>
                        <td class="px-4 py-2">{{ $b['name'] }}</td>
                        <td class="px-4 py-2">{{ $b['count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const barangays = @json(collect($barangayCounts)->filter(fn ($b) => $b['latitude'] && $b['longitude'])->values());
        const defaultCenter = [13.4115, 121.1803];
        const map = L.map('map').setView(
            barangays.length ? [barangays[0].latitude, barangays[0].longitude] : defaultCenter, 12
        );
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
        const maxCount = Math.max(1, ...barangays.map(b => b.count));
        function colorFor(count) {
            if (count === 0) return '#9ca3af';
            const ratio = count / maxCount;
            if (ratio > 0.66) return '#dc2626';
            if (ratio > 0.33) return '#f59e0b';
            return '#16a34a';
        }
        barangays.forEach(b => {
            L.circleMarker([b.latitude, b.longitude], {
                radius: 8 + Math.min(12, b.count), color: colorFor(b.count), fillColor: colorFor(b.count), fillOpacity: 0.7,
            }).addTo(map).bindPopup(`<strong>${b.name}</strong><br>${b.count} case(s)`);
        });
    </script>
</x-bantay-layout>
