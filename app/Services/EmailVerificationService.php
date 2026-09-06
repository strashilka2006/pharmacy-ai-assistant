<?php

namespace App\Services;

use App\Mail\VerificationCodeMail;
use App\Models\EmailVerification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * Подтверждение почты шестизначным кодом. Порт AJAX-веток register.php.
 *
 * Отличия от оригинала:
 *  - код хранится хешем, а не открытым текстом;
 *  - «подтверждено» больше не живёт в скрытом поле формы, которое клиент
 *    мог просто выставить сам (email_verified=1 в POST) — проверка идёт по базе;
 *  - письмо уходит через очередь, регистрация не ждёт SMTP.
 */
class EmailVerificationService
{
    public function send(string $email): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailVerification::where('email', $email)->delete();

        EmailVerification::create([
            'email' => $email,
            'code_hash' => Hash::make($code),
            'verified' => false,
            'expires_at' => now()->addMinutes(config('pharmacy.email_verification.code_ttl_minutes')),
        ]);

        Mail::to($email)->queue(new VerificationCodeMail($code));
    }

    public function verify(string $email, string $code): bool
    {
        $record = EmailVerification::where('email', $email)
            ->where('verified', false)
            ->latest()
            ->first();

        if (! $record || $record->isExpired() || ! $record->matches($code)) {
            return false;
        }

        $record->update(['verified' => true]);

        return true;
    }

    public function isVerified(string $email): bool
    {
        return EmailVerification::where('email', $email)
            ->where('verified', true)
            ->where('expires_at', '>', now()->subHour())
            ->exists();
    }

    public function forget(string $email): void
    {
        EmailVerification::where('email', $email)->delete();
    }
}
