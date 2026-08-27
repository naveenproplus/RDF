<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Open list of products missing images (for design team) — no auth
Route::match(['get', 'post'], '/tools/missing-product-images', [\App\Http\Controllers\api\ProductAiImageController::class, 'missing']);
Route::match(['get', 'post'], '/tools/ai-product-images/eligible', [\App\Http\Controllers\api\ProductAiImageController::class, 'eligible']);
Route::post('/tools/ai-product-images/generate', [\App\Http\Controllers\api\ProductAiImageController::class, 'generate']);

//Route::group(['prefix'=>'customer'],function (){
    require __DIR__.'/api/customer/customer-api.php';
//});
//Route::group(['prefix'=>'vendor'],function (){
//    require __DIR__.'/api/vendor/vendor-api.php';
//});

//require __DIR__.'/api/general-api.php';
