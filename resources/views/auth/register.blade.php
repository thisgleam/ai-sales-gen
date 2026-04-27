@extends('layouts.auth')

@section('title', 'Register')
@section('subtitle', 'Create your account to start generating sales.')

@section('content')
    <form action="{{ route('register') }}" method="POST" class="space-y-6">
        @csrf

        @if ($errors->any())
            <div class="p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div>
            <label for="name" class="block text-sm font-medium text-slate-300">Full Name</label>
            <div class="mt-1">
                <input id="name" name="name" type="text" autocomplete="name" required 
                    class="block w-full appearance-none rounded-lg border border-slate-700 bg-slate-900/50 px-3 py-2 text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm transition-all"
                    placeholder="John Doe">
            </div>
        </div>

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
                <input id="password" name="password" type="password" autocomplete="new-password" required 
                    class="block w-full appearance-none rounded-lg border border-slate-700 bg-slate-900/50 px-3 py-2 text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm transition-all"
                    placeholder="••••••••">
            </div>
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-300">Confirm Password</label>
            <div class="mt-1">
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required 
                    class="block w-full appearance-none rounded-lg border border-slate-700 bg-slate-900/50 px-3 py-2 text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm transition-all"
                    placeholder="••••••••">
            </div>
        </div>

        <div>
            <button type="submit" 
                class="flex w-full justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition-all active:scale-[0.98]">
                Create Account
            </button>
        </div>

        <div class="mt-6 text-center text-sm">
            <span class="text-slate-400">Already have an account?</span>
            <a href="{{ route('login') }}" class="ml-1 font-medium text-indigo-400 hover:text-indigo-300 transition-colors">
                Sign in instead
            </a>
        </div>
    </form>
@endsection
