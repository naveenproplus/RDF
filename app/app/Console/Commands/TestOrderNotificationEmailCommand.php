<?php

namespace App\Console\Commands;

use App\helper\helper;
use App\Mail\OrderMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestOrderNotificationEmailCommand extends Command
{
    protected $signature = 'order:test-notification-email
                            {--email= : Override receiver email (default: ORDER_NOTIFICATION_EMAIL from .env)}
                            {--order= : Use a real OrderID from DB instead of sample data}';

    protected $description = 'Send a test new-order notification email (sample data or real OrderID)';

    public function handle(): int
    {
        $to = $this->option('email') ?: config('app.ORDER_NOTIFICATION_EMAIL');

        if (empty($to)) {
            $this->error('No receiver email. Set ORDER_NOTIFICATION_EMAIL in .env or pass --email=you@example.com');
            return self::FAILURE;
        }

        $this->info('Mailer: ' . config('mail.default'));
        $this->info('Host: ' . config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port'));
        $this->info('From: ' . config('mail.from.address'));
        $this->info('To: ' . $to);
        $this->newLine();

        try {
            if ($this->option('order')) {
                $orderId = $this->option('order');
                $this->info("Using real order: {$orderId}");
                $sent = Helper::sendOrderNotificationEmail($orderId);
                if (!$sent) {
                    $this->error('Failed to send. Check storage/logs for details.');
                    return self::FAILURE;
                }
            } else {
                $this->info('Using sample order data (no DB write).');
                [$orderDetails, $logo, $companyDetails, $locationDetails] = $this->buildSampleMailData();

                Mail::to($to)->send(
                    new OrderMail('AdminNotification', $orderDetails, $companyDetails, $locationDetails, $logo)
                );
            }

            $this->newLine();
            $this->info('Test order notification email sent successfully.');
            $this->comment('If using Mailpit locally, open http://127.0.0.1:8025 to view it.');
            $this->comment('If using real SMTP, check inbox (and spam) for: ' . $to);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Mail send failed: ' . $e->getMessage());
            $this->line($e->getFile() . ':' . $e->getLine());
            logger($e);
            return self::FAILURE;
        }
    }

    private function buildSampleMailData(): array
    {
        $item1 = (object) [
            'ProductName' => 'SPLIT CASHEWS',
            'Qty' => 2,
            'SRate' => '₹ 110.00',
            'Amount' => '₹ 220.00',
            'productImageUrl' => config('app.url') . '/assets/images/no-image-b.png',
        ];
        $item2 = (object) [
            'ProductName' => 'ALMOND MAMRA',
            'Qty' => 1,
            'SRate' => '₹ 450.00',
            'Amount' => '₹ 450.00',
            'productImageUrl' => config('app.url') . '/assets/images/no-image-b.png',
        ];

        $orderDetails = (object) [
            'OrderID' => 'TEST-ORDER-' . date('YmdHis'),
            'CustomerName' => 'Test Customer',
            'Email' => 'customer.test@example.com',
            'OrderDate' => date('d/m/Y'),
            'PaymentID' => 'TESTPAY1234567890',
            'SubTotal' => '₹ 670.00',
            'ShippingCharge' => '₹ 40.00',
            'DiscountAmount' => '₹ 0.00',
            'TotalAmountInString' => '₹ 710.00',
            'Address' => '12 Sample Street, Suriyampalayam',
            'City' => 'Tiruchengodu',
            'District' => 'Namakkal',
            'State' => 'Tamil Nadu',
            'PostalCode' => '637211',
            'CompleteAddress' => '12 Sample Street, Suriyampalayam, Tiruchengodu, Namakkal, Tamil Nadu, 637211',
            'orderDetails' => collect([$item1, $item2]),
        ];

        $companyDetails = collect([
            'CompanyName' => 'Royal Dry Fruits',
            'Address' => 'Coimbatore',
            'E-Mail' => config('app.ORDER_NOTIFICATION_EMAIL', 'mail.royaldryfruits@gmail.com'),
            'Logo' => '',
        ]);

        // Prefer real company settings if available
        try {
            $dbCompany = collect(\Illuminate\Support\Facades\DB::table('tbl_company_settings')->pluck('KeyValue', 'KeyName'));
            if ($dbCompany->isNotEmpty()) {
                $companyDetails = $dbCompany;
            }
        } catch (\Throwable $e) {
            $this->warn('Could not load company settings from DB; using sample company details.');
        }

        if (empty($companyDetails['Logo'])) {
            $logo = config('app.url') . '/assets/images/no-image-b.png';
        } else {
            $logo = config('app.url') . '/' . $companyDetails['Logo'];
        }

        $locationDetails = (object) [
            'StateName' => 'Tamil Nadu',
            'CityName' => 'Coimbatore',
            'DistrictName' => 'Coimbatore',
            'PostalCode' => '641001',
        ];

        return [$orderDetails, $logo, $companyDetails, $locationDetails];
    }
}
