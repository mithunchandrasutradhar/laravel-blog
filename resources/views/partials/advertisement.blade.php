{{--
    Advertisement Partial
    Variables:
      $position - 'header' | 'sidebar' | 'in-article' | 'footer'
--}}
@php
    $ad = \App\Models\Advertisement::active()->atPosition($position ?? '')->inRandomOrder()->first();
@endphp

@if($ad)

{{-- ── Header / Footer — full-width banner strip ───────────────────────────── --}}
@if($ad->position === 'header' || $ad->position === 'footer')
<div class="ad-strip ad-strip-{{ $ad->position }}">
    <div class="container">
        <div class="ad-strip-inner">
            <span class="ad-label">Ad</span>
            @if($ad->type === 'banner' && $ad->image_url)
                @if($ad->url)
                <a href="{{ $ad->url }}" target="_blank" rel="noopener sponsored nofollow"
                   aria-label="Advertisement: {{ $ad->name }}" class="ad-strip-link">
                    <img src="{{ $ad->image_url }}" alt="{{ $ad->name }}"
                         class="ad-strip-img" loading="lazy">
                </a>
                @else
                <img src="{{ $ad->image_url }}" alt="{{ $ad->name }}"
                     class="ad-strip-img" loading="lazy">
                @endif
            @elseif($ad->type === 'adsense' && $ad->code)
                <div class="ad-strip-code">{!! $ad->code !!}</div>
            @endif
        </div>
    </div>
</div>

{{-- ── Sidebar / In-Article — inline zone ─────────────────────────────────── --}}
@else
<div class="advertisement-zone advertisement-{{ $ad->position }}">
    <small class="ad-label-block">Advertisement</small>
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

@endif
