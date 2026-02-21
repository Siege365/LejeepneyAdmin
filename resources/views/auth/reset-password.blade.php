@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<h1 class="auth-title">Reset Password</h1>
<p class="auth-subtitle">Enter your new password to reset your account password.</p>

<form class="auth-form" method="POST" action="{{ route('password.update') }}">
    @csrf
    
    <!-- Hidden Token Field -->
    <input type="hidden" name="token" value="{{ $token }}">
    
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
                value="{{ old('email', $email ?? '') }}"
                required
                autocomplete="email"
                autofocus
            >
        </div>
        @error('email')
            <span class="error-message show">{{ $message }}</span>
        @enderror
    </div>

    <!-- Password Field -->
    <div class="form-group">
        <label for="password">New Password</label>
        <div class="input-wrapper">
            <i class="fa-solid fa-lock"></i>
            <input 
                type="password" 
                id="password" 
                name="password" 
                placeholder="Enter new password"
                required
                autocomplete="new-password"
            >
        </div>
        @error('password')
            <span class="error-message show">{{ $message }}</span>
        @enderror
    </div>

    <!-- Confirm Password Field -->
    <div class="form-group">
        <label for="password_confirmation">Confirm New Password</label>
        <div class="input-wrapper">
            <i class="fa-solid fa-lock"></i>
            <input 
                type="password" 
                id="password_confirmation" 
                name="password_confirmation" 
                placeholder="Confirm new password"
                required
                autocomplete="new-password"
            >
        </div>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary btn-block">
        <span class="btn-text">Reset Password</span>
    </button>
</form>

<!-- Back to Login Link -->
<div class="auth-footer">
    <p><a href="{{ route('login') }}"><i class="fa-solid fa-arrow-left"></i> Back to Login</a></p>
</div>
@endsection
