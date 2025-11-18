<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/data', [ProductController::class, 'data'])
    ->middleware(['ratelimitsearch', 'securityheaders'])
    ->name('products.data');
