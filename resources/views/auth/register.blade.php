@extends('layouts.auth')

@section('title', 'Sign Up')

@section('content')
<h1 class="auth-title">Sign Up</h1>
<p class="auth-subtitle">Create your admin account to get started.</p>

<form id="signupForm" class="auth-form" method="POST" action="{{ route('register') }}">
    @csrf
    
    <!-- Full Name Field -->
    <div class="form-group">
        <label for="name">Full Name</label>
        <div class="input-wrapper">
            <i class="fa-solid fa-user"></i>
            <input 
                type="text" 
                id="name" 
                name="name" 
                placeholder="Enter your full name"
                value="{{ old('name') }}"
                required
                autocomplete="name"
            >
        </div>
        <span class="error-message" id="nameError"></span>
    </div>

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
        <span class="error-message" id="emailError"></span>
    </div>

    <!-- Password Field -->
    <div class="form-group">
        <label for="password">Password</label>
        <div class="input-wrapper">
            <i class="fa-solid fa-lock"></i>
            <input 
                type="password" 
                id="password" 
                name="password" 
                placeholder="Create a password"
                required
                autocomplete="new-password"
                minlength="8"
            >
            <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                <i class="fa-solid fa-eye"></i>
            </button>
        </div>
        <span class="error-message" id="passwordError"></span>
        <span class="password-hint">Password must be at least 8 characters</span>
    </div>

    <!-- Confirm Password Field -->
    <div class="form-group">
        <label for="password_confirmation">Confirm Password</label>
        <div class="input-wrapper">
            <i class="fa-solid fa-lock"></i>
            <input 
                type="password" 
                id="password_confirmation" 
                name="password_confirmation" 
                placeholder="Confirm your password"
                required
                autocomplete="new-password"
            >
            <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                <i class="fa-solid fa-eye"></i>
            </button>
        </div>
        <span class="error-message" id="confirmPasswordError"></span>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary btn-block" id="signupBtn">
        <span class="btn-text">Create Account</span>
        <span class="btn-loader" style="display: none;">
            <i class="fa-solid fa-spinner fa-spin"></i>
        </span>
    </button>
</form>

<!-- Login Link -->
<div class="auth-footer">
    <p>Already have an account? <a href="{{ route('login') }}">Login</a></p>
</div>
@endsection
