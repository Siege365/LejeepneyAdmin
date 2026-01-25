@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<h1 class="auth-title">Login</h1>
<p class="auth-subtitle">Welcome back! Please login to your account.</p>

<form id="loginForm" class="auth-form" method="POST" action="{{ route('login') }}">
    @csrf
    
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
                placeholder="Enter your password"
                required
                autocomplete="current-password"
            >
            <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                <i class="fa-solid fa-eye"></i>
            </button>
        </div>
        <span class="error-message" id="passwordError"></span>
    </div>

    <!-- Remember Me & Forgot Password -->
    <div class="form-options">
        <label class="checkbox-wrapper">
            <input type="checkbox" name="remember" id="remember">
            <span class="checkmark"></span>
            Remember me
        </label>
        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn btn-primary btn-block" id="loginBtn">
        <span class="btn-text">Login</span>
        <span class="btn-loader" style="display: none;">
        </span>
    </button>
</form>


@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @foreach($errors->all() as $error)
            showNotification('{{ $error }}', 'error');
        @endforeach
    });
</script>
@endif

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showNotification('{{ session('success') }}', 'success');
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showNotification('{{ session('error') }}', 'error');
    });
</script>
@endif
@endsection
