# Global Blog Website

A modern, fast, secure, and SEO-friendly blog platform built with Laravel 12.

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12 / PHP 8.3+ |
| Database | MySQL 8.0 |
| Cache | Redis 7 |
| Frontend | Bootstrap 5.3, Blade, CKEditor 5 |
| Server | Nginx 1.25 |
| Container | Docker + Docker Compose |

## Features

- Multi-role system: Guest, Registered User, Author, Administrator
- Full blog management with rich text editor (CKEditor 5)
- Draft / Published / Scheduled post status
- Category and tag management with images and SEO
- Nested comment system with moderation and reCAPTCHA protection
- MySQL Full-Text search across posts, categories, tags, authors
- Media library with image upload, optimization, and management
- Newsletter subscription with email verification
- Advertisement management (Google AdSense + banner ads) with position control
- Dynamic website settings (logo, favicon, social links, SEO defaults)
- SEO: XML Sitemap, Robots.txt, RSS Feed, Open Graph, Twitter Card, Schema.org JSON-LD
- Analytics dashboard with Chart.js visualizations and GA4 integration
- Performance: Redis caching, Gzip compression, lazy loading, OPcache
- Security: CSRF, XSS protection, rate limiting, SQL injection prevention, password hashing
- Mobile responsive across all screen sizes
- Docker-ready for development and production

## Quick Start (Docker)

```bash
git clone <your-repo-url> blog
cd blog
cp .env.example .env
docker-compose up -d --build
```

Visit http://localhost — the entrypoint script runs migrations and seeds automatically.

**Default credentials:**
- Admin: `admin@myblog.com` / `password`
- Author: `author@myblog.com` / `password`

Admin panel: http://localhost/admin

## Manual Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure .env DB_* and REDIS_* values
php artisan migrate --seed
npm install && npm run build
php artisan storage:link
php artisan serve
```

## Docker Commands (Makefile shortcuts)

| Command | Description |
|---------|-------------|
| `make up` | Start all containers |
| `make down` | Stop all containers |
| `make fresh` | Rebuild from scratch with fresh DB |
| `make shell` | Open bash in app container |
| `make migrate` | Run migrations |
| `make seed` | Run seeders |
| `make cache-clear` | Clear all caches |
| `make logs` | Tail app logs |

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_NAME` | Site name | Global Blog |
| `APP_ENV` | Environment | production |
| `APP_KEY` | Encryption key | (auto-generated) |
| `APP_DEBUG` | Debug mode | false |
| `DB_HOST` | MySQL host | mysql |
| `DB_DATABASE` | Database name | blog |
| `REDIS_HOST` | Redis host | redis |
| `RECAPTCHA_SITE_KEY` | Google reCAPTCHA v2 site key | |
| `RECAPTCHA_SECRET_KEY` | Google reCAPTCHA v2 secret | |
| `GOOGLE_ANALYTICS_ID` | GA4 Measurement ID | |
| `MAIL_*` | SMTP mail settings | |

## User Roles

| Role | Capabilities |
|------|-------------|
| Guest | View posts, search, share, submit comments |
| Registered User | All guest actions + save favorites, manage profile |
| Author | Create/edit own posts, manage own media |
| Administrator | Full access to all content, users, settings |

## License

MIT
