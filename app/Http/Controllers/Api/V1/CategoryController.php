<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * This will return only top-level categories, with their children nested.
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::query()
            // Only fetch categories marked as visible to the public
            ->where('is_visible', true)
            // Only fetch top-level categories (parents)
            ->whereNull('parent_id')
            // Eager-load the children to prevent N+1 query issues
            ->with(['children' => function ($query) {
                // Also ensure the loaded children are visible
                $query->where('is_visible', true)->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        return CategoryResource::collection($categories);
    }
}
