<html lang="en"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f8dfb1;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #dddddd;
        }
        .header {
            background-color: #f8dfb1;
            text-align: center;
            padding: 10px 0;
        }
        .header img {
            width: 80px;
        }
        .content {
            padding: 20px;
            text-align: center;
        }
        .content h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.5;
            color: #333;
            margin: 5px 0;
        }
        .order-number {
            margin: 20px 0;
            font-weight: bold;
        }
        .order-details {
            text-align: left;
            padding: 0 20px;
        }
        .order-details h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .items {
            border-top: 1px solid #dddddd;
            border-bottom: 1px solid #dddddd;
            padding: 20px 0;
        }
        .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 0;
        }
        .item img {
            width: 80px;
            height: auto;
            border: 1px solid #dddddd;
        }
        .item-details {
            flex: 1;
            padding: 0 10px;
        }
        .item-quantity{
            width: 20px;
            text-align: right;
        }
        .item-price,
        .item-total {
            width: 100px;
            text-align: right;
        }
        .summary {
            margin: 20px 0;
            text-align: right;
            font-size: 16px;
        }
        .summary p {
            margin: 5px 0;
        }
        .info-section {
            margin: 20px 0;
            padding: 0 20px;
        }
        .info-section h4 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .info-section p {
            font-size: 16px;
            line-height: 1.5;
        }
        .footer {
            background-color: #f8dfb1;
            text-align: center;
            padding: 20px;
        }
        .footer p {
            font-size: 14px;
            margin: 5px 0;
        }
        .footer .social-icons img {
            width: 24px;
            height: 24px;
            margin: 0 5px;
        }
    </style>
</head>
<body class="activity-stream">
<div class="container">
    <div class="header"></div>
    <div class="content">
        <img src="{{ $logo }}" width="150">
{{--        {{ json_encode($companyDetails) }}--}}
        <h2>Order Confirmation</h2>
        <p>Hey {{ $orderDetails->CustomerName ?? '' }},</p>
        <p>We've got your order! Your world is about to look a whole lot better.</p>
        <p>We'll drop you another email when your order ships.</p>
        <p class="order-number">ORDER NO. {{ $orderDetails->OrderID ?? '' }}</p>
        <p>{{ $orderDetails->OrderDate ?? '' }}</p>
    </div>
    <div class="order-details">
        <h3>Items Ordered</h3>
        <div class="items">
            @foreach ($orderDetails->orderDetails as $product)
                <div class="item">
                    <img src="{{ $product->productImageUrl ?? '' }}" alt="{{ $product->ProductName ?? '-' }}">
                    <div class="item-details">
                        <p>{{ $product->ProductName ?? '-' }}</p>
                    </div>
                    <div class="item-quantity">{{ $product->Qty ?? '-' }}</div>
                    <div class="item-price">{{ $product->SRate ?? '-' }}</div>
                    <div class="item-total">
                        {{ $product->Amount ?? '-' }}
                    </div>
                </div>
            @endforeach
        </div>
        <div class="summary">
            <p>Subtotal: {{ $orderDetails->SubTotal ?? '' }}</p>
            <p>Shipping Charge : {{ $orderDetails->ShippingCharge ?? '' }}</p>
            <p>Discount  : {{ $orderDetails->DiscountAmount ?? '' }}</p>
            <p><strong>Total: {{ $orderDetails->TotalAmountInString }}</strong></p>
        </div>
    </div>
    <div class="info-section">
        <h4>Payment Info</h4>
        <p>PhonePe Payment ID:
            @php
                $paymentId = (string) ($orderDetails->PaymentID ?? '');
                $maskedPaymentId = strlen($paymentId) > 4
                    ? str_repeat('*', strlen($paymentId) - 4) . substr($paymentId, -4)
                    : ($paymentId !== '' ? $paymentId : '-');
            @endphp
            {{ $maskedPaymentId }}
        </p>
    </div>

{{--    <div class="info-section">--}}
{{--        <h4>Shipment Info</h4>--}}
{{--        <p>Courier Name : The Professional Courier</p>--}}
{{--        <p>Courier Track Number : TJK6756778678</p>--}}
{{--    </div>--}}
    <div class="info-section">
        <h4>Shipping Address</h4>
        <p>{{ $orderDetails->Address ?? '' }},<br>
            {{ $orderDetails->City ?? '' }}, {{ $orderDetails->District ?? '' }}<br>
            {{ $orderDetails->State ?? '' }} - {{ $orderDetails->PostalCode ?? '' }}</p>
    </div>
    <div class="info-section">
        @if(!empty($companyDetails['E-Mail']))
            <p>If you need help with anything please don't hesitate to drop us an email: <a href="mailto:{{ $companyDetails['E-Mail'] }}">{{ $companyDetails['E-Mail'] }}</a></p>
        @else
            <p>If you need help with anything please don't hesitate to contact us.</p>
        @endif

            <p>
                @if(!empty($companyDetails['CompanyName']))
                    {{ $companyDetails['CompanyName'] }}<br>
                @endif

                @if(!empty($companyDetails['Address']))
                    {{ $companyDetails['Address'] }},<br>
                @endif
                @if(!empty($locationDetails->CityName))
                    {{ $locationDetails->CityName }},
                @endif
                @if(!empty($locationDetails->DistrictName))
                    {{ $locationDetails->DistrictName }} -
                @endif
                @if(!empty($locationDetails->PostalCode))
                    {{ $locationDetails->PostalCode }}
                @endif
            </p>
        <p style="text-align: center !important;">Copyright © {{ \Carbon\Carbon::now()->format('Y') }}</p>
    </div>

    <div class="footer">
    </div>
</div>
</body></html>
