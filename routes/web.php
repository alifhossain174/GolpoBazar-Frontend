<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\PublisherController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\AudioBooksController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ForgetPasswordController;


Auth::routes();

Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::get('/book/{slug}', [FrontendController::class, 'bookDetails'])->name('BookDetails');
Route::get('/shop', [FrontendController::class, 'shop'])->name('Shop');
Route::post('/filter/books', [FrontendController::class, 'filterBooks'])->name('FilterBooks');
Route::post('product/live/search', [FrontendController::class, 'productLiveSearch'])->name('ProductLiveSearch');


Route::get('/audio/books', [AudioBooksController::class, 'audioBooks'])->name('AudioBooks');
Route::post('/filter/audio/books', [AudioBooksController::class, 'filterAudioBooks'])->name('FilterAudioBooks');


Route::get('/authors', [AuthorController::class, 'authors'])->name('Authors');
Route::get('/author/books/{slug}', [AuthorController::class, 'authorBooks'])->name('AuthorBooks');
Route::post('/filter/author/books', [AuthorController::class, 'filterAuthorBooks'])->name('FilterAuthorBooks');


Route::get('/publishers', [PublisherController::class, 'publishers'])->name('Publishers');
Route::get('/publisher/books/{slug}', [PublisherController::class, 'publisherBooks'])->name('PublisherBooks');
Route::post('/filter/publisher/books', [PublisherController::class, 'filterPublisherBooks'])->name('FilterPublisherBooks');


// cart
Route::get('add/to/cart/{id}', [CartController::class, 'addToCart'])->name('AddToCart');
Route::post('add/to/cart/with/qty', [CartController::class, 'addToCartWithQty'])->name('AddToCartWithQty');
Route::get('remove/cart/item/{id}', [CartController::class, 'removeCartTtem'])->name('RemoveCartTtem');
Route::post('update/cart/qty', [CartController::class, 'updateCartQty'])->name('UpdateCartQty');
Route::get('clear/cart', [CartController::class, 'clearCart'])->name('ClearCart');


// policy pages
Route::get('terms/and/conditions', [PolicyController::class, 'termsAndConditions'])->name('TermsAndConditions');
Route::get('privacy/policy', [PolicyController::class, 'privacyPolicy'])->name('PrivacyPolicy');
Route::get('shipping/policy', [PolicyController::class, 'shippingPolicy'])->name('ShippingPolicy');
Route::get('return/policy', [PolicyController::class, 'returnPolicy'])->name('ReturnPolicy');


// forget password
Route::group(['middleware' => ['web']], function () { //wihout web middleware session will not work
    Route::get('/forget/password', [ForgetPasswordController::class, 'userForgetPassword'])->name('UserForgetPassword');
    Route::post('/send/forget/password/code', [ForgetPasswordController::class, 'sendForgetPasswordCode'])->name('SendForgetPasswordCode');
    Route::get('/new/password', [ForgetPasswordController::class, 'newPasswordPage'])->name('NewPasswordPage');
    Route::post('/change/forgotten/password', [ForgetPasswordController::class, 'changeForgetPassword'])->name('ChangeForgetPassword');
});


Route::group(['middleware' => ['auth']], function () {

    Route::get('/user/verification', [HomeController::class, 'userVerification'])->name('UserVerification');
    Route::post('/user/verify/check', [HomeController::class, 'userVerifyCheck'])->name('UserVerifyCheck');
    Route::get('/user/verification/resend', [HomeController::class, 'userVerificationResend'])->name('UserVerificationResend');


    Route::group(['middleware' => ['CheckUserVerification']], function () {

        Route::get('view/cart', [CartController::class, 'viewCart'])->name('ViewCart');
        Route::get('/home', [HomeController::class, 'index'])->name('home');

    });

});
