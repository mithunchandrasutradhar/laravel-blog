/**
 * app.js — Custom Blog JavaScript
 * ES6+ Vanilla JS, no jQuery dependency
 * Pairs with Bootstrap 5.3 and Alpine.js
 */

'use strict';

/* =====================================================
   Reading Progress Bar
   ===================================================== */
(function initReadingProgress() {
    const bar     = document.getElementById('reading-progress-bar');
    const article = document.getElementById('post-content');
    if (!bar || !article) return;

    bar.classList.remove('d-none');

    function updateProgress() {
        const articleTop    = article.getBoundingClientRect().top + window.scrollY;
        const articleBottom = articleTop + article.offsetHeight;
        const scrolled      = window.scrollY;
        const windowH       = window.innerHeight;
        const total         = articleBottom - articleTop - windowH;
        const progress      = Math.min(100, Math.max(0, ((scrolled - articleTop) / total) * 100));
        bar.style.width = progress + '%';
    }

    window.addEventListener('scroll', updateProgress, { passive: true });
    updateProgress();
})();


/* =====================================================
   Back to Top Button
   ===================================================== */
(function initBackToTop() {
    const btn = document.getElementById('backToTop');
    if (!btn) return;

    window.addEventListener('scroll', function() {
        if (window.scrollY > 400) {
            btn.style.display = 'flex';
            btn.style.alignItems = 'center';
            btn.style.justifyContent = 'center';
            requestAnimationFrame(() => btn.classList.add('visible'));
        } else {
            btn.classList.remove('visible');
            setTimeout(() => {
                if (!btn.classList.contains('visible')) btn.style.display = 'none';
            }, 300);
        }
    }, { passive: true });

    btn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();


/* =====================================================
   Navbar Scroll Shadow
   ===================================================== */
(function initNavbarScroll() {
    const header = document.querySelector('.site-header');
    if (!header) return;

    window.addEventListener('scroll', function() {
        header.classList.toggle('scrolled', window.scrollY > 10);
    }, { passive: true });
})();


/* =====================================================
   Copy Link Share Button
   ===================================================== */
(function initCopyLink() {
    document.querySelectorAll('[data-copy-url]').forEach(btn => {
        btn.addEventListener('click', async function() {
            const url  = this.dataset.copyUrl;
            const text = this.querySelector('.copy-link-text');

            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(url);
                } else {
                    // Fallback for older browsers
                    const ta = document.createElement('textarea');
                    ta.value = url;
                    ta.style.position = 'fixed';
                    ta.style.opacity  = '0';
                    document.body.appendChild(ta);
                    ta.focus();
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                }

                this.classList.add('copied');
                if (text) text.textContent = 'Copied!';
                this.querySelector('i')?.classList.replace('fa-link', 'fa-check');

                setTimeout(() => {
                    this.classList.remove('copied');
                    if (text) text.textContent = 'Copy Link';
                    this.querySelector('i')?.classList.replace('fa-check', 'fa-link');
                }, 2500);
            } catch (err) {
                console.warn('Copy failed:', err);
            }
        });
    });
})();


/* =====================================================
   Lazy Image Loading (IntersectionObserver)
   ===================================================== */
(function initLazyImages() {
    const lazyImages = document.querySelectorAll('img.lazy-img[data-src]');
    if (!lazyImages.length) return;

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                const img = entry.target;
                img.src = img.dataset.src;
                if (img.dataset.srcset) img.srcset = img.dataset.srcset;
                img.addEventListener('load', () => img.classList.add('loaded'), { once: true });
                img.addEventListener('error', () => img.classList.add('loaded'), { once: true });
                observer.unobserve(img);
            });
        }, {
            rootMargin: '200px 0px',
            threshold: 0
        });

        lazyImages.forEach(img => observer.observe(img));
    } else {
        // Fallback: load all
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
            if (img.dataset.srcset) img.srcset = img.dataset.srcset;
            img.classList.add('loaded');
        });
    }
})();


/* =====================================================
   Search Autocomplete (Debounced AJAX)
   ===================================================== */
(function initSearchAutocomplete() {
    const searchInput      = document.getElementById('desktopSearch');
    const suggestionsBox   = document.getElementById('searchSuggestions');
    if (!searchInput || !suggestionsBox) return;

    let debounceTimer = null;
    let currentQuery  = '';
    let abortCtrl     = null;

    function debounce(fn, delay) {
        return function(...args) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function renderSuggestions(items) {
        suggestionsBox.innerHTML = '';

        if (!items || !items.length) {
            suggestionsBox.classList.remove('show');
            return;
        }

        items.forEach(item => {
            const a = document.createElement('a');
            a.className  = 'search-suggestion-item';
            a.href       = item.url;
            a.innerHTML  = `
                ${item.thumbnail ? `<img src="${item.thumbnail}" alt="${escapeHtml(item.title)}" loading="lazy">` : ''}
                <div>
                    <div class="fw-medium">${escapeHtml(item.title)}</div>
                    ${item.category ? `<small class="text-muted">${escapeHtml(item.category)}</small>` : ''}
                </div>
            `;
            suggestionsBox.appendChild(a);
        });

        // "View all results" link
        const viewAll = document.createElement('a');
        viewAll.className = 'search-suggestion-item border-top fw-semibold text-primary';
        viewAll.href = `/search?q=${encodeURIComponent(currentQuery)}`;
        viewAll.innerHTML = `<i class="fas fa-search me-2"></i>View all results for "<em>${escapeHtml(currentQuery)}</em>"`;
        suggestionsBox.appendChild(viewAll);

        suggestionsBox.classList.add('show');
    }

    async function fetchSuggestions(query) {
        if (abortCtrl) abortCtrl.abort();
        abortCtrl = new AbortController();

        try {
            const res = await fetch(`/api/search/suggest?q=${encodeURIComponent(query)}`, {
                signal: abortCtrl.signal,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            renderSuggestions(data.results || data);
        } catch (err) {
            if (err.name !== 'AbortError') console.warn('Search suggest error:', err);
        }
    }

    const debouncedFetch = debounce(fetchSuggestions, 300);

    searchInput.addEventListener('input', function() {
        currentQuery = this.value.trim();
        if (currentQuery.length < 2) {
            suggestionsBox.classList.remove('show');
            suggestionsBox.innerHTML = '';
            return;
        }
        debouncedFetch(currentQuery);
    });

    // Close on outside click
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.classList.remove('show');
        }
    });

    // Keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        const items = suggestionsBox.querySelectorAll('.search-suggestion-item');
        const active = suggestionsBox.querySelector('.search-suggestion-item:focus, .search-suggestion-item.keyboard-active');
        let idx = Array.from(items).indexOf(active);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            idx = Math.min(idx + 1, items.length - 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            idx = Math.max(idx - 1, -1);
        } else if (e.key === 'Escape') {
            suggestionsBox.classList.remove('show');
            return;
        } else { return; }

        items.forEach(i => i.classList.remove('keyboard-active'));
        if (idx >= 0) {
            items[idx].classList.add('keyboard-active');
            items[idx].focus();
        } else {
            searchInput.focus();
        }
    });
})();


/* =====================================================
   Newsletter Form AJAX Submit (Alpine.js helper)
   - The main logic is in the Blade partial via Alpine x-data
   - This provides a vanilla fallback for non-Alpine contexts
   ===================================================== */
(function initNewsletterFallback() {
    document.querySelectorAll('.newsletter-form:not([x-data])').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const email   = form.querySelector('input[type="email"]')?.value?.trim();
            const btn     = form.querySelector('button[type="submit"]');
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');

            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showFormError(form, 'Please enter a valid email address.');
                return;
            }

            const originalText = btn?.innerHTML;
            if (btn) btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            try {
                const res = await fetch('/newsletter/subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfMeta?.content ?? '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email })
                });

                const data = await res.json();
                if (res.ok) {
                    form.innerHTML = `<div class="text-center py-2"><i class="fas fa-check-circle text-success fa-2x mb-2 d-block"></i><p class="mb-0 fw-semibold">Subscribed! Thank you.</p></div>`;
                } else {
                    if (btn) btn.innerHTML = originalText;
                    showFormError(form, data.message || 'Subscription failed.');
                }
            } catch {
                if (btn) btn.innerHTML = originalText;
                showFormError(form, 'Network error. Please try again.');
            }
        });
    });

    function showFormError(form, message) {
        let errEl = form.querySelector('.newsletter-error');
        if (!errEl) {
            errEl = document.createElement('div');
            errEl.className = 'newsletter-error text-danger small mt-1';
            form.appendChild(errEl);
        }
        errEl.textContent = message;
    }
})();


/* =====================================================
   Post View Tracking
   ===================================================== */
(function initViewTracking() {
    const article = document.getElementById('post-article');
    if (!article) return;

    const postId   = article.dataset.postId;
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!postId) return;

    // Delay tracking by 5s so bots/refreshes don't count
    let tracked = false;
    let trackTimeout = setTimeout(() => {
        if (tracked) return;
        fetch(`/api/posts/${postId}/view`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfMeta?.content ?? '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        }).catch(() => {});
        tracked = true;
    }, 5000);

    // Cancel if user leaves before 5s
    window.addEventListener('beforeunload', () => clearTimeout(trackTimeout));
})();


/* =====================================================
   Comment Reply Toggle
   (Primary logic in Blade, this handles edge cases)
   ===================================================== */
(function initCommentReplies() {
    // Ensure forms outside blade script are wired up
    document.querySelectorAll('.comment-reply-toggle').forEach(btn => {
        if (btn.dataset.wired) return;
        btn.dataset.wired = '1';

        btn.addEventListener('click', function() {
            const id     = this.dataset.commentId;
            const formEl = document.getElementById('reply-form-' + id);
            const main   = document.getElementById('replyParentId');
            if (!formEl) return;

            const isHidden = formEl.classList.contains('d-none');
            // Close all open reply forms first
            document.querySelectorAll('[id^="reply-form-"]').forEach(f => f.classList.add('d-none'));
            if (isHidden) {
                formEl.classList.remove('d-none');
                if (main) main.value = id;
                formEl.querySelector('textarea')?.focus();
            }
        });
    });

    document.querySelectorAll('.comment-reply-cancel').forEach(btn => {
        if (btn.dataset.wired) return;
        btn.dataset.wired = '1';
        btn.addEventListener('click', function() {
            document.getElementById('reply-form-' + this.dataset.commentId)?.classList.add('d-none');
        });
    });
})();


/* =====================================================
   Utility: HTML Escape
   ===================================================== */
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}


/* =====================================================
   Ad Click / Impression Tracking helper
   ===================================================== */
(function initAdTracking() {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');

    document.querySelectorAll('[data-ad-id]').forEach(zone => {
        const adId = zone.dataset.adId;
        if (!adId) return;

        // Impression via IntersectionObserver
        if ('IntersectionObserver' in window) {
            const obs = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) return;
                    fetch(`/api/ads/${adId}/impression`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfMeta?.content ?? '' }
                    }).catch(() => {});
                    obs.unobserve(zone);
                });
            }, { threshold: 0.5 });
            obs.observe(zone);
        }

        // Click tracking
        zone.querySelectorAll('[data-ad-click]').forEach(link => {
            link.addEventListener('click', () => {
                fetch(`/api/ads/${adId}/click`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfMeta?.content ?? '' }
                }).catch(() => {});
            });
        });
    });
})();


/* =====================================================
   Responsive Table Wrapper
   Wrap any table inside .post-content that lacks a wrapper
   ===================================================== */
(function wrapTables() {
    document.querySelectorAll('.post-content table').forEach(table => {
        if (table.parentElement.classList.contains('table-responsive')) return;
        const wrapper = document.createElement('div');
        wrapper.className = 'table-responsive mb-3';
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
        table.classList.add('table', 'table-bordered');
    });
})();


/* =====================================================
   Smooth Anchor Links (for ToC / jump links)
   ===================================================== */
(function initSmoothAnchors() {
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', function(e) {
            const id = this.getAttribute('href').slice(1);
            if (!id) return;
            const target = document.getElementById(id);
            if (!target) return;
            e.preventDefault();
            const offset = 80; // header height
            const top = target.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({ top, behavior: 'smooth' });
            // Update hash without jumping
            history.pushState(null, '', '#' + id);
        });
    });
})();


/* =====================================================
   Auto-dismiss Flash Messages after 6s
   ===================================================== */
(function autoDismissFlash() {
    const flashMessages = document.getElementById('flashMessages');
    if (!flashMessages) return;

    setTimeout(() => {
        flashMessages.querySelectorAll('.alert').forEach(alert => {
            const bsAlert = bootstrap.Alert?.getOrCreateInstance?.(alert);
            bsAlert?.close();
        });
    }, 6000);
})();


/* =====================================================
   Newsletter Form — Alpine.js component
   Defined globally so it works on every page
   (footer form, home section, sidebar, etc.)
   ===================================================== */
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
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                const res = await fetch('/newsletter/subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                    },
                    body: JSON.stringify({ email: this.email }),
                });
                const data = await res.json();
                if (res.ok) {
                    this.submitted = true;
                } else {
                    this.error = data.message || data.errors?.email?.[0] || 'Subscription failed. Please try again.';
                }
            } catch {
                this.error = 'Network error. Please try again.';
            } finally {
                this.loading = false;
            }
        }
    };
}
