<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $brands = Brand::query()
            ->when($request->filled('search'), fn($q) => $q->search($request->search))
            ->when($request->has('active'), function ($q) use ($request) {
                return $request->boolean('active') ? $q->active() : $q->inactive();
            })
            ->when($request->filled('sort'), function ($q) use ($request) {
                // Lấy cột cần sort, mặc định là 'created_at'
                $sortColumn = $request->get('sort', 'created_at');
                // Lấy hướng sort (asc/desc), mặc định là 'desc'
                $sortOrder = $request->get('order', 'desc');

                // Bảo mật: Chỉ cho phép sort các cột hợp lệ để tránh SQL Injection
                $allowedColumns = ['name', 'country', 'created_at', 'is_active'];
                if (in_array($sortColumn, $allowedColumns)) {
                    return $q->orderBy($sortColumn, $sortOrder);
                }
            }, function ($q) {
                // Nếu không truyền sort thì mặc định dùng latest
                return $q->latest();
            })
            ->paginate($request->get('per-page', 10));

        return BrandResource::collection($brands);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BrandRequest $request)
    {
        $brand = Brand::create($request->validated());

        return (new BrandResource($brand))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $brand = Brand::withTrashed()
            ->withCount('products')
            ->with(['products'])
            ->find($id);

        if (!$brand) {
            return response()->json(['message' => 'Brand not found.'], 404);
        }

        // Kiểm tra auth
        // update

        return new BrandResource($brand);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BrandRequest $request, Brand $brand)
    {
        if ($brand->trashed()) {
            return response()->json([
                'message' => 'Cannot update a deleted resource. Please restore it first.'
            ], 422);
        }

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
}
