<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="night">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - AI Sales Gen</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="antialiased selection:bg-primary selection:text-primary-content font-sans">
    <div class="min-h-screen flex bg-white">
        <!-- Left: Form Area -->
        <div class="flex-1 flex flex-col justify-center py-12 px-6 sm:px-12 lg:flex-none lg:w-[500px] xl:w-[600px] lg:px-20 bg-white">
            <div class="mx-auto w-full max-w-sm">
                <div>
                    <h2 class="text-4xl font-bold tracking-tight text-gray-900 mb-2">
                        @yield('title')
                    </h2>
                    <p class="text-gray-500 font-medium leading-relaxed">
                        @yield('subtitle')
                    </p>
                </div>

                <div class="mt-10">
                    @yield('content')
                </div>
            </div>
        </div>

        <!-- Right: Geometric Branding Area -->
        <div class="hidden lg:block flex-1 relative overflow-hidden bg-[#2D1B69]">
            <!-- Complex Geometric Background -->
            <div class="absolute inset-0">
                <!-- Base Gradient -->
                <div class="absolute inset-0 bg-gradient-to-br from-[#4F46E5] via-[#2D1B69] to-[#1E1B4B]"></div>
                
                <!-- Geometric Overlays (Replicating the image patterns) -->
                <div class="absolute top-0 right-0 w-full h-full opacity-20">
                    <!-- Grid dots -->
                    <div class="absolute top-10 right-10 w-32 h-32" style="background-image: radial-gradient(#fff 2px, transparent 2px); background-size: 16px 16px;"></div>
                    
                    <!-- Abstract shapes (Using CSS) -->
                    <div class="absolute top-[10%] left-[10%] w-64 h-64 bg-primary/30 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-[20%] right-[10%] w-96 h-96 bg-secondary/20 rounded-full blur-3xl"></div>
                    
                    <!-- SVG Patterns -->
                    <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                                <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="0.5" opacity="0.1"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#grid)" />
                        
                        <!-- Geometric elements from the image -->
                        <path d="M100,100 L300,100 L200,300 Z" fill="white" opacity="0.05" />
                        <circle cx="80%" cy="20%" r="80" fill="none" stroke="white" stroke-width="2" opacity="0.1" stroke-dasharray="10 5" />
                        <rect x="10%" y="60%" width="120" height="120" fill="#00D1FF" opacity="0.2" transform="rotate(15)" />
                    </svg>
                </div>

                <!-- Floating Interactive Elements -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="relative w-full h-full">
                        <!-- Animated decorative pieces -->
                        <div class="absolute top-1/4 left-1/3 w-12 h-12 bg-yellow-400 rotate-45 opacity-40 animate-pulse"></div>
                        <div class="absolute bottom-1/4 right-1/4 w-24 h-24 border-4 border-white rounded-full opacity-10 animate-bounce" style="animation-duration: 4s"></div>
                    </div>
                </div>
            </div>
            
            <!-- Center Branding Content -->
            <div class="relative z-10 h-full flex flex-col justify-center px-20">
                <div class="max-w-lg">
                    <div class="inline-block px-4 py-2 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white text-xs font-bold uppercase tracking-widest mb-6">
                        AI-Powered Content Generation
                    </div>
                    <h1 class="text-6xl font-black text-white leading-[1.1] tracking-tighter mb-8 italic">
                        CRAFT YOUR <span class="text-primary-content opacity-50">FUTURE</span> SALES.
                    </h1>
                    <p class="text-xl text-white/70 font-medium leading-relaxed">
                        Transform raw data into persuasive narratives that drive conversion and grow your business effortlessly.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
