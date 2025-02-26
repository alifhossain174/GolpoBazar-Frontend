<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FrontendController;


Auth::routes();

Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::get('/book/{slug}', [FrontendController::class, 'bookDetails'])->name('BookDetails');
Route::get('/shop', [FrontendController::class, 'shop'])->name('Shop');
Route::post('/filter/products', [FrontendController::class, 'filterProducts'])->name('FilterProducts');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
