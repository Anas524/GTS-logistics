<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FedexTrackingService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $oauthUrl;
    protected string $trackUrl;
    protected ?string $accountNumber;

    public function __construct()
    {
        $cfg = config('services.fedex');

        $this->clientId      = $cfg['client_id'];
        $this->clientSecret  = $cfg['client_secret'];
        $this->oauthUrl      = $cfg['oauth_url'];
        $this->trackUrl      = $cfg['track_url'];
        $this->accountNumber = $cfg['account_number'] ?? null;
    }

    /**
     * Get OAuth token from FedEx (cached for ~50 minutes)
     */
    protected function getAccessToken(): string
    {
        return Cache::remember('fedex_access_token', 50 * 60, function () {
            $response = Http::asForm()->post($this->oauthUrl, [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if (! $response->successful()) {
                throw new \RuntimeException('FedEx OAuth failed: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * Track a shipment by tracking number
     */
    public function track(string $trackingNumber): array
    {
        // Local demo so you can test UI without real FedEx
        if ($trackingNumber === 'DEMO123') {
            return [
                'tracking_number'      => 'DEMO123',
                'status'               => 'In transit',
                'status_code'          => 'IT',
                'status_location'      => 'Dubai',
                'status_location_full' => 'Dubai, AE',
                'status_datetime'      => now()->toIso8601String(),
                'estimated_delivery'   => now()->addDays(2)->toDateString(),
                'shipper'              => 'GTS Logistics',
                'recipient'            => 'Demo Recipient',
                'events'               => [
                    [
                        'eventDescription' => 'Shipment picked up',
                        'dateTime'         => now()->subDays(1)->toIso8601String(),
                        'scanLocation'     => [
                            'city'        => 'Dubai',
                            'countryCode' => 'AE',
                        ],
                    ],
                ],
            ];
        }

        // Real FedEx call
        $token = $this->getAccessToken();

        $payload = [
            'trackingInfo' => [
                [
                    'trackingNumberInfo' => [
                        'trackingNumber' => $trackingNumber,
                    ],
                ],
            ],
            'includeDetailedScans' => true,
        ];

        $response = Http::withToken($token)
            ->acceptJson()
            ->post($this->trackUrl, $payload);

        if (! $response->successful()) {
            logger()->error('FedEx track error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new \RuntimeException('Unable to fetch tracking info from FedEx.');
        }

        return $this->normalizeResponse($response->json());
    }

    /**
     * Normalize FedEx response into simple fields for Blade.
     */
    protected function normalizeResponse($responseData = [])
    {
        // 👇 This line makes the linter happy
        $data = $responseData;

        $result = Arr::get($data, 'output.completeTrackResults.0', []);
        $track  = Arr::get($result, 'trackResults.0', []);

        return [
            'tracking_number'      => Arr::get($track, 'trackingNumber'),
            'status'               => Arr::get($track, 'latestStatusDetail.description'),
            'status_code'          => Arr::get($track, 'latestStatusDetail.code'),
            'status_location'      => Arr::get($track, 'latestStatusDetail.scanLocation.city'),
            'status_location_full' => trim(implode(', ', array_filter([
                Arr::get($track, 'latestStatusDetail.scanLocation.city'),
                Arr::get($track, 'latestStatusDetail.scanLocation.stateOrProvinceCode'),
                Arr::get($track, 'latestStatusDetail.scanLocation.countryCode'),
            ]))),
            'status_datetime'      => Arr::get($track, 'latestStatusDetail.derivedTimeStamp'),
            'estimated_delivery'   => Arr::get($track, 'estimatedDeliveryTimeStamp'),
            'shipper'              => Arr::get($track, 'shipperInformation.contact.personName'),
            'recipient'            => Arr::get($track, 'recipientInformation.contact.personName'),
            'events'               => Arr::get($track, 'scanEvents', []),
        ];
    }
}
