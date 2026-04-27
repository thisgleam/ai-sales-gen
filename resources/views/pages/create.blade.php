<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="night">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Sales Page - AI Sales Gen</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        [x-cloak] { display: none !important; }
        .form-section { transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); }

        @keyframes progress-pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        .progress-pulse { animation: progress-pulse 1.8s ease-in-out infinite; }

        @keyframes step-in {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .step-in { animation: step-in 0.4s ease both; }
    </style>
</head>
<body class="bg-base-200 min-h-screen antialiased selection:bg-primary selection:text-primary-content">

    {{-- ===== AI GENERATION LOADING OVERLAY ===== --}}
    <div id="ai-loading-overlay"
         x-data="aiLoader()"
         x-show="active"
         x-cloak
         @ai-generate-start.window="start()"
         class="fixed inset-0 z-[999] bg-base-100 flex flex-col items-center justify-center px-8">

        {{-- Animated grid background --}}
        <div class="absolute inset-0 opacity-[0.025]" style="background-image: linear-gradient(to right, white 1px, transparent 1px), linear-gradient(to bottom, white 1px, transparent 1px); background-size: 40px 40px;"></div>

        <div class="relative z-10 text-center max-w-md w-full">
            {{-- Brand --}}
            <p class="text-[10px] font-black uppercase tracking-[0.5em] text-primary mb-12 progress-pulse">AI Sales Gen • Generating</p>

            {{-- Spinning indicator --}}
            <div class="flex justify-center mb-12">
                <div class="relative w-16 h-16">
                    <svg class="w-full h-full animate-spin" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="2" class="opacity-10"/>
                        <path d="M32 4a28 28 0 0 1 28 28" stroke="currentColor" stroke-width="3" stroke-linecap="round" class="text-primary"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-2 h-2 rounded-full bg-primary progress-pulse"></div>
                    </div>
                </div>
            </div>

            {{-- Step message --}}
            <div class="h-16 flex flex-col items-center justify-center">
                <p class="text-2xl font-black uppercase italic tracking-tight step-in" :key="currentStep" x-text="steps[currentStep].label"></p>
                <p class="text-xs text-base-content/40 font-medium mt-2 step-in" :key="'sub-' + currentStep" x-text="steps[currentStep].sublabel"></p>
            </div>

            {{-- Step progress dots --}}
            <div class="flex justify-center gap-2 mt-10">
                <template x-for="(step, index) in steps" :key="index">
                    <div class="rounded-full transition-all duration-500"
                         :class="index === currentStep ? 'w-6 h-1.5 bg-primary' : 'w-1.5 h-1.5 bg-base-content/20'"></div>
                </template>
            </div>

            {{-- Progress bar --}}
            <div class="mt-8 h-px bg-base-content/10 rounded-full overflow-hidden">
                <div class="h-full bg-primary rounded-full transition-all duration-1000"
                     :style="'width: ' + progressPercent + '%'"></div>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-[0.3em] opacity-20 mt-3" x-text="progressPercent + '%'"></p>
        </div>
    </div>

    {{-- ===== ERROR ALERT ===== --}}
    @if(session('error'))
        <div class="fixed top-4 right-4 left-4 md:left-auto md:w-[420px] z-50 alert alert-error shadow-2xl"
             x-data="{ visible: true }"
             x-show="visible"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             x-cloak>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="flex-1 min-w-0">
                <p class="font-black text-sm">Generation Failed</p>
                <p class="text-xs opacity-80 mt-0.5 break-words">{{ session('error') }}</p>
            </div>
            <button @click="visible = false" class="btn btn-ghost btn-xs btn-circle ml-auto shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    @endif

    <script>
        function aiLoader() {
            return {
                active: false,
                currentStep: 0,
                progressPercent: 0,
                steps: [
                    { label: 'Analyzing Product',    sublabel: 'Understanding your value proposition...' },
                    { label: 'Crafting Headlines',    sublabel: 'Writing benefit-driven copy...' },
                    { label: 'Building Structure',   sublabel: 'Assembling features & benefits...' },
                    { label: 'Finalizing Content',   sublabel: 'Almost there, polishing the page...' },
                ],
                timer: null,
                start() {
                    this.active = true;
                    this.currentStep = 0;
                    this.progressPercent = 5;
                    let step = 0;
                    const intervals = [4000, 8000, 14000];
                    const progressTargets = [30, 55, 80];
                    intervals.forEach((delay, index) => {
                        setTimeout(() => {
                            if (!this.active) return;
                            step++;
                            this.currentStep = step;
                            this.progressPercent = progressTargets[index];
                        }, delay);
                    });
                    // Slow crawl to 95 while waiting
                    this.timer = setInterval(() => {
                        if (this.progressPercent < 95) this.progressPercent++;
                    }, 800);
                },
                stop() {
                    this.progressPercent = 100;
                    clearInterval(this.timer);
                    setTimeout(() => { this.active = false; }, 300);
                }
            };
        }
    </script>
    <div class="flex flex-col lg:flex-row h-screen overflow-hidden bg-white">
        {{-- Left: Scripting Lab (The Form) --}}
        <div class="flex-1 lg:max-w-[60%] flex flex-col p-6 md:p-16 lg:p-20 overflow-y-auto" 
             x-data="{ step: 1, totalSteps: 4 }">
            
            <header class="mb-20 flex justify-between items-start">
                <div>
                    <a href="{{ route('dashboard') }}" class="group flex items-center gap-2 text-gray-400 hover:text-primary transition-colors mb-8">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        <span class="text-[10px] font-black uppercase tracking-[0.3em]">Exit Lab</span>
                    </a>
                    <h1 class="text-7xl font-black tracking-tighter uppercase italic text-gray-900 leading-[0.8] mb-4">
                        NEW<br><span class="text-primary italic">SCRIPT.</span>
                    </h1>
                </div>
                
                {{-- Vertical Progress --}}
                <div class="flex flex-col gap-2 items-end">
                    <template x-for="i in totalSteps">
                        <div class="h-12 w-1 rounded-full transition-all duration-500"
                             :class="step >= i ? 'bg-primary' : 'bg-gray-100'"></div>
                    </template>
                </div>
            </header>

            <form action="{{ route('pages.store') }}" method="POST" class="space-y-16">
                @csrf

                @if ($errors->any())
                    <div class="p-6 bg-red-50 rounded-2xl border border-red-100 mb-12">
                        <div class="flex items-center gap-3 text-red-600 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12" y1="16" y2="16.01"/></svg>
                            <span class="font-black uppercase tracking-widest text-xs">Correction Needed</span>
                        </div>
                        <ul class="text-xs font-medium text-red-800/70 space-y-1 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Section 01: Identity --}}
                <section x-show="step === 1" x-transition:enter="form-section opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-12">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-[0.5em] text-primary">Stage 01</p>
                        <h2 class="text-4xl font-black tracking-tighter uppercase italic text-gray-900">IDENTITY.</h2>
                    </div>

                    <div class="space-y-12">
                        <div class="group">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-4 group-focus-within:text-primary transition-colors">Product Nomenclature</label>
                            <input type="text" name="product_name" required placeholder="What is it called?" 
                                class="w-full bg-transparent border-b-4 border-gray-100 py-4 text-3xl font-black text-gray-900 placeholder:text-gray-200 focus:outline-none focus:border-primary transition-all">
                        </div>
                        <div class="group">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-4 group-focus-within:text-primary transition-colors">The Narrative Pitch</label>
                            <textarea name="description" required rows="3" placeholder="Define the problem you solve..." 
                                class="w-full bg-transparent border-b-4 border-gray-100 py-4 text-xl font-bold text-gray-900 placeholder:text-gray-200 focus:outline-none focus:border-primary transition-all resize-none"></textarea>
                        </div>
                    </div>

                    <div class="pt-8">
                        <button type="button" @click="step = 2" class="w-full py-6 bg-gray-900 text-white rounded-2xl font-black uppercase tracking-widest italic hover:bg-primary transition-all shadow-xl shadow-gray-200">
                            Next Stage
                        </button>
                    </div>
                </section>

                {{-- Section 02: Utility --}}
                <section x-show="step === 2" x-cloak x-transition:enter="form-section opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-12">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-[0.5em] text-primary">Stage 02</p>
                        <h2 class="text-4xl font-black tracking-tighter uppercase italic text-gray-900">UTILITY.</h2>
                    </div>

                    <div class="space-y-12">
                        <div class="group">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-4 group-focus-within:text-primary transition-colors">Core Capabilities</label>
                            <textarea name="key_features" required rows="3" placeholder="Features, comma separated..." 
                                class="w-full bg-transparent border-b-4 border-gray-100 py-4 text-xl font-bold text-gray-900 placeholder:text-gray-200 focus:outline-none focus:border-primary transition-all resize-none"></textarea>
                        </div>
                        <div class="group">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-4 group-focus-within:text-primary transition-colors">Unique Selling Logic</label>
                            <input type="text" name="usp" required placeholder="Why does it win?" 
                                class="w-full bg-transparent border-b-4 border-gray-100 py-4 text-xl font-bold text-gray-900 placeholder:text-gray-200 focus:outline-none focus:border-primary transition-all">
                        </div>
                    </div>

                    <div class="pt-8 flex gap-4">
                        <button type="button" @click="step = 1" class="px-8 text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-900 transition-colors">Back</button>
                        <button type="button" @click="step = 3" class="flex-1 py-6 bg-gray-900 text-white rounded-2xl font-black uppercase tracking-widest italic hover:bg-primary transition-all shadow-xl shadow-gray-200">
                            Continue
                        </button>
                    </div>
                </section>

                {{-- Section 03: Market --}}
                <section x-show="step === 3" x-cloak x-transition:enter="form-section opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-12">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-[0.5em] text-primary">Stage 03</p>
                        <h2 class="text-4xl font-black tracking-tighter uppercase italic text-gray-900">MARKET.</h2>
                    </div>

                    <div class="space-y-12">
                        <div class="group">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-4 group-focus-within:text-primary transition-colors">Target Archetype</label>
                            <input type="text" name="target_audience" required placeholder="Who are we writing for?" 
                                class="w-full bg-transparent border-b-4 border-gray-100 py-4 text-xl font-bold text-gray-900 placeholder:text-gray-200 focus:outline-none focus:border-primary transition-all">
                        </div>
                        <div class="group">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-4 group-focus-within:text-primary transition-colors">Value Structure</label>
                            <input type="text" name="price" required placeholder="Price point or model..." 
                                class="w-full bg-transparent border-b-4 border-gray-100 py-4 text-xl font-bold text-gray-900 placeholder:text-gray-200 focus:outline-none focus:border-primary transition-all">
                        </div>
                    </div>

                    <div class="pt-8 flex gap-4">
                        <button type="button" @click="step = 2" class="px-8 text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-900 transition-colors">Back</button>
                        <button type="button" @click="step = 4" class="flex-1 py-6 bg-gray-900 text-white rounded-2xl font-black uppercase tracking-widest italic hover:bg-primary transition-all shadow-xl shadow-gray-200">
                            Analyze
                        </button>
                    </div>
                </section>

                {{-- Section 04: Launch --}}
                <section x-show="step === 4" x-cloak x-transition:enter="form-section opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-12">
                    <div class="text-center py-20 space-y-12" x-data="{ generating: false }">
                        <div class="space-y-4">
                            <h2 class="text-6xl font-black tracking-tighter uppercase italic text-gray-900 leading-none">READY TO<br><span class="text-primary">EXECUTE.</span></h2>
                            <p class="text-gray-400 font-medium max-w-sm mx-auto">The script is complete. AI is standing by to generate your high-conversion assets.</p>
                        </div>
                        
                        <div class="space-y-6">
                            <button type="submit"
                                    @click="$dispatch('ai-generate-start'); setTimeout(() => generating = true, 50)"
                                    :disabled="generating"
                                    class="w-full py-8 bg-primary text-white rounded-3xl font-black uppercase tracking-[0.2em] italic text-xl shadow-2xl shadow-primary/30 hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50">
                                Launch Script
                            </button>
                            <button type="button" @click="step = 3" class="text-[10px] font-black uppercase tracking-widest text-gray-300 hover:text-gray-900 transition-colors">Re-evaluate Inputs</button>
                        </div>
                    </div>
                </section>
            </form>
        </div>

        {{-- Right: Intelligence Sidebar (The Oracle) --}}
        <div class="hidden lg:flex flex-1 bg-gray-50 p-24 flex-col justify-between relative overflow-hidden border-l border-gray-100" 
             x-data="{ activeStep: 1 }" 
             @step-changed.window="activeStep = $event.detail">
            
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle at 2px 2px, black 1px, transparent 0); background-size: 24px 24px;"></div>
            
            <div class="relative z-10 space-y-16">
                <div class="flex items-center gap-4">
                    <div class="w-8 h-8 bg-gray-900 rounded flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-[0.5em] text-gray-900">Oracle Intelligence v2.0</span>
                </div>

                <div class="space-y-12">
                    <div class="p-8 bg-white rounded-3xl shadow-sm border border-gray-100 max-w-sm">
                        <p class="text-[10px] font-black uppercase tracking-widest text-primary mb-4">Context Awareness</p>
                        <h3 class="text-3xl font-black italic uppercase tracking-tighter text-gray-900 mb-4 leading-none" 
                            x-text="activeStep === 1 ? 'IDENTITY.' : activeStep === 2 ? 'UTILITY.' : activeStep === 3 ? 'MARKET.' : 'FINAL.'"></h3>
                        <p class="text-sm text-gray-500 font-medium leading-relaxed" 
                           x-text="activeStep === 1 ? 'Nomenclature defines perception. A name is the first conversion point.' : activeStep === 2 ? 'Logic over fluff. Features are boring, benefits are life-changing.' : activeStep === 3 ? 'Resonance is key. If you talk to everyone, you talk to no one.' : 'Execution phase. The AI will now synthesize all vectors into one narrative.'"></p>
                    </div>

                    <div class="flex gap-2">
                        <template x-for="i in 3">
                            <div class="w-2 h-2 rounded-full bg-primary/20 animate-pulse" :style="'animation-delay: ' + (i*200) + 'ms'"></div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="relative z-10 flex flex-col gap-1">
                <span class="text-[8px] font-black uppercase tracking-[0.5em] text-gray-300">CORE_V2.0_READY</span>
                <span class="text-[8px] font-black uppercase tracking-[0.5em] text-gray-300">LATENCY_32MS_STABLE</span>
            </div>
        </div>
    </div>
</body>
</html>
