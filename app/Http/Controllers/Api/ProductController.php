<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Product::class, 'product');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->filled('trashed')) {
            $this->authorize('viewTrash', Product::class);
        }

        $products = Product::query()
            ->with(['brand:id,name', 'category:id,name'])
            ->filter($request->all())
            ->paginate($request->input('per_page', 10));

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['brand', 'category']);
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return new ProductResource($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->batches()->exists()) {
            return response()->json([
                'message' => 'This product cannot be deleted because it has import history. Please deactivate it instead (set Active = false).'
            ], 422);
        }

        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'The product has been moved to trash successfully.',
            'deleted_id' => $product->id
        ], 200);
    }

    public function restore($id)
    {
        // Chủ động lấy Product từ trong thùng rác ra
        $product = Product::withTrashed()->findOrFail($id);

        // Gọi Policy để check xem có quyền khôi phục không
        $this->authorize('restore', $product);

        // Tiến hành khôi phục
        $product->restore();

        return response()->json([
            'status' => 'success',
            'message' => 'Product restored successfully.',
            'restored_id' => $product->id
        ], 200);
    }
}
