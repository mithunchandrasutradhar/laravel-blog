@extends('admin.layouts.admin')

@section('title', 'Settings')

@section('breadcrumb')
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('page-title', 'Settings')
@section('page-subtitle', 'Configure your blog platform')

@push('styles')
<style>
    .nav-pills .nav-link {
        color: #495057;
        border-radius: .5rem;
        padding: .6rem 1rem;
    }
    .nav-pills .nav-link.active {
        background-color: #0d6efd;
        color: #fff;
    }
    .nav-pills .nav-link i {
        width: 20px;
        text-align: center;
    }
    .settings-pane { display: none; }
    .settings-pane.active { display: block; }
    .logo-preview-box {
        border: 2px dashed #dee2e6;
        border-radius: .5rem;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        overflow: hidden;
        transition: border-color .2s;
    }
    .logo-preview-box:hover { border-color: #0d6efd; }
    .logo-preview-box img { height: 60px; object-fit: contain; }
    .favicon-preview-box {
        border: 2px dashed #dee2e6;
        border-radius: .5rem;
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        overflow: hidden;
        transition: border-color .2s;
    }
    .favicon-preview-box img { width: 48px; height: 48px; object-fit: cover; }
</style>
@endpush

@section('content')

<div class="row g-4" x-data="settingsPage()">

    {{-- ── Sidebar tabs ── --}}
    <div class="col-xl-3 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-2">
                <div class="nav flex-column nav-pills gap-1" id="settingsTabs">
                    <button class="nav-link active text-start" @click="tab='general'" :class="{ active: tab === 'general' }">
                        <i class="fas fa-sliders-h me-2"></i>General
                    </button>
                    <button class="nav-link text-start" @click="tab='seo'" :class="{ active: tab === 'seo' }">
                        <i class="fas fa-search me-2"></i>SEO
                    </button>
                    <button class="nav-link text-start" @click="tab='social'" :class="{ active: tab === 'social' }">
                        <i class="fas fa-share-alt me-2"></i>Social
                    </button>
                    <button class="nav-link text-start" @click="tab='mail'" :class="{ active: tab === 'mail' }">
                        <i class="fas fa-envelope me-2"></i>Mail
                    </button>
                    <button class="nav-link text-start" @click="tab='appearance'" :class="{ active: tab === 'appearance' }">
                        <i class="fas fa-palette me-2"></i>Appearance
                    </button>
                    <button class="nav-link text-start" @click="tab='comments'" :class="{ active: tab === 'comments' }">
                        <i class="fas fa-comments me-2"></i>Comments
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tab content ── --}}
    <div class="col-xl-9 col-lg-8">

        {{-- ══ General ══ --}}
        <div x-show="tab === 'general'" x-transition>
            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="general">

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h6 class="fw-bold mb-0"><i class="fas fa-sliders-h text-primary me-2"></i>General Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Site Name <span class="text-danger">*</span></label>
                                <input type="text" name="site_name" class="form-control"
                                       value="{{ old('site_name', $settings['site_name'] ?? config('app.name')) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Contact Email</label>
                                <input type="email" name="contact_email" class="form-control"
                                       value="{{ old('contact_email', $settings['contact_email'] ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="tel" name="phone" class="form-control"
                                       value="{{ old('phone', $settings['phone'] ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Address</label>
                                <input type="text" name="address" class="form-control"
                                       value="{{ old('address', $settings['address'] ?? '') }}">
                            </div>

                            {{-- Logo --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Site Logo</label>
                                <div class="logo-preview-box mb-2" @click="$refs.logoInput.click()">
                                    <template x-if="logoPreview">
                                        <img :src="logoPreview" alt="Logo">
                                    </template>
                                    <template x-if="!logoPreview">
                                        <div class="text-muted small text-center">
                                            <i class="fas fa-image d-block mb-1"></i>Click to upload
                                        </div>
                                    </template>
                                </div>
                                <input type="file" name="logo" class="d-none" accept="image/*"
                                       x-ref="logoInput" @change="previewLogo($event)">
                            </div>

                            {{-- Favicon --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Favicon</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="favicon-preview-box" @click="$refs.faviconInput.click()">
                                        <template x-if="faviconPreview">
                                            <img :src="faviconPreview" alt="Favicon">
                                        </template>
                                        <template x-if="!faviconPreview">
                                            <i class="fas fa-globe text-muted"></i>
                                        </template>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-primary d-block mb-1"
                                                @click="$refs.faviconInput.click()">
                                            Upload
                                        </button>
                                        <div class="form-text">32×32px .ico or .png</div>
                                    </div>
                                </div>
                                <input type="file" name="favicon" class="d-none" accept="image/*,.ico"
                                       x-ref="faviconInput" @change="previewFavicon($event)">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">About / Tagline</label>
                                <textarea name="about" class="form-control" rows="3"
                                          placeholder="Brief about your blog...">{{ old('about', $settings['about'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-end py-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save General Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ══ SEO ══ --}}
        <div x-show="tab === 'seo'" x-transition>
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="seo">

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h6 class="fw-bold mb-0"><i class="fas fa-search text-success me-2"></i>SEO Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Default Meta Title</label>
                                <input type="text" name="default_meta_title" class="form-control"
                                       value="{{ old('default_meta_title', $settings['default_meta_title'] ?? '') }}"
                                       maxlength="60" placeholder="Default page title...">
                                <div class="form-text">Max 60 characters. Used when a page has no custom title.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Default Meta Description</label>
                                <textarea name="default_meta_description" class="form-control" rows="3"
                                          maxlength="160"
                                          placeholder="Default meta description...">{{ old('default_meta_description', $settings['default_meta_description'] ?? '') }}</textarea>
                                <div class="form-text">Max 160 characters.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Google Analytics ID</label>
                                <input type="text" name="google_analytics_id" class="form-control"
                                       value="{{ old('google_analytics_id', $settings['google_analytics_id'] ?? '') }}"
                                       placeholder="G-XXXXXXXXXX or UA-XXXXXX-X">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Google Search Console Verification</label>
                                <input type="text" name="google_search_console" class="form-control"
                                       value="{{ old('google_search_console', $settings['google_search_console'] ?? '') }}"
                                       placeholder="Meta content value...">
                                <div class="form-text">The content value from the meta tag verification method.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Robots.txt</label>
                                <select name="robots" class="form-select">
                                    <option value="index,follow" {{ ($settings['robots'] ?? 'index,follow') === 'index,follow' ? 'selected' : '' }}>index, follow</option>
                                    <option value="noindex,nofollow" {{ ($settings['robots'] ?? '') === 'noindex,nofollow' ? 'selected' : '' }}>noindex, nofollow</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sitemap</label>
                                <div class="d-flex gap-2 align-items-center mt-1">
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        <i class="fas fa-check me-1"></i>Auto-generated
                                    </span>
                                    <a href="/sitemap.xml" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-external-link-alt me-1"></i>View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-end py-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save SEO Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ══ Social ══ --}}
        <div x-show="tab === 'social'" x-transition>
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="social">

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h6 class="fw-bold mb-0"><i class="fas fa-share-alt text-info me-2"></i>Social Media</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @php
                                $socials = [
                                    'facebook'  => ['icon'=>'fab fa-facebook','label'=>'Facebook URL','color'=>'text-primary'],
                                    'twitter'   => ['icon'=>'fab fa-x-twitter','label'=>'Twitter / X URL','color'=>'text-dark'],
                                    'instagram' => ['icon'=>'fab fa-instagram','label'=>'Instagram URL','color'=>'text-danger'],
                                    'linkedin'  => ['icon'=>'fab fa-linkedin','label'=>'LinkedIn URL','color'=>'text-primary'],
                                    'youtube'   => ['icon'=>'fab fa-youtube','label'=>'YouTube URL','color'=>'text-danger'],
                                    'tiktok'    => ['icon'=>'fab fa-tiktok','label'=>'TikTok URL','color'=>'text-dark'],
                                ];
                            @endphp
                            @foreach($socials as $key => $social)
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="{{ $social['icon'] }} {{ $social['color'] }} me-1"></i>{{ $social['label'] }}
                                </label>
                                <input type="url" name="social_{{ $key }}" class="form-control"
                                       value="{{ old('social_' . $key, $settings['social_' . $key] ?? '') }}"
                                       placeholder="https://...">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-end py-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Social Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ══ Mail ══ --}}
        <div x-show="tab === 'mail'" x-transition>
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="mail">

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h6 class="fw-bold mb-0"><i class="fas fa-envelope text-warning me-2"></i>Mail Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mail Driver</label>
                                <select name="mail_mailer" class="form-select">
                                    <option value="smtp"     {{ ($settings['mail_mailer'] ?? 'smtp') === 'smtp'     ? 'selected' : '' }}>SMTP</option>
                                    <option value="sendmail" {{ ($settings['mail_mailer'] ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                    <option value="mailgun"  {{ ($settings['mail_mailer'] ?? '') === 'mailgun'  ? 'selected' : '' }}>Mailgun</option>
                                    <option value="ses"      {{ ($settings['mail_mailer'] ?? '') === 'ses'      ? 'selected' : '' }}>Amazon SES</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">SMTP Host</label>
                                <input type="text" name="mail_host" class="form-control"
                                       value="{{ old('mail_host', $settings['mail_host'] ?? '') }}"
                                       placeholder="smtp.mailgun.org">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">SMTP Port</label>
                                <input type="number" name="mail_port" class="form-control"
                                       value="{{ old('mail_port', $settings['mail_port'] ?? 587) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">SMTP Encryption</label>
                                <select name="mail_encryption" class="form-select">
                                    <option value="tls" {{ ($settings['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ ($settings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="">None</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">SMTP Username</label>
                                <input type="text" name="mail_username" class="form-control"
                                       value="{{ old('mail_username', $settings['mail_username'] ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">SMTP Password</label>
                                <input type="password" name="mail_password" class="form-control"
                                       value="{{ old('mail_password', $settings['mail_password'] ?? '') }}"
                                       placeholder="Leave blank to keep current">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">From Name</label>
                                <input type="text" name="mail_from_name" class="form-control"
                                       value="{{ old('mail_from_name', $settings['mail_from_name'] ?? config('app.name')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">From Address</label>
                                <input type="email" name="mail_from_address" class="form-control"
                                       value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-paper-plane me-1"></i>Send Test Email
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Mail Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ══ Appearance ══ --}}
        <div x-show="tab === 'appearance'" x-transition>
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="appearance">

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h6 class="fw-bold mb-0"><i class="fas fa-palette text-info me-2"></i>Appearance</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Posts per Page</label>
                                <input type="number" name="posts_per_page" class="form-control" min="1" max="100"
                                       value="{{ old('posts_per_page', $settings['posts_per_page'] ?? 12) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Default Post Layout</label>
                                <select name="post_layout" class="form-select">
                                    <option value="grid" {{ ($settings['post_layout'] ?? 'grid') === 'grid' ? 'selected' : '' }}>Grid</option>
                                    <option value="list" {{ ($settings['post_layout'] ?? '') === 'list' ? 'selected' : '' }}>List</option>
                                    <option value="masonry" {{ ($settings['post_layout'] ?? '') === 'masonry' ? 'selected' : '' }}>Masonry</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Primary Color</label>
                                <div class="input-group">
                                    <input type="color" name="primary_color" class="form-control form-control-color"
                                           value="{{ old('primary_color', $settings['primary_color'] ?? '#0d6efd') }}">
                                    <input type="text" class="form-control"
                                           value="{{ old('primary_color', $settings['primary_color'] ?? '#0d6efd') }}"
                                           onchange="this.previousElementSibling.value=this.value">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Footer Text</label>
                                <input type="text" name="footer_text" class="form-control"
                                       value="{{ old('footer_text', $settings['footer_text'] ?? '© ' . date('Y') . ' All rights reserved.') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Custom CSS</label>
                                <textarea name="custom_css" class="form-control font-monospace" rows="6"
                                          placeholder="/* Custom CSS overrides */">{{ old('custom_css', $settings['custom_css'] ?? '') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Custom JS (before &lt;/body&gt;)</label>
                                <textarea name="custom_js" class="form-control font-monospace" rows="4"
                                          placeholder="// Custom JavaScript">{{ old('custom_js', $settings['custom_js'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-end py-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Appearance Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ══ Comments ══ --}}
        <div x-show="tab === 'comments'" x-transition>
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="comments">

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h6 class="fw-bold mb-0"><i class="fas fa-comments text-warning me-2"></i>Comment Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_comments"
                                           name="enable_comments" value="1"
                                           {{ ($settings['enable_comments'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="enable_comments">
                                        Enable Comments
                                    </label>
                                    <div class="form-text">Allow readers to comment on posts.</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="require_approval"
                                           name="require_approval" value="1"
                                           {{ ($settings['require_approval'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="require_approval">
                                        Require Approval
                                    </label>
                                    <div class="form-text">New comments are held for moderation before appearing.</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="notify_on_comment"
                                           name="notify_on_comment" value="1"
                                           {{ ($settings['notify_on_comment'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="notify_on_comment">
                                        Email Notification on New Comment
                                    </label>
                                </div>
                            </div>
                            <div class="col-12"><hr></div>
                            <div class="col-12">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-shield-alt text-success me-2"></i>reCAPTCHA</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">reCAPTCHA Site Key</label>
                                <input type="text" name="recaptcha_site_key" class="form-control"
                                       value="{{ old('recaptcha_site_key', $settings['recaptcha_site_key'] ?? '') }}"
                                       placeholder="6Lc...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">reCAPTCHA Secret Key</label>
                                <input type="text" name="recaptcha_secret_key" class="form-control"
                                       value="{{ old('recaptcha_secret_key', $settings['recaptcha_secret_key'] ?? '') }}"
                                       placeholder="6Lc...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Close Comments After (days)</label>
                                <input type="number" name="close_comments_after" class="form-control" min="0"
                                       value="{{ old('close_comments_after', $settings['close_comments_after'] ?? 0) }}"
                                       placeholder="0 = never close">
                                <div class="form-text">Set 0 to never auto-close.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Max Comment Length</label>
                                <input type="number" name="max_comment_length" class="form-control" min="100"
                                       value="{{ old('max_comment_length', $settings['max_comment_length'] ?? 1000) }}">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-end py-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Comment Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    function settingsPage() {
        return {
            tab: '{{ request("tab", "general") }}',
            logoPreview: {{ isset($settings['logo']) && $settings['logo'] ? '"' . asset('storage/' . $settings['logo']) . '"' : 'null' }},
            faviconPreview: {{ isset($settings['favicon']) && $settings['favicon'] ? '"' . asset('storage/' . $settings['favicon']) . '"' : 'null' }},

            previewLogo(event) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = e => this.logoPreview = e.target.result;
                reader.readAsDataURL(file);
            },

            previewFavicon(event) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = e => this.faviconPreview = e.target.result;
                reader.readAsDataURL(file);
            }
        }
    }
</script>
@endpush
