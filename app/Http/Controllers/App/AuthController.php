<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Jobs\SendMailJob;
use App\Mail\LoginAlert;
use App\Mail\PasswordResetOtp;
use App\Mail\TwoFactorLoginCode;
use App\Models\AuthSetting;
use App\Models\PasswordResetCode;
use App\Models\TwoFactorCode;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function loginView()
    {
        return view('app.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        if (!\Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            $validator->errors()->add('password', 'The password does not match with username');
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = \Auth::user();

        // Check if 2FA is enabled
        if ($user->two_factor_enabled) {
            \Auth::logout();

            $request->session()->put('2fa_user_id', $user->id);
            $request->session()->forget(['2fa_resend_count', '2fa_resend_locked_until']);

            if ($user->two_factor_method === 'email') {
                $this->sendTwoFactorCode($user);
            }

            return redirect()->route('two-factor.challenge');
        }

        $this->sendLoginAlert($user, $request);

        ActivityLogService::log('LOGIN', $user);

        return redirect()->route('index');
    }

    protected function sendLoginAlert(User $user, Request $request): void
    {
        $ip = $request->ip();
        $ua = $request->userAgent() ?? 'Unknown';

        // Parse user-agent into readable device/browser string
        $device = $this->parseUserAgent($ua);

        // Resolve IP to location via ip-api.com (non-blocking, done here before queue)
        $location = $this->resolveIpLocation($ip);

        SendMailJob::dispatch($user->email, new LoginAlert(
            userName: $user->name,
            ipAddress: $ip,
            device: $device,
            location: $location,
            loginAt: now()->format('M d, Y \a\t h:i A'),
        ));
    }

    protected function parseUserAgent(string $ua): string
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

        return "{$browser} on {$os}";
    }

    protected function resolveIpLocation(string $ip): string
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(3)->get("http://ip-api.com/json/{$ip}");

            if ($response->successful() && $response->json('status') === 'success') {
                $data = $response->json();
                $parts = array_filter([$data['city'] ?? null, $data['regionName'] ?? null, $data['country'] ?? null]);
                $location = implode(', ', $parts);

                if (!empty($data['isp'])) {
                    $location .= ' (' . $data['isp'] . ')';
                }

                return $location ?: 'Unknown location';
            }
        } catch (\Throwable $e) {
            // Silently fail — don't block login
        }

        return 'Unknown location';
    }

    protected function sendTwoFactorCode(User $user): void
    {
        // Clean up old codes
        TwoFactorCode::where('user_id', $user->id)->delete();

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        TwoFactorCode::create([
            'user_id' => $user->id,
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
        ]);

        SendMailJob::dispatch($user->email, new TwoFactorLoginCode($code, $user->name));
    }

    public function twoFactorChallenge(Request $request)
    {
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('loginView');
        }

        $user = User::find($request->session()->get('2fa_user_id'));
        if (!$user) {
            $request->session()->forget('2fa_user_id');
            return redirect()->route('loginView');
        }

        return view('app.two-factor-challenge', [
            'method' => $user->two_factor_method,
            'email' => $user->email,
        ]);
    }

    public function verifyTwoFactor(Request $request)
    {
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('loginView');
        }

        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = User::find($request->session()->get('2fa_user_id'));
        if (!$user) {
            $request->session()->forget('2fa_user_id');
            return redirect()->route('loginView');
        }

        $code = $request->input('code');

        // Check if it's a recovery code
        if (strlen($code) === 10) {
            $recoveryCodes = json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true);
            $index = array_search($code, $recoveryCodes);

            if ($index !== false) {
                // Remove the used recovery code
                unset($recoveryCodes[$index]);
                $user->update([
                    'two_factor_recovery_codes' => Crypt::encryptString(json_encode(array_values($recoveryCodes))),
                ]);

                $request->session()->forget('2fa_user_id');
                \Auth::login($user);
                $this->sendLoginAlert($user, $request);
                ActivityLogService::log('LOGIN', $user);
                return redirect()->route('index');
            }

            return back()->withErrors(['code' => 'Invalid recovery code.']);
        }

        // Verify based on method
        if ($user->two_factor_method === 'app') {
            $google2fa = new Google2FA();
            $secret = Crypt::decryptString($user->two_factor_secret);

            if (!$google2fa->verifyKey($secret, $code)) {
                return back()->withErrors(['code' => 'The verification code is invalid.']);
            }
        } else {
            // Email OTP
            $record = TwoFactorCode::where('user_id', $user->id)->latest()->first();

            if (!$record || $record->isExpired()) {
                return back()->withErrors(['code' => 'The verification code has expired. Please request a new one.']);
            }

            if (!Hash::check($code, $record->code)) {
                return back()->withErrors(['code' => 'The verification code is invalid.']);
            }

            TwoFactorCode::where('user_id', $user->id)->delete();
        }

        $request->session()->forget('2fa_user_id');
        \Auth::login($user);
        $this->sendLoginAlert($user, $request);
        ActivityLogService::log('LOGIN', $user);
        return redirect()->route('index');
    }

    public function resendTwoFactorCode(Request $request)
    {
        if (!$request->session()->has('2fa_user_id')) {
            return response()->json(['message' => 'Session expired.'], 401);
        }

        $user = User::find($request->session()->get('2fa_user_id'));
        if (!$user || $user->two_factor_method !== 'email') {
            return response()->json(['message' => 'Invalid request.'], 400);
        }

        // Check 1-hour lockdown
        $lockedUntil = $request->session()->get('2fa_resend_locked_until');
        if ($lockedUntil && now()->lt($lockedUntil)) {
            $minutes = now()->diffInMinutes($lockedUntil, false);
            return response()->json([
                'message' => "Too many resend attempts. Please try again in {$minutes} minute(s).",
                'locked' => true,
                'remaining' => 0,
            ], 429);
        }

        // Prevent resend while a valid code was sent less than 1 minute ago
        $activeCode = TwoFactorCode::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->where('created_at', '>', now()->subMinute())
            ->first();

        if ($activeCode) {
            $seconds = now()->diffInSeconds($activeCode->created_at->addMinute(), false);
            $remaining = 3 - (int) $request->session()->get('2fa_resend_count', 0);
            return response()->json([
                'message' => "Please wait {$seconds} seconds before requesting a new code.",
                'remaining' => max($remaining, 0),
            ], 429);
        }

        // Track resend count (max 3)
        $resendCount = (int) $request->session()->get('2fa_resend_count', 0) + 1;
        $request->session()->put('2fa_resend_count', $resendCount);

        if ($resendCount > 3) {
            $request->session()->put('2fa_resend_locked_until', now()->addHour());
            return response()->json([
                'message' => 'Too many resend attempts. You are locked out for 1 hour.',
                'locked' => true,
                'remaining' => 0,
            ], 429);
        }

        $this->sendTwoFactorCode($user);

        return response()->json([
            'message' => 'A new code has been sent to your email.',
            'remaining' => 3 - $resendCount,
        ]);
    }

    public function registerView(){
        if (!AuthSetting::get('registration_enabled', '1')) {
            return redirect()->route('loginView')->with('error', 'Registration is currently disabled.');
        }

        return view('app.register');
    }

    public function register(Request $request){
        if (!AuthSetting::get('registration_enabled', '1')) {
            abort(403, 'Registration is currently disabled.');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'email' => ['required', 'email','unique:users'],
            'password' => ['required',"confirmed", Password::min(7)],
        ]);

        $validated = $validator->validated();

        $user = User::create([
            'name' => $validated["name"],
            "email" => $validated["email"],
            "password" => Hash::make($validated["password"])
        ]);

        auth()->login($user);

        return redirect()->route('index');
    }

    public function logout()
    {
        $user = auth()->user();
        ActivityLogService::log('LOGOUT', $user);
        auth()->logout();
        return redirect()->route('login');
    }

    // ── Password Reset ────────────────────────────────────────────

    public function forgotPasswordView()
    {
        return view('app.forgot-password');
    }

    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'fingerprint' => ['nullable', 'string', 'max:64'],
        ]);

        $user = User::where('email', $request->input('email'))->first();
        $ip = $request->ip();
        $fingerprint = $request->input('fingerprint', '');

        // Check if blocked (account + IP + fingerprint)
        if ($this->isResetBlocked($user->id, $ip, $fingerprint)) {
            return back()->with('error', 'Password reset has been temporarily blocked due to too many attempts. Please try again later.')
                ->withInput();
        }

        // Get existing reset record for this user + IP combo
        $record = PasswordResetCode::where('user_id', $user->id)
            ->where('ip_address', $ip)
            ->latest()
            ->first();

        // Rate limit: don't resend if code was sent less than 1 minute ago
        if ($record && $record->created_at->diffInSeconds(now()) < 60 && !$record->isExpired()) {
            return back()->with('error', 'A code was already sent. Please check your email or wait before retrying.')
                ->withInput();
        }

        // Check resend count on existing record
        if ($record && !$record->isExpired() && $record->resend_count >= 5) {
            $record->update(['blocked_until' => now()->addHour()]);
            return back()->with('error', 'Too many resend attempts. Password reset is blocked for 1 hour.')
                ->withInput();
        }

        // Generate code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        if ($record && !$record->isExpired()) {
            // Resend: update existing record
            $record->update([
                'code' => Hash::make($code),
                'resend_count' => $record->resend_count + 1,
                'fingerprint' => $fingerprint ?: $record->fingerprint,
            ]);
        } else {
            // Clean up old codes for this user
            PasswordResetCode::where('user_id', $user->id)->delete();

            $record = PasswordResetCode::create([
                'user_id' => $user->id,
                'code' => Hash::make($code),
                'ip_address' => $ip,
                'fingerprint' => $fingerprint,
                'resend_count' => 0,
                'attempt_count' => 0,
                'expires_at' => now()->addMinutes(10),
            ]);
        }

        SendMailJob::dispatch($user->email, new PasswordResetOtp($code, $user->name));

        return redirect()->route('password.reset')
            ->with('reset_email', $user->email)
            ->with('success', 'A verification code has been sent to your email.');
    }

    public function resetPasswordView(Request $request)
    {
        $email = session('reset_email') ?? $request->query('email');
        if (!$email) {
            return redirect()->route('password.forgot');
        }

        return view('app.reset-password', ['email' => $email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'fingerprint' => ['nullable', 'string', 'max:64'],
        ]);

        $user = User::where('email', $request->input('email'))->first();
        $ip = $request->ip();
        $fingerprint = $request->input('fingerprint', '');

        // Check if blocked
        if ($this->isResetBlocked($user->id, $ip, $fingerprint)) {
            return back()->with('error', 'Password reset has been temporarily blocked due to too many failed attempts. Please try again later.')
                ->withInput(['email' => $request->input('email')]);
        }

        $record = PasswordResetCode::where('user_id', $user->id)
            ->where('ip_address', $ip)
            ->latest()
            ->first();

        if (!$record) {
            return redirect()->route('password.forgot')
                ->with('error', 'No reset code found. Please request a new one.');
        }

        if ($record->isExpired()) {
            return redirect()->route('password.forgot')
                ->with('error', 'The verification code has expired. Please request a new one.');
        }

        // Check attempt count
        if ($record->attempt_count >= 5) {
            $record->update(['blocked_until' => now()->addHour()]);
            return back()->with('error', 'Too many failed attempts. Password reset is blocked for 1 hour.')
                ->withInput(['email' => $request->input('email')]);
        }

        if (!Hash::check($request->input('code'), $record->code)) {
            $record->increment('attempt_count');
            $remaining = 5 - $record->fresh()->attempt_count;

            if ($remaining <= 0) {
                $record->update(['blocked_until' => now()->addHour()]);
                return back()->with('error', 'Too many failed attempts. Password reset is blocked for 1 hour.')
                    ->withInput(['email' => $request->input('email')]);
            }

            return back()->withErrors(['code' => "Invalid code. {$remaining} attempt(s) remaining."])
                ->withInput(['email' => $request->input('email')]);
        }

        // Code is valid — reset password
        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        // Clean up all reset codes for this user
        PasswordResetCode::where('user_id', $user->id)->delete();

        return redirect()->route('loginView')
            ->with('success', 'Password reset successfully. Please sign in with your new password.');
    }

    public function resendResetCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'fingerprint' => ['nullable', 'string', 'max:64'],
        ]);

        $user = User::where('email', $request->input('email'))->first();
        $ip = $request->ip();
        $fingerprint = $request->input('fingerprint', '');

        if ($this->isResetBlocked($user->id, $ip, $fingerprint)) {
            return response()->json([
                'message' => 'Password reset is blocked due to too many attempts. Please try again later.',
                'blocked' => true,
            ], 429);
        }

        $record = PasswordResetCode::where('user_id', $user->id)
            ->where('ip_address', $ip)
            ->latest()
            ->first();

        if (!$record || $record->isExpired()) {
            return response()->json(['message' => 'Reset session expired. Please start over.'], 410);
        }

        // Rate limit: 1 minute between resends
        if ($record->updated_at->diffInSeconds(now()) < 60) {
            $wait = 60 - $record->updated_at->diffInSeconds(now());
            return response()->json([
                'message' => "Please wait {$wait} seconds before resending.",
                'remaining' => 5 - $record->resend_count,
            ], 429);
        }

        if ($record->resend_count >= 5) {
            $record->update(['blocked_until' => now()->addHour()]);
            return response()->json([
                'message' => 'Too many resend attempts. Password reset is blocked for 1 hour.',
                'blocked' => true,
            ], 429);
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $record->update([
            'code' => Hash::make($code),
            'resend_count' => $record->resend_count + 1,
            'fingerprint' => $fingerprint ?: $record->fingerprint,
        ]);

        SendMailJob::dispatch($user->email, new PasswordResetOtp($code, $user->name));

        return response()->json([
            'message' => 'A new code has been sent to your email.',
            'remaining' => 5 - $record->fresh()->resend_count,
        ]);
    }

    protected function isResetBlocked(int $userId, string $ip, string $fingerprint): bool
    {
        // Check by account + IP
        $blocked = PasswordResetCode::where('user_id', $userId)
            ->where('ip_address', $ip)
            ->where('blocked_until', '>', now())
            ->exists();

        if ($blocked) {
            return true;
        }

        // Check by IP + fingerprint (if fingerprint provided)
        if ($fingerprint) {
            $blocked = PasswordResetCode::where('ip_address', $ip)
                ->where('fingerprint', $fingerprint)
                ->where('blocked_until', '>', now())
                ->exists();
        }

        return $blocked;
    }
}
