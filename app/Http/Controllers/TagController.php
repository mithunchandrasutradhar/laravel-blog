<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\View\View;

class TagController extends Controller
{
    /**
     * Posts per page.
     */
    private const PER_PAGE = 12;

    /**
     * Display posts associated with the given tag.
     */
    public function show(string $slug): View
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $posts = Post::published()
            ->withTag($tag->id)
            ->with(['category', 'author', 'tags'])
            ->latestPublished()
            ->paginate(self::PER_PAGE);

        // Related tags: other tags that appear on the same posts
        $relatedTagIds = Post::published()
            ->withTag($tag->id)
            ->join('post_tags', 'posts.id', '=', 'post_tags.post_id')
            ->where('post_tags.tag_id', '!=', $tag->id)
            ->pluck('post_tags.tag_id')
            ->unique();

        $relatedTags = Tag::whereIn('id', $relatedTagIds)->limit(10)->get();

        return view('blog.tag', compact('tag', 'posts', 'relatedTags'));
    }
}
