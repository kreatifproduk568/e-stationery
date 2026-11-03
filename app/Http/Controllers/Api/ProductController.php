<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\ReplaceProductRequest;

class ProductController extends Controller
{
    
    public function index()
    {
        $products = Product::query()
            ->with(['category', 'brand'])
            ->limit(10)
            ->get();

        return ProductResource::collection($products)
            ->additional([
                'success' => true,
                'message' => 'Data produk berhasil diambil',
            ]);
    }


    
    public function show(string $id)
    {
        $product = Product::query()
            ->with(['category', 'brand'])
            ->findOrFail($id);

        return new ProductResource($product);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dibuat',
            'data' => $product,
        ], 201);
    }

    
    
    public function update(UpdateProductRequest $request,string $id): JsonResponse {
        $product = Product::findOrFail($id);

        $product->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui',
            'data' => $product->fresh(),
        ], 200);
    }


    public function replace(ReplaceProductRequest $request,string $id): JsonResponse {
        $product = Product::findOrFail($id);

        $product->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diganti',
            'data' => $product->fresh(),
        ], 200);
    }


    
    public function destroy(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus',
        ], 200);
    }

}
