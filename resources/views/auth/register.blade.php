@extends('layouts.auth')

@section('title', 'Register')
@section('subtitle', 'Create your account to start generating sales.')

@section('content')
    <form action="{{ route('register') }}" method="POST" class="space-y-4">
        @csrf

        @if ($errors->any())
            <div class="alert alert-error shadow-lg">
                <div class="flex flex-col items-start">
                    @foreach ($errors->all() as $error)
                        <span class="text-sm font-semibold">{{ $error }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="form-control w-full">
            <label class="label pt-0" for="name">
                <span class="label-text font-bold opacity-70 uppercase tracking-widest text-[10px]">Full Name</span>
            </label>
            <input id="name" name="name" type="text" autocomplete="name" required 
                class="input input-bordered w-full bg-base-200/50 focus:input-primary transition-all duration-300"
                placeholder="John Doe">
        </div>

        <div class="form-control w-full">
            <label class="label pt-0" for="email">
                <span class="label-text font-bold opacity-70 uppercase tracking-widest text-[10px]">Email Address</span>
            </label>
            <input id="email" name="email" type="email" autocomplete="email" required 
                class="input input-bordered w-full bg-base-200/50 focus:input-primary transition-all duration-300"
                placeholder="name@company.com">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-control w-full">
                <label class="label pt-0" for="password">
                    <span class="label-text font-bold opacity-70 uppercase tracking-widest text-[10px]">Password</span>
                </label>
                <input id="password" name="password" type="password" autocomplete="new-password" required 
                    class="input input-bordered w-full bg-base-200/50 focus:input-primary transition-all duration-300"
                    placeholder="••••••••">
            </div>

            <div class="form-control w-full">
                <label class="label pt-0" for="password_confirmation">
                    <span class="label-text font-bold opacity-70 uppercase tracking-widest text-[10px]">Confirm</span>
                </label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required 
                    class="input input-bordered w-full bg-base-200/50 focus:input-primary transition-all duration-300"
                    placeholder="••••••••">
            </div>
        </div>

        <div class="pt-4">
            <button type="submit" class="btn btn-primary btn-block shadow-lg shadow-primary/20 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]">
                Create Account
            </button>
        </div>

        <div class="divider text-[10px] uppercase tracking-[0.2em] opacity-30 font-bold">Or</div>

        <div class="text-center">
            <p class="text-xs text-base-content/50 font-medium">
                Already registered? 
                <a href="{{ route('login') }}" class="link link-primary no-underline font-bold hover:underline">Sign In Instead</a>
            </p>
        </div>
    </form>
@endsection
