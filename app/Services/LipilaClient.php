<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class LipilaClient
{
    protected string $apiKey;

    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('services.lipila.key', env('LIPILA_API_KEY', ''));
        $this->baseUrl = rtrim((string) config('services.lipila.base_url', env('LIPILA_BASE_URL', 'https://api.lipila.tech')), '/');
    }

    /**
     * Create mobile money collection request.
     */
    public function createMobileCollection(
        string $referenceId,
        float $amount,
        string $accountNumber,
        string $narration = ''
    ): array {
        $order = Order::where('order_number', $referenceId)->first();

        // If sandbox key is placeholder or local testing mode, simulate the initiation
        if (empty($this->apiKey) || str_contains($this->apiKey, 'your_sandbox_key') || app()->environment('local')) {
            $payment = Payment::create([
                'order_id' => $order?->id,
                'provider' => 'lipila',
                'provider_reference' => 'SIM-'.strtoupper(bin2hex(random_bytes(6))),
                'method' => 'mobile_money',
                'amount' => $amount,
                'currency' => 'ZMW',
                'status' => 'initiated',
                'gateway_payload' => [
                    'reference_id' => $referenceId,
                    'account_number' => $accountNumber,
                    'amount' => $amount,
                    'narration' => $narration,
                    'is_simulated' => true,
                ],
            ]);

            return [
                'status' => 'initiated',
                'reference_id' => $referenceId,
                'provider_reference' => $payment->provider_reference,
            ];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(15)
                ->post("{$this->baseUrl}/v1/collections/mobile-money", [
                    'reference' => $referenceId,
                    'amount' => $amount,
                    'currency' => 'ZMW',
                    'account_number' => $accountNumber,
                    'narration' => $narration,
                ]);

            if (! $response->successful()) {
                Log::error('Lipila API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new RuntimeException('Payment gateway error: '.($response->json('message') ?? 'Unknown error'));
            }

            $data = $response->json();

            Payment::create([
                'order_id' => $order?->id,
                'provider' => 'lipila',
                'provider_reference' => $data['id'] ?? $data['reference'] ?? $referenceId,
                'method' => 'mobile_money',
                'amount' => $amount,
                'currency' => 'ZMW',
                'status' => 'initiated',
                'gateway_payload' => [
                    'account_number' => $accountNumber,
                    'narration' => $narration,
                ],
                'gateway_response' => $data,
            ]);

            return $data;
        } catch (\Exception $e) {
            Log::error('Lipila connection exception', ['error' => $e->getMessage()]);
            throw new RuntimeException('Unable to reach payment provider: '.$e->getMessage());
        }
    }
}
