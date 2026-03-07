<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Jobs\SendMailJob;
use App\Jobs\SendVerificationEmailJob;
use App\Mail\EmailVerificationCode as EmailVerificationMail;
use App\Mail\PasswordChangedAlert;
use App\Mail\TwoFactorAlert;
use App\Mail\TwoFactorLoginCode;
use App\Models\EmailVerificationCode;
use App\Models\TwoFactorCode;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use PragmaRX\Google2FA\Google2FA;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'account');

        return view('app.profile.index', [
            'user' => $request->user(),
            'tab' => $tab,
            'sessions' => $this->getUserSessions($request),
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
            'tab' => ['nullable', 'string'],
        ]);

        $oldValues = $user->only(['name', 'email', 'phone']);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        // Reset email verification if email changed
        $emailChanged = $validated['email'] !== $oldValues['email'];
        if ($emailChanged) {
            $validated['email_verified_at'] = null;
        }

        unset($validated['tab']);
        $user->update($validated);

        ActivityLogService::updated($user, $oldValues);

        $message = $emailChanged
            ? 'Account updated. Please verify your new email address.'
            : 'Account updated successfully.';

        return redirect()->route('profile', ['tab' => $request->input('tab', 'account')])->with('success', $message);
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

        ActivityLogService::updated($user, ['password' => '********']);

        SendMailJob::dispatch($user->email, new PasswordChangedAlert($user->name));

        return redirect()->route('profile', ['tab' => 'security'])->with('success', 'Password changed successfully.');
    }

    /**
     * Step 1: Generate a TOTP secret, store it in session, and return QR code data.
     */
    public function setupTwoFactor(Request $request)
    {
        $user = $request->user();
        $google2fa = new Google2FA();

        $secret = $google2fa->generateSecretKey();

        $request->session()->put('2fa_secret', $secret);

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name', 'Laravel'),
            $user->email,
            $secret
        );

        return response()->json([
            'secret' => $secret,
            'qr_url' => $qrCodeUrl,
        ]);
    }

    /**
     * Step 2: Verify the OTP code and activate 2FA.
     */
    public function confirmTwoFactor(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $secret = $request->session()->get('2fa_secret');

        if (!$secret) {
            return back()->with('error', 'Two-factor setup session expired. Please try again.')->withInput(['tab' => 'security']);
        }

        $google2fa = new Google2FA();

        if (!$google2fa->verifyKey($secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'The verification code is invalid. Please try again.'])->withInput(['tab' => 'security']);
        }

        $user = $request->user();

        // Generate recovery codes
        $recoveryCodes = collect(range(1, 8))->map(fn () => Str::random(10))->toArray();

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_method' => 'app',
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($recoveryCodes)),
            'two_factor_confirmed_at' => now(),
        ]);

        $request->session()->forget('2fa_secret');

        ActivityLogService::updated($user, ['two_factor_enabled' => false]);

        SendMailJob::dispatch($user->email, new TwoFactorAlert($user->name, true, $recoveryCodes));

        return redirect()->route('profile', ['tab' => 'security'])
            ->with('success', 'Two-factor authentication enabled successfully.')
            ->with('recovery_codes', $recoveryCodes)
            ->with('two_factor_method', 'app');
    }

    /**
     * Step 1 for email 2FA: validate password, send verification code to email.
     */
    public function sendEmailTwoFactorCode(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->two_factor_enabled) {
            return response()->json(['message' => 'Two-factor authentication is already enabled.'], 422);
        }

        // Rate-limit: prevent resend while a valid code exists
        $activeCode = TwoFactorCode::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->first();

        if ($activeCode) {
            return response()->json(['message' => 'Code already sent. Check your email.']);
        }

        // Clean up old codes
        TwoFactorCode::where('user_id', $user->id)->delete();

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        TwoFactorCode::create([
            'user_id' => $user->id,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
        ]);

        SendMailJob::dispatch($user->email, new TwoFactorLoginCode($code, $user->name));

        return response()->json(['message' => 'Verification code sent to your email.']);
    }

    /**
     * Step 2 for email 2FA: verify code and activate.
     */
    public function confirmEmailTwoFactor(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if ($user->two_factor_enabled) {
            return back()->with('error', 'Two-factor authentication is already enabled.');
        }

        $record = TwoFactorCode::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$record || $record->isExpired()) {
            return back()->withErrors(['code' => 'The verification code has expired. Please request a new one.'])->withInput(['tab' => 'security']);
        }

        if (!Hash::check($request->input('code'), $record->code)) {
            return back()->withErrors(['code' => 'The verification code is invalid.'])->withInput(['tab' => 'security']);
        }

        // Clean up
        TwoFactorCode::where('user_id', $user->id)->delete();

        // Generate recovery codes
        $recoveryCodes = collect(range(1, 8))->map(fn () => Str::random(10))->toArray();

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_method' => 'email',
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($recoveryCodes)),
            'two_factor_confirmed_at' => now(),
        ]);

        ActivityLogService::updated($user, ['two_factor_enabled' => false]);

        SendMailJob::dispatch($user->email, new TwoFactorAlert($user->name, true, $recoveryCodes));

        return redirect()->route('profile', ['tab' => 'security'])
            ->with('success', 'Email two-factor authentication enabled successfully.')
            ->with('recovery_codes', $recoveryCodes)
            ->with('two_factor_method', 'email');
    }

    public function sendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email is already verified.'], 422);
        }

        // Prevent resend while a valid (unexpired) code exists
        $activeCode = EmailVerificationCode::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->first();

        if ($activeCode) {
            $minutesLeft = (int) now()->diffInMinutes($activeCode->expires_at, false);
            return response()->json([
                'message' => "A verification code was already sent. Please wait {$minutesLeft} minute(s) before requesting a new one.",
            ], 429);
        }

        // Clean up old codes
        EmailVerificationCode::where('user_id', $user->id)->delete();

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
        ]);

        // Dispatch queued job to send the email
        SendVerificationEmailJob::dispatch($user->email, $code, $user->name);

        return response()->json(['message' => 'Verification code sent to your email.']);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if ($user->email_verified_at) {
            return redirect()->route('profile')->with('success', 'Email is already verified.');
        }

        $record = EmailVerificationCode::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$record || $record->isExpired()) {
            return back()->withErrors(['code' => 'The verification code has expired. Please request a new one.']);
        }

        if (!Hash::check($request->input('code'), $record->code)) {
            return back()->withErrors(['code' => 'The verification code is invalid.']);
        }

        $user->update(['email_verified_at' => now()]);

        // Clean up
        EmailVerificationCode::where('user_id', $user->id)->delete();

        return redirect()->route('profile')->with('success', 'Email verified successfully!');
    }

    public function disableTwoFactor(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'code' => $user->two_factor_method === 'app' ? ['required', 'string', 'size:6'] : ['nullable'],
        ]);

        if ($user->two_factor_method === 'app') {
            $google2fa = new Google2FA();
            $secret = Crypt::decryptString($user->two_factor_secret);

            if (!$google2fa->verifyKey($secret, $request->input('code'))) {
                return back()->withErrors(['code' => 'The verification code is invalid.']);
            }
        }

        $user->update([
            'two_factor_enabled' => false,
            'two_factor_method' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        ActivityLogService::updated($user, ['two_factor_enabled' => true]);

        SendMailJob::dispatch($user->email, new TwoFactorAlert($user->name, false));

        return redirect()->route('profile', ['tab' => 'security'])->with('success', 'Two-factor authentication disabled.');
    }

    // ── Session Management ────────────────────────────────────────

    protected function getUserSessions(Request $request): array
    {
        $currentSessionId = session()->getId();

        $sessions = DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_activity')
            ->get();

        return $sessions->map(function ($session) use ($currentSessionId) {
            $ua = $session->user_agent ?? '';

            return (object) [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'user_agent' => $ua,
                'device' => $this->parseSessionUserAgent($ua),
                'is_current' => $session->id === $currentSessionId,
                'last_active' => \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                'last_active_at' => \Carbon\Carbon::createFromTimestamp($session->last_activity),
            ];
        })->toArray();
    }

    protected function parseSessionUserAgent(string $ua): array
    {
        // Detect browser
        $browser = 'Unknown Browser';
        if (preg_match('/Edg[\/\s]([\d.]+)/i', $ua)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/OPR[\/\s]([\d.]+)/i', $ua)) {
            $browser = 'Opera';
        } elseif (preg_match('/Chrome[\/\s]([\d.]+)/i', $ua)) {
            $browser = 'Google Chrome';
        } elseif (preg_match('/Firefox[\/\s]([\d.]+)/i', $ua)) {
            $browser = 'Mozilla Firefox';
        } elseif (preg_match('/Safari[\/\s]([\d.]+)/i', $ua) && !str_contains($ua, 'Chrome')) {
            $browser = 'Safari';
        }

        // Detect OS
        $os = 'Unknown OS';
        if (str_contains($ua, 'Windows')) {
            $os = 'Windows';
        } elseif (str_contains($ua, 'Macintosh') || str_contains($ua, 'Mac OS')) {
            $os = 'macOS';
        } elseif (str_contains($ua, 'Linux') && !str_contains($ua, 'Android')) {
            $os = 'Linux';
        } elseif (str_contains($ua, 'Android')) {
            $os = 'Android';
        } elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) {
            $os = 'iOS';
        }

        // Detect device type
        $type = 'desktop';
        if (preg_match('/Mobile|Android|iPhone/i', $ua)) {
            $type = 'mobile';
        } elseif (preg_match('/iPad|Tablet/i', $ua)) {
            $type = 'tablet';
        }

        return [
            'browser' => $browser,
            'os' => $os,
            'type' => $type,
        ];
    }

    public function destroySession(Request $request, string $sessionId)
    {
        $currentSessionId = session()->getId();

        // Prevent terminating current session via this route
        if ($sessionId === $currentSessionId) {
            return back()->with('error', 'You cannot terminate your current session from here. Use logout instead.');
        }

        // Only delete sessions belonging to current user
        DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $request->user()->id)
            ->delete();

        return redirect()->route('profile', ['tab' => 'sessions'])->with('success', 'Session terminated successfully.');
    }

    public function destroyOtherSessions(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', session()->getId())
            ->delete();

        return redirect()->route('profile', ['tab' => 'sessions'])->with('success', 'All other sessions have been terminated.');
    }
}
