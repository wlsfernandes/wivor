<?php

use App\Http\Controllers\Admin\CrmContactController as AdminCrmContactController;
use App\Http\Controllers\Admin\CrmDashboardController as AdminCrmDashboardController;
use App\Http\Controllers\Admin\CrmEventParticipantController as AdminCrmEventParticipantController;
use App\Http\Controllers\Admin\CrmImportController as AdminCrmImportController;
use App\Http\Controllers\Admin\CrmImportMappingController as AdminCrmImportMappingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PhotographerController as AdminPhotographerController;
use App\Http\Controllers\Admin\PhotoRemovalRequestController as AdminPhotoRemovalRequestController;
use App\Http\Controllers\Admin\PromoCodeController as AdminPromoCodeController;
use App\Http\Controllers\Admin\WebsitePolicyController as AdminWebsitePolicyController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| These routes require an authenticated user with administrator access.
|
*/

Route::middleware(['auth', 'can:access-admin'])->group(function () {
    Route::get('/index', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // User management
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
    Route::delete('/list-photographers/{photographer}', [AdminPhotographerController::class, 'destroy'])->name('admin.photographers.destroy');
    Route::post('/list-photographers/{photographer}/payout-status', [AdminPhotographerController::class, 'refreshPayoutStatus'])->name('admin.photographers.payout-status');

    Route::post('/events/{event}/photographers', [EventController::class, 'assignPhotographer'])->name('admin.events.photographers.assign');

    Route::get('/admin/media', [AdminMediaController::class, 'index'])->name('admin.media.index');
    Route::get('/admin/media/{event}', [AdminMediaController::class, 'show'])->name('admin.media.show');
    Route::patch('/admin/media/{event}/assignments/{photographer}/deadline', [AdminMediaController::class, 'extendDeadline'])->name('admin.media.deadline');
    Route::post('/admin/media/{event}/photos/{photo}/retry', [AdminMediaController::class, 'retry'])->name('admin.media.retry');
    Route::patch('/admin/media/{event}/photos/{photo}/unpublish', [AdminMediaController::class, 'unpublish'])->name('admin.media.unpublish');
    Route::delete('/admin/media/{event}/photos/{photo}', [AdminMediaController::class, 'remove'])->name('admin.media.remove');
    Route::post('/admin/media/{event}/close', [AdminMediaController::class, 'closeGallery'])->name('admin.media.close');
    Route::post('/admin/media/{event}/holds', [AdminMediaController::class, 'hold'])->name('admin.media.holds.store');
    Route::delete('/admin/media/{event}/holds/{hold}', [AdminMediaController::class, 'releaseHold'])->name('admin.media.holds.release');

    Route::get('/admin/removal-requests', [AdminPhotoRemovalRequestController::class, 'index'])->name('admin.removal-requests.index');
    Route::patch('/admin/removal-requests/{removalRequest}/resolve', [AdminPhotoRemovalRequestController::class, 'resolve'])->name('admin.removal-requests.resolve');

    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::view('/payments/manual', 'admin.payments.manual')->name('payments.manual');

    Route::resource('/admin/promo-codes', AdminPromoCodeController::class)
        ->names('admin.promo-codes')
        ->except('show');

    Route::get('/admin/website/{policy}/edit', [AdminWebsitePolicyController::class, 'edit'])
        ->name('admin.website.policies.edit');
    Route::put('/admin/website/{policy}', [AdminWebsitePolicyController::class, 'update'])
        ->name('admin.website.policies.update');

    Route::get('/admin/crm', [AdminCrmDashboardController::class, 'index'])->name('admin.crm.dashboard');
    Route::get('/admin/crm/contacts', [AdminCrmContactController::class, 'index'])->name('admin.crm.contacts.index');
    Route::get('/admin/crm/contacts/{contact}', [AdminCrmContactController::class, 'show'])->name('admin.crm.contacts.show');
    Route::get('/admin/crm/events/{event}/participants', [AdminCrmEventParticipantController::class, 'index'])->name('admin.crm.events.participants');
    Route::get('/admin/crm/imports', [AdminCrmImportController::class, 'index'])->name('admin.crm.imports.index');
    Route::get('/admin/crm/events/{event}/imports/create', [AdminCrmImportController::class, 'create'])->name('admin.crm.imports.create');
    Route::post('/admin/crm/events/{event}/imports', [AdminCrmImportController::class, 'store'])->name('admin.crm.imports.store');
    Route::get('/admin/crm/imports/{import}', [AdminCrmImportController::class, 'show'])->name('admin.crm.imports.show');
    Route::get('/admin/crm/imports/{import}/mapping', [AdminCrmImportMappingController::class, 'edit'])->name('admin.crm.imports.mapping.edit');
    Route::put('/admin/crm/imports/{import}/mapping', [AdminCrmImportMappingController::class, 'update'])->name('admin.crm.imports.mapping.update');
    Route::get('/admin/crm/imports/{import}/preview', [AdminCrmImportMappingController::class, 'preview'])->name('admin.crm.imports.preview');
    Route::post('/admin/crm/imports/{import}/start', [AdminCrmImportController::class, 'start'])->name('admin.crm.imports.start');
    Route::post('/admin/crm/imports/{import}/retry', [AdminCrmImportController::class, 'retry'])->name('admin.crm.imports.retry');
    Route::get('/admin/crm/imports/{import}/errors', [AdminCrmImportController::class, 'downloadErrors'])->name('admin.crm.imports.errors');
});
