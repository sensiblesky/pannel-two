<?php

use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerTicketController;
use App\Http\Controllers\Customer\CustomerProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Portal Routes
|--------------------------------------------------------------------------
|
| These routes are for the customer-facing portal. Customers are
| redirected here after login. All routes require auth + customer role.
|
*/

Route::middleware(['auth', 'customer'])->group(function () {

    // Dashboard
    Route::get('/', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');

    // Tickets
    Route::get('/tickets/poll', [CustomerTicketController::class, 'poll'])->name('customer.tickets.poll');
    Route::get('/tickets', [CustomerTicketController::class, 'index'])->name('customer.tickets');
    Route::get('/tickets/create', [CustomerTicketController::class, 'create'])->name('customer.tickets.create');
    Route::post('/tickets', [CustomerTicketController::class, 'store'])->name('customer.tickets.store');
    Route::get('/tickets/{uuid}', [CustomerTicketController::class, 'show'])->name('customer.tickets.show');
    Route::get('/tickets/{uuid}/messages', [CustomerTicketController::class, 'pollMessages'])->name('customer.tickets.poll-messages');
    Route::post('/tickets/{uuid}/ajax-reply', [CustomerTicketController::class, 'ajaxReply'])->name('customer.tickets.ajax-reply');
    Route::post('/tickets/{uuid}/typing', [CustomerTicketController::class, 'typing'])->name('customer.tickets.typing');
    Route::post('/tickets/{uuid}/close', [CustomerTicketController::class, 'closeTicket'])->name('customer.tickets.close');
    Route::post('/tickets/{uuid}/reopen', [CustomerTicketController::class, 'reopenTicket'])->name('customer.tickets.reopen');
    Route::post('/tickets/{uuid}/reply', [CustomerTicketController::class, 'reply'])->name('customer.tickets.reply');

    // Profile
    Route::get('/profile', [CustomerProfileController::class, 'index'])->name('customer.profile');
    Route::put('/profile/account', [CustomerProfileController::class, 'updateAccount'])->name('customer.profile.update-account');
    Route::put('/profile/password', [CustomerProfileController::class, 'updatePassword'])->name('customer.profile.update-password');
});
