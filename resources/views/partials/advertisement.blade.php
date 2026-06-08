{{--
    Advertisement Partial
    Variables:
      $position - Ad zone identifier string (required)
                  e.g. 'header_banner', 'sidebar_top', 'sidebar_bottom',
                       'in_article', 'footer_banner', 'popup'
--}}
@php
    $ad = \App\Models\Advertisement::active()->byPosition($position ?? '')->inRandomOrder()->first();
@endphp

@if($ad)
<div class="advertisement-zone advertisement-{{ $ad->position }}"
     data-ad-id="{{ $ad->id }}"
     data-track="ad-impression"
     style="text-align:center;">

    @if(settings('show_ad_label', true))
    <small class="text-muted d-block mb-1" style="font-size:.65rem;text-transform:uppercase;letter-spacing:.05em;">Advertisement</small>
    @endif

    @if($ad->type === 'image' && $ad->image_url)
        <a href="{{ $ad->click_url }}"
           target="_blank"
           rel="noopener sponsored nofollow"
           data-ad-click="{{ $ad->id }}"
           aria-label="Advertisement: {{ $ad->title }}">
            <img src="{{ $ad->image_url }}"
                 alt="{{ $ad->title }}"
                 class="img-fluid rounded"
                 loading="lazy"
                 width="{{ $ad->width ?? 300 }}"
                 height="{{ $ad->height ?? 250 }}">
        </a>
    @elseif($ad->type === 'code' && $ad->ad_code)
        {!! $ad->ad_code !!}
    @elseif($ad->type === 'text')
        <div class="p-3 bg-light border rounded">
            @if($ad->click_url)
            <a href="{{ $ad->click_url }}" target="_blank" rel="noopener sponsored nofollow" data-ad-click="{{ $ad->id }}">
                <strong>{{ $ad->title }}</strong>
            </a>
            @else
            <strong>{{ $ad->title }}</strong>
            @endif
            @if($ad->description)
            <p class="small text-muted mb-0 mt-1">{{ $ad->description }}</p>
            @endif
        </div>
    @endif
</div>

@push('scripts')
<script>
(function() {
    const adEl = document.querySelector('[data-ad-id="{{ $ad->id }}"]');
    if (!adEl) return;
    // Track impression
    fetch('/api/ads/{{ $ad->id }}/impression', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' }
    }).catch(() => {});
    // Track click
    adEl.querySelector('[data-ad-click]')?.addEventListener('click', function() {
        fetch('/api/ads/{{ $ad->id }}/click', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' }
        }).catch(() => {});
    });
})();
</script>
@endpush
@endif
