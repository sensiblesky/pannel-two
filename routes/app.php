<?php

use App\Http\Controllers\App\AuditController;
use App\Http\Controllers\App\AuthController;
use App\Http\Controllers\App\BranchController;
use App\Http\Controllers\App\DepartmentController;
use App\Http\Controllers\App\AuthSettingsController;
use App\Http\Controllers\App\CommunicationSettingsController;
use App\Http\Controllers\App\GeneralSettingsController;
use App\Http\Controllers\App\ProfileController;
use App\Http\Controllers\App\UserController;
use App\Http\Controllers\App\CustomerController;
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

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

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

    // Customers Management
    Route::get('/users/customers', [CustomerController::class, 'index'])->name('users/customers');
    Route::get('/users/customers/create', [CustomerController::class, 'create'])->name('users/customers-create');
    Route::post('/users/customers', [CustomerController::class, 'store'])->name('users/customers-store');
    Route::get('/users/customers/{customer:uuid}/edit', [CustomerController::class, 'edit'])->name('users/customers-edit');
    Route::put('/users/customers/{customer:uuid}', [CustomerController::class, 'update'])->name('users/customers-update');
    Route::delete('/users/customers/{customer:uuid}', [CustomerController::class, 'destroy'])->name('users/customers-destroy');

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
});
