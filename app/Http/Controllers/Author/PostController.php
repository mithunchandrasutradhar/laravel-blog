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
    private const PER_PAGE = 15;

    public function index(Request $request): View
    {
        $query = Post::where('user_id', auth()->id())
            ->with(['category', 'categories'])
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

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $tags       = Tag::orderBy('name')->get();

        return view('author.posts.create', compact('categories', 'tags'));
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $validated   = $request->validated();
        $categoryIds = $validated['category_ids'] ?? [];

        // Pull out fields handled separately
        unset($validated['category_ids'], $validated['featured_image_path'], $validated['tags']);

        $validated['user_id']     = auth()->id();
        $validated['category_id'] = $categoryIds[0] ?? null;
        $validated['is_featured']    = $request->boolean('is_featured');
        $validated['allow_comments'] = $request->boolean('allow_comments', true);

        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        // Featured image: prefer media-picker path, fall back to direct file upload
        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')
                ->store('posts/images', 'public');
        } elseif ($request->filled('featured_image_path')) {
            $validated['featured_image'] = $request->input('featured_image_path');
        }

        $post = Post::create($validated);

        // Sync all selected categories (many-to-many)
        if ($categoryIds) {
            $post->categories()->sync($categoryIds);
        }

        $tagIds = $this->resolveTagIds($request->input('tags', []));
        $post->tags()->sync($tagIds);

        return redirect()->route('author.posts.index')
            ->with('success', 'Post created successfully.');
    }

    public function show(Post $post): View
    {
        Gate::authorize('view', $post);

        return view('author.posts.show', compact('post'));
    }

    public function edit(Post $post): View
    {
        Gate::authorize('update', $post);

        $categories      = Category::orderBy('name')->get();
        $tags            = Tag::orderBy('name')->get();
        $selectedTagIds  = $post->tags->pluck('id')->toArray();
        $selectedCatIds  = $post->categories->pluck('id')->toArray();
        if (empty($selectedCatIds) && $post->category_id) {
            $selectedCatIds = [$post->category_id];
        }

        return view('author.posts.edit', compact('post', 'categories', 'tags', 'selectedTagIds', 'selectedCatIds'));
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);

        $validated   = $request->validated();
        $categoryIds = $validated['category_ids'] ?? [];

        unset($validated['category_ids'], $validated['featured_image_path'], $validated['tags']);

        $validated['category_id']    = $categoryIds[0] ?? $post->category_id;
        $validated['is_featured']    = $request->boolean('is_featured');
        $validated['allow_comments'] = $request->boolean('allow_comments', true);

        // Featured image handling
        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $validated['featured_image'] = $request->file('featured_image')
                ->store('posts/images', 'public');
        } elseif ($request->filled('featured_image_path')) {
            $validated['featured_image'] = $request->input('featured_image_path');
        } elseif ($request->boolean('remove_featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $validated['featured_image'] = null;
        } else {
            unset($validated['featured_image']);
        }

        if ($validated['status'] === 'published' && empty($validated['published_at']) && $post->status !== 'published') {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        if ($categoryIds) {
            $post->categories()->sync($categoryIds);
        }

        $tagIds = $this->resolveTagIds($request->input('tags', []));
        $post->tags()->sync($tagIds);

        return redirect()->route('author.posts.edit', $post)
            ->with('success', 'Post updated successfully.');
    }

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
