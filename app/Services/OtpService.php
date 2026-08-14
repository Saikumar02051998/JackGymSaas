<?php

namespace App\Services;

use App\Models\User;

class OtpService
{
    public const TTL_MINUTES = 10;

    public function generate(): string
    {
        return (string) random_int(100000, 999999);
    }

    public function issue(User $user): string
    {
        $otp = $this->generate();

        $user->forceFill([
            'email_otp' => $otp,
            'email_otp_expires_at' => now()->addMinutes(self::TTL_MINUTES),
        ])->save();

        return $otp;
    }

    public function verify(User $user, string $otp): bool
    {
        if (! $user->email_otp || $user->email_otp_expires_at === null) {
            return false;
        }

        if (now()->gt($user->email_otp_expires_at)) {
            $this->clear($user);

            return false;
        }

        return hash_equals($user->email_otp, trim($otp));
    }

    public function clear(User $user): void
    {
        if ($user->email_otp === null && $user->email_otp_expires_at === null) {
            return;
        }

        $user->forceFill([
            'email_otp' => null,
            'email_otp_expires_at' => null,
        ])->save();
    }
}
