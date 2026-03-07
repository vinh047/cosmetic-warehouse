<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StockResource;
use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $stocks = Stock::with(['warehouse', 'productBatch'])
            ->filter($request)
            ->paginate(10);

        return StockResource::collection($stocks);
    }

    /**
     * Display the specified resource.
     */
    public function show(Stock $stock)
    {
        $stock->load(['warehouse', 'productBatch']);

        return new StockResource($stock);
    }
}
