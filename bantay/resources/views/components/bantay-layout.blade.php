@props(['title' => '', 'subtitle' => ''])

@php
    $role = auth()->user()?->getRoleNames()->first() ?? 'Viewer';
    $navItems = \App\Support\BantayNav::forRole($role);
    $blurb = \App\Support\BantayNav::blurbForRole($role);
    $liveAlertCount = \App\Support\BantayNav::alertBadgeCount();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title . ' — BANTAY' : 'BANTAY' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 antialiased">
    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside class="w-60 shrink-0 flex flex-col justify-between text-white"
               style="background: linear-gradient(180deg, #0f2545 0%, #163a6b 100%);">
            <div>
                <div class="flex items-center gap-2 px-5 py-6">
                    <div class="w-8 h-8 rounded bg-blue-500/30 flex items-center justify-center text-lg">🛡️</div>
                    <div>
                        <div class="font-bold tracking-wide leading-none">BANTAY</div>
                        <div class="text-xs text-blue-200 leading-none mt-1">{{ $role }}</div>
                    </div>
                </div>

                <nav class="px-3 space-y-1">
                    @foreach ($navItems as $item)
                        @php
                            $isActive = $item['route'] && request()->routeIs($item['route']);
                            $href = $item['route'] ? route($item['route']) : null;
                            // Alerts badge is now live from the database instead of hardcoded.
                            $badge = $item['label'] === 'Alerts' || $item['label'] === 'Alert Summaries'
                                ? ($liveAlertCount ?: null)
                                : ($item['badge'] ?? null);
                        @endphp
                        @if ($href)
                            <a href="{{ $href }}"
                               class="flex items-center gap-3 px-3 py-2 rounded text-sm transition
                                      {{ $isActive ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-white/10' }}">
                                <x-icon :name="$item['icon']" class="w-4 h-4 shrink-0" />
                                <span class="flex-1">{{ $item['label'] }}</span>
                                @if ($badge)
                                    <span class="text-[11px] bg-red-500 text-white rounded-full px-1.5 py-0.5 leading-none">
                                        {{ $badge }}
                                    </span>
                                @endif
                            </a>
                        @else
                            <div class="flex items-center gap-3 px-3 py-2 rounded text-sm text-blue-300/60 cursor-not-allowed"
                                 title="Not built yet">
                                <x-icon :name="$item['icon']" class="w-4 h-4 shrink-0" />
                                <span class="flex-1">{{ $item['label'] }}</span>
                            </div>
                        @endif
                    @endforeach
                </nav>
            </div>

            <div class="p-3 space-y-3">
                @if ($blurb['title'])
                    <div class="bg-white/10 rounded p-3 text-xs">
                        <div class="font-semibold mb-1">{{ $blurb['title'] }}</div>
                        <div class="text-blue-200 leading-relaxed">{{ $blurb['body'] }}</div>
                    </div>
                @endif

                <div class="relative">
                    <button onclick="document.getElementById('user-menu').classList.toggle('hidden')"
                            class="w-full flex items-center gap-2 bg-white/10 hover:bg-white/15 rounded p-2 text-left">
                        <div class="w-8 h-8 rounded-full bg-emerald-400 text-emerald-900 font-semibold flex items-center justify-center text-sm">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium truncate">{{ auth()->user()->name ?? 'User' }}</div>
                            <div class="text-xs text-blue-200 truncate">{{ $role }}</div>
                        </div>
                    </button>
                    <div id="user-menu" class="hidden absolute bottom-full mb-2 left-0 right-0 bg-white text-gray-800 rounded shadow-lg overflow-hidden text-sm">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-50">My Account</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50">Log Out</button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 flex flex-col min-w-0">
            <header class="bg-white border-b px-8 py-5 flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
                    @if ($subtitle)
                        <p class="text-sm text-gray-500 mt-1">{{ $subtitle }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    {{ $actions ?? '' }}
                    <a href="{{ route('admin.alerts.index') }}" class="w-9 h-9 rounded-full border flex items-center justify-center text-gray-500 hover:bg-gray-50 relative">
                        <x-icon name="bell" class="w-4 h-4" />
                        @if ($liveAlertCount)
                            <span class="absolute -top-1 -right-1 text-[10px] bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center">{{ $liveAlertCount }}</span>
                        @endif
                    </a>
                    <div class="w-9 h-9 rounded-full border flex items-center justify-center text-gray-500">
                        <x-icon name="shield" class="w-4 h-4" />
                    </div>
                </div>
            </header>
  <main class="flex-1 p-8">
      {{ $slot }}
      <x-disclaimer-footer />
  </main>
        </div>
    </div>
</body>
</html>
