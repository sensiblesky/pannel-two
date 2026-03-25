<?php

use App\Http\Controllers\Agent\AgentTicketDashboardController;
use App\Http\Controllers\Agent\AgentTicketController;
use App\Http\Controllers\Agent\AgentTicketReportsController;
use App\Http\Controllers\Agent\AgentProfileController;
use App\Http\Controllers\Agent\AgentTagController;
use App\Http\Controllers\Agent\AgentCannedResponseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Agent Portal Routes
|--------------------------------------------------------------------------
|
| These routes are for the agent-facing portal. Agents are redirected here
| after login. All routes require auth + agent role middleware.
|
*/

Route::middleware(['auth', 'agent'])->prefix('agent')->group(function () {

    // Tickets Dashboard
    Route::get('/tickets', [AgentTicketDashboardController::class, 'index'])->name('agent.tickets/dashboard');

    // Ticket list, create, bulk
    Route::get('/tickets/poll', [AgentTicketController::class, 'poll'])->name('agent.tickets/poll');
    Route::get('/tickets/list', [AgentTicketController::class, 'index'])->name('agent.tickets/index');
    Route::get('/tickets/create', [AgentTicketController::class, 'create'])->name('agent.tickets/create');
    Route::post('/tickets', [AgentTicketController::class, 'store'])->name('agent.tickets/store');
    Route::post('/tickets/bulk-action', [AgentTicketController::class, 'bulkAction'])->name('agent.tickets/bulk-action');

    // Ticket AJAX search endpoints (must be before {uuid} wildcard)
    Route::get('/tickets/search/customers', [AgentTicketController::class, 'searchCustomers'])->name('agent.tickets/search-customers');
    Route::get('/tickets/search/agents', [AgentTicketController::class, 'searchAgents'])->name('agent.tickets/search-agents');
    Route::post('/tickets/quick-customer', [AgentTicketController::class, 'storeQuickCustomer'])->name('agent.tickets/quick-customer');

    // Ticket Reports (must be before {uuid} wildcard)
    Route::get('/tickets/reports/overview', [AgentTicketReportsController::class, 'index'])->name('agent.tickets/reports');

    // Ticket Settings — Tags
    Route::get('/tickets/settings/tags', [AgentTagController::class, 'index'])->name('agent.tickets/settings-tags');
    Route::post('/tickets/settings/tags', [AgentTagController::class, 'store'])->name('agent.tickets/settings-tags-store');
    Route::put('/tickets/settings/tags/{id}', [AgentTagController::class, 'update'])->name('agent.tickets/settings-tags-update');
    Route::delete('/tickets/settings/tags/{id}', [AgentTagController::class, 'destroy'])->name('agent.tickets/settings-tags-destroy');

    // Ticket Settings — Canned Responses
    Route::get('/tickets/settings/canned-responses', [AgentCannedResponseController::class, 'index'])->name('agent.tickets/settings-canned-responses');
    Route::post('/tickets/settings/canned-responses', [AgentCannedResponseController::class, 'store'])->name('agent.tickets/settings-canned-responses-store');
    Route::put('/tickets/settings/canned-responses/{id}', [AgentCannedResponseController::class, 'update'])->name('agent.tickets/settings-canned-responses-update');
    Route::delete('/tickets/settings/canned-responses/{id}', [AgentCannedResponseController::class, 'destroy'])->name('agent.tickets/settings-canned-responses-destroy');

    // Canned responses JSON search (used in ticket reply box)
    Route::get('/tickets/canned-responses/search', [AgentCannedResponseController::class, 'search'])->name('agent.tickets/canned-responses-search');

    // Ticket detail routes (wildcard {uuid} must be LAST)
    Route::get('/tickets/{uuid}', [AgentTicketController::class, 'show'])->name('agent.tickets/show');
    Route::get('/tickets/{uuid}/messages', [AgentTicketController::class, 'pollMessages'])->name('agent.tickets/poll-messages');
    Route::post('/tickets/{uuid}/messages/{messageId}/read', [AgentTicketController::class, 'markMessageRead'])->name('agent.tickets/mark-read');
    Route::get('/tickets/{uuid}/messages/{messageId}/readers', [AgentTicketController::class, 'getMessageReaders'])->name('agent.tickets/message-readers');
    Route::post('/tickets/{uuid}/ajax-reply', [AgentTicketController::class, 'ajaxReply'])->name('agent.tickets/ajax-reply');
    Route::post('/tickets/{uuid}/typing', [AgentTicketController::class, 'typing'])->name('agent.tickets/typing');
    Route::post('/tickets/{uuid}/reply', [AgentTicketController::class, 'reply'])->name('agent.tickets/reply');
    Route::put('/tickets/{uuid}/status', [AgentTicketController::class, 'updateStatus'])->name('agent.tickets/update-status');
    Route::put('/tickets/{uuid}/priority', [AgentTicketController::class, 'updatePriority'])->name('agent.tickets/update-priority');
    Route::put('/tickets/{uuid}/assign', [AgentTicketController::class, 'assign'])->name('agent.tickets/assign');
    Route::post('/tickets/{uuid}/tags', [AgentTicketController::class, 'addTag'])->name('agent.tickets/add-tag');
    Route::delete('/tickets/{uuid}/tags/{tagId}', [AgentTicketController::class, 'removeTag'])->name('agent.tickets/remove-tag');
    Route::post('/tickets/{uuid}/watchers', [AgentTicketController::class, 'addWatcher'])->name('agent.tickets/add-watcher');
    Route::delete('/tickets/{uuid}/watchers/{userId}', [AgentTicketController::class, 'removeWatcher'])->name('agent.tickets/remove-watcher');
    Route::delete('/tickets/{uuid}', [AgentTicketController::class, 'destroy'])->name('agent.tickets/destroy');

    // Profile
    Route::get('/profile', [AgentProfileController::class, 'index'])->name('agent.profile');
    Route::put('/profile/account', [AgentProfileController::class, 'updateAccount'])->name('agent.profile/update-account');
    Route::put('/profile/password', [AgentProfileController::class, 'updatePassword'])->name('agent.profile/update-password');
    Route::post('/profile/2fa/setup', [AgentProfileController::class, 'setupTwoFactor'])->name('agent.profile/setup-2fa');
    Route::post('/profile/2fa/confirm', [AgentProfileController::class, 'confirmTwoFactor'])->name('agent.profile/confirm-2fa');
    Route::post('/profile/2fa/email/send', [AgentProfileController::class, 'sendEmailTwoFactorCode'])->name('agent.profile/send-email-2fa-code');
    Route::post('/profile/2fa/email/confirm', [AgentProfileController::class, 'confirmEmailTwoFactor'])->name('agent.profile/confirm-email-2fa');
    Route::delete('/profile/2fa/disable', [AgentProfileController::class, 'disableTwoFactor'])->name('agent.profile/disable-2fa');
    Route::post('/profile/email/send-verification', [AgentProfileController::class, 'sendVerificationEmail'])->name('agent.profile/send-verification');
    Route::post('/profile/email/verify', [AgentProfileController::class, 'verifyEmail'])->name('agent.profile/verify-email');
    Route::delete('/profile/sessions/{session}', [AgentProfileController::class, 'destroySession'])->name('agent.profile/destroy-session');
    Route::delete('/profile/sessions', [AgentProfileController::class, 'destroyOtherSessions'])->name('agent.profile/destroy-other-sessions');
});
