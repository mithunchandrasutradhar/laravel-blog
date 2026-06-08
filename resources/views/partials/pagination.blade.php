{{--
    Custom Bootstrap 5 Pagination Partial
    Pass $paginator (LengthAwarePaginator) to render custom pagination
--}}
@if(isset($paginator) && $paginator->hasPages())
<nav aria-label="Page navigation" class="d-flex justify-content-center mt-4">
    <ul class="pagination pagination-custom mb-0">
        {{-- Previous Page --}}
        @if($paginator->onFirstPage())
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link" aria-hidden="true">
                    <i class="fas fa-chevron-left"></i>
                    <span class="d-none d-sm-inline ms-1">Prev</span>
                </span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page">
                    <i class="fas fa-chevron-left"></i>
                    <span class="d-none d-sm-inline ms-1">Prev</span>
                </a>
            </li>
        @endif

        {{-- Page Numbers --}}
        @php
            $currentPage  = $paginator->currentPage();
            $lastPage     = $paginator->lastPage();
            $window       = 2; // pages on each side of current
            $startPage    = max(1, $currentPage - $window);
            $endPage      = min($lastPage, $currentPage + $window);
        @endphp

        {{-- First page + ellipsis --}}
        @if($startPage > 1)
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
            </li>
            @if($startPage > 2)
                <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
            @endif
        @endif

        @for($page = $startPage; $page <= $endPage; $page++)
            @if($page == $currentPage)
                <li class="page-item active" aria-current="page">
                    <span class="page-link">{{ $page }}</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                </li>
            @endif
        @endfor

        {{-- Ellipsis + last page --}}
        @if($endPage < $lastPage)
            @if($endPage < $lastPage - 1)
                <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
            @endif
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a>
            </li>
        @endif

        {{-- Next Page --}}
        @if($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page">
                    <span class="d-none d-sm-inline me-1">Next</span>
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        @else
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link" aria-hidden="true">
                    <span class="d-none d-sm-inline me-1">Next</span>
                    <i class="fas fa-chevron-right"></i>
                </span>
            </li>
        @endif
    </ul>
</nav>

{{-- Page info --}}
<p class="text-muted text-center small mt-2 mb-0">
    Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ number_format($paginator->total()) }} results
</p>
@endif
