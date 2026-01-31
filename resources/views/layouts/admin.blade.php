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
                <div style="position: relative; margin-right: 1rem;">
                    <button onclick="toggleQuickActions(event)" style="padding: 0.625rem 1.25rem; background: #F59E0B; color: #1E293B; border: none; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 600; font-size: 0.875rem; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" onmouseenter="this.style.background='#D97706'; this.style.boxShadow='0 2px 6px rgba(0,0,0,0.15)'" onmouseleave="this.style.background='#F59E0B'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)'">
                        <i class="fa-solid fa-plus"></i>
                        Quick Actions
                        <i class="fa-solid fa-chevron-down" style="font-size: 0.75rem;"></i>
                    </button>
                    <div id="quickActionsDropdown" style="display: none; position: absolute; top: 100%; right: 0; margin-top: 0.5rem; background: white; border: 1px solid #E2E8F0; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); min-width: 200px; z-index: 1000;">
                        <a href="{{ route('admin.landmarks.create') }}" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: #1E293B; text-decoration: none; border-bottom: 1px solid #F1F5F9; transition: background 0.2s;" onmouseenter="this.style.background='#F8FAFC'" onmouseleave="this.style.background='white'">
                            <i class="fa-solid fa-map-marker-alt" style="color: var(--gold); width: 20px;"></i>
                            <span style="font-weight: 500; font-size: 0.875rem;">Add Landmark</span>
                        </a>
                        <a href="{{ route('admin.routes.create') }}" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: #1E293B; text-decoration: none; border-bottom: 1px solid #F1F5F9; transition: background 0.2s;" onmouseenter="this.style.background='#F8FAFC'" onmouseleave="this.style.background='white'">
                            <i class="fa-solid fa-route" style="color: var(--secondary-blue); width: 20px;"></i>
                            <span style="font-weight: 500; font-size: 0.875rem;">Add Route</span>
                        </a>
                        <a href="{{ route('register') }}" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: #1E293B; text-decoration: none; transition: background 0.2s;" onmouseenter="this.style.background='#F8FAFC'" onmouseleave="this.style.background='white'">
                            <i class="fa-solid fa-user-plus" style="color: var(--success); width: 20px;"></i>
                            <span style="font-weight: 500; font-size: 0.875rem;">Add Admin User</span>
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
    
    <script>
    function toggleQuickActions(event) {
        event.stopPropagation();
        const dropdown = document.getElementById('quickActionsDropdown');
        if (!dropdown) return;
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('quickActionsDropdown');
        if (!dropdown) return;
        const quickActionsContainer = event.target.closest('[style*="position: relative"]');
        
        if (!quickActionsContainer && !dropdown.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });
    </script>
    
    @stack('scripts')
</body>
</html>
