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
    
    <!-- Admin Styles (Vite bundled) -->
    @vite(['resources/css/admin-bundle.css', 'resources/js/admin-bundle.js'])

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/images/LeJeepneyFinal.svg') }}" type="image/svg+xml">
    <link rel="shortcut icon" href="{{ asset('assets/images/LeJeepneyFinal.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/LogoSignInPage.png') }}">
    <meta name="theme-color" content="#F59E0B">
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('assets/images/LeJeepneyFinal.svg') }}" alt="LeJeepney Logo" class="sidebar-logo">
            <span class="sidebar-brand">LeJeepney</span>
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
                <a href="{{ route('admin.audit-trail.index') }}" class="{{ request()->routeIs('admin.audit-trail.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <span>Audit Trail</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li>
                <a href="{{ route('register') }}" class="{{ request()->routeIs('register') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Add Admin User</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.account.settings') }}" class="{{ request()->routeIs('admin.account.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gear"></i>
                    <span>Account Settings</span>
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

                <div class="notification-dropdown-wrapper">
                    <button class="notification-btn" onclick="toggleNotificationDropdown()">
                        <i class="fa-solid fa-bell"></i>
                        @if($notificationCount > 0)
                        <span class="notification-badge">{{ $notificationCount > 9 ? '9+' : $notificationCount }}</span>
                        @endif
                    </button>
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-dropdown-header">
                            <h4>Notifications</h4>
                            @if($notificationCount > 0)
                            <button class="notification-mark-all" onclick="markAllNotificationsRead()">Mark all read</button>
                            @endif
                        </div>
                        <div class="notification-dropdown-body">
                            @forelse($headerNotifications as $notification)
                            <div class="notification-item {{ !$notification->is_read ? 'unread' : '' }}" 
                                 onclick="handleNotificationClick({{ $notification->id }}, '{{ $notification->ticket_id ? route('admin.customer-service.show', $notification->ticket_id) : '#' }}')">
                                <div class="notification-item-icon">
                                    @switch($notification->event_type)
                                        @case('admin_message')
                                            <i class="fa-solid fa-reply" style="color: var(--secondary-blue);"></i>
                                            @break
                                        @case('resolved')
                                            <i class="fa-solid fa-check-circle" style="color: var(--success);"></i>
                                            @break
                                        @case('status_changed')
                                            <i class="fa-solid fa-exchange-alt" style="color: var(--warning);"></i>
                                            @break
                                        @default
                                            <i class="fa-solid fa-bell" style="color: var(--primary-gold);"></i>
                                    @endswitch
                                </div>
                                <div class="notification-item-content">
                                    <p class="notification-item-title">{{ $notification->title }}</p>
                                    <p class="notification-item-text">{{ Str::limit($notification->message, 60) }}</p>
                                    <span class="notification-item-time">{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                                @if(!$notification->is_read)
                                <span class="notification-unread-dot"></span>
                                @endif
                            </div>
                            @empty
                            <div class="notification-empty">
                                <i class="fa-solid fa-bell-slash"></i>
                                <p>No notifications yet</p>
                            </div>
                            @endforelse
                        </div>
                        @if($headerNotifications->count() > 0)
                        <div class="notification-dropdown-footer">
                            <a href="{{ route('admin.notifications.index') }}" class="notification-view-all">
                                View All Notifications
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="user-menu">
                    <a href="{{ route('admin.account.settings') }}" class="user-avatar-sm" title="Account Settings">
                        <i class="fa-solid fa-user"></i>
                    </a>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="content">
            @yield('content')
        </div>
    </main>

    <!-- Toast Container -->
    @include('components.admin.toast-container')
    
    @stack('scripts')
</body>
</html>
