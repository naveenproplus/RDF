<?php

namespace App\Http\Controllers\api\customer;

use App\helper\helper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\web\logController;
use App\Jobs\SaleRegisterInBusyJob;
use App\Mail\OrderMail;
use App\Models\Coupon;
use App\Models\CustomerCart;
use App\Models\CustomerOrderTrack;
use App\Models\DeliveryChargeRule;
use App\Models\Language;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ReviewLike;
use App\Models\Translation;
use App\Models\Wishlist;
use App\Rules\ValidDB;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\enums\activeMenuNames;
use App\Models\DocNum;
use App\enums\docTypes;
use logs;

use function Laravel\Prompts\select;

class CustomerAuthController extends Controller{
    use ApiResponse;
    private $generalDB;
    private $tmpDB;
    private $ActiveMenuName;
    private $FileTypes;

    public function __construct()
    {
        $this->generalDB = Helper::getGeneralDB();
        $this->tmpDB = Helper::getTmpDB();
        $this->ActiveMenuName = activeMenuNames::ManageCustomers->value;
        $this->FileTypes = Helper::getFileTypes(array("category" => array("Images", "Documents")));
    }

    public function RegisteredDetails(request $req)
    {
        $Data = DB::Table('tbl_customer')->where('ActiveStatus', 'Active')->where('CustomerID', $this->ReferID)->where('DFlag', 0)
            ->select('CustomerID', 'CustomerName', 'DOB', 'MobileNo1', 'Email', 'CustomerImage', 'CusTypeID', 'ConTypeIDs', 'GenderID', 'Address', 'CityID', 'TalukID', 'DistrictID', 'StateID', 'CountryID', 'PostalCodeID')
            ->first();
        $CustomerImagePath = $Data->CustomerImage;
        $CustomerImageURL = file_exists($CustomerImagePath) ? url('/') . '/' . $Data->CustomerImage : url('/') . '/assets/images/no-image-b.png';
        $Data->CustomerImage = $CustomerImageURL;
        $Data->ConTypeIDs = unserialize($Data->ConTypeIDs);
        $return = [
            'status' => true,
            'data' => $Data,
        ];
        return response()->json($return);
    }

    public function getConstructionType(request $req){
		return [
			'status' => true,
			'data' => DB::Table('tbl_construction_type')->where('ActiveStatus','Active')->where('DFlag',0)
            ->select('ConTypeID', 'ConTypeName', DB::raw('IF(ConTypeLogo IS NOT NULL AND ConTypeLogo != "", CONCAT("' . url('/') . '/", ConTypeLogo), "") AS ConTypeLogo'))
            ->get(),
		];
	}

    public function getCustomerType()
    {
        return [
            'status' => true,
            'data' => DB::Table('tbl_customer_type')->where('ActiveStatus', 'Active')->where('DFlag', 0)->select('CusTypeID', 'CusTypeName')->get(),
        ];
    }

    public function GetCategory(Request $req)
    {
        $pageNo = $req->PageNo ?? 1;
        $perPage = 15;

        $Category = DB::table('tbl_product_category')->where('ActiveStatus', 'Active')->where('DFlag', 0);

        if ($req->has('SearchText') && !empty($req->SearchText)) {
            $Category->where('PCName', 'like', '%' . $req->SearchText . '%');
        }

        $result = $Category->select('PCName', 'PCID', DB::raw('CONCAT("' . url('/') . '/", COALESCE(NULLIF(PCImage, ""), "assets/images/no-image-b.png")) AS CategoryImage'))->paginate($perPage, ['*'], 'page', $pageNo);

        return response()->json([
            'status' => true,
            'data' => $result->items(),
            'CurrentPage' => $result->currentPage(),
            'LastPage' => $result->lastPage(),
        ]);
    }

    public function GetSubCategory(request $req)
    {
        $PCID = $req->PCID;
        $PCIDs = is_array($PCID) ? $PCID : [$PCID];

        $pageNo = $req->PageNo ?? 1;
        $perPage = 15;

        $SubCategory = DB::table('tbl_product_subcategory as PSC')->join('tbl_product_category as PC', 'PC.PCID', 'PSC.PCID')
            ->where('PSC.ActiveStatus', 'Active')->where('PSC.DFlag', 0)->where('PC.ActiveStatus', 'Active')->where('PC.DFlag', 0)->whereIn('PSC.PCID', $PCIDs);
        if ($req->has('SearchText') && !empty($req->SearchText)) {
            $SubCategory->where('PSC.PSCName', 'like', '%' . $req->SearchText . '%');
        }
        $result = $SubCategory->select('PSC.PSCName', 'PSC.PSCID', 'PC.PCID', 'PC.PCName', DB::raw('CONCAT("' . url('/') . '/", COALESCE(NULLIF(PSC.PSCImage, ""), "assets/images/no-image-b.png")) AS SubCategoryImage'))->paginate($perPage, ['*'], 'page', $pageNo);

        return response()->json([
            'status' => true,
            'data' => $result->items(),
            'CurrentPage' => $result->currentPage(),
            'LastPage' => $result->lastPage(),
        ]);
    }

    public function GetProducts(Request $req)
    {
        $PCID = $req->PCID;
        $PSCID = $req->PSCID;
        $PCIDs = is_array($PCID) ? $PCID : [$PCID];
        $PSCIDs = is_array($PSCID) ? $PSCID : [$PSCID];

        $pageNo = $req->PageNo ?? 1;
        $perPage = 15;

        $products = DB::table('tbl_products as P')->join('tbl_product_category as PC', 'PC.PCID', 'P.CID')->join('tbl_product_subcategory as PSC', 'PSC.PSCID', 'P.SCID')->join('tbl_uom as U', 'U.UID', 'P.UID')
            ->where('P.ActiveStatus', 'Active')->where('P.DFlag', 0)->where('PC.ActiveStatus', 'Active')->where('PC.DFlag', 0)->where('PSC.ActiveStatus', 'Active')->where('PSC.DFlag', 0)
            ->whereIn('P.CID', $PCIDs)->whereIn('P.SCID', $PSCIDs);

        if ($req->has('SearchText') && !empty($req->SearchText)) {
            $search = $req->SearchText;
            $products->where(function ($q) use ($search) {
                $q->where('P.ProductName', 'like', '%' . $search . '%')
                    ->orWhere('P.ProductNameInTranslation', 'like', '%' . $search . '%');
            });
        }

        $products = $products->select('P.ProductName', 'P.ProductID', 'PC.PCName', 'PC.PCID', 'PSC.PSCName', 'PSC.PSCID', 'U.UName', 'U.UCode', 'U.UID', DB::raw('CONCAT("' . url('/') . '/", COALESCE(NULLIF(P.ProductImage, ""), "assets/images/no-image-b.png")) AS ProductImage'))
            ->paginate($perPage, ['*'], 'page', $pageNo);

        foreach ($products as $row) {
            $row->GalleryImages = DB::table('tbl_products_gallery')
                ->where('ProductID', $row->ProductID)
                ->pluck(DB::raw('CONCAT("' . url('/') . '/", COALESCE(NULLIF(gImage, ""), "assets/images/no-image-b.png")) AS gImage'))
                ->toArray();
        }

        return response()->json([
            'status' => true,
            'data' => $products->items(),
            'CurrentPage' => $products->currentPage(),
            'LastPage' => $products->lastPage(),
        ]);
    }

    public function getCart(Request $req)
    {
        $lang = optional($req->auth_customer)->language ?? 'en';
        $customer = $req->auth_customer;
        $coupon_code = $req->coupon_code;
        $coupon_value = 0;
        $subTotalAmount = 0;
        $grandTotalAmount = 0;
        $shipping_charge = 0;
        $Cart = DB::table('tbl_customer_cart as C')
            ->leftJoin('tbl_products_variation as PV', 'PV.VariationID', 'C.ProductVariationID')
            ->join('tbl_products as P', 'P.ProductID', '=', 'C.ProductID')
            ->join('tbl_product_category_type as PCT', 'PCT.PCTID', '=', 'P.CTID')
            ->join('tbl_product_category as PC', 'PC.PCID', '=', 'P.CID')
            ->join('tbl_product_subcategory as PSC', 'PSC.PSCID', '=', 'P.SCID')
            ->join('tbl_uom as U', 'U.UID', '=', 'P.UID')
            ->where('C.CustomerID', $customer->CustomerID)
            ->where('P.ActiveStatus', 'Active')
            ->where('P.DFlag', 0)
            ->where('PC.ActiveStatus', 'Active')
            ->where('PC.DFlag', 0)
            ->where('PSC.ActiveStatus', 'Active')
            ->where('PSC.DFlag', 0)
            ->select(
                'P.ProductName',
                'P.ProductNameInTranslation',
                'P.ProductID',
                'P.PRate as PRate',
                'P.SRate as SRate',
                'PV.VariationID as product_variation_id',
                'C.Qty',
                'PCT.PCTName',
                'PCT.PCTNameInTranslation',
                'PCT.PCTID',
                'PC.PCName',
                'PC.PCNameInTranslation',
                'PC.PCID',
                'PSC.PSCName',
                'PSC.PSCNameInTranslation',
                'U.UName',
                'PSC.PSCID',
                'PV.PRate as variation_PRate',
                'PV.SRate as variation_SRate',
                'P.ProductImage'
            )
            ->get();

        foreach ($Cart as $item) {
            if ($item->product_variation_id) {
                $item->PRate = $item->variation_PRate;
                $item->SRate = $item->variation_SRate;
                $productUnit = DB::table('tbl_products_variation')
                    ->where('tbl_products_variation.ProductID', $item->ProductID)
                    ->where('tbl_products_variation.VariationID', $item->product_variation_id)
                    ->leftJoin('tbl_products_variation_details as D', function ($join) {
                        $join->on('tbl_products_variation.VariationID', '=', 'D.VariationID');
                    })
                    ->leftJoin('tbl_attributes_details as AD', function ($join) {
                        $join->on('AD.ValueID', '=', 'D.AttributeValueID')
                            ->on('AD.AttrID', '=', 'D.AttributeID');
                    })
                    ->leftJoin('tbl_attributes as A', 'A.AttrID', '=', 'AD.AttrID')
                    ->select('AD.Values', 'AD.valuesInTranslation')
                    ->first();
            } else {
                $productUnit = DB::table('tbl_products')
                    ->where('tbl_products.ProductID', $item->ProductID)
                    ->leftJoin('tbl_uom as U', 'U.UID', '=', 'tbl_products.UID')
                    ->select('U.UName', 'U.UNameInTranslation')
                    ->first();
            }

            if (isset($productUnit->valuesInTranslation)) {
                $valuesInTranslation = json_decode($productUnit->valuesInTranslation, true);
                if (isset($valuesInTranslation[$lang])) {
                    $productUnit = $valuesInTranslation[$lang];
                } else {
                    $productUnit = $productUnit->Values ?? $productUnit->UName ?? '-';
                }
            } elseif (isset($productUnit->UNameInTranslation)) {
                $UNameInTranslation = json_decode($productUnit->UNameInTranslation, true);
                if (isset($UNameInTranslation[$lang])) {
                    $productUnit = $UNameInTranslation[$lang];
                } else {
                    $productUnit = $productUnit->UName ?? '-';
                }
            } else {
                $productUnit = $productUnit->UName ?? '-';
            }

            $item->unit = $productUnit;
            $item->product_name = json_decode($item->ProductNameInTranslation)->$lang ?? $item->ProductName;
            $item->PCTName = json_decode($item->PCTNameInTranslation)->$lang ?? $item->PCTName;
            $item->PCName = json_decode($item->PCNameInTranslation)->$lang ?? $item->PCName;
            $item->PSCName = json_decode($item->PSCNameInTranslation)->$lang ?? $item->PSCName;
            $item->ProductImage = Helper::productImageFileExists($item->ProductImage) ? url($item->ProductImage) : url("assets/images/no-image-b.png");
            $product_rate = $item->SRate * $item->Qty;
            $item->PTotalRate = Helper::formatAmount($product_rate);
            $item->PRate = Helper::formatAmount($item->PRate);
            $item->SRate = Helper::formatAmount($item->SRate);
            $subTotalAmount += $product_rate;
            unset($item->variation_PRate, $item->variation_SRate, $item->ProductName, $item->PCTNameInTranslation, $item->PCNameInTranslation,
                $item->PSCNameInTranslation, $item->ProductNameInTranslation, $item->UNameInTranslation, $item->UName);
        }

        if ($coupon_code) {
            $coupon = Coupon::where('coupon_code', $coupon_code)
                ->where('DFlag', 0)->where('ActiveStatus', 'Active')
                ->first(['COID', 'type', 'value']);
            if ($coupon->type === 'Percentage') {
                $coupon_value = ($subTotalAmount / 100) * $coupon->value;
            } else {
                $coupon_value = $coupon->value;
            }
        }

        $SAddress = DB::table('tbl_customer_address as CA')->where('CA.CustomerID', $customer->CustomerID)
            ->where('CA.DFlag', 0)->where('CA.isDefault', 1)
            ->leftJoin($this->generalDB . 'tbl_postalcodes as PC', 'PC.PID', 'CA.PostalCodeID')
            ->leftJoin($this->generalDB . 'tbl_cities as CI', 'CI.CityID', 'CA.CityID')
            ->leftJoin($this->generalDB . 'tbl_districts as D', 'D.DistrictID', 'PC.DistrictID')
            ->leftJoin($this->generalDB . 'tbl_states as S', 'S.StateID', 'D.StateID')
            ->leftJoin($this->generalDB . 'tbl_countries as C', 'C.CountryID', 'S.CountryID')
            ->orderBy('CA.CreatedOn', 'desc')
            ->select('CA.AID', 'CA.ReceiverName', 'CA.ReceiverEmail', 'CA.ReceiverMobile', 'CA.Address', 'CA.isDefault', 'CA.StateID',
                'S.StateName', 'S.StateNameInTranslation', 'CA.DistrictID', 'D.DistrictName', 'D.DistrictNameInTranslation', 'CA.CityID',
                'CI.CityName', 'CI.CityNameInTranslation', 'CA.PostalCodeID', 'PC.PostalCode', 'CA.CompleteAddress', 'CA.AddressType')
            ->first();

        if ($subTotalAmount > 0) {
            $free_shipping_above_rate = round(DB::table('tbl_settings')->where('KeyName', 'free_delivery_in_coimbatore_for_above')->pluck('KeyValue')->first() ?? 0, 2);
            if (isset($SAddress->DistrictName) && $SAddress->DistrictName === "Coimbatore" && $subTotalAmount > $free_shipping_above_rate) {
                $shipping_charge = 0;
            } else {
                $deliveryChargeRule = DeliveryChargeRule::where('min_order_value', '<=', $subTotalAmount)
                    ->where('max_order_value', '>=', $subTotalAmount)
                    ->first();

                if ($deliveryChargeRule) {
                    $shipping_charge = round($deliveryChargeRule->delivery_charge ?? 0, 2);
                } else {
                    $shipping_charge = round(DB::table('tbl_settings')->where('KeyName', 'delivery_charge')->pluck('KeyValue')->first() ?? 0, 2);
                }
            }
        }

        if ($coupon_value > $subTotalAmount) {
            $coupon_value = $subTotalAmount;
        }
        $grandTotalAmount = round(($subTotalAmount + $shipping_charge) - $coupon_value, 2);

        if ($SAddress) {
            $SAddress->StateName = json_decode($SAddress->StateNameInTranslation)->$lang ?? Helper::translate($SAddress->StateName, $lang);
            $SAddress->DistrictName = json_decode($SAddress->DistrictNameInTranslation)->$lang ?? Helper::translate($SAddress->DistrictName, $lang);
            $SAddress->CityName = json_decode($SAddress->CityNameInTranslation)->$lang ?? Helper::translate($SAddress->CityName, $lang);
            $SAddress->AddressType = Helper::translate($SAddress->AddressType, $lang);
            $SAddress->CompleteAddress = Helper::translate($SAddress->CompleteAddress, $lang);
            unset($SAddress->StateNameInTranslation, $SAddress->DistrictNameInTranslation, $SAddress->CityNameInTranslation);
        }
        return response()->json(['status' => true, 'sub_total_amount' => Helper::formatAmount($subTotalAmount),
            'coupon_value' => Helper::formatAmount($coupon_value), 'shipping_charge' => Helper::formatAmount($shipping_charge),
            'grand_total_amount' => Helper::formatAmount($grandTotalAmount), 'total_product_count' => $Cart->count(),
            'shipping_address' => $SAddress, 'data' => $Cart]);
    }

    public function AddCart(Request $request)
    {
        $customer = $request->auth_customer;
        DB::beginTransaction();
        try {
            $validatedData = Validator::make($request->all(), [
                'ProductID' => 'required|string|exists:tbl_products,ProductID',
                'ProductVariationID' => 'nullable|string|exists:tbl_products_variation,VariationID',
                'Qty' => 'nullable|integer|min:1',
            ]);

            if ($validatedData->fails()) {
                return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
            }

            $ProductID = $request->ProductID;
            $ProductVariationID = $request->ProductVariationID ?: null;
            $CustomerID = $customer->CustomerID;
            $Qty = (int) ($request->Qty ?? 1);

            $cart = CustomerCart::firstOrNew([
                'CustomerID' => $CustomerID,
                'ProductID' => $ProductID,
                'ProductVariationID' => $ProductVariationID,
            ]);

            $isNew = !$cart->exists;
            $cart->Qty = $Qty;
            $cart->save();

            DB::commit();
            if ($isNew) {
                return $this->successResponse([], "Product added to Cart Successfully");
            }
            return $this->successResponse([], "Product quantity updated Successfully");
        } catch (Exception $e) {
            logger($e);
            DB::rollback();
            return response()->json(['status' => false, 'message' => "Product add to Cart Failed!"]);
        }
    }

    public function UpdateCart(Request $request)
    {
        $customer = $request->auth_customer;
        DB::beginTransaction();
        try {
            $validatedData = Validator::make($request->all(), [
                'Qty' => 'required|integer',
                'ProductID' => 'required|string|exists:tbl_products,ProductID',
                'ProductVariationID' => 'nullable|string|exists:tbl_products_variation,VariationID',
            ]);

            if ($validatedData->fails()) {
                return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
            }

            $Qty = $request->Qty;
            $ProductID = $request->ProductID;
            $ProductVariationID = $request->ProductVariationID;
            $CustomerID = $customer->CustomerID;

            $cart = CustomerCart::where('CustomerID', $CustomerID)
                ->where('ProductID', $ProductID);
            if (isset($ProductVariationID)) {
                $updated = $cart->where('ProductVariationID', $ProductVariationID)->update(["Qty" => $Qty]);
            } else {
                $updated = $cart->where('ProductVariationID', null)->update(["Qty" => $Qty]);
            }
            DB::commit();
            if ($updated) {
                return $this->successResponse([], "Product Update Successfully");
            } else {
                return $this->errorResponse([], "Product not exists!", 422);
            }
        } catch (Exception $e) {
            logger($e);
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => "Product Update Failed!",
            ]);
        }
    }

    public function DeleteCart(Request $request): JsonResponse
    {
        $customer = $request->auth_customer;
        DB::beginTransaction();
        try {
            $validatedData = Validator::make($request->all(), [
                'ProductID' => 'required|string|exists:tbl_products,ProductID',
                'ProductVariationID' => 'nullable|string|exists:tbl_products_variation,VariationID',
            ]);

            if ($validatedData->fails()) {
                return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
            }

            $ProductID = $request->ProductID;
            $ProductVariationID = $request->ProductVariationID;
            $CustomerID = $customer->CustomerID;

            $cart = CustomerCart::where('CustomerID', $CustomerID)
                ->where('ProductID', $ProductID);
            if (isset($ProductVariationID)){
                $deleted = $cart->where('ProductVariationID', $ProductVariationID)->delete();
            } else {
                $deleted = $cart->where('ProductVariationID', null)->delete();
            }
            DB::commit();
            if ($deleted) {
                return $this->successResponse([], "Product Deleted Successfully");
            } else {
                return $this->errorResponse([], "This Product was not listed in your cart", 422);
            }
        } catch (Exception $e) {
            logger($e);
            DB::rollBack();
            return $this->errorResponse($e, "Product Deleted Failed!", 422);
        }
    }

    public function getSAddress(Request $req)
    {
        $lang = optional($req->auth_customer)->language ?? 'en';
        $customer = $req->auth_customer;
        $CustomerID = $customer->CustomerID;
        $SAddress = DB::table('tbl_customer_address as CA')->where('CA.CustomerID', $CustomerID)->where('CA.DFlag', 0)
            ->leftJoin($this->generalDB . 'tbl_postalcodes as PC', 'PC.PID', 'CA.PostalCodeID')
            ->leftJoin($this->generalDB . 'tbl_cities as CI', 'CI.CityID', 'CA.CityID')
            ->leftJoin($this->generalDB . 'tbl_districts as D', 'D.DistrictID', 'PC.DistrictID')
            ->leftJoin($this->generalDB . 'tbl_states as S', 'S.StateID', 'D.StateID')
            ->leftJoin($this->generalDB . 'tbl_countries as C', 'C.CountryID', 'S.CountryID')
            ->orderBy('CA.CreatedOn', 'desc')
            ->select('CA.AID', 'CA.ReceiverName', 'CA.ReceiverEmail', 'CA.ReceiverMobile', 'CA.Address', 'CA.isDefault',
                'CA.StateID', 'S.StateName', 'S.StateNameInTranslation', 'CA.DistrictID', 'D.DistrictName', 'D.DistrictNameInTranslation',
                'CA.CityID', 'CI.CityName', 'CI.CityNameInTranslation', 'CA.PostalCodeID', 'PC.PostalCode', 'CA.CompleteAddress', 'CA.AddressType')
            ->get();

        $SAddress->transform(function ($address) use ($lang) {
            $address->CityName = json_decode($address->CityNameInTranslation)->{$lang} ?? $address->CityName;
            $address->StateName = json_decode($address->StateNameInTranslation)->{$lang} ?? $address->StateName;
            $address->DistrictName = json_decode($address->DistrictNameInTranslation)->{$lang} ?? $address->DistrictName;
            $address->AddressType = Helper::translate($address->AddressType, $lang);
            $address->Address = Helper::translate($address->Address, $lang);
            $address->CompleteAddress = Helper::translate($address->CompleteAddress, $lang);
            unset($address->CityNameInTranslation, $address->StateNameInTranslation, $address->DistrictNameInTranslation);
            return $address;
        });
        return response()->json(['status' => true, 'data' => $SAddress]);
    }

    public function createSAddress(Request $req)
    {
        $customer = $req->auth_customer;
        $CustomerID = $customer->CustomerID;
        $OldData = $NewData = [];
        $OldData = DB::table('tbl_customer_address')->where('CustomerID', $CustomerID)->get();
        $status = false;
        try {
            $ValidDB = [];
            $ValidDB['City']['TABLE'] = $this->generalDB . "tbl_cities";
            $ValidDB['City']['ErrMsg'] = "City name does  not exist";
            $ValidDB['City']['WHERE'][] = array("COLUMN" => "CityID", "CONDITION" => "=", "VALUE" => $req->CityID);
            $ValidDB['City']['WHERE'][] = array("COLUMN" => "StateID", "CONDITION" => "=", "VALUE" => $req->StateID);

            //States
            $ValidDB['State']['TABLE'] = $this->generalDB . "tbl_states";
            $ValidDB['State']['ErrMsg'] = "State name does  not exist";
            $ValidDB['State']['WHERE'][] = array("COLUMN" => "StateID", "CONDITION" => "=", "VALUE" => $req->StateID);

            //District
            $ValidDB['District']['TABLE'] = $this->generalDB . "tbl_districts";
            $ValidDB['District']['ErrMsg'] = "District name does  not exist";
            $ValidDB['District']['WHERE'][] = array("COLUMN" => "DistrictID", "CONDITION" => "=", "VALUE" => $req->DistrictID);

            //Postal Code
            $ValidDB['PostalCode']['TABLE'] = $this->generalDB . "tbl_postalcodes";
            $ValidDB['PostalCode']['ErrMsg'] = "Postal Code  does not exist";
            $ValidDB['PostalCode']['WHERE'][] = array("COLUMN" => "PID", "CONDITION" => "=", "VALUE" => $req->PostalCodeID);
            $ValidDB['PostalCode']['WHERE'][] = array("COLUMN" => "StateID", "CONDITION" => "=", "VALUE" => $req->StateID);

            $validatedData = Validator::make($req->all(), [
                'ReceiverName' => 'required|string',
                'ReceiverEmail' => 'required|email',
                'ReceiverMobile' => 'required|string',
                'AddressType' => 'required|string',
                'PostalCodeID' => ['required', new ValidDB($ValidDB['PostalCode'])],
                'CityID' => ['required', new ValidDB($ValidDB['City'])],
                'StateID' => ['required', new ValidDB($ValidDB['State'])],
                'DistrictID' => ['required', new ValidDB($ValidDB['District'])]
            ]);
            if ($validatedData->fails()) {
                return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
            }

            DB::beginTransaction();
            $AID = DocNum::getDocNum(docTypes::CustomerAddress->value, "", Helper::getCurrentFY());
            $completeAddress = $this->getCompleteAddress($req);

            $data = array(
                "AID" => $AID,
                "CustomerID" => $CustomerID,
                "ReceiverName" => $req->ReceiverName,
                "ReceiverEmail" => $req->ReceiverEmail,
                "ReceiverMobile" => $req->ReceiverMobile,
                "CompleteAddress" => $completeAddress,
                "Address" => $req->Address,
                "AddressType" => $req->AddressType,
                "PostalCodeID" => $req->PostalCodeID,
                "CityID" => $req->CityID,
                "DistrictID" => $req->DistrictID,
                "StateID" => $req->StateID,
                "isDefault" => 1,
                "CreatedOn" => date("Y-m-d H:i:s")
            );
            $status = DB::Table('tbl_customer_address')->insert($data);
            if ($status == true) {
                DB::Table('tbl_customer_address')->where('CustomerID', $CustomerID)->whereNot('AID', $AID)->update(['isDefault' => 0]);
                DocNum::updateDocNum(docTypes::CustomerAddress->value);
            }
            DB::commit();
            $NewData = DB::table('tbl_customer_address')->where('CustomerID', $CustomerID)->get();
            $logData = array("Description" => "Shipping Address Created", "ModuleName" => "Customer", "Action" => "Update", "ReferID" => $AID, "OldData" => $OldData, "NewData" => $NewData, "UserID" => $CustomerID, "IP" => $req->ip());
            logs::Store($logData);
            return response()->json(['status' => true, 'message' => "Shipping Address Created Successfully"]);
        } catch (Exception $e) {
            logger($e);
            DB::rollback();
            return response()->json(['status' => false, 'message' => "Shipping Address Creation Failed"]);
        }
    }

    public function updateSAddress(Request $req)
    {
        $customer = $req->auth_customer;
        $CustomerID = $customer->CustomerID;
        $OldData = DB::table('tbl_customer_address')->where('CustomerID', $CustomerID)->get();
        try {

            $ValidDB = [];
            $ValidDB['City']['TABLE'] = $this->generalDB . "tbl_cities";
            $ValidDB['City']['ErrMsg'] = "City name does  not exist";
            $ValidDB['City']['WHERE'][] = array("COLUMN" => "CityID", "CONDITION" => "=", "VALUE" => $req->CityID);
            $ValidDB['City']['WHERE'][] = array("COLUMN" => "StateID", "CONDITION" => "=", "VALUE" => $req->StateID);

            //States
            $ValidDB['State']['TABLE'] = $this->generalDB . "tbl_states";
            $ValidDB['State']['ErrMsg'] = "State name does  not exist";
            $ValidDB['State']['WHERE'][] = array("COLUMN" => "StateID", "CONDITION" => "=", "VALUE" => $req->StateID);

            //District
            $ValidDB['District']['TABLE'] = $this->generalDB . "tbl_districts";
            $ValidDB['District']['ErrMsg'] = "District name does  not exist";
            $ValidDB['District']['WHERE'][] = array("COLUMN" => "DistrictID", "CONDITION" => "=", "VALUE" => $req->DistrictID);

            //Postal Code
            $ValidDB['PostalCode']['TABLE'] = $this->generalDB . "tbl_postalcodes";
            $ValidDB['PostalCode']['ErrMsg'] = "Postal Code  does not exist";
            $ValidDB['PostalCode']['WHERE'][] = array("COLUMN" => "PID", "CONDITION" => "=", "VALUE" => $req->PostalCodeID);
            $ValidDB['PostalCode']['WHERE'][] = array("COLUMN" => "StateID", "CONDITION" => "=", "VALUE" => $req->StateID);

            $validatedData = Validator::make($req->all(), [
                'AID' => 'required|exists:tbl_customer_address,AID',
                'ReceiverName' => 'required|string',
                'ReceiverEmail' => 'required|email',
                'ReceiverMobile' => 'required|string',
                'AddressType' => 'required|string',
                'PostalCodeID' => ['required', new ValidDB($ValidDB['PostalCode'])],
                'CityID' => ['required', new ValidDB($ValidDB['City'])],
                'DistrictID' => ['required', new ValidDB($ValidDB['District'])],
                'StateID' => ['required', new ValidDB($ValidDB['State'])]
            ]);

            if ($validatedData->fails()) {
                return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
            }

            DB::beginTransaction();
            $completeAddress = $this->getCompleteAddress($req);

            $data = array(
                "CustomerID" => $CustomerID,
                "ReceiverName" => $req->ReceiverName,
                "ReceiverEmail" => $req->ReceiverEmail,
                "ReceiverMobile" => $req->ReceiverMobile,
                "CompleteAddress" => $completeAddress,
                "Address" => $req->Address,
                "AddressType" => $req->AddressType,
                "PostalCodeID" => $req->PostalCodeID,
                "CityID" => $req->CityID,
                "DistrictID" => $req->DistrictID,
                "StateID" => $req->StateID,
                "isDefault" => 1,
                "CreatedOn" => date("Y-m-d H:i:s")
            );
            DB::Table('tbl_customer_address')->where('CustomerID', $CustomerID)->where('AID', $req->AID)->update($data);
            DB::Table('tbl_customer_address')->where('CustomerID', $CustomerID)->whereNot('AID', $req->AID)->update(['isDefault' => 0]);
            DB::commit();
            $NewData = DB::table('tbl_customer_address')->where('CustomerID', $CustomerID)->get();
            $logData = array("Description" => "Shipping Address updated", "ModuleName" => "Customer", "Action" => "Update", "ReferID" => $req->AID, "OldData" => $OldData, "NewData" => $NewData, "UserID" => $CustomerID, "IP" => $req->ip());
            logs::Store($logData);
            return response()->json(['status' => true, 'message' => "Shipping Address Updated Successfully"]);
        } catch (Exception $e) {
            logger($e);
            DB::rollback();
            return response()->json(['status' => false, 'message' => "Shipping Address Updation Failed"]);
        }
    }
    public function SetDefault(Request $req){
        $customer = $req->auth_customer;
        $CustomerID = $customer->CustomerID;
        DB::beginTransaction();
        try {
            $validatedData = Validator::make($req->all(), [
                'AID' => [
                    'required',
                    Rule::exists('tbl_customer_address')->where(function ($query) {
                        $query->where('DFlag', 0);
                    }),
                ],
            ]);

            if ($validatedData->fails()) {
                return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
            }
            DB::Table('tbl_customer_address')->where('CustomerID',$CustomerID)->whereNot('AID',$req->AID)->update(['isDefault' =>0]);
            DB::Table('tbl_customer_address')->where('CustomerID',$CustomerID)->where('AID',$req->AID)->update(['isDefault' =>1,'UpdatedBy'=>$CustomerID,'UpdatedOn'=>date("Y-m-d H:i:s")]);
            DB::commit();
            return response()->json(['status' => true,'message' => "Default Address Set Successfully"]);
        }catch(Exception $e) {
            logger($e);
            DB::rollback();
            return response()->json(['status' => false,'message' => "Default Address Set Failed!"]);
        }
    }

    public function DeleteSAddress(Request $req)
    {
        $customer = $req->auth_customer;
        $CustomerID = $customer->CustomerID;
        DB::beginTransaction();
        $status = false;
        try {
            $isDefault = DB::Table('tbl_customer_address')->where('CustomerID', $CustomerID)->where('AID', $req->AID)->where('isDefault', 1)->exists();
            if ($isDefault) {
                return response()->json(['status' => false, 'message' => "Default Address cannot be deleted!"]);
            } else {
                $status = DB::Table('tbl_customer_address')->where('CustomerID', $CustomerID)->where('AID', $req->AID)->update(['DFlag' => 1, 'UpdatedBy' => $CustomerID, 'UpdatedOn' => date('Y-m-d H:i:s')]);
            }
        } catch (Exception $e) {
            $status = false;
        }
        if ($status == true) {
            DB::commit();
            return response()->json(['status' => true, 'message' => "Shipping Address Deleted Successfully"]);
        } else {
            DB::rollback();
            return response()->json(['status' => false, 'message' => "Shipping Address Deleted Failed!"]);
        }
    }

//    Notifications
    public function getNotifications(Request $req)
    {
        $lang = optional($req->auth_customer)->language ?? 'en';
        $language = Language::with('translations')->where('code', $lang)->first();
        $translation = $language->translations ? json_decode($language->translations->value) : new \stdClass();
        $customer = $req->auth_customer;
        $CustomerID = $customer->CustomerID;
        $pageNo = $req->PageNo ?? 1;
        $perPage = 10;

        $Notifications = DB::Table('tbl_notifications as N')->leftJoin('tbl_order as O', 'O.OrderID', 'N.RouteID')
            ->where('N.CustomerID', $CustomerID)
            ->orderBy('N.CreatedOn', 'desc')
            ->select('N.*')
//            ->select('N.*','O.isCustomerRated')
            ->paginate($perPage, ['*'], 'page', $pageNo);

        $Notifications->transform(function ($Notification) use ($translation, $lang) {
            $Notification->Route = $translation->{$Notification->Route} ?? Helper::translate($Notification->Route, $lang);
            $Notification->Title = $translation->{$Notification->Title} ?? Helper::translate($Notification->Title, $lang);
            $Notification->Message = $translation->{$Notification->Message} ?? Helper::translate($Notification->Message, $lang);
            return $Notification;
        });

        return response()->json([
            'status' => true,
            'data' => $Notifications->items(),
            'CurrentPage' => $Notifications->currentPage(),
            'LastPage' => $Notifications->lastPage(),
        ]);
    }

    public function getNotificationsCount(Request $req)
    {
        $customer = $req->auth_customer;
        $CustomerID = $customer->CustomerID;
        $NotificationsCount = DB::table('tbl_notifications')->where('CustomerID', $CustomerID)
            ->where('ReadStatus', 0)->count();
        return response()->json([
            'status' => true,
            'data' => $NotificationsCount,
        ]);
    }

    public function NotificationRead(Request $req)
    {
        $customer = $req->auth_customer;
        $CustomerID = $customer->CustomerID;
        DB::beginTransaction();
        try {
            $validatedData = Validator::make($req->all(), [
                'NID' => 'required|exists:tbl_notifications,NID'
            ]);
            if ($validatedData->fails()) {
                return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
            }
            DB::Table('tbl_notifications')->where('CustomerID', $CustomerID)
                ->where('NID', $req->NID)->update(['ReadStatus' => 1, 'ReadOn' => date('Y-m-d H:i:s')]);
            DB::commit();
            return response()->json(['status' => true, 'message' => "Notification Read Successfully!"]);
        } catch (Exception $e) {
            logger($e);
            DB::rollback();
            return response()->json(['status' => false, 'message' => "Notification Read Failed!"]);
        }
    }

    public function cartOrder(Request $request)
    {
        DB::beginTransaction();
        try {
            $lang = optional($request->auth_customer)->language ?? 'en';
            $customer = $request->auth_customer;
            $coupon_code = $request->coupon_code;
            $CustomerID = $customer->CustomerID;
            $DiscountPer = Helper::formatAmount(0);
            $coupon_value = 0;
            $subTotalAmount = 0;
            $grandTotalAmount = 0;
            $shipping_charge = 0;

            $Cart = DB::table('tbl_customer_cart as C')
                ->leftJoin('tbl_products_variation as PV', 'PV.VariationID', 'C.ProductVariationID')
                ->join('tbl_products as P', 'P.ProductID', '=', 'C.ProductID')
                ->join('tbl_tax as T', 'T.TaxID', '=', 'P.TaxID')
                ->join('tbl_product_category_type as PCT', 'PCT.PCTID', '=', 'P.CTID')
                ->join('tbl_product_category as PC', 'PC.PCID', '=', 'P.CID')
                ->join('tbl_product_subcategory as PSC', 'PSC.PSCID', '=', 'P.SCID')
                ->join('tbl_uom as U', 'U.UID', '=', 'P.UID')
                ->where('C.CustomerID', $CustomerID)
                ->where('P.ActiveStatus', 'Active')
                ->where('P.DFlag', 0)
                ->where('PC.ActiveStatus', 'Active')
                ->where('PC.DFlag', 0)
                ->where('PSC.ActiveStatus', 'Active')
                ->where('PSC.DFlag', 0)
                ->select(
                    'P.ProductName',
                    'P.ProductNameInTranslation',
                    'P.ProductID',
                    'P.SKU',
                    'P.PRate as PRate',
                    'P.SRate as SRate',
                    'PV.VariationID as product_variation_id',
                    'C.Qty',
                    'PCT.PCTName',
                    'PCT.PCTID',
                    'PC.PCName',
                    'PC.PCID',
                    'PSC.PSCName',
                    'U.UName',
                    'U.UCode',
                    'U.UID',
                    'PSC.PSCID',
                    'T.TaxPercentage',
                    'PV.SKU as variation_SKU',
                    'PV.PRate as variation_PRate',
                    'PV.SRate as variation_SRate'
                )
                ->get();

            foreach ($Cart as $item) {
                if ($item->product_variation_id) {
                    $item->SKU = $item->variation_SKU;
                    $item->PRate = $item->variation_PRate;
                    $item->SRate = $item->variation_SRate;
                }
                $item->PRate = round($item->PRate, 2);
                $item->SRate = round($item->SRate, 2);
                $product_rate = $item->SRate * $item->Qty;
                $item->TaxAmount = round(($product_rate * $item->TaxPercentage) / (100 + $item->TaxPercentage), 2);
                $item->PTotalRate = round($product_rate, 2);
                $item->PTotalRateExcludeTax = round($product_rate - $item->TaxAmount, 2);
                $subTotalAmount += $product_rate;
                unset($item->variation_SKU, $item->variation_PRate, $item->variation_SRate);
            }

            if ($coupon_code) {
                $coupon = Coupon::where('coupon_code', $coupon_code)->where('DFlag', 0)->where('ActiveStatus', 'Active')
                    ->first(['COID', 'type', 'value']);
                if ($coupon) {
                    if ($coupon->type === 'Percentage') {
                        $coupon_value = round(($subTotalAmount / 100) * $coupon->value, 2);
                    } else {
                        $coupon_value = $coupon->value;
                    }
                    if ($coupon_value > $subTotalAmount) {
                        $coupon_value = round($subTotalAmount, 2);
                    }
                    if ($coupon->type === 'Percentage') {
                        $DiscountPer = $coupon->value . "%";
                    } else {
                        $DiscountPer = Helper::formatAmount($coupon_value);
                    }
                }
            }

            $SAddress = DB::table('tbl_customer_address as CA')->where('CA.CustomerID', $customer->CustomerID)
                ->where('CA.DFlag', 0)->where('CA.isDefault', 1)
                ->leftJoin($this->generalDB . 'tbl_postalcodes as PC', 'PC.PID', 'CA.PostalCodeID')
                ->leftJoin($this->generalDB . 'tbl_cities as CI', 'CI.CityID', 'CA.CityID')
                ->leftJoin($this->generalDB . 'tbl_districts as D', 'D.DistrictID', 'PC.DistrictID')
                ->leftJoin($this->generalDB . 'tbl_states as S', 'S.StateID', 'D.StateID')
                ->leftJoin($this->generalDB . 'tbl_countries as C', 'C.CountryID', 'S.CountryID')
                ->orderBy('CA.CreatedOn', 'desc')
                ->select('CA.AID', 'CA.ReceiverName', 'CA.ReceiverEmail', 'CA.ReceiverMobile', 'CA.Address', 'CA.isDefault', 'CA.StateID',
                    'S.StateName', 'S.StateNameInTranslation', 'CA.DistrictID', 'D.DistrictName', 'D.DistrictNameInTranslation', 'CA.CityID',
                    'CI.CityName', 'CI.CityNameInTranslation', 'CA.PostalCodeID', 'PC.PostalCode', 'CA.CompleteAddress', 'CA.AddressType')
                ->first();

            if ($subTotalAmount > 0) {
                $free_shipping_above_rate = round(DB::table('tbl_settings')->where('KeyName', 'free_delivery_in_coimbatore_for_above')->pluck('KeyValue')->first() ?? 0, 2);
                if (isset($SAddress->DistrictName) && $SAddress->DistrictName === "Coimbatore" && $subTotalAmount > $free_shipping_above_rate) {
                    $shipping_charge = 0;
                } else {
                    $deliveryChargeRule = DeliveryChargeRule::where('min_order_value', '<=', $subTotalAmount)
                        ->where('max_order_value', '>=', $subTotalAmount)
                        ->first();

                    if ($deliveryChargeRule) {
                        $shipping_charge = round($deliveryChargeRule->delivery_charge ?? 0, 2);
                    } else {
                        $shipping_charge = round(DB::table('tbl_settings')->where('KeyName', 'delivery_charge')->pluck('KeyValue')->first() ?? 0, 2);
                    }
                }
            }
            $grandTotalAmount = round(($subTotalAmount + $shipping_charge) - $coupon_value, 2);

            $SAddress = DB::table('tbl_customer_address as CA')->where('CA.CustomerID', $customer->CustomerID)
                ->where('CA.DFlag', 0)->where('CA.isDefault', 1)
                ->leftJoin($this->generalDB . 'tbl_postalcodes as PC', 'PC.PID', 'CA.PostalCodeID')
                ->leftJoin($this->generalDB . 'tbl_cities as CI', 'CI.CityID', 'CA.CityID')
                ->leftJoin($this->generalDB . 'tbl_districts as D', 'D.DistrictID', 'PC.DistrictID')
                ->leftJoin($this->generalDB . 'tbl_states as S', 'S.StateID', 'D.StateID')
                ->leftJoin($this->generalDB . 'tbl_countries as C', 'C.CountryID', 'S.CountryID')
                ->orderBy('CA.CreatedOn', 'desc')
                ->select('CA.AID', 'CA.ReceiverName', 'CA.ReceiverEmail', 'CA.ReceiverMobile', 'CA.Address', 'CA.isDefault', 'CA.StateID',
                    'S.StateName', 'S.StateNameInTranslation', 'CA.DistrictID', 'D.DistrictName', 'D.DistrictNameInTranslation', 'CA.CityID',
                    'CI.CityName', 'CI.CityNameInTranslation', 'CA.PostalCodeID', 'PC.PostalCode', 'CA.CompleteAddress', 'CA.AddressType')
                ->first();

            if ($SAddress && ($grandTotalAmount > 0)) {
                $OrderID = DocNum::getDocNum(docTypes::Order->value);
                $data = [
                    "OrderID" => $OrderID,
                    "OrderNo" => sprintf("%02d%06d", date('y'), Order::count() + 1),
                    "CustomerName" => $SAddress->ReceiverName,
                    "MobileNo1" => $SAddress->ReceiverMobile,
                    "Email" => $SAddress->ReceiverEmail,
                    "Address" => $SAddress->Address,
                    "State" => $SAddress->StateName,
                    "District" => $SAddress->DistrictName,
                    "City" => $SAddress->CityName,
                    "PostalCode" => $SAddress->PostalCode,
                    "CompleteAddress" => $SAddress->CompleteAddress,
                    "OrderDate" => date('Y-m-d'),
                    "SubTotal" => round($subTotalAmount, 2),
                    "DiscountType" => isset($coupon) ? $coupon->type . " Coupon" : "",
                    "DiscountPer" => $DiscountPer,
                    "DiscountAmount" => $coupon_value,
                    "ShippingCharge" => $shipping_charge,
                    "TotalAmount" => $grandTotalAmount,
                    "CreatedBy" => $CustomerID,
                ];
                $status = Order::create($data);
                if ($status) {
                    foreach ($Cart as $item) {
                        $OrderDetailID = DocNum::getDocNum(docTypes::OrderDetails->value);
                        $data1 = [
                            'DetailID' => $OrderDetailID,
                            'OrderID' => $OrderID,
                            'CTID' => $item->PCTID,
                            'CID' => $item->PCID,
                            'SCID' => $item->PSCID,
                            'ProductID' => $item->ProductID,
                            'ProductName' => $item->ProductName,
                            'ProductNameInTranslation' => $item->ProductNameInTranslation,
                            'SKU' => $item->SKU,
                            'ProductVariationID' => $item->product_variation_id,
                            'Qty' => $item->Qty,
                            'PRate' => $item->PRate,
                            'SRate' => $item->SRate,
                            'TaxPercentage' => $item->TaxPercentage,
                            'TaxAmount' => $item->TaxAmount,
                            'UOMID' => $item->UID,
                            'Amount' => $item->PTotalRate,
                            'AmountExcludeTax' => $item->PTotalRateExcludeTax,
                            'CreatedOn' => date('Y-m-d'),
                            'CreatedBy' => $CustomerID,
                        ];
                        $status1 = OrderDetail::create($data1);
                        if ($status1) {
                            DocNum::updateDocNum(docTypes::OrderDetails->value);
                        }
                    }
                    DocNum::updateDocNum(docTypes::Order->value);
                }
                CustomerCart::where('CustomerID', $CustomerID)->delete();
                DB::commit();
                $orderDetail = Order::find($status->OrderID);
                $orderDetail->CustomerName = Helper::translate($orderDetail->CustomerName, $lang);
                $orderDetail->CompleteAddress = Helper::translate($orderDetail->CompleteAddress, $lang);
                $orderDetail->Status = Helper::translate($orderDetail->Status, $lang);
                $orderDetail->DiscountType = Helper::translate($orderDetail->DiscountType, $lang);
                $orderDetail->TrackStatus = Helper::translate($orderDetail->TrackStatus, $lang);
                $orderDetail->State = json_decode($SAddress->StateNameInTranslation)->$lang ?? Helper::translate($SAddress->StateName, $lang);
                $orderDetail->District = json_decode($SAddress->DistrictNameInTranslation)->$lang ?? Helper::translate($SAddress->DistrictName, $lang);
                $orderDetail->City = json_decode($SAddress->CityNameInTranslation)->$lang ?? Helper::translate($SAddress->CityName, $lang);
                $orderDetail->DiscountAmount = Helper::formatAmount($orderDetail->DiscountAmount);
                $orderDetail->TotalAmount = Helper::formatAmount($orderDetail->TotalAmount);
                $orderDetail->ShippingCharge = Helper::formatAmount($orderDetail->ShippingCharge);
                $orderDetail->SubTotal = Helper::formatAmount($orderDetail->SubTotal);
                $orderDetail->PaymentStatus = Helper::translate($orderDetail->PaymentStatus, $lang);
                return $this->successResponse($orderDetail, "Cart Order Created Successfully!");
            } else {
                return $this->errorResponse([], "Cart is Empty, Can't create Order", 422);
            }
        } catch (Exception $e) {
            logger($e);
            DB::rollback();
            return $this->errorResponse($e, "Cart Order Creation Failed!", 422);
        }
    }

    public function buyNowOrderPreview(Request $request)
    {
        DB::beginTransaction();
        try {
            $lang = optional($request->auth_customer)->language ?? 'en';
            $customer = $request->auth_customer;
            $coupon_code = $request->coupon_code;
            $CustomerID = $customer->CustomerID;
            $DiscountPer = Helper::formatAmount(0);
            $coupon_value = 0;
            $subTotalAmount = 0;
            $grandTotalAmount = 0;
            $shipping_charge = 0;

            $validatedData = Validator::make($request->all(), [
                'ProductID' => 'required|string|exists:tbl_products,ProductID',
                'ProductVariationID' => 'nullable|string|exists:tbl_products_variation,VariationID',
                'Qty' => 'required|integer'
            ]);

            if ($validatedData->fails()) {
                return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
            }

            $ProductID = $request->ProductID;
            $ProductVariationID = $request->ProductVariationID;
            $Qty = $request->Qty ?? 1;
            DB::table('tbl_temp_customer_cart')->where('CustomerID', $CustomerID)->delete();
            DB::table('tbl_temp_customer_cart')->insert(['CustomerID' => $CustomerID, 'ProductID' => $ProductID,
                'ProductVariationID' => $ProductVariationID, 'Qty' => $Qty]);

            $Cart = DB::table('tbl_temp_customer_cart as C')
                ->leftJoin('tbl_products_variation as PV', 'PV.VariationID', 'C.ProductVariationID')
                ->join('tbl_products as P', 'P.ProductID', '=', 'C.ProductID')
                ->join('tbl_product_category_type as PCT', 'PCT.PCTID', '=', 'P.CTID')
                ->join('tbl_product_category as PC', 'PC.PCID', '=', 'P.CID')
                ->join('tbl_product_subcategory as PSC', 'PSC.PSCID', '=', 'P.SCID')
                ->join('tbl_uom as U', 'U.UID', '=', 'P.UID')
                ->where('C.CustomerID', $customer->CustomerID)
                ->where('P.ActiveStatus', 'Active')
                ->where('P.DFlag', 0)
                ->where('PC.ActiveStatus', 'Active')
                ->where('PC.DFlag', 0)
                ->where('PSC.ActiveStatus', 'Active')
                ->where('PSC.DFlag', 0)
                ->select(
                    'P.ProductName',
                    'P.ProductNameInTranslation',
                    'P.ProductID',
                    'P.ProductImage',
                    'P.PRate as PRate',
                    'P.SRate as SRate',
                    'PV.VariationID as product_variation_id',
                    'C.Qty',
                    'PCT.PCTName',
                    'PCT.PCTNameInTranslation',
                    'PCT.PCTID',
                    'PC.PCName',
                    'PC.PCNameInTranslation',
                    'PC.PCID',
                    'PSC.PSCName',
                    'PSC.PSCNameInTranslation',
                    'U.UName',
                    'U.UNameInTranslation',
                    'PSC.PSCID',
                    'PV.SKU as variation_SKU',
                    'PV.PRate as variation_PRate',
                    'PV.SRate as variation_SRate')
                ->get();

            foreach ($Cart as $item) {
                if ($item->product_variation_id) {
                    $item->SKU = $item->variation_SKU;
                    $item->PRate = $item->variation_PRate;
                    $item->SRate = $item->variation_SRate;
                    $productUnit = DB::table('tbl_products_variation')
                        ->where('tbl_products_variation.ProductID', $item->ProductID)
                        ->where('tbl_products_variation.VariationID', $item->product_variation_id)
                        ->leftJoin('tbl_products_variation_details as D', function ($join) {
                            $join->on('tbl_products_variation.VariationID', '=', 'D.VariationID');
                        })
                        ->leftJoin('tbl_attributes_details as AD', function ($join) {
                            $join->on('AD.ValueID', '=', 'D.AttributeValueID')
                                ->on('AD.AttrID', '=', 'D.AttributeID');
                        })
                        ->leftJoin('tbl_attributes as A', 'A.AttrID', '=', 'AD.AttrID')
                        ->select('AD.Values', 'AD.valuesInTranslation')
                        ->first();
                } else {
                    $productUnit = DB::table('tbl_products')
                        ->where('tbl_products.ProductID', $item->ProductID)
                        ->leftJoin('tbl_uom as U', 'U.UID', '=', 'tbl_products.UID')
                        ->select('U.UName', 'U.UNameInTranslation')
                        ->first();
                }

                if (isset($productUnit->valuesInTranslation)) {
                    $valuesInTranslation = json_decode($productUnit->valuesInTranslation, true);
                    if (isset($valuesInTranslation[$lang])) {
                        $productUnit = $valuesInTranslation[$lang];
                    } else {
                        $productUnit = $productUnit->Values ?? $productUnit->UName ?? '-';
                    }
                } elseif (isset($productUnit->UNameInTranslation)) {
                    $UNameInTranslation = json_decode($productUnit->UNameInTranslation, true);
                    if (isset($UNameInTranslation[$lang])) {
                        $productUnit = $UNameInTranslation[$lang];
                    } else {
                        $productUnit = $productUnit->UName ?? '-';
                    }
                } else {
                    $productUnit = $productUnit->UName ?? '-';
                }

                $item->unit = $productUnit;
                $item->product_name = json_decode($item->ProductNameInTranslation)->$lang ?? $item->ProductName;
                $item->PCTName = json_decode($item->PCTNameInTranslation)->$lang ?? $item->PCTName;
                $item->PCName = json_decode($item->PCNameInTranslation)->$lang ?? $item->PCName;
                $item->PSCName = json_decode($item->PSCNameInTranslation)->$lang ?? $item->PSCName;

                $item->ProductImage = Helper::productImageFileExists($item->ProductImage) ? url($item->ProductImage) : url("assets/images/no-image-b.png");
                $product_rate = $item->SRate * $item->Qty;
                $item->PTotalRate = Helper::formatAmount($product_rate);
                $item->PRate = Helper::formatAmount($item->PRate);
                $item->SRate = Helper::formatAmount($item->SRate);
                $subTotalAmount += $product_rate;
                unset($item->variation_SKU, $item->variation_PRate, $item->variation_SRate, $item->ProductName, $item->PCTNameInTranslation,
                    $item->PCNameInTranslation, $item->PSCNameInTranslation, $item->ProductNameInTranslation, $item->UNameInTranslation, $item->UName);
            }

            if ($coupon_code) {
                $coupon = Coupon::where('coupon_code', $coupon_code)
                    ->where('DFlag', 0)
                    ->where('ActiveStatus', 'Active')
                    ->first(['COID', 'type', 'value']);
                if ($coupon->type === 'Percentage') {
                    $coupon_value = ($subTotalAmount / 100) * $coupon->value;
                } else {
                    $coupon_value = $coupon->value;
                }
            }
            $SAddress = DB::table('tbl_customer_address as CA')->where('CA.CustomerID', $customer->CustomerID)
                ->where('CA.DFlag', 0)->where('CA.isDefault', 1)
                ->leftJoin($this->generalDB . 'tbl_postalcodes as PC', 'PC.PID', 'CA.PostalCodeID')
                ->leftJoin($this->generalDB . 'tbl_cities as CI', 'CI.CityID', 'CA.CityID')
                ->leftJoin($this->generalDB . 'tbl_districts as D', 'D.DistrictID', 'PC.DistrictID')
                ->leftJoin($this->generalDB . 'tbl_states as S', 'S.StateID', 'D.StateID')
                ->leftJoin($this->generalDB . 'tbl_countries as C', 'C.CountryID', 'S.CountryID')
                ->orderBy('CA.CreatedOn', 'desc')
                ->select('CA.AID', 'CA.ReceiverName', 'CA.ReceiverEmail', 'CA.ReceiverMobile', 'CA.Address', 'CA.isDefault', 'CA.StateID',
                    'S.StateName', 'S.StateNameInTranslation', 'CA.DistrictID', 'D.DistrictName', 'D.DistrictNameInTranslation', 'CA.CityID',
                    'CI.CityName', 'CI.CityNameInTranslation', 'CA.PostalCodeID', 'PC.PostalCode', 'CA.CompleteAddress', 'CA.AddressType')
                ->first();

            if ($subTotalAmount > 0) {
                $free_shipping_above_rate = round(DB::table('tbl_settings')->where('KeyName', 'free_delivery_in_coimbatore_for_above')->pluck('KeyValue')->first() ?? 0, 2);
                if (isset($SAddress->DistrictName) && $SAddress->DistrictName === "Coimbatore" && $subTotalAmount > $free_shipping_above_rate) {
                    $shipping_charge = 0;
                } else {
                    $deliveryChargeRule = DeliveryChargeRule::where('min_order_value', '<=', $subTotalAmount)
                        ->where('max_order_value', '>=', $subTotalAmount)
                        ->first();

                    if ($deliveryChargeRule) {
                        $shipping_charge = round($deliveryChargeRule->delivery_charge ?? 0, 2);
                    } else {
                        $shipping_charge = round(DB::table('tbl_settings')->where('KeyName', 'delivery_charge')->pluck('KeyValue')->first() ?? 0, 2);
                    }
                }
            }

            if ($coupon_value > $subTotalAmount) {
                $coupon_value = $subTotalAmount;
            }
            $grandTotalAmount = ($subTotalAmount + $shipping_charge) - $coupon_value;
            if ($SAddress) {
                $SAddress->ReceiverName = Helper::translate($SAddress->ReceiverName, $lang);
                $SAddress->StateName = json_decode($SAddress->StateNameInTranslation)->$lang ?? Helper::translate($SAddress->StateName, $lang);
                $SAddress->DistrictName = json_decode($SAddress->DistrictNameInTranslation)->$lang ?? Helper::translate($SAddress->DistrictName, $lang);
                $SAddress->CityName = json_decode($SAddress->CityNameInTranslation)->$lang ?? Helper::translate($SAddress->CityName, $lang);
                $SAddress->CompleteAddress = Helper::translate($SAddress->CompleteAddress, $lang);
                $SAddress->AddressType = Helper::translate($SAddress->AddressType, $lang);
                unset($SAddress->StateNameInTranslation, $SAddress->DistrictNameInTranslation, $SAddress->CityNameInTranslation);
            }

            DB::commit();
            return response()->json(['status' => true, 'sub_total_amount' => Helper::formatAmount($subTotalAmount),
                'coupon_value' => Helper::formatAmount($coupon_value), 'shipping_charge' => Helper::formatAmount($shipping_charge),
                'grand_total_amount' => Helper::formatAmount($grandTotalAmount), 'total_product_count' => $Cart->count(),
                'shipping_address' => $SAddress, 'data' => $Cart]);
        } catch (Exception $e) {
            logger($e);
            DB::rollback();
            return $this->errorResponse($e, "Buy Now Order Preview Creation Failed!", 422);
        }
    }

    public function buyNowOrder(Request $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = Validator::make($request->all(), [
                'ProductID' => 'required|string|exists:tbl_products,ProductID',
                'ProductVariationID' => 'nullable|string|exists:tbl_products_variation,VariationID',
                'Qty' => 'required|integer'
            ]);

            if ($validatedData->fails()) {
                return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
            }

            $lang = optional($request->auth_customer)->language ?? 'en';
            $customer = $request->auth_customer;
            $coupon_code = $request->coupon_code;
            $CustomerID = $customer->CustomerID;
            $DiscountPer = Helper::formatAmount(0);
            $coupon_value = 0;
            $subTotalAmount = 0;
            $grandTotalAmount = 0;
            $shipping_charge = 0;

            $ProductID = $request->ProductID;
            $ProductVariationID = $request->ProductVariationID;
            $Qty = $request->Qty ?? 1;
            $deleteCart = DB::table('tbl_temp_customer_cart')->where('CustomerID', $CustomerID)->delete();
            $tempCart = DB::table('tbl_temp_customer_cart')->insert(['CustomerID' => $CustomerID, 'ProductID' => $ProductID,
                'ProductVariationID' => $ProductVariationID, 'Qty' => $Qty]);

            $Cart = DB::table('tbl_temp_customer_cart as C')
                ->leftJoin('tbl_products_variation as PV', 'PV.VariationID', 'C.ProductVariationID')
                ->join('tbl_products as P', 'P.ProductID', '=', 'C.ProductID')
                ->join('tbl_tax as T', 'T.TaxID', '=', 'P.TaxID')
                ->join('tbl_product_category_type as PCT', 'PCT.PCTID', '=', 'P.CTID')
                ->join('tbl_product_category as PC', 'PC.PCID', '=', 'P.CID')
                ->join('tbl_product_subcategory as PSC', 'PSC.PSCID', '=', 'P.SCID')
                ->join('tbl_uom as U', 'U.UID', '=', 'P.UID')
                ->where('C.CustomerID', $CustomerID)
                ->where('P.ActiveStatus', 'Active')
                ->where('P.DFlag', 0)
                ->where('PC.ActiveStatus', 'Active')
                ->where('PC.DFlag', 0)
                ->where('PSC.ActiveStatus', 'Active')
                ->where('PSC.DFlag', 0)
                ->select(
                    'P.ProductName as product_name',
                    'P.ProductNameInTranslation',
                    'P.ProductID',
                    'P.SKU',
                    'P.PRate as PRate',
                    'P.SRate as SRate',
                    'PV.VariationID as product_variation_id',
                    'C.Qty',
                    'PCT.PCTName',
                    'PCT.PCTNameInTranslation',
                    'PCT.PCTID',
                    'PC.PCName',
                    'PC.PCNameInTranslation',
                    'PC.PCID',
                    'PSC.PSCName',
                    'PSC.PSCNameInTranslation',
                    'U.UName',
                    'U.UCode',
                    'U.UID',
                    'PSC.PSCID',
                    'T.TaxPercentage',
                    'PV.SKU as variation_SKU',
                    'PV.PRate as variation_PRate',
                    'PV.SRate as variation_SRate')
                ->get();

            foreach ($Cart as $item) {
                if ($item->product_variation_id) {
                    $item->SKU = $item->variation_SKU;
                    $item->PRate = $item->variation_PRate;
                    $item->SRate = $item->variation_SRate;
                }
                $item->PRate = round($item->PRate,2);
                $item->SRate = round($item->SRate,2);
                $product_rate = $item->SRate * $item->Qty;
                $item->TaxAmount = round(($product_rate * $item->TaxPercentage) / (100 + $item->TaxPercentage), 2);
                $item->PTotalRate = round($product_rate,2);
                $item->PTotalRateExcludeTax = round($product_rate - $item->TaxAmount, 2);
                $subTotalAmount += $product_rate;
                unset($item->variation_SKU, $item->variation_PRate, $item->variation_SRate);
            }

            if ($coupon_code) {
                $coupon = Coupon::where('coupon_code', $coupon_code)->where('DFlag', 0)->where('ActiveStatus', 'Active')
                    ->first(['COID', 'type', 'value']);
                if ($coupon->type === 'Percentage') {
                    $coupon_value = round(($subTotalAmount / 100) * $coupon->value,2);
                } else {
                    $coupon_value = $coupon->value;
                }
                if ($coupon_value > $subTotalAmount) {
                    $coupon_value = round($subTotalAmount,2);
                }
                if ($coupon->type === 'Percentage') {
                    $DiscountPer = $coupon->value . "%";
                } else {
                    $DiscountPer = Helper::formatAmount($coupon_value);
                }
            }

            $SAddress = DB::table('tbl_customer_address as CA')->where('CA.CustomerID',$customer->CustomerID)
                ->where('CA.DFlag',0)->where('CA.isDefault',1)
                ->leftJoin($this->generalDB.'tbl_postalcodes as PC', 'PC.PID', 'CA.PostalCodeID')
                ->leftJoin($this->generalDB.'tbl_cities as CI', 'CI.CityID', 'CA.CityID')
                ->leftJoin($this->generalDB.'tbl_districts as D', 'D.DistrictID', 'PC.DistrictID')
                ->leftJoin($this->generalDB.'tbl_states as S', 'S.StateID', 'D.StateID')
                ->leftJoin($this->generalDB.'tbl_countries as C','C.CountryID','S.CountryID')
                ->orderBy('CA.CreatedOn','desc')
                ->select('CA.AID', 'CA.ReceiverName', 'CA.ReceiverEmail', 'CA.ReceiverMobile', 'CA.Address', 'CA.isDefault', 'CA.StateID',
                    'S.StateName', 'S.StateNameInTranslation', 'CA.DistrictID', 'D.DistrictName', 'D.DistrictNameInTranslation', 'CA.CityID',
                    'CI.CityName', 'CI.CityNameInTranslation', 'CA.PostalCodeID', 'PC.PostalCode', 'CA.CompleteAddress','CA.AddressType')
                ->first();

            if ($subTotalAmount > 0) {
            $free_shipping_above_rate = round(DB::table('tbl_settings')->where('KeyName', 'free_delivery_in_coimbatore_for_above')->pluck('KeyValue')->first() ?? 0, 2);
            if (isset($SAddress->DistrictName) && $SAddress->DistrictName === "Coimbatore" && $subTotalAmount > $free_shipping_above_rate) {
                $shipping_charge = 0;
            } else {
                $deliveryChargeRule = DeliveryChargeRule::where('min_order_value', '<=', $subTotalAmount)
                    ->where('max_order_value', '>=', $subTotalAmount)
                    ->first();

                if ($deliveryChargeRule) {
                    $shipping_charge = round($deliveryChargeRule->delivery_charge ?? 0, 2);
                } else {
                    $shipping_charge = round(DB::table('tbl_settings')->where('KeyName', 'delivery_charge')->pluck('KeyValue')->first() ?? 0, 2);
                }
            }
        }

//            if ($SAddress) {
//                $SAddress->ReceiverName = Helper::translate($SAddress->ReceiverName, $lang);
//                $SAddress->StateName = json_decode($SAddress->StateNameInTranslation)->$lang ?? Helper::translate($SAddress->StateName, $lang);
//                $SAddress->DistrictName = json_decode($SAddress->DistrictNameInTranslation)->$lang ?? Helper::translate($SAddress->DistrictName, $lang);
//                $SAddress->CityName = json_decode($SAddress->CityNameInTranslation)->$lang ?? Helper::translate($SAddress->CityName, $lang);
//                $SAddress->CompleteAddress = Helper::translate($SAddress->CompleteAddress, $lang);
//                $SAddress->AddressType = Helper::translate($SAddress->AddressType, $lang);
//                unset($SAddress->StateNameInTranslation, $SAddress->DistrictNameInTranslation, $SAddress->CityNameInTranslation);
//            }
            $grandTotalAmount = round(($subTotalAmount + $shipping_charge) - $coupon_value,2);

            if ($SAddress && ($grandTotalAmount > 0)) {
                $OrderID = DocNum::getDocNum(docTypes::Order->value);
                $data = [
                    "OrderID" => $OrderID,
                    "OrderNo" => sprintf("%02d%06d", date('y'), Order::count() + 1),
                    "CustomerName" => $SAddress->ReceiverName,
                    "MobileNo1" => $SAddress->ReceiverMobile,
                    "Email" => $SAddress->ReceiverEmail,
                    "Address" => $SAddress->Address,
                    "State" => $SAddress->StateName,
                    "District" => $SAddress->DistrictName,
                    "City" => $SAddress->CityName,
                    "PostalCode" => $SAddress->PostalCode,
                    "CompleteAddress" => $SAddress->CompleteAddress,
                    "OrderDate" => date('Y-m-d'),
                    "SubTotal" => round($subTotalAmount,2),
                    "DiscountType" => isset($coupon) ? $coupon->type . " Coupon" : "",
                    "DiscountPer" => $DiscountPer,
                    "DiscountAmount" => $coupon_value,
                    "ShippingCharge" => $shipping_charge,
                    "TotalAmount" => $grandTotalAmount,
                    "CreatedBy" => $CustomerID,
                ];
                $status = Order::create($data);
                if ($status) {
                    foreach ($Cart as $item) {
                        $OrderDetailID = DocNum::getDocNum(docTypes::OrderDetails->value);
                        $data1 = [
                            'DetailID' => $OrderDetailID,
                            'OrderID' => $OrderID,
                            'CTID' => $item->PCTID,
                            'CID' => $item->PCID,
                            'SCID' => $item->PSCID,
                            'ProductID' => $item->ProductID,
                            'ProductName' => $item->product_name,
                            'ProductNameInTranslation' => $item->ProductNameInTranslation,
                            'SKU' => $item->SKU,
                            'ProductVariationID' => $item->product_variation_id,
                            'Qty' => $item->Qty,
                            'PRate' => $item->PRate,
                            'SRate' => $item->SRate,
                            'TaxPercentage' => $item->TaxPercentage,
                            'TaxAmount' => $item->TaxAmount,
                            'UOMID' => $item->UID,
                            'Amount' => $item->PTotalRate,
                            'AmountExcludeTax' => $item->PTotalRateExcludeTax,
                            'CreatedOn' => date('Y-m-d'),
                            'CreatedBy' => $CustomerID,
                        ];
                        $status1 = OrderDetail::create($data1);
                        if ($status1) {
                            DocNum::updateDocNum(docTypes::OrderDetails->value);
                        }
                    }
                    DocNum::updateDocNum(docTypes::Order->value);
                }
                DB::commit();
                $orderDetail = Order::find($status->OrderID);
                $orderDetail->CustomerName = Helper::translate($orderDetail->CustomerName, $lang);
                $orderDetail->CompleteAddress = Helper::translate($orderDetail->CompleteAddress, $lang);
                $orderDetail->Status = Helper::translate($orderDetail->Status, $lang);
                $orderDetail->DiscountType = Helper::translate($orderDetail->DiscountType, $lang);
                $orderDetail->TrackStatus = Helper::translate($orderDetail->TrackStatus, $lang);
                $orderDetail->City = Helper::translate($orderDetail->City, $lang);
                $orderDetail->District = Helper::translate($orderDetail->District, $lang);
                $orderDetail->State = Helper::translate($orderDetail->State, $lang);
                $orderDetail->DiscountAmount = Helper::formatAmount($orderDetail->DiscountAmount);
                $orderDetail->TotalAmount = Helper::formatAmount($orderDetail->TotalAmount);
                $orderDetail->ShippingCharge = Helper::formatAmount($orderDetail->ShippingCharge);
                $orderDetail->SubTotal = Helper::formatAmount($orderDetail->SubTotal);
                $orderDetail->PaymentStatus = Helper::translate($orderDetail->PaymentStatus, $lang);
                return $this->successResponse($orderDetail, "Buy Now Order Created Successfully!");
            } else {
                return $this->errorResponse([], "Can't create Buy Now Order", 422);
            }
        } catch(Exception $e) {
            logger($e);
            DB::rollback();
            return $this->errorResponse($e, "Buy Now Order Creation Failed!", 422);
        }
    }
    public function customerOrderList(Request $request)
    {
        $lang = optional($request->auth_customer)->language ?? 'en';
        $language = Language::with('translations')->where('code', $lang)->first();
        $translation = $language->translations ? json_decode($language->translations->value) : new \stdClass();
        $customer = $request->auth_customer;
        $CustomerID = $customer->CustomerID;
        $CompanyStateID = DB::table('tbl_company_settings')->where('KeyName', 'StateID')->value('KeyValue');
        $CompanyState = DB::table($this->generalDB.'tbl_states')->where('StateID', $CompanyStateID)->value('StateName');
        $taxes = [];
        if($request->has('OrderID') && $request->OrderID){
            try {
                $CustomerID = $request->auth_customer->CustomerID;
                $orderDetails = Order::with('orderDetails')
                    ->where('CreatedBy', $CustomerID)
                    ->where('OrderID', $request->OrderID)
                    ->get();
                $orderDetails->transform(function ($order) use ($lang, $translation, $CompanyState, &$taxes) {

                    $isIGST = !($order->State == $CompanyState);

                    if ($order->orderDetails) {
                        $order->orderDetails->transform(function ($detail) use ($lang, &$taxes, $isIGST) {
                            $detail->ProductName = json_decode($detail->ProductNameInTranslation)->$lang ?? $detail->ProductName;
                            $detail->PRate = Helper::formatAmount($detail->PRate);
                            $detail->SRate = Helper::formatAmount($detail->SRate);
                            $detail->Amount = Helper::formatAmount($detail->Amount);
                            $detail->TaxPercentage = $detail->TaxPercentage ? $detail->TaxPercentage . "%" : "0%";

                            $taxPercentage = $detail->TaxPercentage;

                            if (!isset($taxes[$taxPercentage])) {
                                $taxes[$taxPercentage] = $isIGST
                                    ? ['IGST' => 0, 'Total' => 0]
                                    : ['SGST' => 0, 'CGST' => 0, 'Total' => 0];
                            }

                            $taxAmount = $detail->TaxAmount;
                            if ($isIGST) {
                                $taxes[$taxPercentage]['IGST'] += $taxAmount;
                            } else {
                                $halfTaxAmount = $taxAmount / 2;
                                $taxes[$taxPercentage]['SGST'] += $halfTaxAmount;
                                $taxes[$taxPercentage]['CGST'] += $halfTaxAmount;
                            }
                            $taxes[$taxPercentage]['Total'] += $taxAmount;

                            $detail->TaxAmount = Helper::formatAmount($detail->TaxAmount);
                            $detail->AmountExcludeTax = Helper::formatAmount($detail->AmountExcludeTax);

                            if ($detail->ProductVariationID) {
                                $productUnit = DB::table('tbl_products_variation')
                                    ->where('tbl_products_variation.ProductID', $detail->ProductID)
                                    ->where('tbl_products_variation.VariationID', $detail->ProductVariationID)
                                    ->leftJoin('tbl_products_variation_details as D', function ($join) {
                                        $join->on('tbl_products_variation.VariationID', '=', 'D.VariationID');
                                    })
                                    ->leftJoin('tbl_attributes_details as AD', function ($join) {
                                        $join->on('AD.ValueID', '=', 'D.AttributeValueID')
                                            ->on('AD.AttrID', '=', 'D.AttributeID');
                                    })
                                    ->leftJoin('tbl_attributes as A', 'A.AttrID', '=', 'AD.AttrID')
                                    ->select('AD.Values', 'AD.valuesInTranslation')
                                    ->first();
                            } else {
                                $productUnit = DB::table('tbl_products')
                                    ->where('tbl_products.ProductID', $detail->ProductID)
                                    ->leftJoin('tbl_uom as U', 'U.UID', '=', 'tbl_products.UID')
                                    ->select('U.UName', 'U.UNameInTranslation')
                                    ->first();
                            }

                            if (isset($productUnit->valuesInTranslation)) {
                                $valuesInTranslation = json_decode($productUnit->valuesInTranslation, true);
                                if (isset($valuesInTranslation[$lang])) {
                                    $productUnit = $valuesInTranslation[$lang];
                                } else {
                                    $productUnit = $productUnit->Values ?? $productUnit->UName ?? '-';
                                }
                            } elseif (isset($productUnit->UNameInTranslation)) {
                                $UNameInTranslation = json_decode($productUnit->UNameInTranslation, true);
                                if (isset($UNameInTranslation[$lang])) {
                                    $productUnit = $UNameInTranslation[$lang];
                                } else {
                                    $productUnit = $productUnit->UName ?? '-';
                                }
                            } else {
                                $productUnit = $productUnit->UName ?? '-';
                            }

                            $detail->unit = $productUnit;
                            unset($detail->ProductNameInTranslation);
                            return $detail;
                        });
                    }

                    foreach ($taxes as &$tax) {
                        if (isset($tax['IGST'])) {
                            $tax['IGST'] = Helper::formatAmount($tax['IGST']);
                        }
                        if (isset($tax['SGST'])) {
                            $tax['SGST'] = Helper::formatAmount($tax['SGST']);
                        }
                        if (isset($tax['CGST'])) {
                            $tax['CGST'] = Helper::formatAmount($tax['CGST']);
                        }
                        $tax['Total'] = Helper::formatAmount($tax['Total']);
                    }

                    unset($tax);

                    $order->taxes = $taxes;
                    $order->City = Helper::translate($order->City, $lang);
                    $order->District = Helper::translate($order->District, $lang);
                    $order->State = Helper::translate($order->State, $lang);
                    $order->CompleteAddress = Helper::translate($order->CompleteAddress, $lang);
                    $order->DiscountType = $translation->{$order->DiscountType} ?? Helper::translate($order->DiscountType, $lang);
                    $order->TrackStatus = $translation->{$order->TrackStatus} ?? Helper::translate($order->TrackStatus, $lang);
                    $order->PaymentStatus = $translation->{$order->paymentStatus} ?? Helper::translate($order->paymentStatus, $lang);
                    $order->StatusInLang = Helper::translate($order->Status, $lang);
                    $order->SubTotal = Helper::formatAmount($order->SubTotal);
                    $order->DiscountAmount = Helper::formatAmount($order->DiscountAmount);
                    $order->ShippingCharge = Helper::formatAmount($order->ShippingCharge);
                    $order->TotalAmountInString = Helper::formatAmount($order->TotalAmount);
                    $order->isReviewed = ProductReview::where('OrderID', $order->OrderID)->exists();
                    $order->OrderDate = Helper::translate(Carbon::parse($order->OrderDate)->format('D, M d, Y'), $lang);
                    $order->orderTrackDetails->sortBy('orderBy');
                    $order->orderTrackDetails->transform(function ($orderTrack) use ($lang, $translation) {
                        $orderTrack->StatusDate = $orderTrack->StatusDate ? Helper::translate(Carbon::parse($orderTrack->StatusDate)->format('D, M d, Y'), $lang) : null;
                        $orderTrack->Description = $translation->{$orderTrack->Description} ?? Helper::translate($orderTrack->Description, $lang);
                        $orderTrack->Status = $translation->{$orderTrack->Status} ?? Helper::translate($orderTrack->Status, $lang);
                        return $orderTrack;
                    });
                    return $order;
                });

                return response()->json([
                    'status' => true,
                    'data' => $orderDetails,
                ]);
            } catch (Exception $e) {
                logger($e);
                return $this->errorResponse($e, "Failed to fetch order details", 500);
            }
        }else{
            $pageNo = $request->PageNo ?? 1;
            $perPage = 15;
            $orderDetails = Order::with('orderDetails')
                ->where('CreatedBy', $CustomerID)
                ->when($request->has('StatusType') && in_array($request->StatusType, ['In progress', 'Delivered']), function ($query) use ($request) {
                    return $query->where('Status', $request->StatusType);
                })
                ->OrderBy('CreatedOn', 'desc')
                ->paginate($perPage, ['*'], 'page', $pageNo);

            $orderDetails->transform(function ($order) use ($lang, $translation) {
                $order->OrderDate = Helper::translate($order->OrderDate, $lang);
                $order->PaymentStatus = Helper::translate($order->paymentStatus, $lang);
                if ($order->orderDetails) {
                    $order->orderDetails->transform(function ($detail) use ($lang) {
                        $detail->ProductName = json_decode($detail->ProductNameInTranslation)->$lang ?? $detail->ProductName;
                        $detail->PRate = Helper::formatAmount($detail->PRate);
                        $detail->SRate = Helper::formatAmount($detail->SRate);
                        $detail->Amount = Helper::formatAmount($detail->Amount);

                        if ($detail->ProductVariationID) {
                            $productUnit = DB::table('tbl_products_variation')
                                ->where('tbl_products_variation.ProductID', $detail->ProductID)
                                ->where('tbl_products_variation.VariationID', $detail->ProductVariationID)
                                ->leftJoin('tbl_products_variation_details as D', function ($join) {
                                    $join->on('tbl_products_variation.VariationID', '=', 'D.VariationID');
                                })
                                ->leftJoin('tbl_attributes_details as AD', function ($join) {
                                    $join->on('AD.ValueID', '=', 'D.AttributeValueID')
                                        ->on('AD.AttrID', '=', 'D.AttributeID');
                                })
                                ->leftJoin('tbl_attributes as A', 'A.AttrID', '=', 'AD.AttrID')
                                ->select('AD.Values', 'AD.valuesInTranslation')
                                ->first();
                        } else {
                            $productUnit = DB::table('tbl_products')
                                ->where('tbl_products.ProductID', $detail->ProductID)
                                ->leftJoin('tbl_uom as U', 'U.UID', '=', 'tbl_products.UID')
                                ->select('U.UName', 'U.UNameInTranslation')
                                ->first();
                        }

                        if (isset($productUnit->valuesInTranslation)) {
                            $valuesInTranslation = json_decode($productUnit->valuesInTranslation, true);
                            if (isset($valuesInTranslation[$lang])) {
                                $productUnit = $valuesInTranslation[$lang];
                            } else {
                                $productUnit = $productUnit->Values ?? $productUnit->UName ?? '-';
                            }
                        } elseif (isset($productUnit->UNameInTranslation)) {
                            $UNameInTranslation = json_decode($productUnit->UNameInTranslation, true);
                            if (isset($UNameInTranslation[$lang])) {
                                $productUnit = $UNameInTranslation[$lang];
                            } else {
                                $productUnit = $productUnit->UName ?? '-';
                            }
                        } else {
                            $productUnit = $productUnit->UName ?? '-';
                        }

                        $detail->unit = $productUnit;
                        unset($detail->ProductNameInTranslation);
                        return $detail;
                    });
                }
                $order->TotalAmountInString = Helper::formatAmount($order->TotalAmount);
                return $order;
            });

            return response()->json([
                'status' => true,
                'data' => $orderDetails->items(),
                'CurrentPage' => $orderDetails->currentPage(),
                'LastPage' => $orderDetails->lastPage(),
            ]);
        }
    }

    public function paymentSuccessStatus(Request $request)
    {
        $customer = $request->auth_customer;
        $CustomerID = $customer->CustomerID;
        $validatedData = Validator::make($request->all(), [
            'OrderID' => 'required|string|exists:tbl_order,OrderID',
            'PaymentID' => 'required|string',
        ]);

        if ($validatedData->fails()) {
            return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
        }

        DB::beginTransaction();
        try {
            $OrderID = $request->OrderID;
            $orderDetails = Order::where('CreatedBy', $CustomerID)
                ->where('OrderID', $OrderID)->first();
            if ($orderDetails->PaymentID === null) {
                $orderDetails->update(['PaymentID' => $request->PaymentID, 'TrackStatus' => 'Order Confirmed', 'OrderDate' => Carbon::now(), 'ExpectedDelivery' => Carbon::now()->addDays(5)]);

                $orderTracks = [
                    [
                        "CustomerID" => $CustomerID,
                        "OrderID" => $OrderID,
                        "Status" => "Order Confirmed",
                        "Description" => "Your Order Has Been Confirmed, You Will Receive Shipped Within 24Hrs",
                        "StatusDate" => Carbon::now(),
                        "orderBy" => 1,
                        "UpdatedBy" => $CustomerID
                    ],
                    [
                        "CustomerID" => $CustomerID,
                        "OrderID" => $OrderID,
                        "Status" => "Shipped",
                        "Description" => "Will Be Updated Soon",
                        "StatusDate" => null,
                        "orderBy" => 2,
                        "UpdatedBy" => $CustomerID
                    ]
                ];

                foreach ($orderTracks as $orderTrack) {
                    $existingOrderTrack = CustomerOrderTrack::where([
                        'CustomerID' => $orderTrack['CustomerID'],
                        'OrderID' => $orderTrack['OrderID'],
                        'Status' => $orderTrack['Status']
                    ])->first();

                    if (!$existingOrderTrack) {
                        CustomerOrderTrack::create($orderTrack);
                    }
                }
//                [$orderDetails, $logo, $companyDetails, $locationDetails] = $this->generateMailData($OrderID);
//                Mail::to($orderDetails->Email)->send(new OrderMail("Confirmation", 'orderDetails', 'companyDetails', 'locationDetails', 'logo'));
                $Title = "Order Place Successfully";
                $Message = "Your Order placed successfully";
                try {
                    Helper::saveNotification($CustomerID, $Title, $Message, 'Order', $OrderID);
                } catch (\Throwable $e) {
                    logger($e);
                }
                try {
                    SaleRegisterInBusyJob::dispatchSync($OrderID);
                } catch (\Throwable $e) {
                    logger($e);
                }
                DB::commit();

                // After commit — mail/SMS failure must never affect order placement
                try {
                    Helper::sendOrderNotificationEmail($OrderID);
                } catch (\Throwable $e) {
                    logger($e);
                }
                try {
                    Helper::sendOrderNotificationSms($OrderID);
                } catch (\Throwable $e) {
                    logger($e);
                }

                return $this->successResponse([], "Payment Status Successfully Updated");
            } else {
                return $this->errorResponse([], "Payment status already updated!.", 500);
            }
        } catch (Exception $e) {
            logger($e);
            DB::rollBack();
            return $this->errorResponse($e, "Failed to update Payment status", 500);
        }
    }

    public function updateDeliveredStatus(Request $request): JsonResponse
    {
        $customer = $request->auth_customer;
        $CustomerID = $customer->CustomerID;
        $validatedData = Validator::make($request->all(), [
            'OrderID' => 'required|string|exists:tbl_order,OrderID'
        ]);

        if ($validatedData->fails()) {
            return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
        }

        DB::beginTransaction();
        try {
            $OrderID = $request->OrderID;
            $orderDetails = Order::where('CreatedBy', $CustomerID)
                ->where('OrderID', $OrderID)->first();
            $shippedStatus = CustomerOrderTrack::where('OrderID', $OrderID)->whereStatus('Shipped')->whereNotNull('StatusDate')->exists();
            if (($orderDetails->PaymentID !== null) && ($orderDetails->TrackStatus === "Shipped") && $shippedStatus) {
                $orderDetails->update(['Status' => 'Delivered', 'TrackStatus' => 'Delivered']);
                CustomerOrderTrack::firstOrCreate([
                    "CustomerID" => $CustomerID,
                    "OrderID" => $OrderID,
                    "Status" => "Delivered",
                    "Description" => "Your Order delivered successfully",
                    "StatusDate" => Carbon::now(),
                    "orderBy" => 3,
                    "UpdatedBy" => $CustomerID
                ]);

                if($customer->IsGoogleReviewed == 0){
                    $customer->update(['IsAskGoogleReview' => 1]);
                }

                $Title = "Order Delivered";
                $Message = "Your Order delivered successfully";
                Helper::saveNotification($CustomerID, $Title, $Message, 'Order', $OrderID);
                DB::commit();
                return $this->successResponse([], "Your Order marked as delivered successfully");
            } else {
                return $this->errorResponse([], "Can't process your request", 500);
            }
        } catch (Exception $e) {
            logger($e);
            DB::rollBack();
            return $this->errorResponse($e, "Failed to update Delivery status", 500);
        }
    }

    public function createReview(Request $request)
    {
        $customer = $request->auth_customer;
        $CustomerID = $customer->CustomerID;
        $validatedData = Validator::make($request->all(), [
            'OrderID' => 'required|string|exists:tbl_order,OrderID',
            'ProductID' => 'required|string',
            'rating' => 'required|string',
            'review' => 'required|string',
        ]);

        if ($validatedData->fails()) {
            return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
        }
        DB::beginTransaction();
        try {
            $OrderID = $request->OrderID;
            $order = OrderDetail::where('CreatedBy', $CustomerID)->where('OrderID', $OrderID)
                ->where('ProductID', $request->ProductID)->exists();
            $ProductReview = ProductReview::where([
                "CustomerID" => $CustomerID,
                "OrderID" => $request->OrderID,
                "ProductID" => $request->ProductID
            ])->exists();
            if ($order && (!$ProductReview)) {
                $data = [
                    "CustomerID" => $CustomerID,
                    "OrderID" => $request->OrderID,
                    "ProductID" => $request->ProductID,
                    "rating" => $request->rating,
                    "review" => $request->review,
                ];
                $productReview = ProductReview::create($data);
            } else {
                return $this->errorResponse([], "Not eligible to review this product", 500);
            }
            DB::commit();
            return $this->successResponse($productReview, "Review saved Successfully.");
        } catch (Exception $e) {
            logger($e);
            DB::rollBack();
            return $this->errorResponse($e, "Failed to save the review", 500);
        }
    }
    public function saveReviewLike(Request $request)
    {
        $customer = $request->auth_customer;
        $CustomerID = $customer->CustomerID;
        DB::beginTransaction();
        try {
            $validation = ((DB::table('tbl_product_reviews')->where('ReviewID', $request->ReviewID)->count() > 0)
                && (DB::table('tbl_products')->where('ProductID', $request->ProductID)->count() > 0));
            if($validation){
                $reviewLike = ReviewLike::firstOrCreate(['CustomerID' => $CustomerID, 'ReviewID' => $request->ReviewID,
                    'ProductID' => $request->ProductID]);
                DB::commit();
                return $this->successResponse($reviewLike, "Review Liked Successfully.");
            } else {
                return $this->errorResponse([], "Review ID or Product ID mismatch with the DB", 422);
            }
        } catch (Exception $e) {
            logger($e);
            DB::rollBack();
            return $this->errorResponse($e, "Failed to like the review", 500);
        }
    }

    /**
     * @param Request $req
     * @return string
     */
    public function getCompleteAddress(Request $req): string
    {
        $city = DB::table($this->generalDB . 'tbl_cities')->where('CityID', $req->CityID)->value('CityName');
        $district = DB::table($this->generalDB . 'tbl_districts')->where('DistrictID', $req->DistrictID)->value('DistrictName');
        $state = DB::table($this->generalDB . 'tbl_states')->where('StateID', $req->StateID)->value('StateName');
        $postalCode = DB::table($this->generalDB . 'tbl_postalcodes')->where('PID', $req->PostalCodeID)->value('PostalCode');

        $completeAddress = $req->Address . ', ' . $city . ', ' . $district . ', ' . $state . ', ' . $postalCode;
        return $completeAddress;
    }

    public function generateMailData($OrderID)
    {
        $generalDB = Helper::getGeneralDB();
        $orderDetails = Order::with('orderDetails')->where('OrderID', $OrderID)->get();
        $orderDetails->transform(function ($order) {
            if ($order->orderDetails) {
                $order->orderDetails->transform(function ($detail) {
                    $detail->PRate = Helper::formatAmount($detail->PRate);
                    $detail->SRate = Helper::formatAmount($detail->SRate);
                    $detail->Amount = Helper::formatAmount($detail->Amount);
                    return $detail;
                });
            }
            $order->SubTotal = Helper::formatAmount(($order->SubTotal));
            $order->DiscountAmount = Helper::formatAmount($order->DiscountAmount);
            $order->ShippingCharge = Helper::formatAmount($order->ShippingCharge);
            $order->TotalAmountInString = Helper::formatAmount($order->TotalAmount);
            $order->OrderDate = Carbon::parse($order->OrderDate)->format('d/m/Y');
            return $order;
        });
        if(count($orderDetails) > 0){
            $orderDetails = $orderDetails[0];
        }
        $companyDetails = collect(DB::Table('tbl_company_settings')->pluck( 'KeyValue', 'KeyName'));
        if (empty($companyDetails['Logo'])) {
            $logo = config('app.url') . '/' . 'assets/images/no-image-b.png';
        } else {
            $logo = config('app.url') . '/' . $companyDetails['Logo'];
        }

        $stateID = $companyDetails->get('StateID');
        $cityID = $companyDetails->get('CityID');
        $districtID = $companyDetails->get('DistrictID');
        $postalCodeID = $companyDetails->get('PostalCodeID');

        $locationDetails = DB::table($generalDB.'tbl_states as S')
            ->join($generalDB.'tbl_cities as CI', 'CI.StateID', '=', 'S.StateID')
            ->join($generalDB.'tbl_districts as D', 'D.StateID', '=', 'S.StateID')
            ->join($generalDB.'tbl_postalcodes as PC', 'PC.PID', '=', 'CI.PostalID')
            ->select(
                'S.StateName',
                'CI.CityName',
                'D.DistrictName',
                'PC.PostalCode'
            )
            ->where('S.StateID', $stateID)
            ->where('CI.CityID', $cityID)
            ->where('D.DistrictID', $districtID)
            ->where('PC.PID', $postalCodeID)
            ->first();
        return [$orderDetails, $logo, $companyDetails, $locationDetails];
    }

    /**
     * @deprecated Use Helper::sendOrderNotificationEmail() — kept for compatibility.
     * Notify admin inbox when a new paid order is confirmed.
     * Never throws — mail failures must not affect order placement.
     */
    public function sendOrderNotificationEmail(string $OrderID): bool
    {
        try {
            return Helper::sendOrderNotificationEmail($OrderID);
        } catch (\Throwable $e) {
            logger($e);
            return false;
        }
    }

    public function googleReviewDecision(Request $request)
    {
        $customer = $request->auth_customer;
        $validatedData = Validator::make($request->all(), [
            'decision' => 'required|string'
        ]);
        if ($validatedData->fails()) {
            return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
        }
        DB::beginTransaction();
        try {
            $customer->update([
                'IsAskGoogleReview' => 0,
                'IsGoogleReviewed' => $request->decision == "true" ? 1 : 0
            ]);
            DB::commit();
            return $this->successResponse($customer, "Google Review Decision saved Successfully.");
        } catch (Exception $e) {
            logger("Error in CustomerAuthController@googleReviewDecision : ".$e);
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), "Failed to save the Google review decision", 500);
        }
    }
}
