<?php

use App\Domains\ApiResponse\Http\Controllers\ApiAuthController;
use App\Domains\ApiResponse\Http\Controllers\CatalogController;
use App\Domains\ApiResponse\Http\Controllers\GeneralController;
use App\Domains\ApiResponse\Http\Controllers\TaxInfoController;
use App\Domains\ApiResponse\Http\Controllers\WishlistController;
use App\Domains\Cart\Http\Controllers\CustomerCartController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'v1', 'as' => 'v1.'], function () {

    Route::get('/general', [GeneralController::class, 'generalSettings']);
    Route::get('/banners', [GeneralController::class, 'banners']);
    Route::get('/get-section-products', [CatalogController::class, 'getSectionProducts']);
    Route::get('/get-filtered-products', [CatalogController::class, 'getFilteredProducts']);
    Route::get('/get-attribute/{type}', [CatalogController::class, 'getAttributeItems']);
    Route::get('/get-subjects', [CatalogController::class, 'getSubjects']);
    Route::get('/get-writers', [CatalogController::class, 'getWriters']);
    Route::get('/get-publishers', [CatalogController::class, 'getPublishers']);
    Route::get('/product/{sku}', [CatalogController::class, 'productDetails']);
    Route::get('/related-products/{item_sku}', [CatalogController::class, 'relatedProducts']);

    // searching products api
    Route::get('/search', [CatalogController::class, 'getSearchResult']);
    Route::post('/search/suggestion', [CatalogController::class, 'searchSuggestion']);


    Route::get('/faqs', [GeneralController::class, 'faqPages']);
    Route::get('/page/{slug}', [GeneralController::class, 'singlePages']);


    // cart system
    Route::group(['prefix' => 'cart', 'as' => 'cart.'], function () {
        Route::get('/', [CustomerCartController::class, 'currentCartList']);
        Route::post('/add', [CustomerCartController::class, 'addToCart']);
        Route::post('/update', [CustomerCartController::class, 'updateCustomerCart']);
        Route::post('/mark-as-cart', [CustomerCartController::class, 'updateAsCartItem']);
        Route::post('/remove', [CustomerCartController::class, 'removeFromCart']);
        Route::post('/checkbox', [CustomerCartController::class, 'updateCartCheckbox']);
        Route::post('/choose-shipping', [CustomerCartController::class, 'choose_shipping']);
        Route::post('/payment-method', [CustomerCartController::class, 'addPaymentMethod']);
        Route::post('/place-order', [CustomerCartController::class, 'placedOrder']);


//         below not tested
        Route::post('/store-credit', [CustomerCartController::class, 'storeCredit'])->middleware('auth:sanctum');  // not functional
        Route::post('/cut-of-time', [CustomerCartController::class, 'cutOfTime'])->middleware('auth:sanctum');  // not functional
        Route::post('/shipping', [CustomerCartController::class, 'addShippingAddress'])->middleware('auth:sanctum'); // not functional
        Route::post('/coupon', [CustomerCartController::class, 'couponCodeSubmit'])->middleware('auth:sanctum'); // not functional

        // payment controller
        Route::get('/payment-token', [PaymentController::class, 'paymentToken'])->middleware('auth:sanctum'); // not functional
    });


//     ============== below is not tested ==============

    Route::post('/login-as-customer/{token}', [GeneralController::class, 'loginAsCustomer']);


    Route::post('/contact/message', [GeneralController::class, 'contactMessageSend']);

    // sanctum auth user
    Route::get('/user', [ApiAuthController::class, 'authUser']);
    Route::post('/check-exists-customer', [ApiAuthController::class, 'checkExistsCustomer']);
    Route::post('/resend-otp', [ApiAuthController::class, 'resendOtpCode']);
    Route::post('/verify-otp', [ApiAuthController::class, 'OtpVerifyOtpCode']);
    Route::post('/reset-otp-verify', [ApiAuthController::class, 'OtpVerifyOtpCode']);
    Route::post('/register-customer', [ApiAuthController::class, 'registerCustomer']);
    Route::post('/login', [ApiAuthController::class, 'loginCustomer']);
    Route::post('/forgot-password', [ApiAuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [ApiAuthController::class, 'resetPassword']);
    Route::post('/logout', [ApiAuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::get('/menus', [GeneralController::class, 'menus']);
    Route::get('/categories', [GeneralController::class, 'categories']);
    Route::get('/top-categories', [GeneralController::class, 'top_categories']);

    Route::get('/get-product-brands', [CatalogController::class, 'getProductBrands']);
    Route::get('/category-products/{slug}', [CatalogController::class, 'categoryProducts']);
    Route::get('/store/{slug}', [CatalogController::class, 'storeDetails']);


    Route::get('/featured-products', [CatalogController::class, 'featuredProducts']);
    Route::get('/loving-products', [CatalogController::class, 'lovingProducts']);

    Route::get('/new-arrived-products', [CatalogController::class, 'newArrivedProducts']);
    Route::get('/recent-view-products', [CatalogController::class, 'recentViewProducts']);
    Route::get('/favorite-products', [CatalogController::class, 'lovingProducts']);

    Route::get('/view-file', [TaxInfoController::class, 'viewPdfTaxFile']);
    Route::get('/wishlist', [WishlistController::class, 'index']);


    Route::post('/auth-and-wishlist', [WishlistController::class, 'authWishlistStore']);

    Route::group(['middleware' => ['auth:sanctum', 'verified']], function () {

        Route::post('/update-profile', [ApiAuthController::class, 'updateProfile']);

        //   Route::post('/confirm-order', [OrderController::class, 'confirmOrders']);
        //   Route::post('/payment-confirm', [OrderController::class, 'confirmOrderPayment']);
        //   Route::post('/invoices', [OrderController::class, 'invoices']);
        //   Route::post('/invoice/{id}', [OrderController::class, 'invoiceDetails']);

        Route::post('/add-to-wishlist', [WishlistController::class, 'store']);
        Route::post('/remove-wishlist', [WishlistController::class, 'delete']);

        Route::get('/address', [AddressController::class, 'AllAddress']);
        Route::post('/store-new-address', [AddressController::class, 'StoreNewAddress']);
        Route::post('/delete-address', [AddressController::class, 'deleteAddress']);

        Route::get('/tax-information', [TaxInfoController::class, 'taxInformation']);
        Route::post('/download-file', [TaxInfoController::class, 'downloadFile']);
        Route::post('/upload-tax-form', [TaxInfoController::class, 'uploadTaxForm']);
        Route::post('/upload-tax-information', [TaxInfoController::class, 'uploadTaxInformation']);
        Route::post('/revoke-tax-form', [TaxInfoController::class, 'deleteTaxForm']);

        Route::post('/product-gallery-upload', [CatalogController::class, 'uploadProductGalleryUpload']);


//        includeRouteFiles(__DIR__ . '/api');

    });
});
