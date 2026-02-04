{{-- 
    Reusable Stats Card Component
    Usage: @include('components.admin.stats-card', [
        'title' => 'Total Users',
        'value' => $count,
        'icon' => 'fa-users',
        'color' => 'blue', // blue, green, amber, red, purple
        'change' => '+5%', // optional
        'changeType' => 'positive' // positive, negative, neutral
    ])
--}}

@php
    $colorClasses = [
        'blue' => 'stat-blue',
        'green' => 'stat-green',
        'amber' => 'stat-amber',
        'red' => 'stat-red',
        'purple' => 'stat-purple',
    ];
    $colorClass = $colorClasses[$color ?? 'blue'] ?? 'stat-blue';
@endphp

<div class="stat-card-mini {{ $colorClass }}">
    <div class="stat-icon">
        <i class="fas {{ $icon ?? 'fa-chart-bar' }}"></i>
    </div>
    <div class="stat-content">
        <h3 class="stat-title">{{ $title ?? 'Stat' }}</h3>
        <p class="stat-value" data-stat="{{ $key ?? '' }}">{{ $value ?? 0 }}</p>
        @if(isset($change))
            <span class="stat-change {{ $changeType ?? 'neutral' }}">
                @if(($changeType ?? '') === 'positive')
                    <i class="fas fa-arrow-up"></i>
                @elseif(($changeType ?? '') === 'negative')
                    <i class="fas fa-arrow-down"></i>
                @endif
                {{ $change }}
            </span>
        @endif
    </div>
</div>
