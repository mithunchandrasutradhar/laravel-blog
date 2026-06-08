<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Http\Requests\Author\StorePostRequest;
use App\Http\Requests\Author\UpdatePostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Posts per page in the author's post list.
     */
    private const PER_PAGE = 15;

    /**
     * Display a listing of the author's own posts.
     */
    public function index(Request $request): View
    {
        $query = Post::where('user_id', auth()->id())
            ->with('category')
            ->withCount('comments');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $query->where('title', 'LIKE', "%{$request->q}%");
        }

        $posts = $query->latest()->paginate(self::PER_PAGE)->withQueryString();

        return view('author.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $tags       = Tag::orderBy('name')->get();

        return view('author.posts.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created post (always owned by the logged-in author).
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        $data            = $request->validated();
        $data['user_id'] = auth()->id();

        // Authors can save as draft or submit for review; they cannot publish directly
        // unless the site is configured to allow it. For now, allow full status control.
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')
                ->store('posts/images', 'public');
        }

        $post = Post::create($data);

        // Sync tags
        $tagIds = $this->resolveTagIds($request->input('tags', []));
        $post->tags()->sync($tagIds);

        return redirect()->route('author.posts.index')
            ->with('success', 'Post created successfully.');
    }

    /**
     * Show a single post (preview for the author).
     */
    public function show(Post $post): View
    {
        Gate::authorize('view', $post);

        return view('author.posts.show', compact('post'));
    }

    /**
     * Show the edit form for an author's own post.
     */
    public function edit(Post $post): View
    {
        Gate::authorize('update', $post);

        $categories  = Category::orderBy('name')->get();
        $tags        = Tag::orderBy('name')->get();
        $selectedIds = $post->tags->pluck('id')->toArray();

        return view('author.posts.edit', compact('post', 'categories', 'tags', 'selectedIds'));
    }

    /**
     * Update a post (author may only update their own posts).
     */
    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);

        $data = $request->validated();

        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }

            $data['featured_image'] = $request->file('featured_image')
                ->store('posts/images', 'public');
        }

        if ($data['status'] === 'published' && empty($data['published_at']) && $post->status !== 'published') {
            $data['published_at'] = now();
        }

        $post->update($data);

        $tagIds = $this->resolveTagIds($request->input('tags', []));
        $post->tags()->sync($tagIds);

        return redirect()->route('author.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Delete the author's own post.
     */
    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }

        $post->delete();

        return redirect()->route('author.posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Resolve tag names/IDs to IDs, creating missing tags.
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
