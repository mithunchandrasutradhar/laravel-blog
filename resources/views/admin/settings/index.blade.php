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

<div class="row g-4" x-data="{
    tab: '{{ request('tab', 'general') }}',
    logoPreview: {{ settings('logo') ? json_encode(asset('storage/'.settings('logo'))) : 'null' }},
    faviconPreview: {{ settings('favicon') ? json_encode(asset('storage/'.settings('favicon'))) : 'null' }},
    previewLogo(e) { const f=e.target.files[0]; if(!f)return; const r=new FileReader(); r.onload=ev=>this.logoPreview=ev.target.result; r.readAsDataURL(f); },
    previewFavicon(e) { const f=e.target.files[0]; if(!f)return; const r=new FileReader(); r.onload=ev=>this.faviconPreview=ev.target.result; r.readAsDataURL(f); }
}">

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
        <div x-show="tab === 'general'">
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
                                       value="{{ old('site_name', settings('site_name', config('app.name'))) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Contact Email</label>
                                <input type="email" name="contact_email" class="form-control"
                                       value="{{ old('contact_email', settings('contact_email', '')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="tel" name="contact_phone" class="form-control"
                                       value="{{ old('contact_phone', settings('contact_phone', settings('phone', ''))) }}"
                                       placeholder="+1 (555) 000-0000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Address</label>
                                <input type="text" name="contact_address" class="form-control"
                                       value="{{ old('contact_address', settings('contact_address', settings('address', ''))) }}"
                                       placeholder="123 Main St, City, Country">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Site Description <span class="text-muted fw-normal">(shown in footer &amp; meta description)</span></label>
                                <textarea name="site_description" class="form-control" rows="2"
                                          placeholder="A short tagline or description for your blog..." maxlength="300">{{ old('site_description', settings('site_description', '')) }}</textarea>
                            </div>

                            {{-- Logo --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Site Logo</label>
                                <div class="logo-preview-box mb-2" @click="$refs.logoInput.click()">
                                    <img x-show="logoPreview" :src="logoPreview || ''" alt="Logo"
                                         style="height:60px;object-fit:contain;">
                                    <div x-show="!logoPreview" class="text-muted small text-center">
                                        <i class="fas fa-image d-block mb-1"></i>Click to upload
                                    </div>
                                </div>
                                <input type="file" name="logo" class="d-none" accept="image/*"
                                       x-ref="logoInput" @change="previewLogo($event)">
                                <div class="row g-2 mt-1">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold mb-1">Display Height (px)</label>
                                        <input type="number" name="logo_height" class="form-control form-control-sm"
                                               value="{{ old('logo_height', settings('logo_height', 38)) }}"
                                               min="16" max="200" placeholder="38">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold mb-1">Max Width (px)</label>
                                        <input type="number" name="logo_width" class="form-control form-control-sm"
                                               value="{{ old('logo_width', settings('logo_width', '')) }}"
                                               min="0" max="600" placeholder="auto">
                                    </div>
                                </div>
                                <div class="form-text">Controls logo size across the site. Leave Max Width blank for auto.</div>
                            </div>

                            {{-- Favicon --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Favicon</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="favicon-preview-box" @click="$refs.faviconInput.click()">
                                        <img x-show="faviconPreview" :src="faviconPreview || ''" alt="Favicon"
                                             style="width:48px;height:48px;object-fit:cover;">
                                        <i x-show="!faviconPreview" class="fas fa-globe text-muted"></i>
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
        <div x-show="tab === 'seo'">
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
                                       placeholder="e.g. rqXmBR16_6McEu58AIss2xXuT6bmdykSNbN7MxWtzi4">
                                <div class="form-text">Paste only the <code>content="..."</code> value from the verification meta tag — not the full tag.</div>
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
        <div x-show="tab === 'social'">
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="section" value="social">

                @php
                    $socialPlatforms = [
                        'facebook'  => [
                            'icon'    => 'fab fa-facebook-f',
                            'label'   => 'Facebook',
                            'hint'    => 'https://facebook.com/yourpage',
                            'bg'      => '#1877f2',
                            'preview' => 'fab fa-facebook-f',
                        ],
                        'twitter'   => [
                            'icon'    => 'fab fa-x-twitter',
                            'label'   => 'Twitter / X',
                            'hint'    => 'https://twitter.com/yourhandle',
                            'bg'      => '#000000',
                            'preview' => 'fab fa-x-twitter',
                        ],
                        'instagram' => [
                            'icon'    => 'fab fa-instagram',
                            'label'   => 'Instagram',
                            'hint'    => 'https://instagram.com/yourprofile',
                            'bg'      => 'linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888)',
                            'preview' => 'fab fa-instagram',
                        ],
                        'linkedin'  => [
                            'icon'    => 'fab fa-linkedin-in',
                            'label'   => 'LinkedIn',
                            'hint'    => 'https://linkedin.com/in/yourprofile',
                            'bg'      => '#0077b5',
                            'preview' => 'fab fa-linkedin-in',
                        ],
                        'youtube'   => [
                            'icon'    => 'fab fa-youtube',
                            'label'   => 'YouTube',
                            'hint'    => 'https://youtube.com/yourchannel',
                            'bg'      => '#ff0000',
                            'preview' => 'fab fa-youtube',
                        ],
                        'tiktok'    => [
                            'icon'    => 'fab fa-tiktok',
                            'label'   => 'TikTok',
                            'hint'    => 'https://tiktok.com/@yourhandle',
                            'bg'      => '#010101',
                            'preview' => 'fab fa-tiktok',
                        ],
                        'pinterest' => [
                            'icon'    => 'fab fa-pinterest-p',
                            'label'   => 'Pinterest',
                            'hint'    => 'https://pinterest.com/yourprofile',
                            'bg'      => '#e60023',
                            'preview' => 'fab fa-pinterest-p',
                        ],
                        'github'    => [
                            'icon'    => 'fab fa-github',
                            'label'   => 'GitHub',
                            'hint'    => 'https://github.com/yourusername',
                            'bg'      => '#24292e',
                            'preview' => 'fab fa-github',
                        ],
                    ];
                @endphp

                {{-- Live Preview Card --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 py-3 d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold mb-0"><i class="fas fa-eye text-primary me-2"></i>Footer Preview</h6>
                        <span class="badge bg-success bg-opacity-10 text-success small">Live</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Icons appear in the footer and contact page only when a URL is saved.</p>
                        <div class="d-flex flex-wrap gap-2" id="social-preview-row">
                            @foreach($socialPlatforms as $key => $p)
                            @php $existingUrl = settings('social_' . $key, ''); @endphp
                            <div class="social-preview-icon"
                                 id="preview-{{ $key }}"
                                 style="width:38px;height:38px;border-radius:8px;display:{{ $existingUrl ? 'flex' : 'none' }};align-items:center;justify-content:center;background:{{ $p['bg'] }};"
                                 title="{{ $p['label'] }}">
                                <i class="{{ $p['preview'] }}" style="color:#fff;font-size:.9rem;"></i>
                            </div>
                            @endforeach
                            <div id="preview-empty" style="display:{{ collect($socialPlatforms)->filter(fn($p,$k) => settings('social_'.$k))->isEmpty() ? 'block' : 'none' }};">
                                <span class="text-muted small fst-italic">No social links configured yet.</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Platform Inputs --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h6 class="fw-bold mb-0"><i class="fas fa-share-alt text-info me-2"></i>Social Media Links</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($socialPlatforms as $key => $p)
                            <div class="col-md-6">
                                <label class="form-label fw-semibold d-flex align-items-center gap-2">
                                    <span style="width:28px;height:28px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;background:{{ $p['bg'] }};flex-shrink:0;">
                                        <i class="{{ $p['icon'] }}" style="color:#fff;font-size:.75rem;"></i>
                                    </span>
                                    {{ $p['label'] }}
                                </label>
                                <input type="url"
                                       name="social_{{ $key }}"
                                       id="input-{{ $key }}"
                                       class="form-control"
                                       value="{{ old('social_' . $key, settings('social_' . $key, '')) }}"
                                       placeholder="{{ $p['hint'] }}"
                                       @input="updatePreview('{{ $key }}', $event.target.value, '{{ $p['bg'] }}')">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-end py-3">
                        <button type="submit" class="btn btn-primary px-5">
                            <i class="fas fa-save me-2"></i>Save Social Links
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ══ Mail ══ --}}
        <div x-show="tab === 'mail'">
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
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="testEmailBtn"
                                    onclick="sendTestEmail()">
                                <i class="fas fa-paper-plane me-1"></i>Send Test Email
                            </button>
                            <span id="testEmailResult" class="small" style="display:none;"></span>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Mail Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ══ Appearance ══ --}}
        <div x-show="tab === 'appearance'">
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
        <div x-show="tab === 'comments'">
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
    async function sendTestEmail() {
        const btn    = document.getElementById('testEmailBtn');
        const result = document.getElementById('testEmailResult');
        const email  = prompt('Send test email to:', '{{ auth()->user()->email }}');
        if (!email) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending…';
        result.style.display = 'none';

        try {
            const res  = await fetch('{{ route('admin.settings.test-email') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ email }),
            });
            const data = await res.json();
            result.style.display = 'inline';
            if (data.success) {
                result.className = 'small text-success';
                result.innerHTML = '<i class="fas fa-check-circle me-1"></i>' + data.message;
            } else {
                result.className = 'small text-danger';
                result.innerHTML = '<i class="fas fa-times-circle me-1"></i>' + data.message;
            }
        } catch (e) {
            result.style.display = 'inline';
            result.className = 'small text-danger';
            result.innerHTML = '<i class="fas fa-times-circle me-1"></i>Network error.';
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Send Test Email';
        }
    }

    function updatePreview(platform, url, bg) {
        const box   = document.getElementById('preview-' + platform);
        const empty = document.getElementById('preview-empty');
        if (!box) return;

        if (url && url.trim()) {
            box.style.display = 'flex';
        } else {
            box.style.display = 'none';
        }

        // Show/hide the "no links" message
        const visible = document.querySelectorAll('[id^="preview-"]:not(#preview-empty)');
        const anyVisible = Array.from(visible).some(el => el.style.display !== 'none');
        if (empty) empty.style.display = anyVisible ? 'none' : 'block';
    }
</script>
@endpush

