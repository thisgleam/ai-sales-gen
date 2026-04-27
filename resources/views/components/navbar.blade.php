{{--
    Component: x-navbar
    Props:
      - pageLabel (string|null) — shown next to brand, e.g. "Dashboard"
      - showCreateButton (bool)  — toggle "+ New Page" CTA, default true
--}}
@props([
    'pageLabel'         => null,
    'showCreateButton'  => false,
])

<nav class="navbar bg-base-100 border-b border-base-content/5 px-4 md:px-8 sticky top-0 z-50 min-h-14">

    {{-- Brand --}}
    <div class="flex-1 flex items-center gap-4">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
            <div class="flex flex-col leading-none">
                <span class="text-lg font-black tracking-tighter text-base-content uppercase italic">AI Sales Gen</span>
                @if($pageLabel)
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary mt-0.5">{{ $pageLabel }}</span>
                @endif
            </div>
        </a>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3">

        @if($showCreateButton)
            <a href="{{ route('pages.create') }}"
               class="btn btn-primary btn-sm font-black uppercase tracking-widest italic hidden sm:inline-flex gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                New Page
            </a>
        @endif

        {{-- User dropdown --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open"
                    @keydown.escape.window="open = false"
                    class="flex items-center justify-center w-9 h-9 rounded-full bg-primary/10 border border-primary/20 hover:bg-primary/20 transition-colors cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                    aria-label="Open user menu">
                <span class="text-sm font-black text-primary leading-none">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>
            </button>

            <div x-show="open"
                 @click.outside="open = false"
                 x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                 class="absolute right-0 mt-2 w-56 bg-base-100 border border-base-content/5 rounded-xl shadow-2xl overflow-hidden z-50 origin-top-right">

                {{-- User info --}}
                <div class="px-4 py-3 border-b border-base-content/5">
                    <p class="font-black text-sm truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-base-content/40 font-medium truncate mt-0.5">{{ auth()->user()->email }}</p>
                </div>

                {{-- Menu items --}}
                <ul class="p-1.5">
                    <li>
                        <a href="/logout"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-error font-bold text-xs uppercase tracking-widest hover:bg-error/10 transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</nav>
