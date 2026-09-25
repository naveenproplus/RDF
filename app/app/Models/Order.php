<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;


    protected $table = "tbl_order";
    protected $primaryKey = 'OrderID';
    public $incrementing = false;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'UpdatedOn';


    protected $appends = ['productCount', 'paymentStatus'];

    protected $fillable = [
        "OrderID",
        "OrderNo",
        "CustomerName",
        "MobileNo1",
        "Email",
        "Address",
        "State",
        "District",
        "City",
        "PostalCode",
        "CompleteAddress",
        "Status",
        "TrackStatus",
        "courierName",
        "courierTrackNo",
        "OrderDate",
        "ExpectedDelivery",
        "SubTotal",
        "DiscountType",
        "DiscountPer",
        "DiscountAmount",
        "ShippingCharge",
        "TotalAmount",
        "PaymentID",
        "BusySaleID",
        "DFlag",
        "CreatedOn",
        "CreatedBy",
        "UpdatedOn",
        "UpdatedBy",
        "DeletedOn",
        "DeletedBy",
    ];

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class,'OrderID','OrderID');
    }
    public function orderTrackDetails()
    {
        return $this->hasMany(CustomerOrderTrack::class, 'OrderID', 'OrderID');
    }
    public function getproductCountAttribute(){
        return OrderDetail::where('OrderID', $this->OrderID)->count();
    }
    public function getpaymentStatusAttribute(){
        return $this->PaymentID ? "Payment Completed" : "Payment Pending";
    }
}
