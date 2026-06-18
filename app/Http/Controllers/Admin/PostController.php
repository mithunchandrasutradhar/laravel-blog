<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Admin posts per page.
     */
    private const PER_PAGE = 15;

    /**
     * Whether the current user has full admin-panel access (sees all content).
     */
    private function isAdminPanel(): bool
    {
        $user = auth()->user();
        return $user->isAdmin() || $user->hasPermissionTo('panel.admin');
    }

    /**
     * Display a paginated listing of posts.
     * Admin-panel users see all posts; author-panel users see only their own.
     */
    public function index(Request $request): View
    {
        abort_if(! auth()->user()->hasPermissionTo('posts.viewAny'), 403);

        $query = Post::with(['category', 'categories', 'author'])
            ->withCount('comments');

        // Scope to own posts for non-admin users
        if (! $this->isAdminPanel()) {
            $query->where('user_id', auth()->id());
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Author filter only available to admin-panel users
        if ($this->isAdminPanel() && $request->filled('author')) {
            $query->where('user_id', $request->author);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'LIKE', "%{$q}%")
                    ->orWhere('short_description', 'LIKE', "%{$q}%");
            });
        }

        $posts      = $query->latest()->paginate(self::PER_PAGE)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.posts.index', compact('posts', 'categories'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create(): View
    {
        abort_if(! auth()->user()->hasPermissionTo('posts.create'), 403);

        $categories = Category::orderBy('name')->get();
        $tags       = Tag::orderBy('name')->get();

        return view('admin.posts.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created post.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('posts.create'), 403);

        $data = $request->validated();

        // Handle featured image: media library selection or direct file upload
        if ($request->filled('featured_image_path')) {
            $data['featured_image'] = $request->featured_image_path;
        } elseif ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')
                ->store('posts/images', 'public');
        }

        // Non-admin users always author their own posts; admin can set any user_id
        $data['user_id'] = $this->isAdminPanel()
            ? $request->input('user_id', auth()->id())
            : auth()->id();

        // Use first selected category as the primary category_id
        $categoryIds = array_map('intval', $data['category_ids']);
        $data['category_id'] = $categoryIds[0];
        unset($data['category_ids']);

        // Auto-set published_at when publishing immediately
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post = Post::create($data);

        // Sync categories pivot
        $post->categories()->sync($categoryIds);

        // Sync tags
        if ($request->filled('tags')) {
            $tagIds = $this->resolveTagIds($request->input('tags', []));
            $post->tags()->sync($tagIds);
        }

        ActivityLogger::log('post.created', "Created post \"{$post->title}\"", ['status' => $post->status], $post);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post created successfully.');
    }

    /**
     * Show the form for editing an existing post.
     */
    public function edit(Post $post): View
    {
        abort_if(! auth()->user()->hasPermissionTo('posts.update'), 403);

        if (! $this->isAdminPanel() && $post->user_id !== auth()->id()) {
            abort(403, 'You can only edit your own posts.');
        }
        $categories          = Category::orderBy('name')->get();
        $tags                = Tag::orderBy('name')->get();
        $selectedIds         = $post->tags->pluck('id')->toArray();
        $post->loadMissing('categories');
        $selectedCategoryIds = $post->categories->pluck('id')->toArray();
        // Fallback for posts that predate the pivot table
        if (empty($selectedCategoryIds) && $post->category_id) {
            $selectedCategoryIds = [$post->category_id];
        }

        return view('admin.posts.edit', compact('post', 'categories', 'tags', 'selectedIds', 'selectedCategoryIds'));
    }

    /**
     * Update an existing post.
     */
    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('posts.update'), 403);

        if (! $this->isAdminPanel() && $post->user_id !== auth()->id()) {
            abort(403, 'You can only edit your own posts.');
        }

        $data = $request->validated();

        // Ensure slug is never null — fall back to generating one from the title
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Handle featured image update: media library, direct upload, or removal
        if ($request->filled('featured_image_path')) {
            if ($post->featured_image && !str_starts_with($post->featured_image, 'http')) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $data['featured_image'] = $request->featured_image_path;
        } elseif ($request->hasFile('featured_image')) {
            if ($post->featured_image && !str_starts_with($post->featured_image, 'http')) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')
                ->store('posts/images', 'public');
        } elseif ($request->input('remove_featured_image') === '1') {
            if ($post->featured_image && !str_starts_with($post->featured_image, 'http')) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $data['featured_image'] = null;
        }

        // Use first selected category as the primary category_id
        $categoryIds = array_map('intval', $data['category_ids']);
        $data['category_id'] = $categoryIds[0];
        unset($data['category_ids']);

        // If status changed to published and no published_at set, stamp it now
        if ($data['status'] === 'published' && empty($data['published_at']) && $post->status !== 'published') {
            $data['published_at'] = now();
        }

        $post->update($data);

        // Sync categories pivot
        $post->categories()->sync($categoryIds);

        // Sync tags
        $tagIds = $this->resolveTagIds($request->input('tags', []));
        $post->tags()->sync($tagIds);

        ActivityLogger::log('post.updated', "Updated post \"{$post->title}\"", ['status' => $post->status], $post);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Display a single post (admin preview).
     */
    public function show(Post $post): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('posts.viewAny'), 403);

        return redirect()->route('admin.posts.edit', $post);
    }

    /**
     * Bulk-delete multiple posts by ID.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('posts.delete'), 403);

        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->withErrors(['error' => 'No posts selected.']);
        }

        Post::whereIn('id', $ids)->each(function (Post $post) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $post->delete();
        });

        ActivityLogger::log('post.bulk_deleted', 'Bulk deleted ' . count($ids) . ' post(s)', ['ids' => $ids]);

        return redirect()->route('admin.posts.index')
            ->with('success', count($ids) . ' post(s) deleted.');
    }

    /**
     * Delete a post (soft delete).
     */
    public function destroy(Post $post): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('posts.delete'), 403);

        if (! $this->isAdminPanel() && $post->user_id !== auth()->id()) {
            abort(403, 'You can only delete your own posts.');
        }

        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }

        ActivityLogger::log('post.deleted', "Deleted post \"{$post->title}\"", ['id' => $post->id]);

        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Resolve an array of tag names/IDs to tag IDs, creating new tags as needed.
     *
     * @param  array<int|string>  $tags
     * @return array<int>
     */
    private function resolveTagIds(array $tags): array
    {
        return collect($tags)->map(function ($tag) {
            if (is_numeric($tag)) {
                return (int) $tag;
            }

            return Tag::findOrCreateByName($tag)->id;
        })->toArray();
    }
}
