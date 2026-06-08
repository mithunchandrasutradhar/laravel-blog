@if(isset($breadcrumbs) && count($breadcrumbs))
<nav aria-label="breadcrumb" class="breadcrumb-nav">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('home') }}" class="text-decoration-none">
                <i class="fas fa-home me-1"></i>Home
            </a>
        </li>
        @foreach($breadcrumbs as $crumb)
            @if($loop->last)
                <li class="breadcrumb-item active" aria-current="page">{{ $crumb['label'] }}</li>
            @else
                <li class="breadcrumb-item">
                    <a href="{{ $crumb['url'] }}" class="text-decoration-none">{{ $crumb['label'] }}</a>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
@endif
