<?php

namespace App\Services;

use App\Contracts\SmsSender;
use App\Mail\LoginCodeMail;
use App\Models\LoginCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginCodeService
{
    private const TTL_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;
    private const RESEND_SECONDS = 30;
    private const SENDS_PER_WINDOW = 5;

    public function __construct(private SmsSender $sms) {}

    public function normalize(string $value, string $channel): string
    {
        if ($channel === 'email') {
            return Str::lower(trim($value));
        }

        $digits = preg_replace('/\D/', '', $value);

        if (str_starts_with($digits, '0')) {
            $digits = '254' . substr($digits, 1);
        } elseif (strlen($digits) === 9) {
            $digits = '254' . $digits;
        }

        return '+' . $digits;
    }

    public function send(string $identifier, string $channel, string $role, ?string $ip = null): LoginCode
    {
        $this->guardSending($identifier);

        LoginCode::active()->where('identifier', $identifier)->update(['consumed_at' => now()]);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = Str::random(48);

        $record = LoginCode::create([
            'identifier' => $identifier,
            'channel' => $channel,
            'role' => $role,
            'code_hash' => Hash::make($code),
            'link_token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
            'ip_address' => $ip,
        ]);

        $this->deliver($record, $code, $token);

        return $record;
    }

    public function verify(string $identifier, string $code): User
    {
        $record = LoginCode::active()
            ->where('identifier', $identifier)
            ->latest('id')
            ->first();

        if (! $record) {
            throw ValidationException::withMessages(['code' => 'This code has expired. Request a new one.']);
        }

        if ($record->attempts >= self::MAX_ATTEMPTS) {
            throw ValidationException::withMessages(['code' => 'Too many wrong attempts. Request a new code.']);
        }

        if (! Hash::check($code, $record->code_hash)) {
            $record->increment('attempts');

            throw ValidationException::withMessages(['code' => 'That code is not correct.']);
        }

        return $this->consume($record);
    }

    public function verifyLink(string $token): User
    {
        $record = LoginCode::active()
            ->where('link_token_hash', hash('sha256', $token))
            ->first();

        if (! $record) {
            throw ValidationException::withMessages(['link' => 'This sign-in link has expired. Request a new one.']);
        }

        return $this->consume($record);
    }

    private function consume(LoginCode $record): User
    {
        $record->update(['consumed_at' => now()]);

        $column = $record->channel === 'email' ? 'email' : 'phone';
        $verifiedColumn = $record->channel === 'email' ? 'email_verified_at' : 'phone_verified_at';

        $user = User::firstOrNew([$column => $record->identifier]);

        if (! $user->exists) {
            $user->role = $record->role;
        }

        $user->{$verifiedColumn} ??= now();
        $user->save();

        return $user;
    }

    private function guardSending(string $identifier): void
    {
        $latest = LoginCode::where('identifier', $identifier)->latest('id')->first();

        if ($latest && $latest->created_at->gt(now()->subSeconds(self::RESEND_SECONDS))) {
            throw ValidationException::withMessages([
                'identifier' => 'Wait ' . self::RESEND_SECONDS . ' seconds before requesting another code.',
            ]);
        }

        $key = 'login-code:' . $identifier;

        if (RateLimiter::tooManyAttempts($key, self::SENDS_PER_WINDOW)) {
            throw ValidationException::withMessages([
                'identifier' => 'Too many codes requested. Try again in ' . ceil(RateLimiter::availableIn($key) / 60) . ' minutes.',
            ]);
        }

        RateLimiter::hit($key, self::TTL_MINUTES * 60);
    }

    private function deliver(LoginCode $record, string $code, string $token): void
    {
        if ($record->channel === 'email') {
            Mail::to($record->identifier)->send(new LoginCodeMail($code, url('/login/link/' . $token)));

            return;
        }

        $this->sms->send(
            $record->identifier,
            "SafariHub code: {$code}. It expires in " . self::TTL_MINUTES . ' minutes. Never share it.'
        );
    }
}
