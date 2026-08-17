<?php

use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

// Public Material Design 3 Storefront
Route::get('/', [StorefrontController::class, 'index'])->name('storefront');
Route::post('/api/order/track', [StorefrontController::class, 'trackOrder'])->name('order.track');

// Admin Login Redirect
Route::redirect('/login', '/admin/login')->name('login');
