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
     * Send SMS via SMS Alert push API.
     * Optional DLT template id is sent when configured.
     */
    public function send(string $mobileNumber, string $message, ?string $templateId = null): array
    {
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

        $templateId = $templateId ?: config('app.ORDER_SMS_TEMPLATE_ID');
        if (!empty($templateId)) {
            $params['template'] = $templateId;
        }

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
