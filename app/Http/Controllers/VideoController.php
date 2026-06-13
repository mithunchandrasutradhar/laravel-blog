<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::whereHas('videos', fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();

        $activeCategory = null;

        $query = Video::active()->with('category')->ordered();

        if ($request->filled('category')) {
            $activeCategory = $categories->firstWhere('slug', $request->category);
            if ($activeCategory) {
                $query->where('category_id', $activeCategory->id);
            }
        }

        $videos = $query->paginate(12)->withQueryString();

        return view('videos.index', compact('videos', 'categories', 'activeCategory'));
    }
}
