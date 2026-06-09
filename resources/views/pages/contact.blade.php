@extends('layouts.app')

@php
    $seo = [
        'title'     => 'Contact Us — ' . settings('site_name', config('app.name')),
        'canonical' => route('contact'),
    ];
@endphp

@section('content')

    {{-- Page Header --}}
    <div class="page-header bg-light border-bottom py-4">
        <div class="container">
            @include('partials.breadcrumb', [
                'breadcrumbs' => [
                    ['label' => 'Contact', 'url' => route('contact')],
                ]
            ])
            <h1 class="h3 fw-bold mt-2 mb-0">Contact Us</h1>
        </div>
    </div>

    <div class="container py-5">
        <div class="row g-5 justify-content-center">

            {{-- Contact Form --}}
            <div class="col-lg-7">

                @if(session('contact_success'))
                <div class="text-center py-5">
                    <div class="success-icon mb-4">
                        <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width:80px;height:80px;">
                            <i class="fas fa-check-circle text-success fa-3x"></i>
                        </div>
                    </div>
                    <h2 class="h4 fw-bold mb-2">Message Sent!</h2>
                    <p class="text-muted mb-4">Thank you for reaching out. We'll get back to you as soon as possible.</p>
                    <a href="{{ route('home') }}" class="btn btn-primary">Back to Home</a>
                </div>

                @else
                <div class="card border-0 shadow-sm p-4 p-md-5">
                    <h2 class="h5 fw-bold mb-1">Send us a message</h2>
                    <p class="text-muted mb-4">We typically respond within 1–2 business days.</p>

                    <form action="{{ route('contact.store') }}" method="POST" id="contactForm" novalidate>
                        @csrf

                        <div class="row g-3">
                            {{-- Name --}}
                            <div class="col-sm-6">
                                <label for="contact_name" class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="contact_name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}"
                                       placeholder="John Doe"
                                       required autocomplete="name">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Email --}}
                            <div class="col-sm-6">
                                <label for="contact_email" class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="contact_email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}"
                                       placeholder="john@example.com"
                                       required autocomplete="email">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Subject --}}
                            <div class="col-12">
                                <label for="contact_subject" class="form-label fw-medium">Subject <span class="text-danger">*</span></label>
                                <input type="text" name="subject" id="contact_subject"
                                       class="form-control @error('subject') is-invalid @enderror"
                                       value="{{ old('subject') }}"
                                       placeholder="What is this about?"
                                       required>
                                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Message --}}
                            <div class="col-12">
                                <label for="contact_message" class="form-label fw-medium">Message <span class="text-danger">*</span></label>
                                <textarea name="message" id="contact_message" rows="6"
                                          class="form-control @error('message') is-invalid @enderror"
                                          placeholder="Write your message here..."
                                          required>{{ old('message') }}</textarea>
                                @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- reCAPTCHA --}}
                            @if(settings('recaptcha_site_key'))
                            <div class="col-12">
                                <div class="g-recaptcha" data-sitekey="{{ settings('recaptcha_site_key') }}"></div>
                                @error('g-recaptcha-response')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            @endif

                            {{-- Submit --}}
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary px-5 py-2 fw-semibold">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                @endif

            </div>

            {{-- Contact Info --}}
            <div class="col-lg-4 col-md-8">
                <div class="contact-info">
                    <h3 class="h5 fw-bold mb-4">Get in Touch</h3>

                    <div class="d-flex flex-column gap-4">
                        @if(settings('contact_email'))
                        <div class="d-flex gap-3 align-items-start">
                            <div class="contact-icon rounded-3 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;">
                                <i class="fas fa-envelope text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-semibold mb-1">Email</div>
                                <a href="mailto:{{ settings('contact_email') }}" class="text-muted text-decoration-none">
                                    {{ settings('contact_email') }}
                                </a>
                            </div>
                        </div>
                        @endif

                        @if(settings('contact_phone'))
                        <div class="d-flex gap-3 align-items-start">
                            <div class="contact-icon rounded-3 bg-success bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;">
                                <i class="fas fa-phone-alt text-success"></i>
                            </div>
                            <div>
                                <div class="fw-semibold mb-1">Phone</div>
                                <a href="tel:{{ settings('contact_phone') }}" class="text-muted text-decoration-none">
                                    {{ settings('contact_phone') }}
                                </a>
                            </div>
                        </div>
                        @endif

                        @if(settings('contact_address'))
                        <div class="d-flex gap-3 align-items-start">
                            <div class="contact-icon rounded-3 bg-warning bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;">
                                <i class="fas fa-map-marker-alt text-warning"></i>
                            </div>
                            <div>
                                <div class="fw-semibold mb-1">Address</div>
                                <address class="text-muted mb-0">{!! nl2br(e(settings('contact_address'))) !!}</address>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Social Links --}}
                    <div class="mt-5">
                        <h4 class="h6 fw-bold mb-3">Follow Us</h4>
                        <div class="d-flex gap-3 flex-wrap">
                            @if(settings('social_facebook'))
                            <a href="{{ settings('social_facebook') }}" class="btn btn-outline-secondary btn-sm rounded-circle" style="width:38px;height:38px;padding:0;line-height:36px;" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            @endif
                            @if(settings('social_twitter'))
                            <a href="{{ settings('social_twitter') }}" class="btn btn-outline-secondary btn-sm rounded-circle" style="width:38px;height:38px;padding:0;line-height:36px;" target="_blank" rel="noopener noreferrer" aria-label="Twitter">
                                <i class="fab fa-x-twitter"></i>
                            </a>
                            @endif
                            @if(settings('social_instagram'))
                            <a href="{{ settings('social_instagram') }}" class="btn btn-outline-secondary btn-sm rounded-circle" style="width:38px;height:38px;padding:0;line-height:36px;" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            @endif
                            @if(settings('social_linkedin'))
                            <a href="{{ settings('social_linkedin') }}" class="btn btn-outline-secondary btn-sm rounded-circle" style="width:38px;height:38px;padding:0;line-height:36px;" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection

@if(settings('recaptcha_site_key'))
@push('scripts')
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endpush
@endif
