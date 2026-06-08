<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Admin posts per page.
     */
    private const PER_PAGE = 15;

    /**
     * Display a paginated listing of all posts.
     */
    public function index(Request $request): View
    {
        $query = Post::with(['category', 'author'])
            ->withCount('comments');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('author')) {
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
        $categories = Category::topLevel()->get();

        return view('admin.posts.index', compact('posts', 'categories'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $tags       = Tag::orderBy('name')->get();

        return view('admin.posts.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created post.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')
                ->store('posts/images', 'public');
        }

        // Set author to the current admin user if not specified
        $data['user_id'] = $request->input('user_id', auth()->id());

        // Auto-set published_at when publishing immediately
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post = Post::create($data);

        // Sync tags
        if ($request->filled('tags')) {
            $tagIds = $this->resolveTagIds($request->input('tags', []));
            $post->tags()->sync($tagIds);
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post created successfully.');
    }

    /**
     * Show the form for editing an existing post.
     */
    public function edit(Post $post): View
    {
        $categories  = Category::orderBy('name')->get();
        $tags        = Tag::orderBy('name')->get();
        $selectedIds = $post->tags->pluck('id')->toArray();

        return view('admin.posts.edit', compact('post', 'categories', 'tags', 'selectedIds'));
    }

    /**
     * Update an existing post.
     */
    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $data = $request->validated();

        // Handle featured image update
        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }

            $data['featured_image'] = $request->file('featured_image')
                ->store('posts/images', 'public');
        }

        // If status changed to published and no published_at set, stamp it now
        if ($data['status'] === 'published' && empty($data['published_at']) && $post->status !== 'published') {
            $data['published_at'] = now();
        }

        $post->update($data);

        // Sync tags
        $tagIds = $this->resolveTagIds($request->input('tags', []));
        $post->tags()->sync($tagIds);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Display a single post (admin preview).
     */
    public function show(Post $post): View
    {
        $post->load(['category', 'author', 'tags', 'comments.user']);

        return view('admin.posts.show', compact('post'));
    }

    /**
     * Delete a post (soft delete).
     */
    public function destroy(Post $post): RedirectResponse
    {
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }

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
