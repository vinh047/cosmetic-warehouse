<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Brand\StoreBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Brand::class, 'brand');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Kiểm tra quyền xem thùng rác nếu có truyền param ?trashed=...
        if ($request->filled('trashed')) {
            $this->authorize('viewTrash', Brand::class);
        }

        $brands = Brand::query()
            ->withCount('products')
            ->filter($request->all())
            ->paginate($request->input('per_page', 10));

        return BrandResource::collection($brands);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBrandRequest $request)
    {
        $brand = Brand::create($request->validated());

        return (new BrandResource($brand))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        $brand->load('products');

        return new BrandResource($brand);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $brand->update($request->validated());

        return new BrandResource($brand);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        // kiểm tra brand có sản phẩm ko
        // có sản phẩm ko cho xóa
        if ($brand->products()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete this brand because it has associated products. Please delete or reassign products first.',
                'products_count' => $brand->products()->count()
            ], 422); // 422 Unprocessable Content hoặc 409 Conflict
        }

        $brand->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Brand moved to trash successfully.',
            'deleted_id' => $brand->id
        ], 200);
    }

    public function restore($id)
    {
        // Chủ động tìm Brand trong thùng rác
        $brand = Brand::withTrashed()->findOrFail($id);

        // Gọi Policy để check xem ông này có quyền restore không
        $this->authorize('restore', $brand);

        // Thực hiện khôi phục
        $brand->restore();

        return response()->json([
            'status' => 'success',
            'message' => 'Brand restored successfully.',
            'restored_id' => $brand->id
        ], 200);
    }
}
