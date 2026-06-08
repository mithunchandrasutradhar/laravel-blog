{{--
    Social Share Buttons Partial
    Variables:
      $url   - URL to share (required)
      $title - Title to share (required)
      $variant: 'horizontal' | 'vertical' (default horizontal)
--}}
@php
    $shareUrl   = urlencode($url ?? url()->current());
    $shareTitle = urlencode($title ?? 'Check this out');
    $variant    = $variant ?? 'horizontal';
@endphp

<div class="social-share-buttons d-flex {{ $variant === 'vertical' ? 'flex-column' : 'flex-wrap' }} gap-2" role="group" aria-label="Share this post">
    {{-- Facebook --}}
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}"
       class="btn btn-social btn-facebook btn-sm"
       target="_blank" rel="noopener noreferrer"
       aria-label="Share on Facebook"
       onclick="window.open(this.href,'share-fb','width=580,height=400');return false;">
        <i class="fab fa-facebook-f me-1"></i>
        <span class="d-none d-sm-inline">Facebook</span>
    </a>

    {{-- Twitter / X --}}
    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}"
       class="btn btn-social btn-twitter btn-sm"
       target="_blank" rel="noopener noreferrer"
       aria-label="Share on Twitter"
       onclick="window.open(this.href,'share-twitter','width=550,height=420');return false;">
        <i class="fab fa-x-twitter me-1"></i>
        <span class="d-none d-sm-inline">Twitter</span>
    </a>

    {{-- LinkedIn --}}
    <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ $shareUrl }}&title={{ $shareTitle }}"
       class="btn btn-social btn-linkedin btn-sm"
       target="_blank" rel="noopener noreferrer"
       aria-label="Share on LinkedIn"
       onclick="window.open(this.href,'share-linkedin','width=600,height=460');return false;">
        <i class="fab fa-linkedin-in me-1"></i>
        <span class="d-none d-sm-inline">LinkedIn</span>
    </a>

    {{-- WhatsApp --}}
    <a href="https://api.whatsapp.com/send?text={{ $shareTitle }}%20{{ $shareUrl }}"
       class="btn btn-social btn-whatsapp btn-sm"
       target="_blank" rel="noopener noreferrer"
       aria-label="Share on WhatsApp">
        <i class="fab fa-whatsapp me-1"></i>
        <span class="d-none d-sm-inline">WhatsApp</span>
    </a>

    {{-- Copy Link --}}
    <button type="button"
            class="btn btn-social btn-copy-link btn-sm"
            data-copy-url="{{ $url ?? url()->current() }}"
            id="copyLinkBtn"
            aria-label="Copy link">
        <i class="fas fa-link me-1"></i>
        <span class="copy-link-text d-none d-sm-inline">Copy Link</span>
    </button>
</div>
