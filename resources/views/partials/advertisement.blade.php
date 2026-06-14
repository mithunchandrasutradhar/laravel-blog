{{--
    Advertisement Partial
    Variables:
      $position - 'header' | 'sidebar' | 'in-article' | 'footer'
--}}
@php
    $ad = \App\Models\Advertisement::active()->atPosition($position ?? '')->inRandomOrder()->first();
@endphp

@if($ad)
<div class="advertisement-zone advertisement-{{ $ad->position }}" style="text-align:center;">

    <small class="text-muted d-block mb-1" style="font-size:.65rem;text-transform:uppercase;letter-spacing:.05em;">Advertisement</small>

    @if($ad->type === 'banner' && $ad->image_url)
        @if($ad->url)
        <a href="{{ $ad->url }}" target="_blank" rel="noopener sponsored nofollow"
           aria-label="Advertisement: {{ $ad->name }}">
            <img src="{{ $ad->image_url }}" alt="{{ $ad->name }}"
                 class="img-fluid rounded" loading="lazy">
        </a>
        @else
        <img src="{{ $ad->image_url }}" alt="{{ $ad->name }}"
             class="img-fluid rounded" loading="lazy">
        @endif
    @elseif($ad->type === 'adsense' && $ad->code)
        {!! $ad->code !!}
    @endif

</div>
@endif
