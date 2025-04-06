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
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BkashPaymentController;
use App\Http\Controllers\DashboardController;


// Auth::routes();
Auth::routes([
    'login' => false,
    'register' => false, // Registration Routes...
    'reset' => false, // Password Reset Routes...
    'verify' => false, // Email Verification Routes...
]);


Route::get('.well-known/assetlinks.json', function () {
    $file = file_get_contents(public_path('assetlinks.json'));
    return response($file, 200)->header('Content-Type', 'application/json');
});


Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::get('/book/{slug}', [FrontendController::class, 'bookDetails'])->name('BookDetails');
Route::get('/books', [FrontendController::class, 'books'])->name('Books');
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
Route::get('writer/benefit', [PolicyController::class, 'shippingPolicy'])->name('ShippingPolicy');
Route::get('return/policy', [PolicyController::class, 'returnPolicy'])->name('ReturnPolicy');


// forget password
Route::group(['middleware' => ['web']], function () { //wihout web middleware session will not work
    Route::get('/forget/password', [ForgetPasswordController::class, 'userForgetPassword'])->name('UserForgetPassword');
    Route::post('/send/forget/password/code', [ForgetPasswordController::class, 'sendForgetPasswordCode'])->name('SendForgetPasswordCode');
    Route::get('/new/password', [ForgetPasswordController::class, 'newPasswordPage'])->name('NewPasswordPage');
    Route::post('/change/forgotten/password', [ForgetPasswordController::class, 'changeForgetPassword'])->name('ChangeForgetPassword');
});


// ssl commerz payment routes
Route::get('sslcommerz/order', [PaymentController::class, 'order'])->name('payment.order');
Route::post('sslcommerz/success', [PaymentController::class, 'success'])->name('payment.success');
Route::post('sslcommerz/failure', [PaymentController::class, 'failure'])->name('sslc.failure');
Route::post('sslcommerz/cancel', [PaymentController::class, 'cancel'])->name('sslc.cancel');
Route::post('sslcommerz/ipn', [PaymentController::class, 'ipn'])->name('payment.ipn');


// route for bkash payment
Route::get('get/token', [BkashPaymentController::class, 'getToken'])->name('getToken');
Route::get('create/agreement', [BkashPaymentController::class, 'createAgreement'])->name('createAgreement');
Route::get('execute/agreement', [BkashPaymentController::class, 'executeAgreement'])->name('executeAgreement');
Route::get('create/payment', [BkashPaymentController::class, 'createPayment'])->name('createPayment');
Route::get('execute/payment', [BkashPaymentController::class, 'executePayment'])->name('executePayment');

// place order related routes
Route::get('/order/{order_slug}', [DashboardController::class, 'orderPreview'])->name('OrderPreview');
Route::get('view/cart', [CartController::class, 'viewCart'])->name('ViewCart');
Route::post('apply/coupon', [CheckoutController::class, 'applyCoupon'])->name('ApplyCoupon');
Route::post('place/order', [CheckoutController::class, 'placeOrder'])->name('PlaceOrder');


Route::group(['middleware' => ['auth']], function () {

    // Route::get('/user/verification', [HomeController::class, 'userVerification'])->name('UserVerification');
    // Route::post('/user/verify/check', [HomeController::class, 'userVerifyCheck'])->name('UserVerifyCheck');
    // Route::get('/user/verification/resend', [HomeController::class, 'userVerificationResend'])->name('UserVerificationResend');

    Route::group(['middleware' => ['CheckUserVerification']], function () {

        // Route::get('/home', [HomeController::class, 'index'])->name('home');
        // Route::get('/user/profile', [DashboardController::class, 'userProfile'])->name('UserProfile');
        // Route::post('/update/profile', [DashboardController::class, 'updateProfile'])->name('UpdateProfile');
        // Route::get('/user/cart', [DashboardController::class, 'userCart'])->name('UserCart');
        // Route::get('/change/password', [DashboardController::class, 'changePassword'])->name('ChangePassword');
        // Route::post('/update/password', [DashboardController::class, 'updatePassword'])->name('UpdatePassword');
        // Route::get('/user/orders', [DashboardController::class, 'userOrders'])->name('UserOrders');
        // Route::get('/order/details/{order_no}', [DashboardController::class, 'orderDetails'])->name('OrderDetails');

    });

});
