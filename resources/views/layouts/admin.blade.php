<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name') }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Admin Styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}?v={{ time() }}">
    
    <!-- Component Styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/components/toast.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components/pagination.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components/modal.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components/stats-cards.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components/quick-actions.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components/filters.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components/table.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components/conversation.css') }}?v={{ time() }}">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/images/Logo.svg') }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('assets/images/Logo.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/LogoSignInPage.png') }}">
    <meta name="theme-color" content="#F59E0B">
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('assets/images/Logo.svg') }}" alt="Lejeepney Logo" class="sidebar-logo">
            <span class="sidebar-brand">Lejeepney</span>
        </div>
        
        <ul class="nav-links">
            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.landmarks.index') }}" class="{{ request()->routeIs('admin.landmarks.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-map-marker-alt"></i>
                    <span>Landmarks</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.routes.index') }}" class="{{ request()->routeIs('admin.routes.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-route"></i>
                    <span>Routes</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.customer-service.index') }}" class="{{ request()->routeIs('admin.customer-service.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-headset"></i>
                    <span>Customer Service</span>
                </a>
            </li>
            <li>
                <a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Add Admin User</span>
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="user-details">
                    <span class="user-name">{{ Auth::user()->name }}</span>
                    <span class="user-role">Administrator</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Bar -->
        <header class="top-bar">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fa-solid fa-bars"></i>
            </button>
            
            <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
            
            <div class="top-bar-right">
                <!-- Quick Actions Dropdown -->
                <div class="quick-actions">
                    <button class="quick-actions-btn">
                        <i class="fa-solid fa-plus"></i>
                        Quick Actions
                        <i class="fa-solid fa-chevron-down quick-actions-chevron"></i>
                    </button>
                    <div class="quick-actions-menu">
                        <a href="{{ route('admin.landmarks.create') }}" class="quick-actions-item">
                            <i class="fa-solid fa-map-marker-alt quick-actions-icon quick-actions-icon-gold"></i>
                            <span>Add Landmark</span>
                        </a>
                        <a href="{{ route('admin.routes.create') }}" class="quick-actions-item">
                            <i class="fa-solid fa-route quick-actions-icon quick-actions-icon-blue"></i>
                            <span>Add Route</span>
                        </a>
                        <a href="{{ route('register') }}" class="quick-actions-item quick-actions-item-last">
                            <i class="fa-solid fa-user-plus quick-actions-icon quick-actions-icon-green"></i>
                            <span>Add Admin User</span>
                        </a>
                    </div>
                </div>

                <button class="notification-btn">
                    <i class="fa-solid fa-bell"></i>
                    <span class="notification-badge">3</span>
                </button>
                
                <div class="user-menu">
                    <div class="user-avatar-sm">
                        <i class="fa-solid fa-user"></i>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            
            @yield('content')
        </div>
    </main>

    <!-- Admin Scripts -->
    <script src="{{ asset('assets/js/admin.js') }}?v={{ time() }}"></script>
    
    <!-- Component Scripts -->
    <script src="{{ asset('assets/js/components/toast.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('assets/js/components/quick-actions.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('assets/js/components/modal.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('assets/js/components/bulk-actions.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('assets/js/components/filters.js') }}?v={{ time() }}"></script>
    
    <!-- Toast Container -->
    @include('components.admin.toast-container')
    
    @stack('scripts')
</body>
</html>
