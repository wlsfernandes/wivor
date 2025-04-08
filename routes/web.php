<?php


use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PhotographerController;
use Illuminate\Support\Facades\Session;
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


Route::get('/', [HomeController::class, 'welcome'])->name('welcome');

Route::get('/lang/{lang}', function ($lang) {
    Session::put('locale', $lang);
    return redirect()->back();
})->name('lang.switch');

Route::get('/post/{slug}', [PostController::class, 'show'])->name('post.show');

Route::get('/about-us', [HomeController::class, 'aboutUs'])->name('about_us');
Route::get('/our-team', [HomeController::class, 'ourTeam'])->name('our_team');
Route::get('/photobook', [HomeController::class, 'photobook'])->name('photobook');

Route::get('/wilson-fernandes-junior', [HomeController::class, 'junior'])->name('junior');
Route::get('/photographers', [PhotographerController::class, 'photographers'])->name('photographers');
Route::get('/contact', [HomeController::class, 'contactUs'])->name('contact_us');
Route::get('/testimonials', [HomeController::class, 'testimonials'])->name('testimonials');
Route::get('/photographers', [PhotographerController::class, 'photographers'])->name('photographers');
Route::get('/photobook', [PhotographerController::class, 'photobook'])->name('photobook');
Route::get('/signup', [HomeController::class, 'signUp'])->name('signUp');
Route::post('/registerPhotographer', [PhotographerController::class, 'registerPhotographer'])->name('registerPhotographer');
Route::post('/registerUser', [UserController::class, 'registerUser'])->name('registerUser');
Route::post('/send-email', [ContactController::class, 'sendEmail'])->name('contact.send');
Route::get('/post', [PostController::class, 'index'])->name('post');
Route::post('/post', [PostController::class, 'store'])->name('post.store');
Route::get('post/{id}/teaser', [PostController::class, 'teaser'])->name('post.teaser');
Route::get('post/{id}/link', [PostController::class, 'link'])->name('post.link');
Route::post('post/{id}/publish', [PostController::class, 'publish'])->name('post.publish');
Route::get('post/{id}/file/{language}', [PostController::class, 'file'])->name('post.file');
Route::put('/post/{id}/upload-jpg', [PostController::class, 'uploadJPG'])->name('post.uploadJPG');
Route::put('post/{id}/upload-file', [PostController::class, 'uploadFile'])->name('post.uploadFile');



Auth::routes();

Route::middleware('auth')->group(function () {



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


        Route::get('/list-photographers', [PhotographerController::class, 'list'])->name('photographers.list');

        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    });

    // Photographer-only routes
    Route::middleware('can:access-photographer')->group(function () {
        Route::get('/photographers/home', [PhotographerController::class, 'home'])->name('home');
        // Add other photographer-specific routes here
    });


    // Customer home
    Route::middleware('can:access-customer')->group(function () {
        Route::get('/customer/home', [CustomerController::class, 'index'])->name('index');
        // Add other customer-specific routes here
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

