<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Category::class);

        if ($request->filled('trashed')) {
            $this->authorize('viewTrash', Category::class);
        }

        $categories = Category::query()
            ->withCount('products')
            ->filter($request->all())
            ->paginate($request->input('per_page', 10));

        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        Gate::authorize('create', Category::class);

        $category = Category::create($request->validated());

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        Gate::authorize('view', $category);

        $category->loadCount('products')->load('products');

        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        Gate::authorize('update', $category);

        $category->update($request->validated());

        return new CategoryResource($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        Gate::authorize('delete', $category);

        // kiểm tra category có sản phẩm ko
        // có sản phẩm ko cho xóa
        if ($category->products()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete this category because it has associated products. Please delete or reassign products first.',
                'products_count' => $category->products()->count()
            ], 422); // 422 Unprocessable Content hoặc 409 Conflict
        }

        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Category moved to trash successfully.',
            'deleted_id' => $category->id
        ], 200);
    }

    public function restore($id)
    {
        // Chủ động lấy Category trong thùng rác
        $category = Category::withTrashed()->findOrFail($id);

        Gate::authorize('restore', $category);

        // Gọi Policy kiểm tra quyền
        $this->authorize('restore', $category);

        $category->restore();

        return response()->json([
            'status' => 'success',
            'message' => 'Category restored successfully.',
            'restored_id' => $category->id
        ], 200);
    }
}
