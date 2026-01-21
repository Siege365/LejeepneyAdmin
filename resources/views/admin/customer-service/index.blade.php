@extends('layouts.admin')

@section('title', 'Customer Service')
@section('page-title', 'Customer Service')

@section('content')
<!-- Page Header -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="margin-bottom: 0;">
        <div>
            <h2 style="font-size: 1.25rem; margin-bottom: 0.25rem;">Customer Service</h2>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Manage customer inquiries and support tickets.</p>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
    <button class="btn btn-primary btn-sm">
        All Tickets
        <span style="background: rgba(255,255,255,0.25); padding: 0.125rem 0.5rem; border-radius: 9999px; margin-left: 0.375rem; font-weight: 600;">
            {{ $tickets->count() }}
        </span>
    </button>
    <button class="btn btn-outline btn-sm">
        <span style="background: #FEF3C7; color: #D97706; padding: 0.25rem 0.5rem; border-radius: 9999px; margin-right: 0.375rem; font-size: 0.75rem; font-weight: 600;">
            {{ $tickets->where('status', 'pending')->count() }}
        </span>
        Pending
    </button>
    <button class="btn btn-outline btn-sm">
        <span style="background: #DBEAFE; color: #2563EB; padding: 0.25rem 0.5rem; border-radius: 9999px; margin-right: 0.375rem; font-size: 0.75rem; font-weight: 600;">
            {{ $tickets->where('status', 'in-progress')->count() }}
        </span>
        In Progress
    </button>
    <button class="btn btn-outline btn-sm">
        <span style="background: #D1FAE5; color: #059669; padding: 0.25rem 0.5rem; border-radius: 9999px; margin-right: 0.375rem; font-size: 0.75rem; font-weight: 600;">
            {{ $tickets->where('status', 'resolved')->count() }}
        </span>
        Resolved
    </button>
</div>

<!-- Tickets Table -->
<div class="card">
    <div class="card-header">
        <h3>Support Tickets</h3>
        <div class="search-box">
            <i class="fa-solid fa-search"></i>
            <input type="text" placeholder="Search tickets...">
        </div>
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;">#</th>
                    <th>Subject</th>
                    <th style="width: 180px;">Customer</th>
                    <th style="width: 110px;">Type</th>
                    <th style="width: 120px;">Date</th>
                    <th style="width: 100px;">Priority</th>
                    <th style="width: 120px;">Status</th>
                    <th style="width: 60px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets ?? [] as $ticket)
                <tr>
                    <td style="font-weight: 600; color: var(--secondary-blue);">#{{ $ticket->id }}</td>
                    <td>
                        <div style="font-weight: 500; color: #1E293B;">{{ $ticket->subject }}</div>
                        <div style="font-size: 0.8125rem; color: #64748B; margin-top: 0.125rem;">{{ $ticket->customer_email }}</div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.875rem;">
                                {{ substr($ticket->customer_name, 0, 1) }}
                            </div>
                            <span style="font-weight: 500;">{{ $ticket->customer_name }}</span>
                        </div>
                    </td>
                    <td>
                        @php
                            $typeStyles = [
                                'complaint' => ['bg' => '#FEE2E2', 'color' => '#DC2626', 'icon' => 'fa-exclamation-circle'],
                                'feedback' => ['bg' => '#E0E7FF', 'color' => '#6366F1', 'icon' => 'fa-comment'],
                                'bug' => ['bg' => '#FECACA', 'color' => '#B91C1C', 'icon' => 'fa-bug'],
                                'inquiry' => ['bg' => '#DBEAFE', 'color' => '#2563EB', 'icon' => 'fa-circle-question'],
                                'suggestion' => ['bg' => '#D1FAE5', 'color' => '#059669', 'icon' => 'fa-lightbulb'],
                                'report' => ['bg' => '#FEF3C7', 'color' => '#D97706', 'icon' => 'fa-flag']
                            ];
                            $tStyle = $typeStyles[$ticket->type] ?? $typeStyles['inquiry'];
                        @endphp
                        <span style="background: {{ $tStyle['bg'] }}; color: {{ $tStyle['color'] }}; padding: 0.375rem 0.75rem; border-radius: 9999px; font-size: 0.8125rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.375rem; white-space: nowrap;">
                            <i class="fa-solid {{ $tStyle['icon'] }}"></i>
                            {{ ucfirst($ticket->type) }}
                        </span>
                    </td>
                    <td style="font-size: 0.875rem; color: #64748B;">
                        {{ $ticket->created_at->format('M d, Y') }}
                        <div style="font-size: 0.75rem; color: #94A3B8;">{{ $ticket->created_at->format('h:i A') }}</div>
                    </td>
                    <td>
                        @php
                            $priorityStyles = [
                                'high' => ['bg' => '#FEE2E2', 'color' => '#DC2626', 'icon' => 'fa-circle-exclamation'],
                                'medium' => ['bg' => '#FEF3C7', 'color' => '#D97706', 'icon' => 'fa-circle-pause'],
                                'low' => ['bg' => '#DBEAFE', 'color' => '#2563EB', 'icon' => 'fa-circle-info']
                            ];
                            $pStyle = $priorityStyles[$ticket->priority];
                        @endphp
                        <span style="background: {{ $pStyle['bg'] }}; color: {{ $pStyle['color'] }}; padding: 0.375rem 0.75rem; border-radius: 9999px; font-size: 0.8125rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.375rem;">
                            <i class="fa-solid {{ $pStyle['icon'] }}"></i>
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </td>
                    <td>
                        @php
                            $statusStyles = [
                                'resolved' => ['bg' => '#D1FAE5', 'color' => '#059669', 'icon' => 'fa-circle-check'],
                                'in-progress' => ['bg' => '#DBEAFE', 'color' => '#2563EB', 'icon' => 'fa-spinner'],
                                'pending' => ['bg' => '#FEF3C7', 'color' => '#D97706', 'icon' => 'fa-clock']
                            ];
                            $sStyle = $statusStyles[$ticket->status];
                        @endphp
                        <span style="background: {{ $sStyle['bg'] }}; color: {{ $sStyle['color'] }}; padding: 0.375rem 0.75rem; border-radius: 9999px; font-size: 0.8125rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.375rem;">
                            <i class="fa-solid {{ $sStyle['icon'] }}"></i>
                            {{ ucfirst(str_replace('-', ' ', $ticket->status)) }}
                        </span>
                    </td>
                    <td>
                        <div class="kebab-menu">
                            <button type="button" class="kebab-trigger" onclick="toggleKebabMenu(this)">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <div class="kebab-dropdown">
                                <a href="{{ route('customer-service.show', $ticket->id) }}" class="kebab-item">
                                    <i class="fa-solid fa-eye"></i> View
                                </a>
                                <a href="#" class="kebab-item" onclick="event.preventDefault(); window.location='{{ route('customer-service.show', $ticket->id) }}';">
                                    <i class="fa-solid fa-reply"></i> Reply
                                </a>
                                <div class="kebab-divider"></div>
                                <a href="#" class="kebab-item" onclick="return confirm('Mark this ticket as resolved?');">
                                    <i class="fa-solid fa-circle-check"></i> Resolve
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 3rem;">
                        <i class="fa-solid fa-headset" style="font-size: 3rem; color: #CBD5E1; margin-bottom: 1rem;"></i>
                        <p style="color: #64748B; margin: 0; font-size: 1rem; font-weight: 500;">No Support Tickets</p>
                        <p style="color: #94A3B8; margin-top: 0.5rem; font-size: 0.875rem;">All caught up! There are no customer inquiries at the moment.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($tickets->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #E2E8F0;">
            {{ $tickets->links('vendor.pagination.custom') }}
        </div>
    @endif
</div>
@endsection
