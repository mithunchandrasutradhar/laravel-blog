import 'bootstrap';
import Alpine from 'alpinejs';
import axios from 'axios';

window.Alpine = Alpine;
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

Alpine.start();

// Reading progress bar
document.addEventListener('DOMContentLoaded', () => {
    const bar = document.getElementById('reading-progress');
    if (bar) {
        window.addEventListener('scroll', () => {
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            bar.style.width = docHeight > 0 ? (window.scrollY / docHeight * 100) + '%' : '0%';
        });
    }

    // Back to top
    const btn = document.getElementById('back-to-top');
    if (btn) {
        window.addEventListener('scroll', () => {
            btn.style.display = window.scrollY > 400 ? 'flex' : 'none';
        });
        btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    // Auto-dismiss alerts
    document.querySelectorAll('.alert-dismissible[data-auto-dismiss]').forEach(el => {
        setTimeout(() => el.remove(), parseInt(el.dataset.autoDismiss, 10) || 5000);
    });

    // Lazy images
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries, obs) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    const img = e.target;
                    if (img.dataset.src) { img.src = img.dataset.src; delete img.dataset.src; }
                    obs.unobserve(img);
                }
            });
        });
        document.querySelectorAll('img[data-src]').forEach(img => io.observe(img));
    }

    // Copy link share
    document.querySelectorAll('.btn-copy-link').forEach(btn => {
        btn.addEventListener('click', () => {
            navigator.clipboard?.writeText(window.location.href).then(() => {
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                setTimeout(() => btn.innerHTML = orig, 2000);
            });
        });
    });

});
