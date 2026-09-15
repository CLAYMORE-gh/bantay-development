@php $r = $rule; @endphp

<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700">Rule Name</label>
    <input type="text" name="name" value="{{ old('name', $r->name ?? '') }}" placeholder="e.g. Dengue Weekly Spike"
           class="mt-1 block w-full rounded border-gray-300">
</div>

<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700">Disease</label>
    <select name="disease_id" class="mt-1 block w-full rounded border-gray-300">
        <option value="">— Select —</option>
        @foreach ($diseases as $disease)
            <option value="{{ $disease->id }}" {{ old('disease_id', $r->disease_id ?? '') == $disease->id ? 'selected' : '' }}>
                {{ $disease->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="grid grid-cols-2 gap-4 mb-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Condition Type</label>
        <select name="threshold_type" id="threshold_type" class="mt-1 block w-full rounded border-gray-300">
            @php $types = ['absolute' => 'Absolute case count', 'percentage_increase' => '% increase vs previous period', 'consecutive_increase' => 'Consecutive rising periods', 'geographic_spread' => 'Geographic spread (# barangays)']; @endphp
            @foreach ($types as $value => $label)
                <option value="{{ $value }}" {{ old('threshold_type', $r->threshold_type ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Threshold Value</label>
        <input type="number" step="0.01" name="threshold_value" value="{{ old('threshold_value', $r->threshold_value ?? '') }}"
               class="mt-1 block w-full rounded border-gray-300">
        <p class="text-xs text-gray-500 mt-1" id="threshold-hint">
            Meaning depends on condition type — e.g. case count, percent, # of periods, or # of barangays.
        </p>
    </div>
</div>

<div class="grid grid-cols-2 gap-4 mb-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Severity When Triggered</label>
        <select name="severity" class="mt-1 block w-full rounded border-gray-300">
            @foreach (['watch' => 'Watch', 'alert' => 'Alert', 'critical' => 'Critical'] as $value => $label)
                <option value="{{ $value }}" {{ old('severity', $r->severity ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Priority (1 = highest)</label>
        <input type="number" name="priority" min="1" max="255" value="{{ old('priority', $r->priority ?? 50) }}"
               class="mt-1 block w-full rounded border-gray-300">
    </div>
</div>

<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700">Effective Date</label>
    <input type="date" name="effective_date" value="{{ old('effective_date', isset($r->effective_date) ? $r->effective_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
           class="mt-1 block w-full rounded border-gray-300">
</div>

<div class="mb-4 flex items-center">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $r->is_active ?? true) ? 'checked' : '' }}>
    <label for="is_active" class="ml-2 text-sm text-gray-700">Active</label>
</div>
