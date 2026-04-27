@extends('layouts.auth')

@section('title', 'Welcome back !')
@section('subtitle', 'Enter to get unlimited access to data & information.')

@section('content')
    <form action="{{ route('login') }}" method="POST" class="space-y-6">
        @csrf

        @if ($errors->any())
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-1">
            <label class="block text-sm font-semibold text-gray-700" for="email">
                Email <span class="text-red-500">*</span>
            </label>
            <input id="email" name="email" type="email" autocomplete="email" required 
                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all placeholder:text-gray-400"
                placeholder="Enter your mail address">
        </div>

        <div class="space-y-1">
            <label class="block text-sm font-semibold text-gray-700" for="password">
                Password <span class="text-red-500">*</span>
            </label>
            <div class="relative group">
                <input id="password" name="password" type="password" autocomplete="current-password" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all placeholder:text-gray-400"
                    placeholder="Enter password">
                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88L4.62 4.62"/><path d="M1 1l22 22"/><path d="M9.09 9.09a3 3 0 0 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.52 13.52 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/></svg>
                </button>
            </div>
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 cursor-pointer group">
                <input id="remember" name="remember" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                <span class="text-gray-600 font-semibold group-hover:text-gray-900 transition-colors">Remember me</span>
            </label>
            <a href="{{ route('password.request') }}" class="text-primary font-semibold hover:underline">
                Forgot your password ?
            </a>
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full py-4 rounded-xl bg-primary text-white font-bold shadow-xl shadow-primary/20 hover:bg-primary/90 hover:scale-[1.01] active:scale-[0.99] transition-all">
                Log In
            </button>
        </div>

        <div class="text-center pt-4">
            <p class="text-sm text-gray-600 font-medium">
                Don't have an account ? 
                <a href="{{ route('register') }}" class="text-primary font-bold hover:underline">Register here</a>
            </p>
        </div>
    </form>
@endsection

