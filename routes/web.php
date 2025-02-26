<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\PublisherController;


Auth::routes();

Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::get('/book/{slug}', [FrontendController::class, 'bookDetails'])->name('BookDetails');
Route::get('/shop', [FrontendController::class, 'shop'])->name('Shop');
Route::post('/filter/books', [FrontendController::class, 'filterBooks'])->name('FilterBooks');


Route::get('/authors', [AuthorController::class, 'authors'])->name('Authors');
Route::get('/author/books/{slug}', [AuthorController::class, 'authorBooks'])->name('AuthorBooks');
Route::post('/filter/author/books', [AuthorController::class, 'filterAuthorBooks'])->name('FilterAuthorBooks');


Route::get('/publishers', [PublisherController::class, 'publishers'])->name('Publishers');
Route::get('/publisher/books/{slug}', [PublisherController::class, 'publisherBooks'])->name('PublisherBooks');
Route::post('/filter/publisher/books', [PublisherController::class, 'filterPublisherBooks'])->name('FilterPublisherBooks');


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
