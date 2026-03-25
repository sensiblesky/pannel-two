<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Jobs\SendMailJob;
use App\Mail\PasswordChangedAlert;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CustomerProfileController extends Controller
{
    public function index(Request $request)
    {
        return view('customer.profile', [
            'user' => $request->user(),
            'tab' => $request->query('tab', 'account'),
        ]);
    }

    public function updateAccount(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $emailChanged = $validated['email'] !== $user->email;
        if ($emailChanged) {
            $validated['email_verified_at'] = null;
        }

        $user->update($validated);

        return redirect()->route('customer.profile', ['tab' => 'account'])
            ->with('success', $emailChanged ? 'Account updated. Please verify your new email.' : 'Account updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        SendMailJob::dispatch($user->email, new PasswordChangedAlert($user->name));

        return redirect()->route('customer.profile', ['tab' => 'security'])->with('success', 'Password changed successfully.');
    }
}
