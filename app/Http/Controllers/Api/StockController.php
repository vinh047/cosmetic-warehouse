<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StockResource;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Stock::class);

        $stocks = Stock::with(['warehouse', 'productBatch'])
            ->filter($request->all())
            ->paginate($request->input('per_page', 10));

        return StockResource::collection($stocks);
    }

    /**
     * Display the specified resource.
     */
    public function show(Stock $stock)
    {
        Gate::authorize('view', $stock);

        $stock->load(['warehouse', 'productBatch']);

        return new StockResource($stock);
    }
}
