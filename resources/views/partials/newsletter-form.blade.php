{{--
    Newsletter Form Partial
    $variant: 'default' | 'footer' | 'sidebar' | 'inline'
--}}
@php $variant = $variant ?? 'default'; @endphp

<div class="newsletter-form-wrapper" x-data="newsletterForm()" id="newsletter-{{ $variant }}">
    <form @submit.prevent="submit" novalidate>
        @csrf

        {{-- Form fields — visible by default, hidden after submit --}}
        <div x-show="!submitted">
            @if($variant === 'sidebar')
                <div class="input-group">
                    <input type="email" x-model="email"
                           class="form-control form-control-sm"
                           placeholder="Your email address"
                           required
                           aria-label="Email address for newsletter">
                    <button type="submit" class="btn btn-warning btn-sm fw-semibold" :disabled="loading">
                        <span x-show="!loading"><i class="fas fa-paper-plane"></i></span>
                        <span x-show="loading" style="display:none;"><i class="fas fa-spinner fa-spin"></i></span>
                    </button>
                </div>
            @elseif($variant === 'footer')
                <div class="input-group">
                    <input type="email" x-model="email"
                           class="form-control"
                           placeholder="Enter your email"
                           required
                           aria-label="Email address for newsletter">
                    <button type="submit" class="btn btn-primary fw-semibold" :disabled="loading">
                        <span x-show="!loading">Subscribe</span>
                        <span x-show="loading" style="display:none;"><i class="fas fa-spinner fa-spin"></i></span>
                    </button>
                </div>
            @else
                <div class="d-flex flex-column flex-sm-row gap-2">
                    <input type="email" x-model="email"
                           class="form-control"
                           placeholder="Enter your email address"
                           required
                           aria-label="Email address for newsletter">
                    <button type="submit" class="btn btn-primary px-4 fw-semibold" :disabled="loading">
                        <span x-show="!loading">Subscribe</span>
                        <span x-show="loading" style="display:none;"><i class="fas fa-spinner fa-spin me-1"></i>Subscribing...</span>
                    </button>
                </div>
            @endif

            <div x-show="error" style="display:none;" class="mt-2">
                <small class="text-{{ $variant === 'sidebar' ? 'warning' : 'danger' }}" x-text="error"></small>
            </div>
        </div>

        {{-- Success state — hidden by default, shown after submit --}}
        <div x-show="submitted" style="display:none;" class="text-center py-2">
            <i class="fas fa-check-circle text-{{ $variant === 'sidebar' ? 'warning' : 'success' }} fa-2x mb-2 d-block"></i>
            <p class="mb-0 fw-semibold {{ $variant === 'sidebar' ? 'text-white' : '' }}">You're subscribed!</p>
            <small class="{{ $variant === 'sidebar' ? 'opacity-75' : 'text-muted' }}">Thank you for subscribing.</small>
        </div>
    </form>
</div>

@once
@push('scripts')
<script>
function newsletterForm() {
    return {
        email: '',
        loading: false,
        submitted: false,
        error: '',
        async submit() {
            this.error = '';
            if (!this.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email)) {
                this.error = 'Please enter a valid email address.';
                return;
            }
            this.loading = true;
            try {
                const res = await fetch('/newsletter/subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email: this.email })
                });
                const data = await res.json();
                if (res.ok) {
                    this.submitted = true;
                } else {
                    this.error = data.message || 'Subscription failed. Please try again.';
                }
            } catch {
                this.error = 'Network error. Please try again.';
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endpush
@endonce
