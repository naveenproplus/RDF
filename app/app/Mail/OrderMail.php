<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $orderType;
    public $orderDetails;
    public $companyDetails;
    public $locationDetails;
    public $logo;

    public function __construct($orderType, $orderDetails, $companyDetails, $locationDetails, $logo)
    {
        $this->orderType = $orderType;
        $this->orderDetails = $orderDetails;
        $this->companyDetails = $companyDetails;
        $this->locationDetails = $locationDetails;
        $this->logo = $logo;
    }

    public function build()
    {
        $orderDetails = $this->orderDetails;
        $companyDetails = $this->companyDetails;
        $locationDetails = $this->locationDetails;
        $logo = $this->logo;
        $orderId = $orderDetails->OrderID ?? '';

        if ($this->orderType === "AdminNotification") {
            return $this->subject("New Order Received - {$orderId}")
                ->view('emails.order_confirmation', compact('orderDetails', 'companyDetails', 'locationDetails', 'logo'));
        }

        if ($this->orderType === "Confirmation") {
            return $this->subject("Your order placed successfully by Royal Dry Fruits")
                ->view('emails.order_confirmation', compact('orderDetails', 'companyDetails', 'locationDetails', 'logo'));
        }

        return $this->subject("Your order shipped successfully by Royal Dry Fruits")
            ->view('emails.order_shipment', compact('orderDetails', 'companyDetails', 'locationDetails', 'logo'));
    }
}
