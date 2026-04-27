@extends('layouts.auth')

@section('title', 'Login')
@section('subtitle', 'Welcome back! Please enter your details.')

@section('content')
    <form action="{{ route('login') }}" method="POST" class="space-y-6">
        @csrf

        @if ($errors->any())
            <div class="p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div>
            <label for="email" class="block text-sm font-medium text-slate-300">Email address</label>
            <div class="mt-1">
                <input id="email" name="email" type="email" autocomplete="email" required 
                    class="block w-full appearance-none rounded-lg border border-slate-700 bg-slate-900/50 px-3 py-2 text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm transition-all"
                    placeholder="you@example.com">
            </div>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
            <div class="mt-1">
                <input id="password" name="password" type="password" autocomplete="current-password" required 
                    class="block w-full appearance-none rounded-lg border border-slate-700 bg-slate-900/50 px-3 py-2 text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm transition-all"
                    placeholder="••••••••">
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember" name="remember" type="checkbox" 
                    class="h-4 w-4 rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900">
                <label for="remember" class="ml-2 block text-sm text-slate-400">Remember me</label>
            </div>

            <div class="text-sm">
                <a href="{{ route('password.request') }}" class="font-medium text-indigo-400 hover:text-indigo-300 transition-colors">
                    Forgot your password?
                </a>
            </div>
        </div>

        <div>
            <button type="submit" 
                class="flex w-full justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition-all active:scale-[0.98]">
                Sign in
            </button>
        </div>

        <div class="mt-6 text-center text-sm">
            <span class="text-slate-400">Don't have an account?</span>
            <a href="{{ route('register') }}" class="ml-1 font-medium text-indigo-400 hover:text-indigo-300 transition-colors">
                Sign up for free
            </a>
        </div>
    </form>
@endsection
