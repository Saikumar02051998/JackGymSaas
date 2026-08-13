<?php

namespace App\Services;

use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    protected ?Api $api = null;
    protected string $keyId = '';
    protected string $webhookSecret = '';

    public function __construct(
        ?string $keyId = null,
        ?string $keySecret = null,
        ?string $webhookSecret = null
    ) {
        $this->keyId = (string) ($keyId ?? gym_setting('razorpay_key_id') ?? '');
        $secret = (string) ($keySecret ?? gym_setting('razorpay_key_secret') ?? '');
        $this->webhookSecret = (string) ($webhookSecret ?? gym_setting('razorpay_webhook_secret') ?? '');

        if ($this->keyId !== '' && $secret !== '') {
            $this->api = new Api($this->keyId, $secret);
        }
    }

    public static function forGym(): static
    {
        return new static(
            (string) (gym_setting('razorpay_key_id') ?? ''),
            (string) (gym_setting('razorpay_key_secret') ?? ''),
            (string) (gym_setting('razorpay_webhook_secret') ?? ''),
        );
    }

    public static function forPlatform(): static
    {
        return new static(
            (string) (config('services.razorpay.key_id') ?? ''),
            (string) (config('services.razorpay.key_secret') ?? ''),
            (string) (config('services.razorpay.webhook_secret') ?? ''),
        );
    }

    public function isConfigured(): bool
    {
        return $this->api !== null;
    }

    public function keyId(): string
    {
        return $this->keyId;
    }

    public function webhookSecret(): string
    {
        return $this->webhookSecret;
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
        $secret = $this->webhookSecret;

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
