<?php

namespace App\Console\Commands;

use App\helper\helper;
use Illuminate\Console\Command;

class TestOrderNotificationSmsCommand extends Command
{
    protected $signature = 'order:test-notification-sms
                            {--mobile= : Override receiver mobile (default: ORDER_NOTIFICATION_MOBILE)}
                            {--order= : Use a real OrderID from DB (required unless --dry-message)}
                            {--dry-message : Only print the SMS text; do not call SMS Alert}';

    protected $description = 'Send a test new-order notification SMS to ORDER_NOTIFICATION_MOBILE';

    public function handle(): int
    {
        $mobile = $this->option('mobile') ?: config('app.ORDER_NOTIFICATION_MOBILE');
        $apiKey = (string) config('app.SMS_ALERT_API_KEY');
        $sender = (string) config('app.SMS_ALERT_SENDER_ID');
        $templateId = (string) config('app.ORDER_SMS_TEMPLATE_ID');

        $this->info('Sender: ' . ($sender !== '' ? $sender : '(missing)'));
        $this->info('API key: ' . ($apiKey !== '' ? 'set (' . strlen($apiKey) . ' chars)' : '(missing)'));
        $this->info('Template: ' . ($templateId !== '' ? $templateId : '(none)'));
        $this->info('Mobile: ' . ($mobile !== '' ? $mobile : '(missing)'));
        $this->newLine();

        if ($mobile === '') {
            $this->error('No mobile. Set ORDER_NOTIFICATION_MOBILE in .env or pass --mobile=7200955220');
            return self::FAILURE;
        }

        if ($this->option('dry-message') && !$this->option('order')) {
            $orderId = 'O2026-TEST0001';
            $customerName = 'Test Customer';
            $orderNum = preg_replace('/\D/', '', $orderId);
            $orderUrl = rtrim((string) config('app.ORDER_SMS_VIEW_URL', 'https://app.royaldryfruits.biz/admin/ord'), '/');
            $message = "New order received from {$customerName}. Order ID: {$orderNum}. View order: {$orderUrl}. Royal Dry Fruits";
            $this->line($message);
            return self::SUCCESS;
        }

        $orderId = $this->option('order');
        if (!$orderId) {
            $this->error('Pass --order=ORDERID (e.g. --order=O2026-00000020) or use --dry-message');
            return self::FAILURE;
        }

        if ($this->option('dry-message')) {
            $order = \App\Models\Order::where('OrderID', $orderId)->first();
            if (!$order) {
                $this->error("Order not found: {$orderId}");
                return self::FAILURE;
            }
            $customerName = preg_replace('/[^A-Za-z\s]/', '', (string) ($order->CustomerName ?? 'Customer'));
            $customerName = trim(preg_replace('/\s+/', ' ', (string) $customerName)) ?: 'Customer';
            $orderNum = preg_replace('/\D/', '', $orderId) ?: '0';
            $orderUrl = rtrim((string) config('app.ORDER_SMS_VIEW_URL', 'https://app.royaldryfruits.biz/admin/ord'), '/');
            $message = "New order received from {$customerName}. Order ID: {$orderNum}. View order: {$orderUrl}. Royal Dry Fruits";
            $this->line($message);
            return self::SUCCESS;
        }

        if ($apiKey === '' || $sender === '') {
            $this->error('SMS Alert not configured. Set SMS_ALERT_API_KEY (or SMS_API_KEY) and SMS_ALERT_SENDER_ID.');
            return self::FAILURE;
        }

        // Temporarily override mobile for this test run if --mobile was passed
        if ($this->option('mobile')) {
            config(['app.ORDER_NOTIFICATION_MOBILE' => $mobile]);
        }

        $this->info("Sending order SMS for {$orderId} → {$mobile} ...");
        $ok = helper::sendOrderNotificationSms($orderId);

        if (!$ok) {
            $this->error('SMS failed. Check storage/logs/laravel-*.log for details.');
            return self::FAILURE;
        }

        $this->info('SMS sent successfully. Check phone ' . $mobile);
        return self::SUCCESS;
    }
}
