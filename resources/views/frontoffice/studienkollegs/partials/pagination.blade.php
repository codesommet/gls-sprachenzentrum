@if ($paginator->hasPages())
    <nav class="pagination-nav" role="navigation" aria-label="Pagination Navigation">

        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="pagination-btn disabled" aria-disabled="true">
                « Previous
            </span>
        @else
            <a class="pagination-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page">
                « Previous
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)

            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="pagination-btn disabled" aria-disabled="true">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pagination-btn active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="pagination-btn" href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a class="pagination-btn primary" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page">
                Next »
            </a>
        @else
            <span class="pagination-btn disabled" aria-disabled="true">
                Next »
            </span>
        @endif

    </nav>
@endif
