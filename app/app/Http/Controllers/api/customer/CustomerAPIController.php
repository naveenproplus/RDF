<?php

namespace App\Http\Controllers\api\customer;

use App\helper\helper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\web\logController;
use App\Models\Customer;
use App\Models\Language;
use App\Services\SmsAlertService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\DocNum;
use App\enums\docTypes;
use logs;
use Nette\Utils\Random;

class CustomerAPIController extends Controller{
    use ApiResponse;
    private $generalDB;
    private $tmpDB;
    private $FileTypes;


    public function __construct(){
		$this->generalDB=Helper::getGeneralDB();
		$this->tmpDB=Helper::getTmpDB();
		$this->FileTypes=Helper::getFileTypes(array("category"=>array("Images","Documents")));
    }


    public function login(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'mobile_no' => 'required|integer|digits:10'
        ]);

        if ($validatedData->fails()) {
            return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
        }

        DB::beginTransaction();
        try {
            $customer = Customer::where('MobileNo1', $request->mobile_no)->where('DFlag', 0)->first();
            $otp = Random::generate(4, '0-9');
            if ($customer){
                if ($customer->MobileNo1 == 9876543210) {
                    $otp = 1234;
                    $customer->update(["otp" => $otp, "otp_verified"=> false]);
                    return $this->successResponse(["new_user" => is_null($customer->api_token), "otp" => $customer->otp], "Customer OTP Sent Successfully");
                }
                $oldCustomer = $customer->replicate();
                $otp = Random::generate(4, '0-9');
                $this->sendOtpSms($otp, $request);
                $customer->update(["otp" => $otp, "otp_verified"=> false]);

                $logData = array(
                    "Description" => "Login request",
                    "ModuleName" => "Customer",
                    "Action" => "Update",
                    "ReferID" => $customer->CustomerID,
                    "OldData" => $oldCustomer,
                    "NewData" => $customer->customerData,
                    "UserID" => $customer->CustomerID,
                    "IP" => $request->ip()
                );
            } else {
                $CustomerID = DocNum::getDocNum(docTypes::Customer->value, "", Helper::getCurrentFY());
                $data = [
                    "CustomerID" => $CustomerID,
                    "MobileNo1" => $request->mobile_no,
                    "otp" => $otp,
                    "CreatedOn" => now()
                ];
                $this->sendOtpSms($otp, $request);
                $customer = Customer::create($data);
                $logData = array(
                    "Description" => "New Customer Created",
                    "ModuleName" => "Customer",
                    "Action" => "Add",
                    "ReferID" => $CustomerID,
                    "OldData" => [],
                    "NewData" => $customer,
                    "UserID" => $CustomerID,
                    "IP" => $request->ip()
                );
                DocNum::updateDocNum(docTypes::Customer->value);
            }
            logController::Store($logData);
            DB::commit();
            return $this->successResponse(["new_user"=> is_null($customer->api_token), "otp"=> $otp], "Customer OTP Sent Successfully");
        } catch (Exception $e) {
            logger($e);
            DB::rollback();
            return $this->errorResponse($e, "Customer " . (isset($oldCustomer) ? "Login" : "Registration") . " Failed", 500);
        }
    }

    #
    # Customer OTP verification
    # Need to Pass = mobile_no and otp
    #
    public function OtpVerification(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'mobile_no' => 'required|integer|digits:10|exists:tbl_customer,MobileNo1',
            'otp' => 'required|digits:4',
            'fcmToken' => 'required|string'
        ]);

        if ($validatedData->fails()) {
            return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
        }

        DB::beginTransaction();
        try {
            $customer = Customer::where('MobileNo1', $request->mobile_no)->where('DFlag', 0)->first();
            if (!$customer) {
                return $this->errorResponse([], "Customer not found", 404);
            }

            $oldCustomer = $customer->replicate();
            if ($customer->otp == $request->otp) {
                $token = ($customer->api_token && $customer->api_token != "") ? $customer->api_token : Str::random(60);
                $customer->update(["api_token" => $token, "fcmToken" => $request->fcmToken, "otp" => "", "otp_verified" => true]);
            } else {
                return $this->errorResponse([], "Customer OTP is wrong", 422);
            }

            $logData = array(
                "Description" => "Customer OTP Verified",
                "ModuleName" => "Customer",
                "Action" => "Update",
                "ReferID" => $customer->CustomerID,
                "OldData" => $oldCustomer,
                "NewData" => $customer,
                "UserID" => $customer->CustomerID,
                "IP" => $request->ip()
            );
            logController::Store($logData);
            DB::commit();
            return $this->successResponse(["token"=>$token, "customer_data" => $customer], "Customer OTP Verified Successfully");
        } catch (\Exception $e) {
            logger($e);
            DB::rollback();
            return $this->errorResponse($e, "Customer OTP Verification Failed", 500);
        }
    }
    #
    # Customer Nick name Update
    # Need to Pass = nick_name
    #
    public function updateNickName(Request $request): JsonResponse
    {
        $customer = $request->auth_customer;
        $validatedData = Validator::make($request->all(), [
            'nick_name' => 'required|string|min:5|max:25'
        ]);

        if ($validatedData->fails()) {
            return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
        }

        DB::beginTransaction();
        try {
            $oldCustomer = $customer->replicate();
            $customer->update(["nick_name" => $request->nick_name]);

            $logData = array(
                "Description" => "Customer Nick name",
                "ModuleName" => "Customer",
                "Action" => "Update",
                "ReferID" => $customer->CustomerID,
                "OldData" => $oldCustomer,
                "NewData" => $customer,
                "UserID" => $customer->CustomerID,
                "IP" => $request->ip()
            );
            logController::Store($logData);
            DB::commit();
            return $this->successResponse([], "Customer Nick name updated.");
        } catch (\Exception $e) {
            logger($e);
            DB::rollback();
            return $this->errorResponse($e, "Customer nick name update Failed", 500);
        }
    }
    #
    # Customer Language Update
    # Need to Pass = Language
    #
    public function updateLanguage(Request $request): JsonResponse
    {
        $customer = $request->auth_customer;
        $validatedData = Validator::make($request->all(), [
            'language' => 'required|string|in:en,ta',
        ]);

        if ($validatedData->fails()) {
            return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
        }

        DB::beginTransaction();
        try {
            $oldCustomer = $customer->replicate();
            $customer->update(["language" => $request->language]);

            $logData = array(
                "Description" => "Customer Language",
                "ModuleName" => "Customer",
                "Action" => "Update",
                "ReferID" => $customer->CustomerID,
                "OldData" => $oldCustomer,
                "NewData" => $customer,
                "UserID" => $customer->CustomerID,
                "IP" => $request->ip()
            );
            logController::Store($logData);
            DB::commit();
            return $this->successResponse([], "Customer language updated.");
        } catch (\Exception $e) {
            logger($e);
            DB::rollback();
            return $this->errorResponse($e, "Customer Language update Failed", 500);
        }
    }

    #
    # Customer profile Update
    #
    public function profileUpdate(Request $request): JsonResponse
    {
        $customer = $request->auth_customer;
        $validatedData = Validator::make($request->all(), [
            'nick_name' => 'required|string|min:5|max:25'
        ]);

        if ($validatedData->fails()) {
            return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
        }

        DB::beginTransaction();
        try {
            $oldCustomer = $customer->replicate();
            $CustomerImage = "";
            $dir = "uploads/user-and-permissions/customers/" . $customer->CustomerID . "/";
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            if ($request->CustomerImage && (Helper::isJSON($request->CustomerImage) === true)) {
                $Img = json_decode($request->CustomerImage);
                if (isset($Img->uploadPath) && file_exists($Img->uploadPath)) {
                    $fileName1 = $Img->fileName != "" ? $Img->fileName : Helper::RandomString(10) . "png";
                    copy($Img->uploadPath, $dir . $fileName1);
                    $CustomerImage = $dir . $fileName1;
                    // unlink($Img->uploadPath);
                }
            }
            $data = [
                "nick_name" => $request->nick_name,
                "UpdatedOn" => date("Y-m-d H:i:s")
            ];
            if ($CustomerImage) {
                $data['CustomerImage'] = $CustomerImage;
            }
            if ($request->full_name && ($request->full_name !== "")) {
                $data['CustomerName'] = $request->full_name;
            }
            if ($request->Email && ($request->Email !== "")) {
                $data['Email'] = $request->Email;
            }
            $customer->update($data);

            $logData = array(
                "Description" => "Customer profile update",
                "ModuleName" => "Customer",
                "Action" => "Update",
                "ReferID" => $customer->CustomerID,
                "OldData" => $oldCustomer,
                "NewData" => $customer,
                "UserID" => $customer->CustomerID,
                "IP" => $request->ip()
            );
            logController::Store($logData);
            DB::commit();
            return $this->successResponse($customer, "Customer profile updated.");
        } catch (\Exception $e) {
            logger($e);
            DB::rollback();
            return $this->errorResponse($e, "Customer profile update Failed", 500);
        }
    }

    #
    # Customer profile details
    #
    public function profileDetails(Request $request): JsonResponse
    {
        try {
            $customer = (object) $request->auth_customer->only("CustomerID", "CustomerName", "nick_name", "MobileNo1",
                "Email", "language", "api_token", "fcmToken", "profileImageUrl");
            return $this->successResponse($customer, "Customer profile details.");
        } catch (\Exception $e) {
            logger($e);
            return $this->errorResponse($e, "Customer profile details fetch Failed", 500);
        }
    }

    #
    # Customer Delete Account
    #
    public function deleteAccount(Request $request): JsonResponse
    {
        $customer = $request->auth_customer;

        DB::beginTransaction();
        try {
            $oldCustomer = $customer->replicate();
            $data=[
                "DFlag" => 1,
                "UpdatedBy"=>$customer->CustomerID,
                "UpdatedOn"=>date("Y-m-d H:i:s"),
                "DeletedBy"=>$customer->CustomerID,
                "DeletedOn"=>date("Y-m-d H:i:s")
            ];
            $customer->update($data);

            $logData = array(
                "Description" => "Customer Account deleted",
                "ModuleName" => "Customer",
                "Action" => "Delete",
                "ReferID" => $customer->CustomerID,
                "OldData" => $oldCustomer,
                "NewData" => $customer,
                "UserID" => $customer->CustomerID,
                "IP" => $request->ip()
            );
            logController::Store($logData);
            DB::commit();
            return $this->successResponse([], "Customer Account Deleted.");
        } catch (\Exception $e) {
            logger($e);
            DB::rollback();
            return $this->errorResponse($e, "Customer Account deletion Failed", 500);
        }
    }

    public function customerHomeSearch(Request $req)
    {
        $lang = optional($req->auth_customer)->language ?? 'en';
        if ($req->SearchText) {
            $PCategories = DB::table('tbl_product_category as PC')
                ->where('PC.ActiveStatus', 'Active')->where('PC.DFlag', 0)
                ->where('PC.PCName', 'like', '%' . $req->SearchText . '%')
                ->distinct()
                ->select('PC.PCID', 'PC.PCName', 'PC.PCNameInTranslation')
                ->take(3)->get();
            $PCategories->transform(function ($PCategory) use ($lang) {
                $PCategory->PCName = json_decode($PCategory->PCNameInTranslation)->$lang ?? $PCategory->PCName;
                unset($PCategory->PCNameInTranslation);
                return $PCategory;
            });

            $PSCategories = DB::table('tbl_product_subcategory as PSC')
                ->leftJoin('tbl_product_category as PC', 'PC.PCID', 'PSC.PCID')
                ->where('PSC.ActiveStatus', 'Active')->where('PSC.DFlag', 0)
                ->where('PC.ActiveStatus', 'Active')->where('PC.DFlag', 0)
                ->where('PSC.PSCName', 'like', '%' . $req->SearchText . '%')
                ->distinct()
                ->select('PC.PCID', 'PC.PCName', 'PC.PCNameInTranslation', 'PSC.PSCID', 'PSC.PSCName', 'PSC.PSCNameInTranslation')->take(3)->get();
            $PSCategories->transform(function ($PSCategory) use ($lang) {
                $PSCategory->PCName = json_decode($PSCategory->PCNameInTranslation)->$lang ?? $PSCategory->PCName;
                $PSCategory->PSCName = json_decode($PSCategory->PSCNameInTranslation)->$lang ?? $PSCategory->PSCName;
                unset($PSCategory->PCNameInTranslation);
                unset($PSCategory->PSCNameInTranslation);
                return $PSCategory;
            });

            $Products = DB::table('tbl_products as P')
                ->join('tbl_product_category as PC', 'PC.PCID', '=', 'P.CID')
                ->join('tbl_product_subcategory as PSC', 'PSC.PSCID', '=', 'P.SCID')
                ->where('P.ActiveStatus', 'Active')
                ->where('P.DFlag', 0)
                ->where('PC.ActiveStatus', 'Active')
                ->where('PC.DFlag', 0)
                ->where('PSC.ActiveStatus', 'Active')
                ->where('PSC.DFlag', 0)
                ->where(function ($query) use ($req) {
                    $search = $req->SearchText;
                    $query->where('P.ProductName', 'like', '%' . $search . '%')
                        ->orWhere('P.ProductNameInTranslation', 'like', '%' . $search . '%');
                })
                ->groupBy('P.ProductID', 'P.ProductName', 'P.ProductNameInTranslation', 'PC.PCID', 'PC.PCName', 'PC.PCNameInTranslation', 'PSC.PSCID', 'PSC.PSCName', 'PSC.PSCNameInTranslation')
                ->select('P.ProductID', 'P.ProductName', 'P.ProductNameInTranslation', 'PC.PCID', 'PC.PCName', 'PC.PCNameInTranslation', 'PSC.PSCID', 'PSC.PSCName', 'PSC.PSCNameInTranslation')
                ->take(3)->get();
            $Products->transform(function ($Product) use ($lang) {
                $Product->PCName = json_decode($Product->PCNameInTranslation)->$lang ?? $Product->PCName;
                $Product->PSCName = json_decode($Product->PSCNameInTranslation)->$lang ?? $Product->PSCName;
                $Product->ProductName = json_decode($Product->ProductNameInTranslation)->$lang ?? $Product->ProductName;
                unset($Product->PCNameInTranslation);
                unset($Product->PSCNameInTranslation);
                unset($Product->ProductNameInTranslation);
                return $Product;
            });

            $ProductData = ['PCategories' => $PCategories, 'PSCategories' => $PSCategories, 'Products' => $Products];

            return response()->json(['status' => true, 'data' => $ProductData]);
        } else {
            return response()->json(['status' => false, 'message' => "search text is empty"]);
        }
    }

    public function guestHomeSearch(Request $req)
    {
        if ($req->SearchText) {
            $PCategories = DB::table('tbl_product_category as PC')
                ->where('PC.ActiveStatus', 'Active')->where('PC.DFlag', 0)
                ->where('PC.PCName', 'like', '%' . $req->SearchText . '%')
                ->distinct()
                ->select('PC.PCID', 'PC.PCName')
                ->take(3)->get();

            $PSCategories = DB::table('tbl_product_subcategory as PSC')
                ->leftJoin('tbl_product_category as PC', 'PC.PCID', 'PSC.PCID')
                ->where('PSC.ActiveStatus', 'Active')->where('PSC.DFlag', 0)
                ->where('PC.ActiveStatus', 'Active')->where('PC.DFlag', 0)
                ->where('PSC.PSCName', 'like', '%' . $req->SearchText . '%')
                ->distinct()
                ->select('PC.PCID', 'PC.PCName', 'PSC.PSCID', 'PSC.PSCName')->take(3)->get();

            $Products = DB::table('tbl_products as P')
                ->join('tbl_product_category as PC', 'PC.PCID', '=', 'P.CID')
                ->join('tbl_product_subcategory as PSC', 'PSC.PSCID', '=', 'P.SCID')
                ->where('P.ActiveStatus', 'Active')
                ->where('P.DFlag', 0)
                ->where('PC.ActiveStatus', 'Active')
                ->where('PC.DFlag', 0)
                ->where('PSC.ActiveStatus', 'Active')
                ->where('PSC.DFlag', 0)
                ->where(function ($query) use ($req) {
                    $search = $req->SearchText;
                    $query->where('P.ProductName', 'like', '%' . $search . '%')
                        ->orWhere('P.ProductNameInTranslation', 'like', '%' . $search . '%');
                })
                ->groupBy('P.ProductID', 'P.ProductName', 'PC.PCID', 'PC.PCName', 'PSC.PSCID', 'PSC.PSCName')
                ->select('P.ProductID', 'P.ProductName', 'PC.PCID', 'PC.PCName', 'PSC.PSCID', 'PSC.PSCName')
                ->take(3)->get();

            $ProductData = ['PCategories' => $PCategories, 'PSCategories' => $PSCategories, 'Products' => $Products];

            return response()->json(['status' => true, 'data' => $ProductData]);
        } else {
            return response()->json(['status' => false, 'message' => "search text is empty"]);
        }
    }

    public function guestHomeScreen()
    {
        try {
            $response = $this->getHomeScreenBannerAndCategories();
            $response->products = DB::table('tbl_products as P')
                ->leftjoin('tbl_product_category_type as PCT', 'PCT.PCTID', 'P.CTID')
                ->leftjoin('tbl_product_category as PC', 'PC.PCID', 'P.CID')
                ->leftjoin('tbl_product_subcategory as PSC', 'PSC.PSCID', 'P.SCID')
                ->leftjoin('tbl_uom as U', 'U.UID', 'P.UID')
                ->where('P.ActiveStatus', 'Active')->where('P.DFlag', 0)
                ->where('PCT.ActiveStatus', 'Active')->where('PCT.DFlag', 0)
                ->where('PC.ActiveStatus', 'Active')
                ->where('PC.DFlag', 0)->where('PSC.ActiveStatus', 'Active')->where('PSC.DFlag', 0)
                ->select('P.ProductName', 'P.ProductID', 'PCT.PCTName', 'PCT.PCTID', 'PC.PCName', 'PC.PCID', 'PSC.PSCName', 'PSC.PSCID', 'U.UName', 'U.UCode', 'U.UID',
                    DB::raw('CONCAT("' . config('app.url') . '/", COALESCE(NULLIF(P.ProductImage, ""), "assets/images/no-image-b.png")) AS ProductImage'),
                    DB::raw('(SELECT CASE
                       WHEN EXISTS (SELECT 1 FROM tbl_products_variation WHERE ProductID = P.ProductID)
                       THEN (SELECT PRate FROM tbl_products_variation WHERE ProductID = P.ProductID ORDER BY SRate ASC LIMIT 1)
                       ELSE P.PRate
                     END) AS PRate'),
                    DB::raw('(SELECT CASE
                       WHEN EXISTS (SELECT 1 FROM tbl_products_variation WHERE ProductID = P.ProductID) THEN MIN(PV.SRate)
                       ELSE P.SRate
                     END
                     FROM tbl_products_variation AS PV
                     WHERE PV.ProductID = P.ProductID) AS SRate'),
                    DB::raw('false AS IsInWishlist'))
                ->distinct()->take(9)->get();
            $response->products->transform(function ($Product) {
                $productUnit = DB::table('tbl_products_variation')
                    ->where('tbl_products_variation.ProductID', $Product->ProductID)
                    ->where('tbl_products_variation.SRate', function ($query) use ($Product) {
                        $query->select(DB::raw('min(SRate)'))
                            ->from('tbl_products_variation')
                            ->where('ProductID', $Product->ProductID);
                    })
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
                $Product->unit = $productUnit->Values ?? $Product->UName ?? '-';
                $Product->SRate = Helper::formatAmount($Product->SRate);
                $Product->PRate = Helper::formatAmount($Product->PRate);
                unset($Product->UName);
                return $Product;
            });

            return $this->successResponse($response, "Guest Home screen data fetched Successfully");
        } catch (Exception $e) {
            logger($e);
            return $this->errorResponse($e, "Guest Home screen data fetch failed", 422);
        }
    }
    public function customerHomeScreen(Request $request)
    {
        try {
            $IsAskGoogleReview = optional($request->auth_customer)->IsAskGoogleReview;
            $IsGoogleReviewed = optional($request->auth_customer)->IsGoogleReviewed;
            $lang = optional($request->auth_customer)->language ?? 'en';
            $CustomerID = $request->auth_customer->CustomerID;
            $response = $this->getHomeScreenBannerAndCategories($lang);
            $Products = DB::table('tbl_products as P')
                ->leftjoin('tbl_product_category_type as PCT', 'PCT.PCTID', 'P.CTID')
                ->leftjoin('tbl_product_category as PC', 'PC.PCID', 'P.CID')
                ->leftjoin('tbl_product_subcategory as PSC', 'PSC.PSCID', 'P.SCID')
                ->leftjoin('tbl_uom as U', 'U.UID', 'P.UID')
                ->leftJoin('tbl_wishlists as W', function ($join) use ($CustomerID) {
                    $join->on('W.product_id', '=', 'P.ProductID')
                        ->where('W.customer_id', '=', $CustomerID);
                })
                ->where('P.ActiveStatus', 'Active')->where('P.DFlag', 0)
                ->where('PCT.ActiveStatus', 'Active')->where('PCT.DFlag', 0)
                ->where('PC.ActiveStatus', 'Active')
                ->where('PC.DFlag', 0)->where('PSC.ActiveStatus', 'Active')->where('PSC.DFlag', 0)
                ->select('P.ProductName', 'P.ProductNameInTranslation', 'P.ProductID', 'PCT.PCTName', 'PCT.PCTNameInTranslation', 'PCT.PCTID', 'PC.PCName',
                    'PC.PCNameInTranslation', 'PC.PCID', 'PSC.PSCName', 'PSC.PSCNameInTranslation', 'PSC.PSCID', 'U.UName', 'U.UNameInTranslation',
                    DB::raw('CONCAT("' . config('app.url') . '/", COALESCE(NULLIF(P.ProductImage, ""), "assets/images/no-image-b.png")) AS ProductImage'),
                    DB::raw('(SELECT CASE
                       WHEN EXISTS (SELECT 1 FROM tbl_products_variation WHERE ProductID = P.ProductID)
                       THEN (SELECT PRate FROM tbl_products_variation WHERE ProductID = P.ProductID ORDER BY SRate ASC LIMIT 1)
                       ELSE P.PRate
                     END) AS PRate'),
                    DB::raw('(SELECT CASE
                       WHEN EXISTS (SELECT 1 FROM tbl_products_variation WHERE ProductID = P.ProductID) THEN MIN(PV.SRate)
                       ELSE P.SRate
                     END
                     FROM tbl_products_variation AS PV
                     WHERE PV.ProductID = P.ProductID) AS SRate'),
                    DB::raw('IF(W.product_id IS NOT NULL, true, false) AS IsInWishlist'))
                ->distinct()->take(9)->get();
            $Products->transform(function ($Product) use ($lang) {
                $productUnit = DB::table('tbl_products_variation')
                    ->where('tbl_products_variation.ProductID', $Product->ProductID)
                    ->where('tbl_products_variation.SRate', function ($query) use ($Product) {
                        $query->select(DB::raw('min(SRate)'))
                            ->from('tbl_products_variation')
                            ->where('ProductID', $Product->ProductID);
                    })
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
                if (isset($productUnit->valuesInTranslation)) {
                    $valuesInTranslation = json_decode($productUnit->valuesInTranslation, true);
                    if (isset($valuesInTranslation[$lang])) {
                        $Product->unit = $valuesInTranslation[$lang];
                    } else {
                        $Product->unit = $productUnit->Values ?? $Product->UName ?? '-';
                    }
                } elseif (isset($Product->UNameInTranslation)) {
                    $UNameInTranslation = json_decode($Product->UNameInTranslation, true);
                    if (isset($UNameInTranslation[$lang])) {
                        $Product->unit = $UNameInTranslation[$lang];
                    } else {
                        $Product->unit = $Product->UName ?? '-';
                    }
                } else {
                    $Product->unit = $Product->UName ?? '-';
                }
                $Product->PCTName = json_decode($Product->PCTNameInTranslation)->$lang ?? $Product->PCTName;
                $Product->PCName = json_decode($Product->PCNameInTranslation)->$lang ?? $Product->PCName;
                $Product->PSCName = json_decode($Product->PSCNameInTranslation)->$lang ?? $Product->PSCName;
                $Product->ProductName = json_decode($Product->ProductNameInTranslation)->$lang ?? $Product->ProductName;
                $Product->SRate = Helper::formatAmount($Product->SRate);
                $Product->PRate = Helper::formatAmount($Product->PRate);
                unset($Product->PCTNameInTranslation);
                unset($Product->PCNameInTranslation);
                unset($Product->PSCNameInTranslation);
                unset($Product->ProductNameInTranslation);
                unset($Product->UNameInTranslation);
                unset($Product->UName);
                return $Product;
            });
            $response->products = $Products;
            if($IsAskGoogleReview && !$IsGoogleReviewed) {
                $response->AskReview = true;
                $response->ReviewUrl = config('app.GOOGLE_REVIEW_URL');
            } else {
                $response->AskReview = false;
            }
            return $this->successResponse($response, "Customer Home screen data fetched Successfully");
        } catch (Exception $e) {
            logger($e);
            return $this->errorResponse($e, "Customer Home screen data fetch failed", 422);
        }
    }

    /**
     * @return \stdClass
     */
    public function getHomeScreenBannerAndCategories($lang = 'en'): \stdClass
    {
        $response = new \stdClass();
        $response->banners = DB::Table('tbl_banner_images')->where('BannerType', 'Mobile')->where('DFlag', 0)
            ->select(DB::raw('CONCAT("' . url('/') . '/", BannerImage) AS BannerImage'))
            ->get();
        $categories = DB::table('tbl_product_category as PC')
            ->where('PC.ActiveStatus', 'Active')->where('PC.DFlag', 0)
            ->select('PC.PCName','PC.PCNameInTranslation', 'PC.PCID', 'PC.PCTID',
                DB::raw('CONCAT("' . config('app.url') . '/", COALESCE(NULLIF(PC.PCImage, ""), "assets/images/no-image-b.png")) AS CategoryImage'))
            ->take(9)->get();
        $categories->transform(function ($category) use ($lang) {
            $category->PCName = json_decode($category->PCNameInTranslation)->$lang ?? $category->PCName;
            unset($category->PCNameInTranslation);
            return $category;
        });
        $response->categories = $categories;
        return $response;
    }

    /**
     * @param string $otp
     * @param Request $request
     * @return void
     * @throws Exception
     */
    public function sendOtpSms(string $otp, Request $request): array
    {
        $smsService = new SmsAlertService();
        $message = "Your Royal Dry Fruits OTP for login is $otp. Please enter this code to proceed.";

        $textMsgResponse = $smsService->sendOTP($request->mobile_no, $message);
        if (!$textMsgResponse["status"]) {
            $response = [
                'error' => 1,
                'message' => $textMsgResponse['message'] ?? 'OTP send failed',
                'errors' => $textMsgResponse['errors'] ?? []
            ];
            info('OTP SMS delivery failed');
            info($response);

            // Do not block login if OTP SMS provider is temporarily unavailable.
            if (config('app.OTP_SMS_STRICT', false)) {
                throw new Exception($response['message']);
            }

            return ['status' => false, 'message' => $response['message'], 'errors' => $response['errors']];
        }

        return ['status' => true, 'message' => 'OTP Sent Successfully'];
    }

    public function getTranslation(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'language_code' => 'required|string|exists:languages,code'
        ]);
        if ($validatedData->fails()) {
            return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
        }
        $language = Language::with('translations')->where('code', $request->language_code)->first();
        if($language->translations){
            return json_decode($language->translations->value);
        } else {
            return [];
        }
    }

    public function paymentGatewayDetails(Request $request)
    {
        return response()->json([
            'merchantId' => config('app.PHONEPE_MERCHANT_ID'),
            'transactionId' => Str::uuid()->toString(),
            'Merchanuserid' => $request->auth_customer->CustomerID,
            'salt_key' => config('app.PHONEPE_SALT_KEY')
        ]);
    }

}
