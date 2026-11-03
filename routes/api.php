<?php

// use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'E-Stationery API berjalan',
    ]);
});



// Route::get('/products', function () {
//     $products = Product::query()
//         ->limit(10)
//         ->get();

//     return response()->json([
//         'success' => true,
//         'message' => 'Data produk berhasil diambil',
//         'data' => $products,
//     ]);
// });



Route::get(
    '/products',
    [ProductController::class, 'index']
);

Route::get(
    '/products/{id}',
    [ProductController::class, 'show']
);
    
Route::post('/products', [ProductController::class, 'store']);
Route::put('/products/{id}', [
    ProductController::class,
    'replace',
]);

Route::patch('/products/{id}', [
    ProductController::class,
    'update',
]);

Route::delete(
    '/products/{id}',
    [ProductController::class, 'destroy']
);
