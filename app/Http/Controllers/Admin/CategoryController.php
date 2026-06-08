<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Admin categories per page.
     */
    private const PER_PAGE = 15;

    /**
     * Display a listing of categories.
     */
    public function index(Request $request): View
    {
        $query = Category::withCount('posts');

        if ($request->filled('q')) {
            $query->where('name', 'LIKE', "%{$request->q}%");
        }

        $categories = $query->orderBy('name')->paginate(self::PER_PAGE)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the create category form.
     */
    public function create(): View
    {
        $parents = Category::topLevel()->orderBy('name')->get();

        return view('admin.categories.create', compact('parents'));
    }

    /**
     * Store a new category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show the edit form for a category.
     */
    public function edit(Category $category): View
    {
        $parents = Category::topLevel()
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    /**
     * Update a category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Show a category detail (admin view).
     */
    public function show(Category $category): View
    {
        $category->loadCount('posts');

        return view('admin.categories.show', compact('category'));
    }

    /**
     * Delete a category.
     *
     * Before deleting, move all posts in this category to an "Uncategorised"
     * fallback category to prevent orphaned posts.
     */
    public function destroy(Category $category): RedirectResponse
    {
        // Move posts to uncategorised (id=1 by convention) before deleting
        $fallback = Category::where('slug', 'uncategorised')->first();

        if ($fallback && $fallback->id !== $category->id) {
            $category->posts()->update(['category_id' => $fallback->id]);
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
