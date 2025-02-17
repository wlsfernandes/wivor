<?php


use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::get('/s3-test', function () {
    try {
        // Upload a test file to S3
        $testFileName = 'test-file.txt';
        Storage::disk('s3')->put($testFileName, 'This is a test file.');

        // Generate the file URL
        $url = Storage::disk('s3')->url($testFileName);

        return response()->json([
            'message' => 'S3 is accessible!',
            'file_url' => $url,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'S3 access failed!',
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/', [HomeController::class, 'welcome'])->name('welcome');

Route::get('/lang/{lang}', function ($lang) {
    Session::put('locale', $lang);
    return redirect()->back();
})->name('lang.switch');

Route::get('/post/{slug}', [PostController::class, 'show'])->name('post.show');

Route::get('/about-us', [HomeController::class, 'aboutUs'])->name('about_us');
Route::get('/our-team', [HomeController::class, 'ourTeam'])->name('our_team');
Route::get('/contact', [HomeController::class, 'contactUs'])->name('contact_us');
Route::get('/testimonials', [HomeController::class, 'testimonials'])->name('testimonials');




Auth::routes();

Route::middleware(['auth', 'institution.scope'])->group(function () {


    Route::middleware('can:access-admin')->group(function () {
        //Posts
        Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
        Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
        Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
        Route::get('/posts/{id}', [PostController::class, 'show'])->name('posts.show');
        Route::get('/posts/{id}/edit', [PostController::class, 'edit'])->name('posts.edit');
        Route::get('/posts/{id}/teaser', [PostController::class, 'teaser'])->name('posts.teaser');
        Route::put('posts/{id}/upload-jpg', [PostController::class, 'uploadJPG'])->name('posts.uploadJPG');
        Route::put('/posts/{id}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy');
        //User
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');


        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    });


    // Paypall
    Route::get('paypal/payment/{id}', [PayPalController::class, 'createPayment'])->name('paypal.payment');
    Route::get('paypal/capture', [PayPalController::class, 'capturePayment'])->name('paypal.capture');
    Route::get('payment/success', function () {
        return view('paypal.payment-success');
    })->name('success');
    Route::get('payment/error', function () {
        return view('paypal.payment-failed');
    })->name('error');
    Route::get('test/paypal', function () {
        return view('paypal.test-paypal');
    })->name('test.paypal');

});

Route::middleware('auth')->group(function () {
    Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);
    Route::post('/formsubmit', [App\Http\Controllers\HomeController::class, 'FormSubmit'])->name('FormSubmit');
    Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index']);
});



//Route::get('/', [App\Http\Controllers\HomeController::class, 'root']);

// Authenticated routes

