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
    private const PER_PAGE = 15;

    public function index(Request $request): View
    {
        abort_if(! auth()->user()->hasPermissionTo('categories.viewAny'), 403);

        $query = Category::with('parent')->withCount('posts');

        if ($request->filled('q')) {
            $query->where('name', 'LIKE', "%{$request->q}%");
        }

        $categories = $query->orderBy('name')->paginate(self::PER_PAGE)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        abort_if(! auth()->user()->hasPermissionTo('categories.create'), 403);

        $parents = Category::orderBy('name')->get();

        return view('admin.categories.create', compact('parents'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('categories.create'), 403);

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

    public function edit(Category $category): View
    {
        abort_if(! auth()->user()->hasPermissionTo('categories.update'), 403);

        $parents = Category::where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('categories.update'), 403);

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

    public function show(Category $category): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('categories.viewAny'), 403);

        return redirect()->route('admin.categories.edit', $category);
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('categories.delete'), 403);

        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->withErrors(['error' => 'No categories selected.']);
        }

        $fallback = Category::where('slug', 'uncategorised')->first();
        $deleted  = 0;
        $skipped  = 0;

        Category::whereIn('id', $ids)->each(function (Category $category) use ($fallback, &$deleted, &$skipped) {
            if ($fallback && $fallback->id === $category->id) {
                $skipped++;
                return;
            }

            if ($fallback) {
                $category->posts()->update(['category_id' => $fallback->id]);
            } elseif ($category->posts()->exists()) {
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

    public function destroy(Category $category): RedirectResponse
    {
        abort_if(! auth()->user()->hasPermissionTo('categories.delete'), 403);

        $fallback = Category::where('slug', 'uncategorised')->first();

        if ($fallback && $fallback->id === $category->id) {
            return redirect()->route('admin.categories.index')
                ->withErrors(['error' => 'The default "Uncategorised" category cannot be deleted.']);
        }

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
