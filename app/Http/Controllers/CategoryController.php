<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Posts per page.
     */
    private const PER_PAGE = 12;

    /**
     * Display a listing of all top-level categories.
     */
    public function index(): View
    {
        $categories = Category::topLevel()
            ->withCount(['posts' => fn ($q) => $q->where('status', 'published')->where('published_at', '<=', now())])
            ->with('allChildren')
            ->orderBy('name')
            ->get();

        // Flatten the full descendant tree so any depth of sub-category appears
        foreach ($categories as $cat) {
            $cat->setRelation('descendants', $this->flatDescendants($cat->allChildren));
        }

        return view('categories.index', compact('categories'));
    }

    private function flatDescendants(Collection $children): Collection
    {
        $flat = collect();
        foreach ($children as $child) {
            $flat->push($child);
            if ($child->relationLoaded('allChildren') && $child->allChildren->isNotEmpty()) {
                $flat = $flat->merge($this->flatDescendants($child->allChildren));
            }
        }
        return $flat;
    }

    /**
     * Display posts belonging to the given category.
     *
     * Resolves the Category by slug using implicit route model binding via a
     * manual lookup (no binding key override needed since the route param is
     * named "slug" and not "id").
     */
    public function show(string $slug): View
    {
        $category = Category::where('slug', $slug)
            ->withCount(['posts' => fn ($q) => $q->where('status', 'published')->where('published_at', '<=', now())])
            ->firstOrFail();

        // Include posts in child categories as well
        $categoryIds = $this->getCategoryTree($category);

        $posts = Post::published()
            ->whereIn('category_id', $categoryIds)
            ->with(['category', 'author', 'tags'])
            ->latestPublished()
            ->paginate(self::PER_PAGE);

        // Child categories for sub-navigation
        $children = $category->children()->withCount(['posts' => fn ($q) => $q->where('status', 'published')])->get();

        return view('categories.show', compact('category', 'posts', 'children'));
    }

    /**
     * Collect the IDs of a category and all its descendants.
     *
     * @return array<int>
     */
    private function getCategoryTree(Category $category): array
    {
        $ids = [$category->id];

        foreach ($category->allChildren as $child) {
            $ids = array_merge($ids, $this->getCategoryTree($child));
        }

        return $ids;
    }
}
