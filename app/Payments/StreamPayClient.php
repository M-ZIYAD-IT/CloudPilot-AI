<?php

namespace App\Payments;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin wrapper over Stream's payment gateway API (docs.streampay.sa).
 * Auth is a static base64(api_key:api_secret) token, not OAuth - Stream
 * doesn't document a separate sandbox host, so "dev" vs "live" is just
 * whichever key pair is in .env.
 */
final class StreamPayClient
{
    public function createPaymentLink(string $name, string $successUrl, string $failureUrl, array $metadata = []): array
    {
        $response = $this->client()->post('/payment_links', [
            'name' => $name,
            'items' => [
                ['product_id' => $this->productId(), 'quantity' => 1],
            ],
            'contact_information_type' => 'EMAIL',
            'currency' => config('services.streampay.currency', 'SAR'),
            'max_number_of_payments' => 1,
            'success_redirect_url' => $successUrl,
            'failure_redirect_url' => $failureUrl,
            'custom_metadata' => $metadata,
        ])->throw()->json();

        return [
            'id' => $response['id'],
            'url' => $response['url'],
        ];
    }

    public function getInvoice(string $invoiceId): array
    {
        return $this->client()->get("/invoices/{$invoiceId}")->throw()->json();
    }

    /**
     * Per docs.streampay.sa/webhooks: HMAC-SHA256 of "{timestamp}.{rawBody}"
     * using the webhook secret, compared against the "v1=" part of the
     * X-Webhook-Signature header (format: "t={timestamp},v1={signature}").
     */
    public function verifyWebhookSignature(string $rawBody, string $signatureHeader): bool
    {
        $secret = config('services.streampay.webhook_secret');

        if (blank($secret) || blank($signatureHeader)) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $segment) {
            [$key, $value] = array_pad(explode('=', $segment, 2), 2, null);
            $parts[$key] = $value;
        }

        if (empty($parts['t']) || empty($parts['v1'])) {
            return false;
        }

        $expected = hash_hmac('sha256', "{$parts['t']}.{$rawBody}", $secret);

        return hash_equals($expected, $parts['v1']);
    }

    private function client()
    {
        $apiKey = config('services.streampay.api_key');
        $apiSecret = config('services.streampay.api_secret');

        if (blank($apiKey) || blank($apiSecret)) {
            throw new RuntimeException('STREAMPAY_API_KEY/STREAMPAY_API_SECRET are not configured.');
        }

        $token = base64_encode("{$apiKey}:{$apiSecret}");

        return Http::baseUrl(config('services.streampay.base_url'))
            ->withHeaders(['x-api-key' => $token])
            ->timeout(15)
            ->asJson();
    }

    private function productId(): string
    {
        $productId = config('services.streampay.product_id');

        if (blank($productId)) {
            throw new RuntimeException('STREAMPAY_PRODUCT_ID is not configured - create a one-time "Full Report Unlock" product in the Stream dashboard first.');
        }

        return $productId;
    }
}
