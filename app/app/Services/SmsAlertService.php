<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsAlertService
{
    protected string $apiKey;
    protected string $sender;
    protected ?string $route;

    public function __construct()
    {
        $this->apiKey = config('app.SMS_ALERT_API_KEY', '');
        $this->sender = config('app.SMS_ALERT_SENDER_ID', '');
        $this->route = config('app.SMS_ALERT_ROUTE') ?: null;
    }

    public function sendOTP(string $mobileNumber, string $message): array
    {
        return $this->send($mobileNumber, $message);
    }

    /**
     * Shorten a long URL via SMS Alert (needed for DLT {#uro#} — long paths fail template match).
     */
    public function createShortUrl(string $longUrl): ?string
    {
        if ($this->apiKey === '' || $longUrl === '') {
            return null;
        }

        try {
            $response = Http::timeout(20)->get('https://www.smsalert.co.in/api/createshorturl.json', [
                'apikey' => $this->apiKey,
                'url' => $longUrl,
            ]);
            $data = $response->json();
            if (!is_array($data) || strtolower((string) ($data['status'] ?? '')) !== 'success') {
                Log::warning('SMS Alert short URL failed', ['body' => $response->body()]);
                return null;
            }

            $short = data_get($data, 'description.data.Link.short_url')
                ?: data_get($data, 'description.short_url')
                ?: data_get($data, 'short_url');

            if (!is_string($short) || $short === '') {
                return null;
            }

            // Prefer https for uro matching
            return preg_replace('#^http://#i', 'https://', $short);
        } catch (\Throwable $e) {
            Log::warning('SMS Alert short URL exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Send SMS via SMS Alert push API.
     * $templateId is accepted for call-site compatibility but not sent —
     * SMS Alert matches message text to templates registered in their dashboard.
     */
    public function send(string $mobileNumber, string $message, ?string $templateId = null): array
    {
        // $templateId unused — see note below about SMS Alert template matching.
        if ($this->apiKey === '' || $this->sender === '') {
            return [
                'status' => false,
                'message' => 'SMS Alert is not configured',
                'errors' => ['config' => 'SMS_ALERT_API_KEY and SMS_ALERT_SENDER_ID are required'],
            ];
        }

        $mobile = preg_replace('/\D/', '', $mobileNumber);
        if (str_starts_with($mobile, '91') && strlen($mobile) > 10) {
            $mobile = substr($mobile, 2);
        }

        $params = [
            'apikey' => $this->apiKey,
            'sender' => $this->sender,
            'mobileno' => $mobile,
            'text' => $message,
        ];

        if ($this->route) {
            $params['route'] = $this->route;
        }

        // Note: SMS Alert push.json matches content against templates in the
        // SMS Alert dashboard. TRAI DLT IDs (ORDER_SMS_TEMPLATE_ID) are not
        // sent as API params — the order template must be added in the portal.

        try {
            $response = Http::timeout(30)->get('https://www.smsalert.co.in/api/push.json', $params);
            $responseData = $response->json();

            if (!is_array($responseData)) {
                Log::warning('SMS Alert invalid response', ['body' => $response->body()]);
                return [
                    'status' => false,
                    'message' => 'SMS Send Failed',
                    'errors' => ['response' => $response->body()],
                ];
            }

            $status = strtolower((string) ($responseData['status'] ?? ''));
            if (in_array($status, ['success', 'ok'], true)) {
                return [
                    'status' => true,
                    'message' => 'SMS Sent Successfully',
                    'response' => $responseData,
                ];
            }

            return [
                'status' => false,
                'message' => $responseData['description'] ?? $responseData['message'] ?? 'SMS Send Failed',
                'errors' => $responseData,
            ];
        } catch (\Throwable $e) {
            Log::error('SMS Alert exception', ['message' => $e->getMessage()]);
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'errors' => ['exception' => $e->getMessage()],
            ];
        }
    }
}
