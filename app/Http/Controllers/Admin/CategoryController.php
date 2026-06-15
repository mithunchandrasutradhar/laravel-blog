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
        $query = Category::with('parent')->withCount('posts');

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
        $parents = Category::orderBy('name')->get();

        return view('admin.categories.create', compact('parents'));
    }

    /**
     * Store a new category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->filled('image_path')) {
            $data['image'] = $request->image_path;
        } elseif ($request->hasFile('image')) {
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
        $parents = Category::where('id', '!=', $category->id)
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

        if ($request->filled('image_path')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->image_path;
        } elseif ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        } elseif ($request->input('remove_image') === '1') {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = null;
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Show a category detail (admin view).
     */
    public function show(Category $category): RedirectResponse
    {
        return redirect()->route('admin.categories.edit', $category);
    }

    /**
     * Bulk-delete selected categories.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->withErrors(['error' => 'No categories selected.']);
        }

        $fallback = Category::where('slug', 'uncategorised')->first();
        $deleted  = 0;
        $skipped  = 0;

        Category::whereIn('id', $ids)->each(function (Category $category) use ($fallback, &$deleted, &$skipped) {
            // Never delete the fallback category itself
            if ($fallback && $fallback->id === $category->id) {
                $skipped++;
                return;
            }

            if ($fallback) {
                // Move all posts to the fallback before deleting
                $category->posts()->update(['category_id' => $fallback->id]);
            } elseif ($category->posts()->exists()) {
                // No fallback available and category still has posts — skip
                $skipped++;
                return;
            }

            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $category->delete();
            $deleted++;
        });

        $message = $deleted . ' category(s) deleted.';
        if ($skipped > 0) {
            $message .= ' ' . $skipped . ' skipped (default "Uncategorised" category cannot be removed).';
        }

        return redirect()->route('admin.categories.index')->with('success', $message);
    }

    /**
     * Delete a category.
     *
     * Before deleting, move all posts in this category to an "Uncategorised"
     * fallback category to prevent orphaned posts.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $fallback = Category::where('slug', 'uncategorised')->first();

        // Refuse to delete the fallback category itself
        if ($fallback && $fallback->id === $category->id) {
            return redirect()->route('admin.categories.index')
                ->withErrors(['error' => 'The default "Uncategorised" category cannot be deleted.']);
        }

        // Move posts away before the FK-RESTRICT fires
        if ($fallback) {
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
