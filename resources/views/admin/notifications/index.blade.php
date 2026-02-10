@extends('layouts.admin')
@section('page-title', 'Notifications')
@section('title', 'Notifications')

@push('styles')
    @vite(['resources/css/pages/notifications.css'])
@endpush

@section('content')
<div class="content-wrapper">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h1>
                <i class="fa-solid fa-bell"></i>
                Notifications
            </h1>
            <p class="page-subtitle">View all your notifications</p>
        </div>
        <div class="page-header-right">
            @if($notifications->total() > 0)
            <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST" class="mark-all-read-form" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-outline">
                    <i class="fa-solid fa-check-double"></i>
                    Mark All as Read
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Notifications List -->
    <div class="card">
        <div class="notifications-list">
            @forelse($notifications as $notification)
            <div class="notification-item {{ $notification->is_read ? 'read' : 'unread' }}">
                <div class="notification-icon {{ $notification->type }}">
                    @if($notification->type === 'new_ticket')
                        <i class="fa-solid fa-ticket"></i>
                    @elseif($notification->type === 'ticket_reply')
                        <i class="fa-solid fa-reply"></i>
                    @elseif($notification->type === 'status_changed')
                        <i class="fa-solid fa-sync"></i>
                    @elseif($notification->type === 'resolved')
                        <i class="fa-solid fa-check-circle"></i>
                    @else
                        <i class="fa-solid fa-bell"></i>
                    @endif
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h4>{{ $notification->title }}</h4>
                        <span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="notification-message">{{ $notification->message }}</p>
                    <div class="notification-actions">
                        @if($notification->ticket)
                        <a href="{{ route('admin.customer-service.show', $notification->ticket_id) }}" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-eye"></i>
                            View Ticket
                        </a>
                        @endif
                        @if(!$notification->is_read)
                        <form action="{{ route('admin.notifications.mark-read', $notification->id) }}" method="POST" class="mark-as-read-form" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline">
                                <i class="fa-solid fa-check"></i>
                                Mark as Read
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @if(!$notification->is_read)
                <div class="notification-unread-badge"></div>
                @endif
            </div>
            @empty
            <div class="notifications-empty">
                <i class="fa-solid fa-bell-slash fa-3x"></i>
                <h3>No notifications yet</h3>
                <p>You'll see notifications here when there's activity on support tickets.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
    <div class="table-footer">
        @include('components.admin.pagination', ['paginator' => $notifications])
    </div>
    @endif
</div>

@endsection

@push('scripts')
    @vite(['resources/js/pages/notifications.js'])
@endpush
