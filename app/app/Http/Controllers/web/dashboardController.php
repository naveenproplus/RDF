<?php

namespace App\Http\Controllers\web;

use App\helper\helper;
use App\Http\Controllers\Controller;
use App\Models\ServerSideProcess;
use Illuminate\Http\Request;
use DB;
use Auth;
use activeMenuNames;
use docTypes;
use general;
use Ledgers;
use SSP;
use Carbon\Carbon;

class dashboardController extends Controller
{
    private $general;
    private $support;
    private $DocNum;
    private $UserID;
    private $ActiveMenuName;
    private $PageTitle;
    private $CRUD;
    private $logs;
    private $Settings;
    private $FY;
    private $FYDBName;
    private $generalDB;
    private $Menus;

    public function __construct()
    {
        $this->ActiveMenuName = activeMenuNames::Dashboard->value;
        $this->PageTitle = "Dashboard";
        $this->middleware('auth');
        $this->generalDB = Helper::getGeneralDB();
        $this->middleware(function ($request, $next) {
            $this->UserID = auth()->user()->UserID;
            $this->general = new general($this->UserID, $this->ActiveMenuName);
            $this->Menus = $this->general->loadMenu();
            $this->CRUD = $this->general->getCrudOperations($this->ActiveMenuName);
            $this->FY = $this->general->UserInfo['FY'];
            $this->FYDBName = $this->FY->DBName != "" ? $this->FY->DBName . "." : "";
            $this->Settings = $this->general->getSettings();
            return $next($request);
        });
    }

    public function dashboard(Request $req)
    {
        //return $this->getOutstandings();
        $FormData = $this->general->UserInfo;
        $FormData['ActiveMenuName'] = $this->ActiveMenuName;
        $FormData['PageTitle'] = $this->PageTitle;
        $FormData['menus'] = $this->Menus;
        $FormData['crud'] = $this->CRUD;
        $FormData['DashboardType'] = auth()->user()->DashboardType;
        return view('app.dashboard', $FormData);
    }

    public function getDashboardStats()
    {
        $tmp = Ledgers::getLedger(["FYDBName" => $this->FYDBName]);
        $return = json_decode(
            json_encode(
                [
                    "customer" => [
                        "orderValues" => 0,
                        "received" => 0,
                        "outstanding" => 0,
                        "stats" => [
                            "active" => 0,
                            "inactive" => 0,
                            "deleted" => 0,
                            "total" => 0
                        ]
                    ],
                    "vendor" => [
                        "orderValues" => 0,
                        "paid" => 0,
                        "outstanding" => 0,
                        "stats" => [
                            "active" => 0,
                            "inactive" => 0,
                            "deleted" => 0,
                            "total" => 0
                        ]
                    ],
                    "employee" => [
                        "stats" => [
                            "active" => 0,
                            "inactive" => 0,
                            "deleted" => 0,
                            "total" => 0
                        ]
                    ]
                ]
            )
        );
        //Customer
        $sql = "SELECT LedgerType,IFNULL(SUM(IFNULL(debit,0)),0) as ordersValue,IFNULL(SUM(IFNULL(Credit,0)),0) as Received,IFNULL(SUM(IFNULL(debit,0)),0)- IFNULL(SUM(IFNULL(Credit,0)),0) as Balance FROM  " . $tmp->DBName . $tmp->TableName . " Where LedgerType='Customer' and PaymentType='Order' Group By LedgerType";
        $result = DB::SELECT($sql);
        if (count($result) > 0) {
            $return->customer->orderValues = Helper::shortenValue($result[0]->ordersValue);
            $return->customer->received = Helper::shortenValue($result[0]->Received);
            $return->customer->outstanding = Helper::shortenValue($result[0]->Balance);
        }
        //Vendor
        $sql = "SELECT LedgerType,IFNULL(SUM(IFNULL(Credit,0)),0) as ordersValue,IFNULL(SUM(IFNULL(Debit,0)),0) as paid,IFNULL(SUM(IFNULL(Credit,0)),0)- IFNULL(SUM(IFNULL(Debit,0)),0) as Balance FROM  " . $tmp->DBName . $tmp->TableName . " Where LedgerType='Vendor' and PaymentType='Order' Group By LedgerType";
        $result = DB::SELECT($sql);
        if (count($result) > 0) {
            $return->vendor->orderValues = Helper::shortenValue($result[0]->ordersValue);
            $return->vendor->paid = Helper::shortenValue($result[0]->paid);
            $return->vendor->outstanding = Helper::shortenValue($result[0]->Balance);
        }
        Ledgers::dropTable($tmp->TableName, $tmp->DBName);

        //vendor stats
        $return->vendor->stats->total = DB::Table('tbl_vendors')->count();
        $return->vendor->stats->deleted = DB::Table('tbl_vendors')->where('DFlag', 1)->count();
        $return->vendor->stats->inactive = DB::Table('tbl_vendors')->where('DFlag', 0)->where('ActiveStatus', 'Inactive')->count();
        $return->vendor->stats->active = DB::Table('tbl_vendors')->where('DFlag', 0)->where('ActiveStatus', 'Active')->count();

        //customer stats
        $return->customer->stats->total = DB::Table('tbl_customer')->count();
        $return->customer->stats->deleted = DB::Table('tbl_customer')->where('DFlag', 1)->count();
        $return->customer->stats->inactive = DB::Table('tbl_customer')->where('DFlag', 0)->where('ActiveStatus', 'Inactive')->count();
        $return->customer->stats->active = DB::Table('tbl_customer')->where('DFlag', 0)->where('ActiveStatus', 'Active')->count();

        //employees stats
        $return->employee->stats->total = DB::Table('users')->where('LoginType', 'Admin')->count();
        $return->employee->stats->deleted = DB::Table('users')->where('LoginType', 'Admin')->where('DFlag', 1)->count();
        $return->employee->stats->inactive = DB::Table('users')->where('LoginType', 'Admin')->where('DFlag', 0)->where('ActiveStatus', 'Inactive')->count();
        $return->employee->stats->active = DB::Table('users')->where('LoginType', 'Admin')->where('DFlag', 0)->where('ActiveStatus', 'Active')->count();
        return $return;
    }

    public function getRecentQuoteEnquiry(Request $request)
    {
        $columns = array(
            array('db' => 'EnqNo', 'dt' => '0'),
            array('db' => 'EnqDate', 'dt' => '1', 'formatter' => function ($d, $row) {
                return date($this->Settings['DATE-FORMAT'], strtotime($d));
            }),
            array('db' => 'ReceiverName', 'dt' => '2'),
            array('db' => 'ReceiverMobNo', 'dt' => '3'),
            array('db' => 'ExpectedDeliveryDate', 'dt' => '4', 'formatter' => function ($d, $row) {
                return date($this->Settings['DATE-FORMAT'], strtotime($d));
            }),
            array('db' => 'CustomerID', 'dt' => '5',
                'formatter' => function ($d, $row) {
                    return DB::table('tbl_customer')->where('CustomerID', $d)->value('Email');
                }
            ),
            array('db' => 'Status', 'dt' => '6',
                'formatter' => function ($d, $row) {
                    $html = "";
                    if ($d == "New") {
                        $html = "<span class='badge badge-info m-1'>" . $d . "</span>";
                    } elseif ($d == "Converted to Quotation") {
                        $html = "<span class='badge badge-secondary m-1'>" . $d . "</span>";
                    } elseif ($d == "Quote Requested") {
                        $html = "<span class='badge badge-primary m-1'>" . $d . "</span>";
                    } elseif ($d == "Accepted") {
                        $html = "<span class='badge badge-success m-1'>" . $d . "</span>";
                    }
                    return $html;
                }
            ),
            array(
                'db' => 'EnqID',
                'dt' => '7',
                'formatter' => function ($d, $row) {
                    $html = '<button type="button" data-id="' . $d . '" class="btn btn-outline-info ' . $this->general->UserInfo['Theme']['button-size'] . '  mr-10 btnView">View Enquiry</button>';
                    return $html;
                }
            )
        );//$this->FYDBName.'tbl_enquiry as e LEFT JOIN tbl_customer as C ON C.CustomerID = e.CustomerID LEFT JOIN '.$this->generalDB.'tbl_countries as CO On CO.CountryID=C.CountryID';
        $data = array();
        $data['POSTDATA'] = $request;
        $data['TABLE'] = $this->FYDBName . 'tbl_enquiry';
        $data['PRIMARYKEY'] = 'EnqID';
        $data['COLUMNS'] = $columns;
        $data['COLUMNS1'] = $columns;
        $data['GROUPBY'] = null;
        $data['WHERERESULT'] = null;
        $data['WHEREALL'] = "Status != 'Cancelled'";
        return SSP::SSP($data);
    }

    public function getRecentOrders(Request $request)
    {
        $ServerSideProcess = new ServerSideProcess();
        $columns = array(
            array('db' => 'O.OrderNo', 'dt' => '0'),
            array('db' => 'O.OrderDate', 'dt' => '1'),
            array('db' => 'O.CustomerName', 'dt' => '2',),
            array('db' => 'O.MobileNo1', 'dt' => '3'),
            array('db' => 'O.TotalAmount', 'dt' => '4'),
            array('db' => 'O.TrackStatus', 'dt' => '5'),
//            array('db' => 'O.PaymentID', 'dt' => '6'),
            array('db' => 'O.OrderID', 'dt' => '6')
        );
        $columns1 = array(
            array('db' => 'OrderNo', 'dt' => '0'),
            array('db' => 'OrderDate', 'dt' => '1', 'formatter' => function ($d, $row) {
                return date("d - M - Y", strtotime($d));
            }),
            array('db' => 'CustomerName', 'dt' => '2'),
//            array( 'db' => 'CreatedBy', 'dt' => '1','formatter' =>function($d,$row){
//                $customer = DB::Table("tbl_customer")->Where("CustomerID", $row['CreatedBy'])->first();
//                logger($customer);
//                $html= $customer->CustomerName ?? $customer->nick_name ?? $customer->MobileNo1;
//                return $html;
//            } ),
            array(
                'db' => 'MobileNo1', 'dt' => '3'),
            array('db' => 'TotalAmount', 'dt' => '4'),
            array('db' => 'TrackStatus',
                'dt' => '5',
                'formatter' => function ($d, $row) {
                    $Status = "<span class='badge block badge-secondary mr-2'> Pending </span>";
                    $result = DB::Table("tbl_order")->Where("OrderID", $row['OrderID'])->get();
                    if (count($result) > 0) {
                        if ($result[0]->DFlag == 1) {
                            $Status = "<span class='badge block badge-danger mr-2'> Deleted </span>";
                        } else {
                            if ($d == "Order Confirmed") {
                                $Status = "<span class='badge block badge-warning mr-2'> Order Confirmed </span>";
                            } elseif ($d == "Shipped") {
                                $Status = "<span class='badge block badge-secondary mr-2'> Shipped </span>";
                            } elseif ($d == "Delivered") {
                                $Status = "<span class='badge block badge-success mr-2'> Delivered </span>";
                            } else {
                                $Status = "<span class='badge block badge-info mr-2'> Payment Pending </span>";
                            }
                        }
                    }
                    return $Status;
                }),
//            array(
//                'db' => 'PaymentID',
//                'dt' => '6',
//                'formatter' => function ($d, $row) {
//                    if ($d == "") {
//                        return '<span class="badge badge-warning">Pending</span>';
//                    } else {
//                        return '<span class="badge badge-success">Completed</span>';
//                    }
//                }
//            ),
            array(
                'db' => 'OrderID',
                'dt' => '6',
                'formatter' => function ($d, $row) {
                    $html = '<a href="' . route('admin.order.edit', $d) . '"><button type="button" data-id="' . $d . '" class="btn  btn-outline-success ' . $this->general->UserInfo['Theme']['button-size'] . ' mr-10 btnEdit" data-original-title="Edit"><i class="fa ' . (($row['TrackStatus'] === "Order Confirmed") ? "fa-pencil" : "fa-eye") . '"></i></button></a>';
                    if ($row['TrackStatus']) {
                        $html .= '<a href="' . route('generatePackingLabel', $d) . '" target="_blank"><button type="button" data-id="' . $d . '" class="btn  btn-outline-success ' . $this->general->UserInfo['Theme']['button-size'] . ' mr-10" data-original-title="Packing Slip"><i class="fa fa-paper-plane"></i></button></a>';
                    }
                    return $html;
                }
            )
        );
        $Where = "O.PaymentID != ''";
        $data = array();
        $data['POSTDATA'] = $request;
        $data['TABLE'] = "tbl_order as O";
        $data['PRIMARYKEY'] = 'O.OrderID';
        $data['COLUMNS'] = $columns;
        $data['COLUMNS1'] = $columns1;
        $data['GROUPBY'] = null;
        $data['WHERERESULT'] = null;
        $data['WHEREALL'] = $Where;
        return $ServerSideProcess->SSP($data);
    }

    public function getOrderStats(request $req)
    {
        $FYFromDate = date("Y-m-d", strtotime($this->FY->FromDate));
        $FYToDate = date("Y-m-d", strtotime($this->FY->ToDate));
        $FromDate = date("Y-m-d", strtotime($this->FY->FromDate));
        $return = [];
        $return[] = ["Month", "Quote Enquiry", "Quotes", "Orders"];
        for ($i = 0; $i < 12; $i++) {
            $tmp = [];
            $FromDate = date("Y-m-d", strtotime($i . " month ", strtotime($FYFromDate)));
            $ToDate = date("Y-m-t", strtotime($FromDate));
            $tmp[] = date("M, Y", strtotime($FromDate));

            $tmp[] = DB::Table($this->FYDBName . 'tbl_enquiry')->where('EnqDate', '>=', $FromDate)->where('EnqDate', '<=', $ToDate)->count();
            $tmp[] = DB::Table($this->FYDBName . 'tbl_quotation')->where('QDate', '>=', $FromDate)->where('QDate', '<=', $ToDate)->count();
            $tmp[] = DB::Table($this->FYDBName . 'tbl_order')->where('OrderDate', '>=', $FromDate)->where('OrderDate', '<=', $ToDate)->count();
            $return[] = $tmp;
        }
        return $return;
    }

    public function getPaymentStats(request $req)
    {
        return 0;
        $FYFromDate = date("Y-m-d", strtotime($this->FY->FromDate));
        $FYToDate = date("Y-m-d", strtotime($this->FY->ToDate));
        $FromDate = date("Y-m-d", strtotime($this->FY->FromDate));
        $return = [];
        $tmp = [];
        $tmp[] = "Month";
        if ($this->Settings['enable-advance-receipts']) {
            $tmp[] = "Advance Receipts";
        }
        $tmp[] = "Receipts";
        if ($this->Settings['enable-advance-payments']) {
            $tmp[] = "Advance Payments";
        }
        $tmp[] = "Payments";
        $return[] = $tmp;//["Month", "Receipts", "Payments"]
        for ($i = 0; $i < 12; $i++) {
            $tmp = [];
            $FromDate = date("Y-m-d", strtotime($i . " month ", strtotime($FYFromDate)));
            $ToDate = date("Y-m-t", strtotime($FromDate));
            $tmp[] = date("M, Y", strtotime($FromDate));
            //Receipts
            $receipts = json_decode(json_encode(["AdvanceTotal" => 0, "OrderAmount" => 0, "ReceiptAmount" => 0]));
            $sql = "SELECT IFNULL(SUM(CASE WHEN PaymentType='Advance' THEN TotalAmount ELSE 0 END),0) as AdvanceTotal,IFNULL(SUM(CASE WHEN PaymentType='Order' THEN TotalAmount ELSE 0 END),0) as OrderAmount,IFNULL(SUM(TotalAmount),0) as ReceiptAmount FROM " . $this->FYDBName . "tbl_receipts Where TranDate>='" . date("Y-m-d", strtotime($FromDate)) . "' and TranDate<='" . date("Y-m-d", strtotime($ToDate)) . "'";
            $result = DB::SELECT($sql);
            if (count($result) > 0) {
                $receipts->AdvanceTotal = $result[0]->AdvanceTotal;
                $receipts->OrderAmount = $result[0]->OrderAmount;
                $receipts->ReceiptAmount = $result[0]->ReceiptAmount;
            }
            if ($this->Settings['enable-advance-receipts']) {
                $tmp[] = $receipts->AdvanceTotal;
            }
            $tmp[] = $receipts->OrderAmount;
            //payments
            $payments = json_decode(json_encode(["AdvanceTotal" => 0, "OrderAmount" => 0, "PaidAmount" => 0]));
            $sql = "SELECT IFNULL(SUM(CASE WHEN PaymentType='Advance' THEN TotalAmount ELSE 0 END),0) as AdvanceTotal,IFNULL(SUM(CASE WHEN PaymentType='Order' THEN TotalAmount ELSE 0 END),0) as OrderAmount,IFNULL(SUM(TotalAmount),0) as PaidAmount FROM " . $this->FYDBName . "tbl_payments Where TranDate>='" . date("Y-m-d", strtotime($FromDate)) . "' and TranDate<='" . date("Y-m-d", strtotime($ToDate)) . "'";
            $result = DB::SELECT($sql);
            if (count($result) > 0) {
                $payments->AdvanceTotal = $result[0]->AdvanceTotal;
                $payments->OrderAmount = $result[0]->OrderAmount;
                $payments->PaidAmount = $result[0]->PaidAmount;
            }
            if ($this->Settings['enable-advance-payments']) {
                $tmp[] = $payments->AdvanceTotal;
            }
            $tmp[] = $payments->PaidAmount;
            $return[] = $tmp;
        }
        return $return;
    }

    public function getUpcomingPayments(Request $req)
    {
        $tmpDB = Helper::getTmpDB();
        $dateTime = Carbon::parse($req->start);
        $req->start = $dateTime->format('Y-m-d');
        $dateTime = Carbon::parse($req->end);
        $req->end = $dateTime->format('Y-m-d');
        $return = [];
        $StartDate = date("Y-m-d", strtotime($req->start));
        $EndDate = date("Y-m-d", strtotime($req->end));
        $TableName = "tbl_upcoming_payments_" . Helper::RandomString(7);
        $sql = "CREATE TEMPORARY TABLE IF NOT EXISTS " . $tmpDB . $TableName . " (TranDate DATE, LedgerType ENUM('Customer', 'Vendor'), LedgerID VARCHAR(50), LedgerName VARCHAR(200), MobileNumber VARCHAR(20), DueDate DATE, Amount DOUBLE DEFAULT 0);";
        DB::Statement($sql);

        $result = DB::Table('tbl_financial_year')->get();

        foreach ($result as $data) {
            if (Helper::checkDBExists($data->DBName)) {
                if (Helper::checkTableExists($data->DBName, "tbl_order")) {
                    $sql = "Insert Into " . $tmpDB . $TableName . " (TranDate , LedgerType, LedgerID, LedgerName, MobileNumber, DueDate, Amount) ";
                    $sql .= " SELECT O.OrderDate, 'Customer' as LedgerType, O.CustomerID, C.CustomerName,C.MobileNo1, DATE_ADD(O.OrderDate, INTERVAL C.CreditDays DAY) AS DueDate,SUM(O.NetAmount)-SUM(O.TotalPaidAmount) as Amount";
                    $sql .= " FROM " . $data->DBName . ".tbl_order AS O LEFT JOIN tbl_customer AS C ON C.CustomerID = O.CustomerID WHERE PaymentStatus <> 'Paid'  and C.isEnableCreditLimit='Enabled'";
                    $sql .= " Group By O.OrderDate, O.CustomerID, C.CustomerName,C.MobileNo1,C.CreditDays,C.isEnableCreditLimit";
                    DB::Statement($sql);
                }
                if (Helper::checkTableExists($data->DBName, "tbl_vendor_orders")) {
                    $sql = "Insert Into " . $tmpDB . $TableName . " (TranDate , LedgerType, LedgerID, LedgerName, MobileNumber, DueDate, Amount) ";
                    $sql .= "SELECT O.OrderDate,'Vendor' as LedgerType, O.VendorID, IFNULL(V.VendorName,'Vendor') as VendorName,V.MobileNumber1, DATE_ADD(O.OrderDate, INTERVAL IFNULL(V.CreditDays,30) DAY) AS DueDate,SUM(O.NetAmount)-SUM(O.TotalPaidAmount) as Amount ";
                    $sql .= " FROM  " . $data->DBName . ".tbl_vendor_orders as O LEFT JOIN tbl_vendors as V ON V.VendorID=O.VendorID WHERE O.PaymentStatus <> 'Paid'  and V.isEnableCreditLimit='Enabled'";
                    $sql .= " Group By O.OrderDate, O.VendorID, V.VendorName,V.MobileNumber1,V.CreditDays,V.isEnableCreditLimit";
                    DB::Statement($sql);
                }
            }
        }
        $sql = "Select * From " . $tmpDB . $TableName . " Where Amount>0 and  DueDate>='" . date("Y-m-d", strtotime($StartDate)) . "' and DueDate<='" . date("Y-m-d", strtotime($EndDate)) . "'";
        $result = DB::SELECT($sql);
        foreach ($result as $data) {
            $title = $data->LedgerType == "Vendor" ? " To " : " From ";
            $color = $data->LedgerType == "Vendor" ? "#dc3545" : "#198754";
            $return[] = [
                "title" => $title . $data->LedgerName . " : Rs." . Helper::NumberFormat($data->Amount, $this->Settings['price-decimals']),
                "start" => date("Y-m-d", strtotime($data->DueDate)),
                "end" => date("Y-m-d", strtotime($data->DueDate)),
                "color" => $color
            ];
        }
        return $return;
    }

    public function getCustomerCircleStats(Request $req)
    {
        $currentDate = Carbon::now();

        $startDate = $currentDate->startOfWeek()->format('Y-m-d');

        $endDate = $currentDate->endOfWeek()->format('Y-m-d');
        $return = json_decode(
            json_encode(
                [
                    "lastMonth" => 0,
                    "today" => 0,
                    "thisWeek" => 0,
                    "thisMonth" => 0
                ]
            )
        );

        $return->lastMonth = DB::Table("tbl_customer")->where('CreatedOn', '>=', date("Y-m-01", strtotime('-1 Month')))->where('CreatedOn', '<=', date("Y-m-t", strtotime('-1 Month')))->where('DFlag', 0)->count();
        $return->today = DB::Table("tbl_customer")->where('CreatedOn', '=', date("Y-m-d"))->where('DFlag', 0)->count();
        $return->thisWeek = DB::Table("tbl_customer")->where('CreatedOn', '>=', date("Y-m-01", strtotime($startDate)))->where('CreatedOn', '<=', date("Y-m-t", strtotime($endDate)))->where('DFlag', 0)->count();
        $return->thisMonth = DB::Table("tbl_customer")->where('CreatedOn', '>=', date("Y-m-01"))->where('CreatedOn', '<=', date("Y-m-t"))->where('DFlag', 0)->count();
        $return->total = DB::Table("tbl_customer")->where('DFlag', 0)->count();

        $return->lastMonth = floatval($return->lastMonth) > 1000 ? Helper::shortenValue($return->lastMonth) : $return->lastMonth;
        $return->today = floatval($return->today) > 1000 ? Helper::shortenValue($return->today) : $return->today;
        $return->thisWeek = floatval($return->thisWeek) > 1000 ? Helper::shortenValue($return->thisWeek) : $return->thisWeek;
        $return->thisMonth = floatval($return->thisMonth) > 1000 ? Helper::shortenValue($return->thisMonth) : $return->thisMonth;
        $return->total = floatval($return->total) > 1000 ? Helper::shortenValue($return->total) : $return->total;
        return $return;
    }

    public function getOrdersCircleStats(Request $req)
    {
        $currentDate = Carbon::now();
        $startDate = $currentDate->startOfWeek()->format('Y-m-d');
        $endDate = $currentDate->endOfWeek()->format('Y-m-d');
        $return = json_decode(
            json_encode(
                [
                    "lastMonth" => 0,
                    "today" => 0,
                    "thisWeek" => 0,
                    "thisMonth" => 0
                ]
            )
        );
        $return->lastMonth = DB::Table("tbl_order")->where('OrderDate', '>=', date("Y-m-01", strtotime('-1 Month')))->where('OrderDate', '<=', date("Y-m-t", strtotime('-1 Month')))->count();
        $return->today = DB::Table("tbl_order")->where('OrderDate', '=', date("Y-m-d"))->count();
        $return->thisWeek = DB::Table("tbl_order")->where('OrderDate', '>=', date("Y-m-01", strtotime($startDate)))->where('OrderDate', '<=', date("Y-m-t", strtotime($endDate)))->count();
        $return->thisMonth = DB::Table("tbl_order")->where('OrderDate', '>=', date("Y-m-01"))->where('OrderDate', '<=', date("Y-m-t"))->count();

        $return->lastMonth = floatval($return->lastMonth) > 1000 ? Helper::shortenValue($return->lastMonth) : $return->lastMonth;
        $return->today = floatval($return->today) > 1000 ? Helper::shortenValue($return->today) : $return->today;
        $return->thisWeek = floatval($return->thisWeek) > 1000 ? Helper::shortenValue($return->thisWeek) : $return->thisWeek;
        $return->thisMonth = floatval($return->thisMonth) > 1000 ? Helper::shortenValue($return->thisMonth) : $return->thisMonth;

        return $return;
    }

    public function getShipmentCircleStats(Request $req)
    {
        $currentDate = Carbon::now();

        $startDate = $currentDate->startOfWeek()->format('Y-m-d');

        $endDate = $currentDate->endOfWeek()->format('Y-m-d');
        $return = (object)[
            "thisYear" => 0,
            "today" => 0,
            "thisWeek" => 0,
            "thisMonth" => 0
        ];

        $oneYearAgo = Carbon::now()->subYear()->startOfDay();
        $today = Carbon::now()->endOfDay();
        $return->thisYear = DB::table('tbl_order')->where('TrackStatus', 'Order Confirmed')->whereBetween('OrderDate', [$oneYearAgo, $today])->count();
        $return->today = DB::Table("tbl_order")->where('TrackStatus', 'Order Confirmed')->where('OrderDate', '=', date("Y-m-d"))->count();
        $return->thisWeek = DB::Table("tbl_order")->where('TrackStatus', 'Order Confirmed')->where('OrderDate', '>=', date("Y-m-01", strtotime($startDate)))->where('OrderDate', '<=', date("Y-m-t", strtotime($endDate)))->count();
        $return->thisMonth = DB::Table("tbl_order")->where('TrackStatus', 'Order Confirmed')->where('OrderDate', '>=', date("Y-m-01"))->where('OrderDate', '<=', date("Y-m-t"))->count();

        $return->thisYear = floatval($return->thisYear) > 1000 ? Helper::shortenValue($return->thisYear) : $return->thisYear;
        $return->today = floatval($return->today) > 1000 ? Helper::shortenValue($return->today) : $return->today;
        $return->thisWeek = floatval($return->thisWeek) > 1000 ? Helper::shortenValue($return->thisWeek) : $return->thisWeek;
        $return->thisMonth = floatval($return->thisMonth) > 1000 ? Helper::shortenValue($return->thisMonth) : $return->thisMonth;
        return $return;
    }
}
