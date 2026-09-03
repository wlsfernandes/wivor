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
use App\Http\Controllers\PhotographerUploadController;
use App\Http\Controllers\PhotoDeliveryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Admin\PhotographerController as AdminPhotographerController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Frontend\PhotographerRegistrationController;
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


/********************** Event pages ************************************************************/
Route::get('/list-events', [EventController::class, 'listEvents'])->name('events.listEvents');

Route::middleware('auth')->group(function () {
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::patch('/events/{event}/publish', [EventController::class, 'publish'])->name('events.publish');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
});

Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/events/{event:slug}/photos/{photo}', [PhotoDeliveryController::class, 'gallery'])->name('events.photos.show');
Route::get('/events/{event:slug}/photos/{photo}/image.jpg', [PhotoDeliveryController::class, 'image'])->name('events.photos.image');

/********************** Cart (guest checkout selection) ****************************************/
Route::get('/cart', [CartController::class, 'index'])->name('cart.show');
Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
Route::delete('/cart/items/{photo}', [CartController::class, 'destroy'])->name('cart.items.destroy');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

/********************** Checkout (guest, Stripe-hosted) *****************************************/
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/{order}/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/{order}/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

/********************** Secure order & download page (guest, token-gated) ***********************/
Route::get('/orders/{accessToken}', [OrderController::class, 'show'])->name('orders.show');
Route::get('/orders/{accessToken}/photos/{photo}/download', [OrderController::class, 'download'])->name('orders.download');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemaps/events-{page}.xml', [SitemapController::class, 'events'])->whereNumber('page')->name('sitemap.events');
Route::get('/sitemaps/photos-{page}.xml', [SitemapController::class, 'photos'])->whereNumber('page')->name('sitemap.photos');



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
Route::post('/registerPhotographer', [PhotographerRegistrationController::class, 'store'])->name('registerPhotographer');
Route::post('/registerUser', [UserController::class, 'registerUser'])->name('registerUser');
Route::post('/send-email', [ContactController::class, 'sendEmail'])->name('contact.send');

Auth::routes(['register' => false, 'verify' => true]);

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


        Route::get('/list-photographers', [AdminPhotographerController::class, 'index'])->name('photographers.list');
        Route::get('/list-photographers/{photographer}', [AdminPhotographerController::class, 'show'])->name('admin.photographers.show');
        Route::patch('/list-photographers/{photographer}/approve', [AdminPhotographerController::class, 'approve'])->name('admin.photographers.approve');
        Route::patch('/list-photographers/{photographer}/decline', [AdminPhotographerController::class, 'decline'])->name('admin.photographers.decline');
        Route::patch('/list-photographers/{photographer}/suspend', [AdminPhotographerController::class, 'suspend'])->name('admin.photographers.suspend');
        Route::patch('/list-photographers/{photographer}/restore', [AdminPhotographerController::class, 'restore'])->name('admin.photographers.restore');

        Route::get('/admin/media', [AdminMediaController::class, 'index'])->name('admin.media.index');
        Route::get('/admin/media/{event}', [AdminMediaController::class, 'show'])->name('admin.media.show');
        Route::patch('/admin/media/{event}/assignments/{photographer}/deadline', [AdminMediaController::class, 'extendDeadline'])->name('admin.media.deadline');
        Route::post('/admin/media/{event}/photos/{photo}/retry', [AdminMediaController::class, 'retry'])->name('admin.media.retry');
        Route::patch('/admin/media/{event}/photos/{photo}/unpublish', [AdminMediaController::class, 'unpublish'])->name('admin.media.unpublish');
        Route::delete('/admin/media/{event}/photos/{photo}', [AdminMediaController::class, 'remove'])->name('admin.media.remove');
        Route::post('/admin/media/{event}/close', [AdminMediaController::class, 'closeGallery'])->name('admin.media.close');
        Route::post('/admin/media/{event}/holds', [AdminMediaController::class, 'hold'])->name('admin.media.holds.store');
        Route::delete('/admin/media/{event}/holds/{hold}', [AdminMediaController::class, 'releaseHold'])->name('admin.media.holds.release');

        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    });

    Route::middleware('can:photographer-account')->group(function () {
        Route::get('/photographers/application-status', [PhotographerController::class, 'applicationStatus'])->name('photographer.application-status');
    });

    // Approved and email-verified photographer routes
    Route::middleware(['verified', 'can:access-photographer'])->group(function () {
        Route::get('/photographers/dashboard', [PhotographerController::class, 'dashboard'])->name('photographer.dashboard');
        Route::get('/photographers/all-events', [PhotographerController::class, 'allEvents'])->name('photographer.allEvents');
        Route::get('/photographers/my-events', [PhotographerController::class, 'myEvents'])->name('photographer.myEvents');
        Route::get('/photographers/new-event', [PhotographerController::class, 'newEvent'])->name('photographer.newEvent');
        Route::post('/photographers/event/create', [EventController::class, 'store'])->name('eventCreatedByPhotographer');

        Route::prefix('/photographers/events/{event}/uploads')->name('photographer.uploads.')->group(function () {
            Route::get('/', [PhotographerUploadController::class, 'show'])->name('show');
            Route::post('/batches', [PhotographerUploadController::class, 'createBatch'])->name('batches.store');
            Route::get('/status', [PhotographerUploadController::class, 'statuses'])->name('status');
            Route::post('/photos/{photo}/complete', [PhotographerUploadController::class, 'complete'])->name('complete');
            Route::post('/photos/{photo}/retry-url', [PhotographerUploadController::class, 'retryUrl'])->name('retry-url');
            Route::patch('/photos/{photo}/metadata', [PhotographerUploadController::class, 'updateMetadata'])->name('metadata');
            Route::get('/photos/{photo}/preview', [PhotoDeliveryController::class, 'photographerPreview'])->name('preview');
            Route::delete('/photos/{photo}', [PhotographerUploadController::class, 'destroy'])->name('destroy');
            Route::post('/publish', [PhotographerUploadController::class, 'publish'])->name('publish');
        });
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
