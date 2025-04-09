<?php


use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EventController;
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


/**********************  public pages  *********************************************************/ 
Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/list-events', [EventController::class, 'listEvents'])->name('events.listEvents');
Route::post('/events', [EventController::class, 'store'])->name('events.store');
Route::get('/events/{id}/publish', [EventController::class, 'publish'])->name('events.publish');
Route::get('/events/{id}/edit', [EventController::class, 'edit'])->name('events.edit');
Route::get('/events/{id}/teaser', [EventController::class, 'teaser'])->name('events.teaser');
Route::put('/events/{id}/upload-jpg', [EventController::class, 'uploadJPG'])->name('events.uploadJPG');
Route::put('/events/{id}', [EventController::class, 'update'])->name('events.update');
Route::delete('/events/{id}', [EventController::class, 'destroy'])->name('events.destroy');
Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');
Route::get('/events/{slug}', [EventController::class, 'show'])->name('event.show');



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

Auth::routes();

Route::middleware('auth')->group(function () {


    Route::middleware('can:access-admin')->group(function () {
        
      
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
        Route::get('/photographers/dashboard', [PhotographerController::class, 'dashboard'])->name('photographer.dashboard');
        Route::get('/photographers/all-events', [PhotographerController::class, 'allEvents'])->name('photographer.allEvents');
        Route::get('/photographers/my-events', [PhotographerController::class, 'myEvents'])->name('photographer.myEvents');
        Route::get('/photographers/new-event', [PhotographerController::class, 'newEvent'])->name('photographer.newEvent');
        Route::post('/photographers/event/create', [EventController::class, 'eventCreatedByPhotographer'])->name('eventCreatedByPhotographer');
    });


    // Customer home
    Route::middleware('can:access-customer')->group(function () {
        Route::get('/customer/dashboard', [CustomerController::class, 'dashboard'])->name('customer.dashboard');
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

