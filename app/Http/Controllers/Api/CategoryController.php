<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = Category::query()
            ->withCount('products')
            ->filter($request->all())
            ->paginate($request->input('per_page', 10));

        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::withCount('products')
            ->with(['products'])
            ->findOrFail($id);

        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
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
}
