<?php

use App\Http\Controllers\App\AuditController;
use App\Http\Controllers\App\AuthController;
use App\Http\Controllers\App\BranchController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\DepartmentController;
use App\Http\Controllers\App\AuthSettingsController;
use App\Http\Controllers\App\CommunicationSettingsController;
use App\Http\Controllers\App\GeneralSettingsController;
use App\Http\Controllers\App\ProfileController;
use App\Http\Controllers\App\UserController;
use App\Http\Controllers\App\TicketDashboardController;
use App\Http\Controllers\App\TicketController;
use App\Http\Controllers\App\TicketCategoryController;
use App\Http\Controllers\App\TicketPriorityController;
use App\Http\Controllers\App\TicketStatusController;
use App\Http\Controllers\App\TagController;
use App\Http\Controllers\App\TicketFeatureSettingsController;
use App\Http\Controllers\App\TicketReportsController;
use App\Http\Controllers\App\MessageAlertController;
use App\Http\Controllers\App\HelpCenterController;
use App\Http\Controllers\App\HelpPageSettingsController;
use App\Http\Controllers\App\AgentController;
use App\Http\Controllers\App\RealtimeSettingsController;
use App\Http\Controllers\App\CannedResponseController;
use App\Http\Controllers\RealtimeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| App Routes
|--------------------------------------------------------------------------
|
| Custom application routes separated from the theme demo routes.
| These are the routes that will remain after theme cleanup.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginView'])->name('loginView');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::get('/register', [AuthController::class, 'registerView'])->name('registerView');
    Route::post('/register', [AuthController::class, 'register'])->name('register');

    // Two-Factor Challenge (during login)
    Route::get('/two-factor-challenge', [AuthController::class, 'twoFactorChallenge'])->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [AuthController::class, 'verifyTwoFactor'])->name('two-factor.verify');
    Route::post('/two-factor-challenge/resend', [AuthController::class, 'resendTwoFactorCode'])->name('two-factor.resend');

    // Forgot / Reset Password
    Route::get('/forgot-password', [AuthController::class, 'forgotPasswordView'])->name('password.forgot');
    Route::post('/forgot-password', [AuthController::class, 'sendResetCode'])->name('password.forgot.send');
    Route::get('/reset-password', [AuthController::class, 'resetPasswordView'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset.submit');
    Route::post('/forgot-password/resend', [AuthController::class, 'resendResetCode'])->name('password.forgot.resend');
});

// ─── Public Help Center (no auth required) ───────────────
Route::get('/help-center', [HelpCenterController::class, 'index'])->name('help-center');
Route::post('/help-center', [HelpCenterController::class, 'store'])->name('help-center.store');
Route::get('/help-center/submitted/{uuid}', [HelpCenterController::class, 'submitted'])->name('help-center.submitted');
Route::get('/help-center/track', [HelpCenterController::class, 'trackTicket'])->name('help-center.track');

// Logout (shared - any authenticated user)
Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('logout');

// Alert sound URL (shared - any authenticated user needs this for polling)
Route::middleware('auth')->get('/api/alert-sound', [MessageAlertController::class, 'getDefault'])->name('api/alert-sound');

// Realtime config & channel auth (shared - any authenticated user)
Route::middleware('auth')->get('/api/realtime/config', [RealtimeController::class, 'config'])->name('api/realtime-config');
Route::middleware('auth')->post('/api/realtime/auth', [RealtimeController::class, 'auth'])->name('api/realtime-auth');

// ─── Admin/Agent Panel (/app/...) ─────────────────────────────────
Route::middleware(['auth', 'admin.agent'])->prefix('app')->group(function () {

    // Dashboard (admin only)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile/account', [ProfileController::class, 'updateAccount'])->name('profile/update-account');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile/update-password');
    Route::post('/profile/2fa/setup', [ProfileController::class, 'setupTwoFactor'])->name('profile/setup-2fa');
    Route::post('/profile/2fa/confirm', [ProfileController::class, 'confirmTwoFactor'])->name('profile/confirm-2fa');
    Route::post('/profile/2fa/email/send', [ProfileController::class, 'sendEmailTwoFactorCode'])->name('profile/send-email-2fa-code');
    Route::post('/profile/2fa/email/confirm', [ProfileController::class, 'confirmEmailTwoFactor'])->name('profile/confirm-email-2fa');
    Route::delete('/profile/2fa/disable', [ProfileController::class, 'disableTwoFactor'])->name('profile/disable-2fa');
    Route::post('/profile/email/send-verification', [ProfileController::class, 'sendVerificationEmail'])->name('profile/send-verification');
    Route::post('/profile/email/verify', [ProfileController::class, 'verifyEmail'])->name('profile/verify-email');
    Route::delete('/profile/sessions/{session}', [ProfileController::class, 'destroySession'])->name('profile/destroy-session');
    Route::delete('/profile/sessions', [ProfileController::class, 'destroyOtherSessions'])->name('profile/destroy-other-sessions');

    // Users Management
    Route::get('/users', [UserController::class, 'index'])->name('users/index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users/create');
    Route::post('/users', [UserController::class, 'store'])->name('users/store');
    Route::get('/users/{user:uid}/edit', [UserController::class, 'edit'])->name('users/edit');
    Route::put('/users/{user:uid}', [UserController::class, 'update'])->name('users/update');
    Route::post('/users/{user:uid}/suspend', [UserController::class, 'suspend'])->name('users/suspend');
    Route::post('/users/{user:uid}/unsuspend', [UserController::class, 'unsuspend'])->name('users/unsuspend');
    Route::delete('/users/{user:uid}', [UserController::class, 'destroy'])->name('users/destroy');

    // Configuration - General Settings
    Route::get('/config/general', [GeneralSettingsController::class, 'index'])->name('config/general');
    Route::put('/config/general', [GeneralSettingsController::class, 'update'])->name('config/general-update');

    // Configuration - Authentication Settings
    Route::get('/config/authentication', [AuthSettingsController::class, 'index'])->name('config/authentication');
    Route::put('/config/authentication', [AuthSettingsController::class, 'update'])->name('config/authentication-update');

    // Configuration - Branches
    Route::get('/config/branches', [BranchController::class, 'index'])->name('config/branches');
    Route::get('/config/branches/create', [BranchController::class, 'create'])->name('config/branches-create');
    Route::post('/config/branches', [BranchController::class, 'store'])->name('config/branches-store');
    Route::get('/config/branches/{branch:uuid}/edit', [BranchController::class, 'edit'])->name('config/branches-edit');
    Route::put('/config/branches/{branch:uuid}', [BranchController::class, 'update'])->name('config/branches-update');
    Route::delete('/config/branches/{branch:uuid}', [BranchController::class, 'destroy'])->name('config/branches-destroy');

    // Configuration - Departments
    Route::get('/config/departments', [DepartmentController::class, 'index'])->name('config/departments');
    Route::get('/config/departments/create', [DepartmentController::class, 'create'])->name('config/departments-create');
    Route::post('/config/departments', [DepartmentController::class, 'store'])->name('config/departments-store');
    Route::get('/config/departments/{department:uuid}/edit', [DepartmentController::class, 'edit'])->name('config/departments-edit');
    Route::put('/config/departments/{department:uuid}', [DepartmentController::class, 'update'])->name('config/departments-update');
    Route::delete('/config/departments/{department:uuid}', [DepartmentController::class, 'destroy'])->name('config/departments-destroy');

    // Configuration - Audit Logs
    Route::get('/config/audit', [AuditController::class, 'index'])->name('config/audit');

    // Configuration - Communication
    Route::get('/config/communication', [CommunicationSettingsController::class, 'index'])->name('config/communication');
    Route::put('/config/communication', [CommunicationSettingsController::class, 'update'])->name('config/communication-update');
    Route::post('/config/communication/test', [CommunicationSettingsController::class, 'testEmail'])->name('config/communication-test');

    // Configuration - Message Alerts
    Route::get('/config/message-alerts', [MessageAlertController::class, 'index'])->name('config/message-alerts');
    Route::post('/config/message-alerts', [MessageAlertController::class, 'store'])->name('config/message-alerts-store');
    Route::get('/config/message-alerts/default-url', [MessageAlertController::class, 'getDefault'])->name('config/message-alerts-default-url');
    Route::put('/config/message-alerts/{id}/default', [MessageAlertController::class, 'setDefault'])->name('config/message-alerts-default');
    Route::delete('/config/message-alerts/{id}', [MessageAlertController::class, 'destroy'])->name('config/message-alerts-destroy');

    // Configuration - Help Page
    Route::get('/config/help-page', [HelpPageSettingsController::class, 'index'])->name('config/help-page');
    Route::put('/config/help-page', [HelpPageSettingsController::class, 'update'])->name('config/help-page-update');

    // Configuration - Realtime
    Route::get('/config/realtime', [RealtimeSettingsController::class, 'index'])->name('config/realtime');
    Route::put('/config/realtime', [RealtimeSettingsController::class, 'update'])->name('config/realtime-update');
    Route::post('/config/realtime/test', [RealtimeSettingsController::class, 'testConnection'])->name('config/realtime-test');

    // ─── Tickets ─────────────────────────────────────────────
    Route::get('/tickets', [TicketDashboardController::class, 'index'])->name('tickets/dashboard');
    Route::get('/tickets/poll', [TicketController::class, 'poll'])->name('tickets/poll');
    Route::get('/tickets/list', [TicketController::class, 'index'])->name('tickets/index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets/create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets/store');
    Route::post('/tickets/bulk-action', [TicketController::class, 'bulkAction'])->name('tickets/bulk-action');

    // Ticket AJAX search endpoints (must be before {uuid} wildcard)
    Route::get('/tickets/search/customers', [TicketController::class, 'searchCustomers'])->name('tickets/search-customers');
    Route::get('/tickets/search/agents', [TicketController::class, 'searchAgents'])->name('tickets/search-agents');
    Route::post('/tickets/quick-customer', [TicketController::class, 'storeQuickCustomer'])->name('tickets/quick-customer');

    // Ticket Reports (must be before {uuid} wildcard)
    Route::get('/tickets/reports/overview', [TicketReportsController::class, 'index'])->name('tickets/reports');

    // Ticket Settings (must be before {uuid} wildcard)
    Route::get('/tickets/settings/categories', [TicketCategoryController::class, 'index'])->name('tickets/settings-categories');
    Route::post('/tickets/settings/categories', [TicketCategoryController::class, 'store'])->name('tickets/settings-categories-store');
    Route::put('/tickets/settings/categories/{id}', [TicketCategoryController::class, 'update'])->name('tickets/settings-categories-update');
    Route::delete('/tickets/settings/categories/{id}', [TicketCategoryController::class, 'destroy'])->name('tickets/settings-categories-destroy');

    Route::get('/tickets/settings/priorities', [TicketPriorityController::class, 'index'])->name('tickets/settings-priorities');
    Route::post('/tickets/settings/priorities', [TicketPriorityController::class, 'store'])->name('tickets/settings-priorities-store');
    Route::put('/tickets/settings/priorities/{id}', [TicketPriorityController::class, 'update'])->name('tickets/settings-priorities-update');
    Route::delete('/tickets/settings/priorities/{id}', [TicketPriorityController::class, 'destroy'])->name('tickets/settings-priorities-destroy');

    Route::get('/tickets/settings/statuses', [TicketStatusController::class, 'index'])->name('tickets/settings-statuses');
    Route::post('/tickets/settings/statuses', [TicketStatusController::class, 'store'])->name('tickets/settings-statuses-store');
    Route::put('/tickets/settings/statuses/{id}', [TicketStatusController::class, 'update'])->name('tickets/settings-statuses-update');
    Route::delete('/tickets/settings/statuses/{id}', [TicketStatusController::class, 'destroy'])->name('tickets/settings-statuses-destroy');

    Route::get('/tickets/settings/tags', [TagController::class, 'index'])->name('tickets/settings-tags');
    Route::post('/tickets/settings/tags', [TagController::class, 'store'])->name('tickets/settings-tags-store');
    Route::put('/tickets/settings/tags/{id}', [TagController::class, 'update'])->name('tickets/settings-tags-update');
    Route::delete('/tickets/settings/tags/{id}', [TagController::class, 'destroy'])->name('tickets/settings-tags-destroy');

    Route::get('/tickets/settings/agents', [AgentController::class, 'index'])->name('tickets/settings-agents');
    Route::post('/tickets/settings/agents', [AgentController::class, 'store'])->name('tickets/settings-agents-store');
    Route::put('/tickets/settings/agents/{id}', [AgentController::class, 'update'])->name('tickets/settings-agents-update');
    Route::delete('/tickets/settings/agents/{id}', [AgentController::class, 'destroy'])->name('tickets/settings-agents-destroy');

    Route::get('/tickets/settings/canned-responses', [CannedResponseController::class, 'index'])->name('tickets/settings-canned-responses');
    Route::post('/tickets/settings/canned-responses', [CannedResponseController::class, 'store'])->name('tickets/settings-canned-responses-store');
    Route::put('/tickets/settings/canned-responses/{id}', [CannedResponseController::class, 'update'])->name('tickets/settings-canned-responses-update');
    Route::delete('/tickets/settings/canned-responses/{id}', [CannedResponseController::class, 'destroy'])->name('tickets/settings-canned-responses-destroy');
    Route::get('/tickets/canned-responses/search', [CannedResponseController::class, 'search'])->name('tickets/canned-responses-search');

    Route::get('/tickets/settings/general', [TicketFeatureSettingsController::class, 'index'])->name('tickets/settings-general');

    // Ticket detail routes (wildcard {uuid} must be LAST)
    Route::get('/tickets/{uuid}', [TicketController::class, 'show'])->name('tickets/show');
    Route::get('/tickets/{uuid}/messages', [TicketController::class, 'pollMessages'])->name('tickets/poll-messages');
    Route::post('/tickets/{uuid}/ajax-reply', [TicketController::class, 'ajaxReply'])->name('tickets/ajax-reply');
    Route::post('/tickets/{uuid}/typing', [TicketController::class, 'typing'])->name('tickets/typing');
    Route::post('/tickets/{uuid}/reply', [TicketController::class, 'reply'])->name('tickets/reply');
    Route::put('/tickets/{uuid}/status', [TicketController::class, 'updateStatus'])->name('tickets/update-status');
    Route::put('/tickets/{uuid}/priority', [TicketController::class, 'updatePriority'])->name('tickets/update-priority');
    Route::put('/tickets/{uuid}/assign', [TicketController::class, 'assign'])->name('tickets/assign');
    Route::post('/tickets/{uuid}/tags', [TicketController::class, 'addTag'])->name('tickets/add-tag');
    Route::delete('/tickets/{uuid}/tags/{tagId}', [TicketController::class, 'removeTag'])->name('tickets/remove-tag');
    Route::post('/tickets/{uuid}/watchers', [TicketController::class, 'addWatcher'])->name('tickets/add-watcher');
    Route::delete('/tickets/{uuid}/watchers/{userId}', [TicketController::class, 'removeWatcher'])->name('tickets/remove-watcher');
    Route::delete('/tickets/{uuid}', [TicketController::class, 'destroy'])->name('tickets/destroy');
    Route::put('/tickets/settings/general', [TicketFeatureSettingsController::class, 'update'])->name('tickets/settings-general-update');
});
