<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Author;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RssController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Blog listing & single post
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])
    ->middleware('track.post.view')
    ->name('posts.show');

// Category / tag / author archives
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/tag/{slug}', [TagController::class, 'show'])->name('tag.show');
Route::get('/author/{username}', [AuthorController::class, 'show'])->name('author.show');

// Search
Route::get('/search', [SearchController::class, 'index'])->name('search');

// Static pages
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');

// Contact
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Comments (posted from blog post page)
Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');

// Newsletter
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('/newsletter/verify/{token}', [NewsletterController::class, 'verify'])->name('newsletter.verify');
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

/*
|--------------------------------------------------------------------------
| XML / Feed Routes (no CSRF, no auth)
|--------------------------------------------------------------------------
*/

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/rss.xml', [RssController::class, 'feed'])->name('rss');

/*
|--------------------------------------------------------------------------
| Post View Tracking (AJAX)
|--------------------------------------------------------------------------
*/

Route::post('/posts/{post}/view', function (\App\Models\Post $post) {
    \App\Models\PostView::record($post, request()->ip(), request()->userAgent() ?? '');
    return response()->json(['ok' => true]);
})->name('posts.track-view');

/*
|--------------------------------------------------------------------------
| Auth Routes  (guests only unless noted)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // Registration
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    // Login
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    // Password reset
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Email verification
    Route::get('/verify-email', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Confirm password
    Route::get('/confirm-password', [ConfirmPasswordController::class, 'show'])->name('password.confirm');
    Route::post('/confirm-password', [ConfirmPasswordController::class, 'store']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Saved / bookmarked posts
    Route::get('/saved-posts', [BlogController::class, 'saved'])->name('posts.saved');
    Route::post('/posts/{post}/save', [BlogController::class, 'toggleSave'])->name('posts.save');
});

/*
|--------------------------------------------------------------------------
| Author Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'author'])->prefix('author')->name('author.')->group(function () {
    Route::get('/dashboard', [Author\DashboardController::class, 'index'])->name('dashboard');

    // Author's own posts
    Route::resource('posts', Author\PostController::class);

    // Media
    Route::get('/media', [Author\MediaController::class, 'index'])->name('media.index');
    Route::post('/media', [Author\MediaController::class, 'store'])->name('media.store');
    Route::delete('/media/{media}', [Author\MediaController::class, 'destroy'])->name('media.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Posts
    Route::resource('posts', Admin\PostController::class);

    // Categories
    Route::resource('categories', Admin\CategoryController::class);

    // Tags
    Route::resource('tags', Admin\TagController::class);

    // Comments
    Route::get('/comments', [Admin\CommentController::class, 'index'])->name('comments.index');
    Route::patch('/comments/{comment}/approve', [Admin\CommentController::class, 'approve'])->name('comments.approve');
    Route::patch('/comments/{comment}/reject', [Admin\CommentController::class, 'reject'])->name('comments.reject');
    Route::delete('/comments/{comment}', [Admin\CommentController::class, 'destroy'])->name('comments.destroy');

    // Users
    Route::resource('users', Admin\UserController::class);

    // Subscribers
    Route::get('/subscribers', [Admin\SubscriberController::class, 'index'])->name('subscribers.index');
    Route::delete('/subscribers/{subscriber}', [Admin\SubscriberController::class, 'destroy'])->name('subscribers.destroy');
    Route::get('/subscribers/export', [Admin\SubscriberController::class, 'export'])->name('subscribers.export');

    // Settings
    Route::get('/settings', [Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
    Route::put('/settings/{group}', [Admin\SettingsController::class, 'updateGroup'])->name('settings.update-group');

    // Advertisements
    Route::resource('advertisements', Admin\AdvertisementController::class);

    // Media library
    Route::get('/media', [Admin\MediaController::class, 'index'])->name('media.index');
    Route::post('/media', [Admin\MediaController::class, 'store'])->name('media.store');
    Route::delete('/media/{media}', [Admin\MediaController::class, 'destroy'])->name('media.destroy');
    Route::get('/media/browse', [Admin\MediaController::class, 'browse'])->name('media.browse');

    // Analytics
    Route::get('/analytics', [Admin\AnalyticsController::class, 'index'])->name('analytics.index');
});
