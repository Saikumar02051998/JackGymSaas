<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailService
{
    public const OTP_LOG_CHANNEL = 'otp';

    public function sendOtp(User $user, string $otp, string $purpose): bool
    {
        $mailer = config('mail.default', 'log');

        $logOtp = function (string $level, ?string $reason = null) use ($user, $otp, $purpose): void {
            $message = "OTP for {$user->email} ({$purpose}): {$otp}";

            if ($reason) {
                $message .= " | {$reason}";
            }

            Log::channel(self::OTP_LOG_CHANNEL)->{$level}($message);
        };

        if (in_array($mailer, ['log', 'array', 'sendmail'], true) || empty(config('mail.from.address'))) {
            $logOtp('info', 'Mail not configured, OTP logged instead of sent');

            return false;
        }

        try {
            Mail::to($user->email)->send(new OtpMail($otp, $purpose));

            return true;
        } catch (\Throwable $e) {
            $logOtp('error', $e->getMessage());

            return false;
        }
    }
}
