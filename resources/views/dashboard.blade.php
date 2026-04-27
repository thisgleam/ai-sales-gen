<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="night">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - AI Sales Gen</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-base-200 min-h-screen antialiased selection:bg-primary selection:text-primary-content">

    <x-navbar pageLabel="The Workshop" />

    <main class="max-w-7xl mx-auto px-6 lg:px-12 py-12">
        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success mb-12 rounded-2xl shadow-xl shadow-success/10 border-none" x-data x-init="setTimeout(() => $el.remove(), 4000)">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                <span class="font-bold tracking-tight text-sm uppercase">{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-12 gap-12">
            
            {{-- Left: Identity & Stats (4 cols) --}}
            <aside class="md:col-span-4 space-y-12">
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-primary to-secondary rounded-2xl blur opacity-10 group-hover:opacity-20 transition duration-1000"></div>
                    <div class="relative bg-base-100 rounded-2xl p-8 border border-base-content/5">
                        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-primary mb-4">Command Center</p>
                        <h1 class="text-4xl font-black tracking-tighter uppercase italic leading-[0.9] mb-4">
                            CRAFTING<br><span class="text-base-content/30 italic font-light">EXCELLENCE.</span>
                        </h1>
                        <p class="text-sm text-base-content/50 font-medium leading-relaxed mb-8">
                            Managing {{ $salesPages->count() }} active marketing campaigns.
                        </p>
                        <a href="{{ route('pages.create') }}" class="btn btn-primary btn-block rounded-xl shadow-2xl shadow-primary/20 font-black uppercase tracking-widest italic group">
                            <span class="group-hover:scale-110 transition-transform">+ New Campaign</span>
                        </a>
                    </div>
                </div>

                {{-- Stats Stack --}}
                <div class="grid grid-cols-1 gap-4">
                    <div class="bg-base-300/30 rounded-2xl p-6 border border-base-content/5">
                        <p class="text-[10px] font-black uppercase tracking-widest opacity-40 mb-2">Live</p>
                        <p class="text-3xl font-black text-primary">{{ $salesPages->where('status', 'published')->count() }}</p>
                    </div>
                </div>
                
                {{-- Engine Meta --}}
                <div class="px-2">
                    <div class="flex items-center gap-2 opacity-20 text-[8px] font-black uppercase tracking-[0.5em]">
                        <div class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></div>
                        AI_SALES_ENGINE_V2_ACTIVE
                    </div>
                </div>
            </aside>

            {{-- Right: Content List (8 cols) --}}
            <section class="md:col-span-8">
                @if($salesPages->isEmpty())
                    <div class="bg-base-100 rounded-3xl border border-dashed border-base-content/10 flex flex-col items-center justify-center py-32 px-12 text-center">
                        <div class="w-16 h-16 bg-base-200 rounded-full flex items-center justify-center mb-8 opacity-20">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                        </div>
                        <h2 class="text-2xl font-black uppercase italic tracking-tighter mb-4">No assets found.</h2>
                        <p class="text-base-content/40 max-w-xs text-sm font-medium leading-relaxed mb-8">
                            Your creative workshop is empty. Start by building your first sales page.
                        </p>
                        <a href="{{ route('pages.create') }}" class="btn btn-outline btn-sm rounded-lg opacity-40 hover:opacity-100 font-black uppercase tracking-widest text-[10px]">Initialize First Script</a>
                    </div>
                @else
                    <div class="flex items-center justify-between mb-8 px-2">
                        <h2 class="text-[10px] font-black uppercase tracking-[0.5em] opacity-30">Active Assets</h2>
                        <div class="flex items-center gap-4 text-[10px] font-black uppercase tracking-widest opacity-20 hover:opacity-40 transition-opacity cursor-pointer">
                            <span>Sort By Date</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m6 9 6 6 6-6"/></svg>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach($salesPages as $page)
                            <div class="group relative bg-base-100 rounded-2xl border border-base-content/5 hover:border-primary/40 transition-all duration-500 overflow-hidden shadow-sm hover:shadow-2xl hover:shadow-primary/5">
                                <div class="absolute inset-y-0 left-0 w-1 bg-primary transform scale-y-0 group-hover:scale-y-100 transition-transform duration-500"></div>
                                
                                <div class="p-6 md:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-4 mb-3">
                                            <div class="text-[10px] font-black uppercase tracking-widest px-2 py-1 rounded bg-primary/5 text-primary">
                                                ID: {{ str_pad($page->id, 4, '0', STR_PAD_LEFT) }}
                                            </div>
                                            <span class="text-[10px] font-bold uppercase tracking-widest opacity-30">
                                                {{ $page->created_at->format('M d, Y') }}
                                            </span>
                                        </div>
                                        
                                        <h3 class="text-2xl font-black tracking-tighter uppercase italic group-hover:text-primary transition-colors truncate">
                                            {{ $page->product_name }}
                                        </h3>
                                        
                                        @if($page->generated_content)
                                            <p class="text-sm text-base-content/40 font-medium mt-2 line-clamp-1 max-w-md">
                                                {{ $page->generated_content['headline'] ?? 'System pending content generation...' }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('pages.show', $page) }}" 
                                           class="btn btn-sm btn-ghost rounded-lg font-black uppercase tracking-widest text-[10px] opacity-40 hover:opacity-100 hover:bg-primary/10">
                                            Preview
                                        </a>

                                        {{-- Actions Dropdown (Simplified for Editorial Look) --}}
                                        <div class="flex items-center bg-base-200/50 rounded-xl p-1">
                                            <form action="{{ route('pages.regenerate', $page) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="p-2 hover:text-primary transition-colors" title="Regenerate">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M3 21v-5h5"/></svg>
                                                </button>
                                            </form>
                                            
                                            <div class="w-px h-4 bg-base-content/5"></div>

                                            <form action="{{ route('pages.destroy', $page) }}" method="POST" class="inline"
                                                  x-data @submit.prevent="if(confirm('Delete permanently?')) $el.submit()">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-2 hover:text-error transition-colors" title="Delete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </main>
</body>
</html>
