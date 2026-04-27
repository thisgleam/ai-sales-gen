<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $salesPage->product_name }} - Sales Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-white text-slate-900 antialiased">

    {{-- Hero Section --}}
    <section class="relative py-24 px-6 overflow-hidden">
        <div class="absolute inset-0 bg-slate-50 -z-10"></div>
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-5xl md:text-7xl font-black tracking-tighter uppercase italic leading-[0.9] mb-8">
                {{ $salesPage->generated_content['headline'] ?? '' }}
            </h1>
            <p class="text-xl md:text-2xl text-slate-500 font-medium max-w-2xl mx-auto mb-12">
                {{ $salesPage->generated_content['sub_headline'] ?? '' }}
            </p>
            <div class="flex justify-center">
                <a href="#" class="px-10 py-5 bg-slate-900 text-white rounded-full font-black uppercase tracking-widest italic hover:scale-105 transition-transform">
                    {{ $salesPage->generated_content['call_to_action'] ?? 'Get Started' }}
                </a>
            </div>
        </div>
    </section>

    {{-- Description --}}
    <section class="py-24 px-6 bg-white border-y border-slate-100">
        <div class="max-w-3xl mx-auto text-center">
            <p class="text-2xl font-bold leading-relaxed text-slate-800">
                {{ $salesPage->generated_content['product_description'] ?? '' }}
            </p>
        </div>
    </section>

    {{-- Benefits Grid --}}
    <section class="py-24 px-6 bg-slate-50">
        <div class="max-w-5xl mx-auto">
            <div class="grid md:grid-cols-2 gap-8">
                @foreach($salesPage->generated_content['benefits'] ?? [] as $benefit)
                    <div class="p-8 bg-white rounded-3xl shadow-sm border border-slate-100">
                        <div class="w-10 h-10 bg-slate-900 rounded-lg flex items-center justify-center mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>
                        </div>
                        <p class="text-xl font-black uppercase italic tracking-tight">{{ $benefit }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section class="py-24 px-6 bg-white">
        <div class="max-w-5xl mx-auto">
            <div class="mb-16">
                <h2 class="text-4xl font-black tracking-tighter uppercase italic">The Capabilities.</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-12">
                @foreach($salesPage->generated_content['features'] ?? [] as $feature)
                    <div class="space-y-4">
                        <h3 class="text-lg font-black uppercase tracking-widest text-slate-900">{{ $feature['name'] ?? '' }}</h3>
                        <p class="text-slate-500 font-medium leading-relaxed">{{ $feature['description'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Social Proof --}}
    <section class="py-24 px-6 bg-slate-900 text-white text-center">
        <div class="max-w-3xl mx-auto">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mx-auto mb-12 opacity-20"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 2.5 1 4.066 2 5.028.19.183.19.183.19.183s1.29 1.157 2.81 1.789c-.312.002-.69.002-1 .001zM14 3c-1.244 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 2.5 1 4.066 2 5.028.19.183.19.183.19.183s1.29 1.157 2.81 1.789c-.312.002-.69.002-1 .001M22 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 2.5 1 4.066 2 5.028.19.183.19.183.19.183s1.29 1.157 2.81 1.789c-.312.002-.69.002-1 .001z"/></svg>
            <p class="text-3xl font-bold italic leading-relaxed mb-8">
                "{{ $salesPage->generated_content['social_proof_placeholder'] ?? '' }}"
            </p>
            <p class="text-[10px] font-black uppercase tracking-[0.5em] opacity-40">Verified User Feedback</p>
        </div>
    </section>

    {{-- Footer/CTA --}}
    <footer class="py-32 px-6 bg-white text-center">
        <div class="max-w-2xl mx-auto">
            <h2 class="text-4xl font-black tracking-tighter uppercase italic mb-4">{{ $salesPage->generated_content['pricing_display'] ?? '' }}</h2>
            <p class="text-slate-400 font-medium mb-12">Start your transformation today. No hidden fees.</p>
            <a href="#" class="px-16 py-6 bg-slate-900 text-white rounded-full font-black uppercase tracking-[0.2em] italic hover:scale-105 transition-transform inline-block">
                {{ $salesPage->generated_content['call_to_action'] ?? 'Get Started' }}
            </a>
        </div>
    </footer>

</body>
</html>
