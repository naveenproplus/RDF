<?php

namespace App\Console\Commands;

use App\helper\helper;
use App\Mail\OrderMail;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TestOrderNotificationEmailCommand extends Command
{
    protected $signature = 'order:test-notification-email
                            {--email= : Override receiver email (default: ORDER_NOTIFICATION_EMAIL)}
                            {--order= : Use a real OrderID from DB (optional)}';

    protected $description = 'Send a test new-order notification email and print a clear success/failure response';

    public function handle(): int
    {
        $to = $this->option('email') ?: config('app.ORDER_NOTIFICATION_EMAIL');
        $orderId = $this->option('order');
        $started = microtime(true);

        $this->line('--- Mail config ---');
        $this->line('Mailer : ' . config('mail.default'));
        $this->line('Host   : ' . config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port'));
        $this->line('From   : ' . config('mail.from.address'));
        $this->line('To     : ' . ($to ?: '(missing)'));
        $this->line('Order  : ' . ($orderId ?: '(sample data)'));
        $this->newLine();

        if (empty($to)) {
            return $this->printResponse(false, 'ORDER_NOTIFICATION_EMAIL is empty. Set it in .env or pass --email=', $started);
        }

        try {
            if ($orderId) {
                $this->sendRealOrderMail($orderId, $to);
                $message = "Order notification email sent for {$orderId}";
            } else {
                [$orderDetails, $logo, $companyDetails, $locationDetails] = $this->buildSampleMailData();
                Mail::to($to)->send(
                    new OrderMail('AdminNotification', $orderDetails, $companyDetails, $locationDetails, $logo)
                );
                $message = 'Sample order notification email sent';
            }

            return $this->printResponse(true, $message, $started, ['to' => $to]);
        } catch (\Throwable $e) {
            logger($e);

            return $this->printResponse(false, $e->getMessage(), $started, [
                'to' => $to,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'exception' => class_basename($e),
            ]);
        }
    }

    private function printResponse(bool $ok, string $message, float $started, array $extra = []): int
    {
        $payload = array_merge([
            'status' => $ok ? 'success' : 'failed',
            'message' => $message,
            'ms' => (int) round((microtime(true) - $started) * 1000),
        ], $extra);

        $this->newLine();
        $this->line('=== RESPONSE ===');
        foreach ($payload as $key => $value) {
            $color = ($key === 'status') ? ($ok ? 'info' : 'error') : 'line';
            $this->{$color}($key . ': ' . (is_scalar($value) ? $value : json_encode($value)));
        }
        $this->line('===============');
        $this->newLine();
        $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $ok ? self::SUCCESS : self::FAILURE;
    }

    private function sendRealOrderMail(string $orderId, string $to): void
    {
        $generalDB = helper::getGeneralDB();
        $orders = Order::with('orderDetails')->where('OrderID', $orderId)->get();
        if ($orders->isEmpty()) {
            throw new \RuntimeException("Order not found: {$orderId}");
        }

        $orders->transform(function ($order) {
            if ($order->orderDetails) {
                $order->orderDetails->transform(function ($detail) {
                    $detail->PRate = helper::formatAmount($detail->PRate);
                    $detail->SRate = helper::formatAmount($detail->SRate);
                    $detail->Amount = helper::formatAmount($detail->Amount);
                    return $detail;
                });
            }
            $order->SubTotal = helper::formatAmount($order->SubTotal);
            $order->DiscountAmount = helper::formatAmount($order->DiscountAmount);
            $order->ShippingCharge = helper::formatAmount($order->ShippingCharge);
            $order->TotalAmountInString = helper::formatAmount($order->TotalAmount);
            $order->OrderDate = Carbon::parse($order->OrderDate)->format('d/m/Y');
            return $order;
        });
        $orderDetails = $orders[0];

        $companyDetails = collect(DB::table('tbl_company_settings')->pluck('KeyValue', 'KeyName'));
        $logo = empty($companyDetails['Logo'])
            ? config('app.url') . '/assets/images/no-image-b.png'
            : config('app.url') . '/' . $companyDetails['Logo'];

        $locationDetails = null;
        try {
            $locationDetails = DB::table($generalDB . 'tbl_states as S')
                ->join($generalDB . 'tbl_cities as CI', 'CI.StateID', '=', 'S.StateID')
                ->join($generalDB . 'tbl_districts as D', 'D.StateID', '=', 'S.StateID')
                ->join($generalDB . 'tbl_postalcodes as PC', 'PC.PID', '=', 'CI.PostalID')
                ->select('S.StateName', 'CI.CityName', 'D.DistrictName', 'PC.PostalCode')
                ->where('S.StateID', $companyDetails->get('StateID'))
                ->where('CI.CityID', $companyDetails->get('CityID'))
                ->where('D.DistrictID', $companyDetails->get('DistrictID'))
                ->where('PC.PID', $companyDetails->get('PostalCodeID'))
                ->first();
        } catch (\Throwable $e) {
            $this->warn('Location lookup skipped: ' . $e->getMessage());
        }

        // Temporarily override notify email for this test if --email was passed
        config(['app.ORDER_NOTIFICATION_EMAIL' => $to]);

        Mail::to($to)->send(
            new OrderMail('AdminNotification', $orderDetails, $companyDetails, $locationDetails, $logo)
        );
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
            'E-Mail' => config('app.ORDER_NOTIFICATION_EMAIL', 'naveenselva6005@gmail.com'),
            'Logo' => '',
        ]);

        try {
            $dbCompany = collect(DB::table('tbl_company_settings')->pluck('KeyValue', 'KeyName'));
            if ($dbCompany->isNotEmpty()) {
                $companyDetails = $dbCompany;
            }
        } catch (\Throwable $e) {
            $this->warn('Could not load company settings; using sample company details.');
        }

        $logo = empty($companyDetails['Logo'])
            ? config('app.url') . '/assets/images/no-image-b.png'
            : config('app.url') . '/' . $companyDetails['Logo'];

        $locationDetails = (object) [
            'StateName' => 'Tamil Nadu',
            'CityName' => 'Coimbatore',
            'DistrictName' => 'Coimbatore',
            'PostalCode' => '641001',
        ];

        return [$orderDetails, $logo, $companyDetails, $locationDetails];
    }
}
