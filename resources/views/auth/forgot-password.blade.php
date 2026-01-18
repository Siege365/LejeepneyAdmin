@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<h1 class="auth-title">Forgot Password</h1>
<p class="auth-subtitle">Enter your email address and we'll send you a link to reset your password.</p>

<form class="auth-form" method="POST" action="{{ route('password.email') }}">
    @csrf
    
    @if (session('status'))
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif
    
    <!-- Email Field -->
    <div class="form-group">
        <label for="email">Email Address</label>
        <div class="input-wrapper">
            <i class="fa-solid fa-envelope"></i>
            <input 
                type="email" 
                id="email" 
                name="email" 
                placeholder="Enter your email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
            >
        </div>
        @error('email')
            <span class="error-message show">{{ $message }}</span>
        @enderror
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary btn-block">
        <span class="btn-text">Send Reset Link</span>
    </button>
</form>

<!-- Back to Login Link -->
<div class="auth-footer">
    <p><a href="{{ route('login') }}"><i class="fa-solid fa-arrow-left"></i> Back to Login</a></p>
</div>
@endsection
