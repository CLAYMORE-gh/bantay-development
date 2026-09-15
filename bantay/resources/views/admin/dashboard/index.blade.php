<x-bantay-layout title="Surveillance Dashboard" subtitle="Disease trends, case counts, and validation status at a glance">

    <x-slot name="actions">
        <span class="text-xs bg-gray-100 border rounded-full px-3 py-1.5 text-gray-600">Illustrative data</span>
    </x-slot>

    <div class="mb-6 flex flex-wrap gap-4 items-end">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600">Reporting Period</label>
                <select name="period" onchange="this.form.submit()" class="mt-1 rounded border-gray-300 text-sm">
                    <option value="">All periods</option>
                    @foreach ($periods as $period)
                        <option value="{{ $period->id }}" {{ $periodId == $period->id ? 'selected' : '' }}>
                            {{ $period->label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600">Trend grouping</label>
                <select name="group_by" onchange="this.form.submit()" class="mt-1 rounded border-gray-300 text-sm">
                    <option value="week" {{ $groupBy === 'week' ? 'selected' : '' }}>Weekly</option>
                    <option value="month" {{ $groupBy === 'month' ? 'selected' : '' }}>Monthly</option>
                    <option value="year" {{ $groupBy === 'year' ? 'selected' : '' }}>Yearly</option>
                </select>
            </div>
        </form>
        <a href="{{ route('admin.dashboard.map') }}"
           class="ml-auto px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
            View Geographic Map →
        </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white shadow-sm border rounded-lg p-4">
            <div class="text-xs text-gray-500 uppercase tracking-wide">Valid Records</div>
            <div class="text-2xl font-semibold text-gray-900 mt-1">{{ $summary['total_valid_records'] }}</div>
        </div>
        <div class="bg-white shadow-sm border rounded-lg p-4">
            <div class="text-xs text-gray-500 uppercase tracking-wide">Quarantined</div>
            <div class="text-2xl font-semibold text-red-600 mt-1">{{ $summary['total_quarantined'] }}</div>
        </div>
        <div class="bg-white shadow-sm border rounded-lg p-4">
            <div class="text-xs text-gray-500 uppercase tracking-wide">Active Diseases</div>
            <div class="text-2xl font-semibold text-gray-900 mt-1">{{ $summary['active_diseases'] }}</div>
        </div>
        <div class="bg-white shadow-sm border rounded-lg p-4">
            <div class="text-xs text-gray-500 uppercase tracking-wide">Active Barangays</div>
            <div class="text-2xl font-semibold text-gray-900 mt-1">{{ $summary['active_barangays'] }}</div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white shadow-sm border rounded-lg p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Cases by Disease</h3>
            <canvas id="diseaseChart" height="220"></canvas>
        </div>
        <div class="bg-white shadow-sm border rounded-lg p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Cases by Age Bracket</h3>
            <canvas id="ageChart" height="220"></canvas>
        </div>
    </div>

    <div class="bg-white shadow-sm border rounded-lg p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Case Trend ({{ ucfirst($groupBy) }})</h3>
        <canvas id="trendChart" height="100"></canvas>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script>
        const diseaseData = @json($diseaseCounts);
        const ageData = @json($ageBrackets);
        const trendData = @json($trend);

        new Chart(document.getElementById('diseaseChart'), {
            type: 'pie',
            data: {
                labels: diseaseData.map(d => d.label),
                datasets: [{ data: diseaseData.map(d => d.count), backgroundColor: ['#1d4ed8','#2563eb','#3b82f6','#60a5fa','#93c5fd','#bfdbfe'] }],
            },
        });

        new Chart(document.getElementById('ageChart'), {
            type: 'bar',
            data: {
                labels: ageData.map(d => d.label),
                datasets: [{ label: 'Cases', data: ageData.map(d => d.count), backgroundColor: '#163a6b' }],
            },
            options: { plugins: { legend: { display: false } } },
        });

        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: trendData.map(d => d.label),
                datasets: [{ label: 'Cases', data: trendData.map(d => d.count), borderColor: '#163a6b', backgroundColor: 'rgba(22,58,107,0.08)', fill: true, tension: 0.2 }],
            },
        });
    </script>
</x-bantay-layout>
