{{-- 
    Reusable Pagination Component
    Usage: @include('components.admin.pagination', ['paginator' => $items])
--}}

@if ($paginator->hasPages())
<nav class="pagination-nav">
    <div class="pagination-info">
        Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} entries
    </div>
    <div class="pagination-controls">
        {{-- First Page Link --}}
        @if ($paginator->onFirstPage())
            <button class="pagination-btn" disabled>
                <i class="fas fa-angle-double-left"></i>
            </button>
        @else
            <a href="{{ $paginator->url(1) }}" class="pagination-btn">
                <i class="fas fa-angle-double-left"></i>
            </a>
        @endif

        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <button class="pagination-btn" disabled>
                <i class="fas fa-chevron-left"></i>
            </button>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
            @if ($page == $paginator->currentPage())
                <button class="pagination-btn active">{{ $page }}</button>
            @else
                <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn">
                <i class="fas fa-chevron-right"></i>
            </a>
        @else
            <button class="pagination-btn" disabled>
                <i class="fas fa-chevron-right"></i>
            </button>
        @endif

        {{-- Last Page Link --}}
        @if ($paginator->currentPage() == $paginator->lastPage())
            <button class="pagination-btn" disabled>
                <i class="fas fa-angle-double-right"></i>
            </button>
        @else
            <a href="{{ $paginator->url($paginator->lastPage()) }}" class="pagination-btn">
                <i class="fas fa-angle-double-right"></i>
            </a>
        @endif
    </div>
</nav>
@endif
