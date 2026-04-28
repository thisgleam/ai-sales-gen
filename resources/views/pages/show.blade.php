<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ ($salesPage->generated_content['style'] ?? 'modern') === 'modern' ? 'light' : (($salesPage->generated_content['style'] ?? '') === 'night' ? 'night' : 'luxury') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $salesPage->product_name }} - AI Sales Gen</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@300;400;600;700;800;900&family=Playfair+Display:wght@700;800;900&family=Source+Serif+4:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .font-pair-sans {
            --page-font-body: 'Inter', sans-serif;
            --page-font-heading: 'Outfit', sans-serif;
        }

        .font-pair-serif {
            --page-font-body: 'Source Serif 4', serif;
            --page-font-heading: 'Playfair Display', serif;
        }

        body { font-family: var(--page-font-body, 'Inter', sans-serif); }
        h1, h2, h3, blockquote { font-family: var(--page-font-heading, 'Outfit', sans-serif); }
        [x-cloak] { display: none !important; }
        .section-enter { animation: fadeUp 0.6s ease both; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
    <script>
        function salesPageEditor(initialState = {}) {
            return {
                loading: false,
                error: initialState.error || '',
                editModal: false,
                editData: {
                    section: '',
                    title: '',
                    value: '',
                    type: 'text',
                    fileName: '',
                    previewUrl: '',
                    previewKind: 'image',
                    previewBroken: false,
                },
                openEdit(detail) {
                    this.releaseMediaPreview();
                    this.resetMediaFileInput();
                    this.editData = {
                        section: '',
                        title: '',
                        value: '',
                        type: 'text',
                        fileName: '',
                        previewUrl: '',
                        previewKind: 'image',
                        previewBroken: false,
                        ...detail,
                    };

                    if (this.editData.type === 'file') {
                        this.setMediaUrlPreview(this.editData.value || '');
                    }

                    this.editModal = true;
                    this.$nextTick(() => this.resetMediaFileInput());
                },
                closeEdit() {
                    this.releaseMediaPreview();
                    this.resetMediaFileInput();
                    this.editData.fileName = '';
                    this.editData.previewUrl = '';
                    this.editData.previewBroken = false;
                    this.editModal = false;
                },
                handleMediaFileChange(event) {
                    const file = event.target.files?.[0];
                    this.releaseMediaPreview();
                    this.editData.previewBroken = false;

                    if (!file) {
                        this.editData.fileName = '';
                        this.setMediaUrlPreview(this.editData.value || '');
                        return;
                    }

                    this.editData.fileName = file.name;
                    this.editData.value = '';
                    this.editData.previewUrl = URL.createObjectURL(file);
                    this.editData.previewKind = file.type.startsWith('video/') ? 'video' : 'image';
                },
                handleMediaUrlInput(value) {
                    this.editData.value = value;
                    this.editData.fileName = '';
                    this.resetMediaFileInput();
                    this.setMediaUrlPreview(value);
                },
                setMediaUrlPreview(url) {
                    this.releaseMediaPreview();
                    this.editData.previewUrl = url || '';
                    this.editData.previewKind = this.detectMediaKind(url);
                    this.editData.previewBroken = false;
                },
                detectMediaKind(url) {
                    const cleanUrl = (url || '').split('?')[0].toLowerCase();

                    return /\.(mp4|webm|ogg)$/.test(cleanUrl) ? 'video' : 'image';
                },
                releaseMediaPreview() {
                    if (this.editData.previewUrl?.startsWith('blob:')) {
                        URL.revokeObjectURL(this.editData.previewUrl);
                    }
                },
                resetMediaFileInput() {
                    if (this.$refs.mediaFileInput) {
                        this.$refs.mediaFileInput.value = '';
                    }
                },
            };
        }
    </script>
</head>
    @php 
        $content = $salesPage->generated_content ?? []; 
        $style = $content['style'] ?? 'modern';
        $fontPair = in_array(($content['font_pair'] ?? 'sans'), ['sans', 'serif'], true) ? ($content['font_pair'] ?? 'sans') : 'sans';
        $fontPairLabels = [
            'sans' => 'Sans',
            'serif' => 'Serif',
        ];
        $styles = [
            'modern' => [
                'body' => 'bg-white text-slate-900',
                'section_b' => 'bg-gray-50',
                'card' => 'bg-gray-50 border-gray-100',
                'accent' => 'text-primary',
                'btn' => 'bg-gray-900 text-white',
                'hero_border' => 'border-gray-50',
                'text_muted' => 'text-gray-400'
            ],
            'night' => [
                'body' => 'bg-slate-950 text-white',
                'section_b' => 'bg-slate-900',
                'card' => 'bg-slate-800 border-slate-700',
                'accent' => 'text-primary',
                'btn' => 'bg-primary text-white shadow-primary/20',
                'hero_border' => 'border-slate-800',
                'text_muted' => 'text-slate-500'
            ],
            'glass' => [
                'body' => 'bg-indigo-950 text-white selection:bg-white selection:text-indigo-900',
                'section_b' => 'bg-white/5 backdrop-blur-xl',
                'card' => 'bg-white/10 backdrop-blur-2xl border-white/10',
                'accent' => 'text-sky-400',
                'btn' => 'bg-white text-indigo-950 shadow-white/10',
                'hero_border' => 'border-white/5',
                'text_muted' => 'text-indigo-300/60'
            ]
        ];
        $s = $styles[$style] ?? $styles['modern'];
    @endphp

    <body x-data="salesPageEditor({ error: @js(session('error')) })"
    @open-edit.window="openEdit($event.detail)"
    class="antialiased font-pair-{{ $fontPair }}"
    data-font-pair="{{ $fontPair }}">
    
    {{-- Loading Overlay --}}
    <div x-show="loading" x-cloak class="fixed inset-0 z-[100] bg-slate-950/90 backdrop-blur-2xl flex flex-col items-center justify-center animate-in fade-in duration-500">
        <div class="relative w-32 h-32 mb-8">
            <div class="absolute inset-0 border-4 border-primary/20 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-t-primary rounded-full animate-spin"></div>
        </div>
        <h2 class="text-3xl font-black italic uppercase tracking-widest text-white animate-pulse">Regenerating...</h2>
        <p class="text-slate-500 mt-4 font-medium uppercase tracking-[0.3em] text-[10px]">Updating your sales engine</p>
    </div>

    {{-- Error Alert --}}
    <template x-if="error">
        <div class="fixed top-8 left-1/2 -translate-x-1/2 z-[110] bg-error text-error-content px-8 py-4 rounded-full shadow-2xl flex items-center gap-4 animate-in slide-in-from-top-8 duration-500">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span class="font-bold uppercase tracking-tighter italic" x-text="error"></span>
            <button @click="error = false" class="hover:opacity-50 ml-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
    </template>

    <main class="{{ $s['body'] }} min-h-screen selection:bg-primary selection:text-white pb-32 transition-colors duration-700">
    
    @auth
        @if(auth()->id() === $salesPage->user_id)
            <div class="fixed top-8 right-8 z-[60] flex flex-col gap-4 pointer-events-none">
                <div class="pointer-events-auto bg-base-100/80 backdrop-blur-xl rounded-3xl shadow-2xl shadow-black/20 border border-base-content/10 p-2 flex flex-col gap-1 transition-colors">
                    <a href="{{ route('dashboard') }}" class="btn btn-ghost btn-square btn-sm rounded-2xl" title="Dashboard">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                    </a>
            <div class="divider m-0 opacity-5"></div>
            {{-- Style Switcher --}}
            <div class="dropdown dropdown-left">
                <button tabindex="0" class="btn btn-ghost btn-square btn-sm rounded-2xl" title="Change Style">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m19 21-7-7"/><path d="M15.3 16.5 21 19l-7-7 2.5-5.7L19 15.3l-2.5 5.7Z"/><path d="m15.3 16.5-2.5 5.7L7 15.3l5.7-2.5 2.6 3.7Z"/><path d="M9 11 3 14l7-7-2.5-5.7L4 9l2.5 5.7Z"/><path d="M9 11 6.5 5.3 14 9l-5.7 2.5-1.8-0.5Z"/></svg>
                </button>
                <ul tabindex="0" class="dropdown-content z-[100] menu p-1.5 shadow-[0_20px_50px_rgba(0,0,0,0.15)] bg-white/95 backdrop-blur-2xl rounded-[2rem] w-40 border border-slate-200/60 gap-1 animate-in fade-in zoom-in-95 duration-200">
                    <li class="menu-title px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Theme</li>
                    <li>
                        <form action="{{ route('pages.update-style', $salesPage) }}" method="POST" @submit="loading = true">
                            @csrf <input type="hidden" name="style" value="modern">
                            <button type="submit" class="flex justify-between items-center px-4 py-3 rounded-2xl transition-all duration-300 {{ ($style === 'modern') ? 'bg-primary text-white shadow-xl shadow-primary/20 font-bold' : 'text-slate-900 hover:bg-slate-100 font-medium' }}">
                                <span class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full {{ ($style === 'modern') ? 'bg-white' : 'bg-slate-900' }}"></div>
                                    Modern
                                </span>
                                @if($style === 'modern')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                @endif
                            </button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('pages.update-style', $salesPage) }}" method="POST" @submit="loading = true">
                            @csrf <input type="hidden" name="style" value="night">
                            <button type="submit" class="flex justify-between items-center px-4 py-3 rounded-2xl transition-all duration-300 {{ ($style === 'night') ? 'bg-primary text-white shadow-xl shadow-primary/20 font-bold' : 'text-slate-900 hover:bg-slate-100 font-medium' }}">
                                <span class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full {{ ($style === 'night') ? 'bg-white' : 'bg-slate-400' }}"></div>
                                    Night
                                </span>
                                @if($style === 'night')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                @endif
                            </button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('pages.update-style', $salesPage) }}" method="POST" @submit="loading = true">
                            @csrf <input type="hidden" name="style" value="glass">
                            <button type="submit" class="flex justify-between items-center px-4 py-3 rounded-2xl transition-all duration-300 {{ ($style === 'glass') ? 'bg-primary text-white shadow-xl shadow-primary/20 font-bold' : 'text-slate-900 hover:bg-slate-100 font-medium' }}">
                                <span class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full {{ ($style === 'glass') ? 'bg-white' : 'bg-primary' }}"></div>
                                    Glass
                                </span>
                                @if($style === 'glass')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                @endif
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
            <div class="divider m-0 opacity-5"></div>
            {{-- Font Switcher --}}
            <div class="dropdown dropdown-left">
                <button tabindex="0" class="btn btn-ghost btn-square btn-sm rounded-2xl" title="Change Font Pair">
                    <span class="text-sm font-black leading-none">Aa</span>
                </button>
                <ul tabindex="0" class="dropdown-content z-[100] menu p-1.5 shadow-[0_20px_50px_rgba(0,0,0,0.15)] bg-white/95 backdrop-blur-2xl rounded-[2rem] w-40 border border-slate-200/60 gap-1 animate-in fade-in zoom-in-95 duration-200">
                    <li class="menu-title px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Fonts</li>
                    @foreach($fontPairLabels as $fontPairValue => $fontPairLabel)
                    <li>
                        <form action="{{ route('pages.update-font-pair', $salesPage) }}" method="POST" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="font_pair" value="{{ $fontPairValue }}">
                            <button type="submit" class="flex justify-between items-center px-4 py-3 rounded-2xl transition-all duration-300 {{ ($fontPair === $fontPairValue) ? 'bg-primary text-white shadow-xl shadow-primary/20 font-bold' : 'text-slate-900 hover:bg-slate-100 font-medium' }}">
                                <span class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full {{ ($fontPair === $fontPairValue) ? 'bg-white' : 'bg-slate-400' }}"></div>
                                    {{ $fontPairLabel }}
                                </span>
                                @if($fontPair === $fontPairValue)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                @endif
                            </button>
                        </form>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="divider m-0 opacity-5"></div>
            <form action="{{ route('pages.regenerate', $salesPage) }}" method="POST" @submit="loading = true">
                @csrf
                <button type="submit" class="btn btn-ghost btn-square btn-sm rounded-2xl" title="Full Regenerate" onclick="return confirm('Regenerate everything?')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
                </button>
            </form>
            <form action="{{ route('pages.destroy', $salesPage) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-ghost btn-square btn-sm rounded-2xl text-error" title="Delete" onclick="return confirm('Delete permanently?')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                </button>
            </form>
        </div>
    </div>

        @endif
    @endauth

    {{-- Success/Error Alerts --}}
        @if(session('success'))
            <div class="fixed top-8 left-1/2 -translate-x-1/2 z-[100] px-6 py-3 bg-gray-900 text-white rounded-full shadow-2xl animate-bounce">
                <p class="text-[10px] font-black uppercase tracking-widest">{{ session('success') }}</p>
            </div>
        @endif

        @php
            $order = $content['order'] ?? ['hero', 'media', 'product_vision', 'benefits', 'features', 'proof', 'pricing'];
        @endphp

        @foreach($order as $sectionKey)
            <div class="relative group/section-wrapper">
                @if(auth()->id() === $salesPage->user_id)
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 opacity-0 group-hover/section-wrapper:opacity-100 transition-opacity z-[60] flex flex-col gap-2">
                        @if(!$loop->first)
                        <form action="{{ route('pages.reorder', $salesPage) }}" method="POST">
                            @csrf
                            <input type="hidden" name="section" value="{{ $sectionKey }}">
                            <input type="hidden" name="direction" value="up">
                            <button type="submit" class="btn btn-circle btn-sm btn-base-200 shadow-2xl border-2 border-base-300 hover:btn-primary" title="Move Up">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m18 15-6-6-6 6"/></svg>
                            </button>
                        </form>
                        @endif
                        @if(!$loop->last)
                        <form action="{{ route('pages.reorder', $salesPage) }}" method="POST">
                            @csrf
                            <input type="hidden" name="section" value="{{ $sectionKey }}">
                            <input type="hidden" name="direction" value="down">
                            <button type="submit" class="btn btn-circle btn-sm btn-base-200 shadow-2xl border-2 border-base-300 hover:btn-primary" title="Move Down">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                @endif
                
                @switch($sectionKey)
                    @case('hero')
                        {{-- Hero --}}
                        <section class="relative pt-32 pb-24 px-6 md:px-12 overflow-hidden border-b {{ $s['hero_border'] }}">
            @if($style === 'glass')
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/20 via-transparent to-purple-600/20 -z-10"></div>
                <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary/20 rounded-full blur-[120px] -z-10"></div>
            @endif
            <div class="max-w-5xl mx-auto text-center relative group">
                @if(auth()->id() === $salesPage->user_id)
                {{-- Headline Actions --}}
                <div class="absolute -top-4 -right-4 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex gap-2">
                    <button @click="$dispatch('open-edit', { section: 'headline', title: 'Edit Headline', value: {{ json_encode($content['headline'] ?? '') }} })" class="btn btn-circle btn-xs btn-neutral shadow-xl" title="Edit Headline">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    </button>
                    <form action="{{ route('pages.regenerate-section', $salesPage) }}" method="POST" @submit="loading = true">
                        @csrf
                        <input type="hidden" name="section" value="headline">
                        <button type="submit" class="btn btn-circle btn-xs btn-primary shadow-xl" title="Regenerate Headline">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        </button>
                    </form>
                </div>
                @endif

                <h1 class="text-6xl md:text-8xl font-black tracking-tighter uppercase italic leading-[0.85] mb-12 animate-in fade-in slide-in-from-bottom-8 duration-700">
                    {{ $content['headline'] ?? '' }}
                </h1>
                
                <div class="relative group max-w-2xl mx-auto">
                    @if(auth()->id() === $salesPage->user_id)
                    {{-- Sub-headline Actions --}}
                    <div class="absolute -top-4 -right-4 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex gap-2">
                        <button @click="$dispatch('open-edit', { section: 'sub_headline', title: 'Edit Sub-headline', value: {{ json_encode($content['sub_headline'] ?? '') }} })" class="btn btn-circle btn-xs btn-neutral shadow-xl" title="Edit Sub-headline">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                        </button>
                        <form action="{{ route('pages.regenerate-section', $salesPage) }}" method="POST" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="section" value="sub_headline">
                            <button type="submit" class="btn btn-circle btn-xs btn-primary shadow-xl" title="Regenerate Sub-headline">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                            </button>
                        </form>
                    </div>
                    @endif
                    <p class="text-xl md:text-2xl {{ $s['text_muted'] }} font-medium leading-relaxed mb-16">
                        {{ $content['sub_headline'] ?? '' }}
                    </p>
                </div>

                <div class="relative group inline-block">
                    @if(auth()->id() === $salesPage->user_id)
                    {{-- CTA Actions --}}
                    <div class="absolute -top-4 -right-4 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex gap-2">
                        <button @click="$dispatch('open-edit', { section: 'call_to_action', title: 'Edit Call to Action', value: {{ json_encode($content['call_to_action'] ?? '') }} })" class="btn btn-circle btn-xs btn-neutral shadow-xl" title="Edit CTA">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                        </button>
                        <form action="{{ route('pages.regenerate-section', $salesPage) }}" method="POST" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="section" value="call_to_action">
                            <button type="submit" class="btn btn-circle btn-xs btn-primary shadow-xl" title="Regenerate CTA">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                            </button>
                        </form>
                    </div>
                    @endif
                    <a href="#" class="px-12 py-6 {{ $s['btn'] }} rounded-full font-black uppercase tracking-widest italic hover:scale-105 transition-transform inline-block shadow-2xl">
                        {{ $content['call_to_action'] ?? 'Get Started' }}
                    </a>
                </div>
            </div>
        </section>

                        @break
                    
                    @case('media')
                        {{-- Media Placeholder --}}
                        <section class="py-12 px-6 md:px-12">
            <div class="max-w-5xl mx-auto">
                <div class="relative group aspect-video rounded-[3rem] overflow-hidden border-8 {{ $s['hero_border'] }} bg-slate-900/5 flex flex-col items-center justify-center transition-all hover:scale-[1.01] hover:shadow-2xl">
                    @php $mediaUrl = $content['media_url'] ?? ''; @endphp
                    @if(empty($mediaUrl))
                        <div class="text-center p-12">
                            <div class="w-20 h-20 bg-primary/10 rounded-3xl flex items-center justify-center mx-auto mb-6 text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                            </div>
                            <h3 class="text-2xl font-black uppercase italic tracking-tighter mb-2">Media Placeholder</h3>
                            <p class="{{ $s['text_muted'] }} font-medium max-w-xs mx-auto">Upload a product demo video or a high-quality product shot here.</p>
                        </div>
                    @else
                        @php 
                            $isDirectVideo = Str::contains($mediaUrl, ['.mp4', '.webm', '.ogg']);
                            $isYoutube = Str::contains($mediaUrl, ['youtube.com', 'youtu.be']);
                            $isVimeo = Str::contains($mediaUrl, 'vimeo.com');
                            
                            $finalUrl = $mediaUrl;
                            if ($isYoutube) {
                                // Extract ID and ensure embed format
                                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $mediaUrl, $matches);
                                if (!empty($matches[1])) {
                                    $videoId = $matches[1];
                                    $finalUrl = "https://www.youtube.com/embed/{$videoId}?autoplay=1&mute=1&loop=1&playlist={$videoId}";
                                }
                            } elseif ($isVimeo) {
                                preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $mediaUrl, $matches);
                                if (!empty($matches[1])) {
                                    $videoId = $matches[1];
                                    $finalUrl = "https://player.vimeo.com/video/{$videoId}?autoplay=1&muted=1&loop=1";
                                }
                            }
                        @endphp

                        @if($isDirectVideo)
                            <video src="{{ $mediaUrl }}" class="w-full h-full object-cover" autoplay loop muted playsinline></video>
                        @elseif($isYoutube || $isVimeo)
                            <iframe src="{{ $finalUrl }}" class="w-full h-full border-0" allow="autoplay; fullscreen" allowfullscreen></iframe>
                        @else
                            <img src="{{ $mediaUrl }}" alt="Product Media" class="w-full h-full object-cover">
                        @endif
                    @endif
                    
                    @if(auth()->id() === $salesPage->user_id)
                    <div class="absolute top-8 right-8 opacity-0 group-hover:opacity-100 transition-all flex gap-2">
                        <button @click="$dispatch('open-edit', { section: 'media_url', title: 'Upload Product Media', type: 'file', value: {{ json_encode($mediaUrl) }} })" class="btn btn-circle btn-primary shadow-xl">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </section>

                        @break

                    @case('product_vision')
                        {{-- Product Vision --}}
                        <section class="py-32 px-6 md:px-12 {{ $s['section_b'] }}">
            <div class="max-w-3xl mx-auto text-center relative group">
                @if(auth()->id() === $salesPage->user_id)
                {{-- Description Actions --}}
                <div class="absolute -top-4 -right-4 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex gap-2">
                    <button @click="$dispatch('open-edit', { section: 'product_description', title: 'Edit Description', value: {{ json_encode($content['product_description'] ?? '') }} })" class="btn btn-circle btn-xs btn-neutral shadow-xl" title="Edit Description">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    </button>
                    <form action="{{ route('pages.regenerate-section', $salesPage) }}" method="POST" @submit="loading = true">
                        @csrf
                        <input type="hidden" name="section" value="product_description">
                        <button type="submit" class="btn btn-circle btn-xs btn-primary shadow-xl" title="Regenerate Description">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        </button>
                    </form>
                </div>
                @endif
                <p class="text-[10px] font-black uppercase tracking-[0.5em] {{ $s['accent'] }} mb-12">The Thesis</p>
                <p class="text-2xl md:text-3xl font-bold leading-snug">
                    {{ $content['product_description'] ?? '' }}
                </p>
            </div>
        </section>

                        @break

                    @case('benefits')
                        {{-- Benefits --}}
                        <section class="py-32 px-6 md:px-12">
            <div class="max-w-5xl mx-auto">
                <div class="flex items-end justify-between mb-20">
                    <h2 class="text-4xl font-black tracking-tighter uppercase italic">CORE<br><span class="{{ $s['accent'] }}">BENEFITS.</span></h2>
                    <div class="h-px flex-1 {{ $style === 'modern' ? 'bg-gray-100' : 'bg-white/10' }} mx-12 hidden md:block"></div>
                </div>
                
                <div class="grid md:grid-cols-2 gap-8 relative group">
                @if(auth()->id() === $salesPage->user_id)
                    {{-- Benefits Actions --}}
                    <div class="absolute -top-4 -right-4 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex gap-2">
                        <button @click="$dispatch('open-edit', { section: 'benefits', title: 'Edit Benefits (One per line)', value: {{ json_encode(implode("\n", $content['benefits'] ?? [])) }}, type: 'list' })" class="btn btn-circle btn-xs btn-neutral shadow-xl" title="Edit Benefits">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                        </button>
                        <form action="{{ route('pages.regenerate-section', $salesPage) }}" method="POST" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="section" value="benefits">
                            <button type="submit" class="btn btn-circle btn-xs btn-primary shadow-xl" title="Regenerate Benefits">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                            </button>
                        </form>
                    </div>
                    @endif
                    @foreach($content['benefits'] ?? [] as $benefit)
                        <div class="p-10 {{ $s['card'] }} rounded-[2.5rem] border hover:shadow-2xl transition-all duration-500 group/item">
                            <div class="w-12 h-12 bg-gray-900 rounded-2xl flex items-center justify-center mb-8 group-hover/item:bg-primary transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>
                            </div>
                            <p class="text-2xl font-black uppercase italic tracking-tighter leading-tight">{{ $benefit }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

                        @break

                    @case('features')
                        {{-- Features --}}
                        <section class="py-32 px-6 md:px-12 {{ $style === 'modern' ? 'bg-gray-900 text-white' : $s['section_b'] }} overflow-hidden relative">
            <div class="absolute top-0 right-0 p-24 opacity-[0.03] rotate-12">
                <svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
            </div>

            <div class="max-w-5xl mx-auto relative group">
                @if(auth()->id() === $salesPage->user_id)
                {{-- Features Actions --}}
                <div class="absolute -top-4 -right-4 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex gap-2">
                    <button @click="$dispatch('open-edit', { section: 'features', title: 'Edit Features (JSON Format)', value: {{ json_encode(json_encode($content['features'] ?? [])) }}, type: 'json' })" class="btn btn-circle btn-xs btn-neutral shadow-xl" title="Edit Features">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    </button>
                    <form action="{{ route('pages.regenerate-section', $salesPage) }}" method="POST" @submit="loading = true">
                        @csrf
                        <input type="hidden" name="section" value="features">
                        <button type="submit" class="btn btn-circle btn-xs btn-primary shadow-xl" title="Regenerate Features">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        </button>
                    </form>
                </div>
                @endif
                <p class="text-[10px] font-black uppercase tracking-[0.5em] {{ $s['accent'] }} mb-20">System Specifications</p>
                <div class="grid md:grid-cols-3 gap-16">
                    @foreach($content['features'] ?? [] as $feature)
                        <div class="space-y-6">
                            <div class="h-px w-12 bg-primary"></div>
                            <h3 class="text-xl font-black uppercase tracking-widest">{{ $feature['name'] ?? '' }}</h3>
                            <p class="{{ $s['text_muted'] }} font-medium leading-relaxed">{{ $feature['description'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

                        @break

                    @case('proof')
                        {{-- Proof --}}
                        <section class="py-32 px-6 md:px-12">
            <div class="max-w-4xl mx-auto text-center relative group">
                @if(auth()->id() === $salesPage->user_id)
                {{-- Social Proof Actions --}}
                <div class="absolute -top-4 -right-4 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex gap-2">
                    <button @click="$dispatch('open-edit', { section: 'social_proof_placeholder', title: 'Edit Testimonial', value: {{ json_encode($content['social_proof_placeholder'] ?? '') }} })" class="btn btn-circle btn-xs btn-neutral shadow-xl" title="Edit Testimonial">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    </button>
                    <form action="{{ route('pages.regenerate-section', $salesPage) }}" method="POST" @submit="loading = true">
                        @csrf
                        <input type="hidden" name="section" value="social_proof_placeholder">
                        <button type="submit" class="btn btn-circle btn-xs btn-primary shadow-xl" title="Regenerate Testimonial">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        </button>
                    </form>
                </div>
                @endif
                <div class="inline-block p-4 {{ $style === 'modern' ? 'bg-gray-50' : 'bg-white/5' }} rounded-full mb-12">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="{{ $s['accent'] }}"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                </div>
                <blockquote class="text-4xl md:text-5xl font-black italic tracking-tighter leading-[0.9] mb-8">
                    "{{ $content['social_proof_placeholder'] ?? '' }}"
                </blockquote>
                <p class="text-[10px] font-black uppercase tracking-[0.5em] {{ $s['text_muted'] }}">Certified User Sentiment</p>
            </div>
        </section>

                        @break

                    @case('pricing')
                        {{-- Footer / Pricing --}}
                        <footer class="py-32 px-6 md:px-12 {{ $s['section_b'] }} border-t {{ $s['hero_border'] }} text-center">
            <div class="max-w-2xl mx-auto relative group">
                @if(auth()->id() === $salesPage->user_id)
                {{-- Pricing Actions --}}
                <div class="absolute -top-4 -right-4 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex gap-2">
                    <button @click="$dispatch('open-edit', { section: 'pricing_display', title: 'Edit Pricing', value: {{ json_encode($content['pricing_display'] ?? '') }} })" class="btn btn-circle btn-xs btn-neutral shadow-xl" title="Edit Pricing">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    </button>
                    <form action="{{ route('pages.regenerate-section', $salesPage) }}" method="POST" @submit="loading = true">
                        @csrf
                        <input type="hidden" name="section" value="pricing_display">
                        <button type="submit" class="btn btn-circle btn-xs btn-primary shadow-xl" title="Regenerate Pricing">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        </button>
                    </form>
                </div>
                @endif
                <h2 class="text-5xl md:text-7xl font-black tracking-tighter uppercase italic mb-8 leading-none">
                    {{ $content['pricing_display'] ?? '' }}
                </h2>
                <p class="{{ $s['text_muted'] }} font-medium mb-16">No commitments. Pure transformation.</p>
                
                <div class="relative group inline-block">
                    @if(auth()->id() === $salesPage->user_id)
                    {{-- CTA Actions --}}
                    <div class="absolute -top-4 -right-4 opacity-0 group-hover:opacity-100 transition-opacity z-20 flex gap-2">
                        <button @click="$dispatch('open-edit', { section: 'call_to_action', title: 'Edit Call to Action', value: {{ json_encode($content['call_to_action'] ?? '') }} })" class="btn btn-circle btn-xs btn-neutral shadow-xl" title="Edit CTA">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                        </button>
                        <form action="{{ route('pages.regenerate-section', $salesPage) }}" method="POST" @submit="loading = true">
                            @csrf
                            <input type="hidden" name="section" value="call_to_action">
                            <button type="submit" class="btn btn-circle btn-xs btn-primary shadow-xl" title="Regenerate CTA">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                            </button>
                        </form>
                    </div>
                    @endif
                    <a href="#" class="px-20 py-8 {{ $s['btn'] }} rounded-full font-black uppercase tracking-[0.2em] italic text-xl shadow-2xl hover:scale-105 transition-all">
                        {{ $content['call_to_action'] ?? 'Get Started' }}
                    </a>
                </div>
            </div>
        </footer>
                        @break
                @endswitch
            </div>
        @endforeach
    </main>

    {{-- Edit Modal --}}
    <div x-show="editModal" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center p-6">
        <div @click="closeEdit()" class="absolute inset-0 bg-slate-950/60 backdrop-blur-md animate-in fade-in duration-300"></div>
        <div class="relative w-full max-w-xl bg-white rounded-[3rem] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
            <div class="p-12">
                <h3 class="text-3xl font-black uppercase italic tracking-tighter mb-8" x-text="editData.title"></h3>
                
                <form action="{{ route('pages.update-section', $salesPage) }}" method="POST" enctype="multipart/form-data" @submit="loading = true; editModal = false">
                    @csrf
                    <input type="hidden" name="section" :value="editData.section">
                    
                    <div x-show="editData.type === 'text' || editData.type === 'list' || editData.type === 'json'">
                        <textarea 
                            name="value" 
                            x-model="editData.value" 
                            class="w-full bg-slate-50 border-2 border-slate-100 rounded-3xl p-6 text-lg font-medium focus:outline-none focus:border-primary transition-colors min-h-[200px]"
                            placeholder="Enter content..."
                        ></textarea>
                        <p x-show="editData.type === 'list'" class="text-[10px] uppercase font-bold text-slate-400 mt-4 tracking-widest">Tip: Enter each item on a new line</p>
                        <p x-show="editData.type === 'json'" class="text-[10px] uppercase font-bold text-slate-400 mt-4 tracking-widest">Tip: Valid JSON format required</p>
                    </div>

                    <div x-show="editData.type === 'file'" class="space-y-6">
                        <div
                            x-show="editData.previewUrl"
                            x-transition.opacity
                            data-testid="media-modal-preview"
                            class="relative aspect-video overflow-hidden rounded-[2rem] bg-slate-100 border-2 border-slate-100"
                        >
                            <template x-if="editData.previewKind === 'video'">
                                <video
                                    :src="editData.previewUrl"
                                    class="w-full h-full object-cover"
                                    controls
                                    muted
                                    playsinline
                                    x-on:error="editData.previewBroken = true"
                                ></video>
                            </template>
                            <template x-if="editData.previewKind !== 'video'">
                                <img
                                    :src="editData.previewUrl"
                                    alt=""
                                    class="w-full h-full object-cover"
                                    x-on:load="editData.previewBroken = false"
                                    x-on:error="editData.previewBroken = true"
                                >
                            </template>
                            <div x-show="editData.previewBroken" class="absolute inset-0 flex items-center justify-center bg-slate-100 text-xs font-black uppercase tracking-widest text-slate-400">
                                Preview unavailable
                            </div>
                        </div>
                        <div class="flex flex-col items-center justify-center border-4 border-dashed border-slate-100 rounded-[2rem] p-12 bg-slate-50 hover:bg-slate-100/50 transition-colors relative group">
                            <input x-ref="mediaFileInput" type="file" name="file" accept="image/*,video/*" class="absolute inset-0 opacity-0 cursor-pointer z-10" @change="handleMediaFileChange($event)">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-4 text-primary group-hover:scale-110 transition-transform">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                </div>
                                <p class="text-sm font-black uppercase tracking-widest text-slate-400" x-text="editData.fileName || 'Select image or video'"></p>
                            </div>
                        </div>
                        <div class="divider">OR</div>
                        <input type="url" name="value" x-model="editData.value" @input="handleMediaUrlInput($event.target.value)" class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-6 py-4 text-sm font-medium focus:outline-none focus:border-primary" placeholder="eg. https://i.imgur.com/example.jpeg">
                    </div>

                    <div class="flex gap-4 mt-10">
                        <button type="submit" class="flex-1 bg-primary text-white py-5 rounded-2xl font-black uppercase tracking-widest italic shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all">
                            Save Changes
                        </button>
                        <button type="button" @click="closeEdit()" class="px-8 bg-slate-100 text-slate-900 py-5 rounded-2xl font-black uppercase tracking-widest italic hover:bg-slate-200 transition-all">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
