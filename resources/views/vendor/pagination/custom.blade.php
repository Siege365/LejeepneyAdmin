@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" style="display: flex; align-items: center; gap: 0.25rem;">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: #F1F5F9; color: #94A3B8; border-radius: 6px; font-size: 0.875rem; cursor: not-allowed;">
                <i class="fa-solid fa-chevron-left"></i>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: #FFFFFF; color: #475569; border: 1px solid #E2E8F0; border-radius: 6px; font-size: 0.875rem; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#F8FAFC'; this.style.borderColor='#CBD5E1';" onmouseout="this.style.background='#FFFFFF'; this.style.borderColor='#E2E8F0';">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; color: #64748B; font-size: 0.875rem;">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px; padding: 0 0.5rem; background: #1E3A5F; color: #FFFFFF; border-radius: 6px; font-size: 0.875rem; font-weight: 600;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" style="display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px; padding: 0 0.5rem; background: #FFFFFF; color: #475569; border: 1px solid #E2E8F0; border-radius: 6px; font-size: 0.875rem; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#F8FAFC'; this.style.borderColor='#CBD5E1';" onmouseout="this.style.background='#FFFFFF'; this.style.borderColor='#E2E8F0';">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: #FFFFFF; color: #475569; border: 1px solid #E2E8F0; border-radius: 6px; font-size: 0.875rem; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#F8FAFC'; this.style.borderColor='#CBD5E1';" onmouseout="this.style.background='#FFFFFF'; this.style.borderColor='#E2E8F0';">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        @else
            <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: #F1F5F9; color: #94A3B8; border-radius: 6px; font-size: 0.875rem; cursor: not-allowed;">
                <i class="fa-solid fa-chevron-right"></i>
            </span>
        @endif
    </nav>
@endif
