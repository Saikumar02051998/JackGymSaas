<?php

namespace App\Services;

use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    protected ?Api $api = null;

    public function __construct()
    {
        $key = config('services.razorpay.key_id', env('RAZORPAY_KEY_ID'));
        $secret = config('services.razorpay.key_secret', env('RAZORPAY_KEY_SECRET'));

        if ($key && $secret) {
            $this->api = new Api($key, $secret);
        }
    }

    public function isConfigured(): bool
    {
        return $this->api !== null;
    }

    public function createOrder(array $params): array
    {
        $this->assertConfigured();

        try {
            $order = $this->api->order->create($params);

            return ['success' => true, 'order' => $order->toArray()];
        } catch (\Throwable $e) {
            Log::error('Razorpay order creation failed', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function verifyPaymentSignature(array $attributes): bool
    {
        $this->assertConfigured();

        try {
            $this->api->utility->verifyPaymentSignature($attributes);

            return true;
        } catch (\Throwable $e) {
            Log::error('Razorpay signature verification failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function fetchPayment(string $paymentId): ?array
    {
        $this->assertConfigured();

        try {
            return $this->api->payment->fetch($paymentId)->toArray();
        } catch (\Throwable $e) {
            Log::error('Razorpay payment fetch failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = config('services.razorpay.webhook_secret', env('RAZORPAY_WEBHOOK_SECRET'));

        if (! $secret) {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }

    public function refund(string $paymentId, float $amount): array
    {
        $this->assertConfigured();

        try {
            $refund = $this->api->payment->fetch($paymentId)->refund([
                'amount' => (int) round($amount * 100),
            ]);

            return ['success' => true, 'refund' => $refund->toArray()];
        } catch (\Throwable $e) {
            Log::error('Razorpay refund failed', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function assertConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('Razorpay is not configured. Add RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET to .env');
        }
    }
}
