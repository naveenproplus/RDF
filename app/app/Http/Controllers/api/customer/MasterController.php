<?php

namespace App\Http\Controllers\api\customer;

use App\helper\helper;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\MobileUpdate;
use App\Models\ProductReview;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use mysql_xdevapi\Collection;

class MasterController extends Controller
{
    use ApiResponse;

//    General Data
    public function latestMobileVersion(){
        $query = MobileUpdate::query();

        if (Schema::hasColumn('tbl_mobile_version', 'DFlag')) {
            $query->where('DFlag', 0);
        }
        if (Schema::hasColumn('tbl_mobile_version', 'ActiveStatus')) {
            $query->where('ActiveStatus', 'Active');
        }
        if (Schema::hasColumn('tbl_mobile_version', 'status')) {
            $query->where('status', 'Active');
        }

        $data = $query->first();
        if($data){
            $data->forceUpdate = $data->update_type === "Force" ? 1 : 0;
            $data->Android = in_array($data->update_to, ['Android and IOS', 'Android']) ? 1 : 0;
            $data->IOS = in_array($data->update_to, ['Android and IOS', 'IOS']) ? 1 : 0;
        }
        return [
            'status' => true,
            'data' => $data,
        ];
    }
    public function getGender(request $req){
        $return = [
            'status' => true,
            'data' => Helper::getGender(),
        ];
        return $return;
    }
    public function getCountry(request $req){
        $return = [
            'status' => true,
            'data' => Helper::getCountry(),
        ];
        return $return;
    }

    public function getPostalCode(request $req){
        $return = [
            'status' => true,
            'data' => Helper::getPostalCode(array("CountryID"=>$req->CountryID,"StateID"=>$req->StateID,"DistrictID"=>$req->DistrictID,"TalukID"=>$req->TalukID)),
        ];
        return $return;
    }
    public function getState(request $req){
        $lang = optional($req->auth_customer)->language ?? 'en';
        $states = Helper::getState(array("CountryID"=>$req->CountryID));
        $states = collect($states);
        $states->transform(function ($state) use ($lang){
            $state->StateName = json_decode($state->StateNameInTranslation)->$lang ?? $state->StateName;
            unset($state->StateNameInTranslation, $state->CreatedOn, $state->UpdatedOn, $state->DeletedOn,
                $state->CreatedBy, $state->DeletedBy, $state->UpdatedBy, $state->ActiveStatus, $state->DFlag);
            return $state;
        });

        return [
            'status' => true,
            'data' => $states
        ];
    }
    public function getDistrict(request $req){
        $lang = optional($req->auth_customer)->language ?? 'en';
        $districts = collect(Helper::getDistrict(array("CountryID"=>$req->CountryID,"StateID"=>$req->StateID)));
        $districts->transform(function ($district) use ($lang){
            $district->DistrictName = json_decode($district->DistrictNameInTranslation)->$lang ?? $district->DistrictName;
            unset($district->DistrictNameInTranslation, $district->CreatedOn, $district->UpdatedOn, $district->DeletedOn,
                $district->CreatedBy, $district->DeletedBy, $district->UpdatedBy, $district->ActiveStatus, $district->DFlag);
            return $district;
        });
        return [
            'status' => true,
            'data' => $districts,
        ];
    }

    public function getCity(Request $req){
        $lang = optional($req->auth_customer)->language ?? 'en';
        $data = Helper::getCity(["CountryID" => $req->CountryID,"StateID" => $req->StateID,"DistrictID" => $req->DistrictID,"TalukID" => $req->TalukID,"PostalID" => $req->PostalID,"PostalCode" => $req->PostalCode,]);

        if (isset($data['error'])) {
            return [
                'status' => false,
                'message' => $data['error'],
                'data' => [],
            ];
        } else {
            $cities = collect($data);
            $cities->transform(function ($city) use ($lang){
                $city->CityName = json_decode($city->CityNameInTranslation)->$lang ?? $city->CityName;
                unset($city->CityNameInTranslation, $city->CreatedOn, $city->UpdatedOn, $city->DeletedOn,
                    $city->CreatedBy, $city->DeletedBy, $city->UpdatedBy, $city->ActiveStatus, $city->DFlag);
                return $city;
            });
            return [
                'status' => true,
                'data' => $cities,
            ];
        }
    }
    public function getPriceRanges(){
        return [
            'status' => true,
            'data' => collect([["key" => "0,200", "value" => "₹ 0 - 200"], ["key" => "200,400", "value" => "₹ 200 - 400"], ["key" => "400,600", "value" => "₹ 400 - 600"],
                ["key" => "600,1000", "value" => "₹ 600 - 1000"], ["key" => "1000,2000", "value" => "₹ 1000 - 2000"], ["key" => "2000,4000", "value" => "₹ 2000 - 4000"],
                ["key" => "4000,6000", "value" => "₹ 4000 - 6000"], ["key" => "6000,8000", "value" => "₹ 6000 - 8000"], ["key" => "8000,1000000", "value" => "₹ 8000 & more"]])
        ];
    }

//    Product Data

    public function GetCustomerCategoryType(Request $req){
        $lang = optional($req->auth_customer)->language ?? 'en';
        $pageNo = $req->PageNo ?? 1;
        $perPage = 15;

        $Category = DB::table('tbl_product_category_type')
            ->where('ActiveStatus', 'Active')->where('DFlag', 0)
            ->when($req->has('SearchText') && !empty($req->SearchText), function ($query) use ($req) {
                return $query->where('PCTName', 'like', '%' . $req->SearchText . '%');
            });

        $result = $Category->select('PCTName', 'PCTNameInTranslation', 'PCTID', DB::raw('CONCAT("' . config('app.url') . '/", COALESCE(NULLIF(PCTImage, ""), "assets/images/no-image-b.png")) AS CategoryTypeImage'))
            ->paginate($perPage, ['*'], 'page', $pageNo);

        $result->transform(function ($result) use ($lang) {
            $translations = json_decode($result->PCTNameInTranslation);
            $result->PCTName = $translations->$lang ?? $result->PCTName;
            unset($result->PCTNameInTranslation);
            return $result;
        });

        return response()->json([
            'status' => true,
            'data' => $result->items(),
            'CurrentPage' => $result->currentPage(),
            'LastPage' => $result->lastPage(),
        ]);
    }
    public function GetCategoryType(Request $req){
        $pageNo = $req->PageNo ?? 1;
        $perPage = 15;

        $Category = DB::table('tbl_product_category_type')
            ->where('ActiveStatus', 'Active')->where('DFlag', 0)
            ->when($req->has('SearchText') && !empty($req->SearchText), function ($query) use ($req) {
                return $query->where('PCTName', 'like', '%' . $req->SearchText . '%');
            });

        $result = $Category->select('PCTName', 'PCTID', DB::raw('CONCAT("' . config('app.url') . '/", COALESCE(NULLIF(PCTImage, ""), "assets/images/no-image-b.png")) AS CategoryTypeImage'))
            ->paginate($perPage, ['*'], 'page', $pageNo);

        return response()->json([
            'status' => true,
            'data' => $result->items(),
            'CurrentPage' => $result->currentPage(),
            'LastPage' => $result->lastPage(),
        ]);
    }

    public function GetCustomerCategory(Request $req){
        $lang = optional($req->auth_customer)->language ?? 'en';
        $pageNo = $req->PageNo ?? 1;
        $perPage = 15;

        $Category = DB::table('tbl_product_category as PC')
            ->where('PC.ActiveStatus', 'Active')->where('PC.DFlag', 0)
            ->when($req->PCTID, function ($query) use ($req) {
                $PCTID = $req->PCTID;
                $PCTIDs = is_array($PCTID) ? $PCTID : [$PCTID];
                return $query->whereIn('PC.PCTID', $PCTIDs);
            })
            ->when($req->has('SearchText') && !empty($req->SearchText), function ($query) use ($req) {
                return $query->where('PC.PCName', 'like', '%' . $req->SearchText . '%');
            });

        $result = $Category->select('PC.PCName', 'PC.PCNameInTranslation', 'PC.PCID', 'PC.PCTID', DB::raw('CONCAT("' . config('app.url') . '/", COALESCE(NULLIF(PC.PCImage, ""), "assets/images/no-image-b.png")) AS CategoryImage'))->paginate($perPage, ['*'], 'page', $pageNo);
        $result->transform(function ($result) use ($lang) {
            $translations = json_decode($result->PCNameInTranslation);
            $result->PCName = $translations->$lang ?? $result->PCName;
            unset($result->PCNameInTranslation);
            return $result;
        });
        return response()->json([
            'status' => true,
            'data' => $result->items(),
            'CurrentPage' => $result->currentPage(),
            'LastPage' => $result->lastPage(),
        ]);
    }

    public function GetCategory(Request $req){
        $pageNo = $req->PageNo ?? 1;
        $perPage = 15;

        $Category = DB::table('tbl_product_category as PC')
            ->where('PC.ActiveStatus', 'Active')->where('PC.DFlag', 0)
            ->when($req->PCTID, function ($query) use ($req) {
                $PCTID = $req->PCTID;
                $PCTIDs = is_array($PCTID) ? $PCTID : [$PCTID];
                return $query->whereIn('PC.PCTID', $PCTIDs);
            })
            ->when($req->has('SearchText') && !empty($req->SearchText), function ($query) use ($req) {
                return $query->where('PC.PCName', 'like', '%' . $req->SearchText . '%');
            });

        $result = $Category->select('PC.PCName', 'PC.PCID', 'PC.PCTID', DB::raw('CONCAT("' . config('app.url') . '/", COALESCE(NULLIF(PC.PCImage, ""), "assets/images/no-image-b.png")) AS CategoryImage'))->paginate($perPage, ['*'], 'page', $pageNo);

        return response()->json([
            'status' => true,
            'data' => $result->items(),
            'CurrentPage' => $result->currentPage(),
            'LastPage' => $result->lastPage(),
        ]);
    }
    public function GetCustomerSubCategory(Request $req){
        $lang = optional($req->auth_customer)->language ?? 'en';
        $pageNo = $req->PageNo ?? 1;
        $perPage = 15;

        $SubCategory = DB::table('tbl_product_subcategory as PSC')
            ->join('tbl_product_category as PC', 'PC.PCID', 'PSC.PCID')
            ->where('PSC.ActiveStatus', 'Active')->where('PSC.DFlag', 0)
            ->where('PC.ActiveStatus', 'Active')->where('PC.DFlag', 0)
            ->when($req->PCTID, function ($query) use ($req) {
                $PCTID = $req->PCTID;
                $PCTIDs = is_array($PCTID) ? $PCTID : [$PCTID];
                return $query->whereIn('PSC.PCTID', $PCTIDs);
            })
            ->when($req->PCID, function ($query) use ($req) {
                $PCID = $req->PCID;
                $PCIDs = is_array($PCID) ? $PCID : [$PCID];
                return $query->whereIn('PSC.PCID', $PCIDs);
            })
            ->when($req->has('SearchText') && !empty($req->SearchText), function ($query) use ($req) {
                return $query->where('PSC.PSCName', 'like', '%' . $req->SearchText . '%');
            });
        $result = $SubCategory->select('PSC.PSCName', 'PSC.PSCNameInTranslation', 'PSC.PSCID', 'PC.PCID', 'PC.PCTID',
            'PC.PCName', 'PC.PCNameInTranslation', DB::raw('CONCAT("' . url('/') . '/", COALESCE(NULLIF(PSC.PSCImage, ""), "assets/images/no-image-b.png")) AS SubCategoryImage'))
            ->paginate($perPage, ['*'], 'page', $pageNo);

        $result->transform(function ($result) use ($lang) {
            $PSCNameInTranslation = json_decode($result->PSCNameInTranslation);
            $PCNameInTranslation = json_decode($result->PCNameInTranslation);
            $result->PSCName = $PSCNameInTranslation->$lang ?? $result->PSCName;
            $result->PCName = $PCNameInTranslation->$lang ?? $result->PCName;
            unset($result->PSCNameInTranslation);
            unset($result->PCNameInTranslation);
            return $result;
        });

        return response()->json([
            'status' => true,
            'data' => $result->items(),
            'CurrentPage' => $result->currentPage(),
            'LastPage' => $result->lastPage(),
        ]);
    }
    public function GetSubCategory(Request $req){
        $pageNo = $req->PageNo ?? 1;
        $perPage = 15;

        $SubCategory = DB::table('tbl_product_subcategory as PSC')
            ->join('tbl_product_category as PC', 'PC.PCID', 'PSC.PCID')
            ->where('PSC.ActiveStatus', 'Active')->where('PSC.DFlag', 0)
            ->where('PC.ActiveStatus', 'Active')->where('PC.DFlag', 0)
            ->when($req->PCTID, function ($query) use ($req) {
                $PCTID = $req->PCTID;
                $PCTIDs = is_array($PCTID) ? $PCTID : [$PCTID];
                return $query->whereIn('PSC.PCTID', $PCTIDs);
            })
            ->when($req->PCID, function ($query) use ($req) {
                $PCID = $req->PCID;
                $PCIDs = is_array($PCID) ? $PCID : [$PCID];
                return $query->whereIn('PSC.PCID', $PCIDs);
            })
            ->when($req->has('SearchText') && !empty($req->SearchText), function ($query) use ($req) {
                return $query->where('PSC.PSCName', 'like', '%' . $req->SearchText . '%');
            });
        $result = $SubCategory->select('PSC.PSCName', 'PSC.PSCID','PC.PCID','PC.PCTID','PC.PCName', DB::raw('CONCAT("' . url('/') . '/", COALESCE(NULLIF(PSC.PSCImage, ""), "assets/images/no-image-b.png")) AS SubCategoryImage'))->paginate($perPage, ['*'], 'page', $pageNo);

        return response()->json([
            'status' => true,
            'data' => $result->items(),
            'CurrentPage' => $result->currentPage(),
            'LastPage' => $result->lastPage(),
        ]);
    }

    public function getProducts(Request $req)
    {
        try {
            $pageNo = $req->PageNo ?? 1;
            $perPage = 15;

            $products = DB::table('tbl_products as P')
                ->join('tbl_product_category_type as PCT', 'PCT.PCTID', 'P.CTID')->join('tbl_product_category as PC', 'PC.PCID', 'P.CID')
                ->join('tbl_product_subcategory as PSC', 'PSC.PSCID', 'P.SCID')->join('tbl_uom as U', 'U.UID', 'P.UID')
                ->where('P.ActiveStatus', 'Active')->where('P.DFlag', 0)
                ->where('PCT.ActiveStatus', 'Active')->where('PCT.DFlag', 0)
                ->where('PC.ActiveStatus', 'Active')
                ->where('PC.DFlag', 0)->where('PSC.ActiveStatus', 'Active')->where('PSC.DFlag', 0)
                ->when($req->PID, function ($query) use ($req) {
                    $PID = $req->PID;
                    $PIDs = is_array($PID) ? $PID : [$PID];
                    return $query->whereIn('P.ProductID', $PIDs);
                })
                ->when($req->PCTID, function ($query) use ($req) {
                    $PCTID = $req->PCTID;
                    $PCTIDs = is_array($PCTID) ? $PCTID : [$PCTID];
                    return $query->whereIn('P.CTID', $PCTIDs);
                })
                ->when($req->PCID, function ($query) use ($req) {
                    $PCID = $req->PCID;
                    $PCIDs = is_array($PCID) ? $PCID : [$PCID];
                    return $query->whereIn('P.CID', $PCIDs);
                })
                ->when($req->PSCID, function ($query) use ($req) {
                    $PSCID = $req->PSCID;
                    $PSCIDs = is_array($PSCID) ? $PSCID : [$PSCID];
                    return $query->whereIn('P.SCID', $PSCIDs);
                })
                ->when($req->has('SearchText') && !empty($req->SearchText), function ($query) use ($req) {
                    $search = $req->SearchText;
                    return $query->where(function ($q) use ($search) {
                        $q->where('P.ProductName', 'like', '%' . $search . '%')
                            ->orWhere('P.ProductNameInTranslation', 'like', '%' . $search . '%');
                    });
                });

            $result = $products->select('P.ProductName', 'P.ProductID', 'PCT.PCTName', 'PCT.PCTID', 'PC.PCName', 'PC.PCID', 'PSC.PSCName', 'PSC.PSCID', 'U.UName', 'U.UCode', 'U.UID',
                DB::raw('CONCAT("' . config('app.url') . '/", COALESCE(NULLIF(P.ProductImage, ""), "assets/images/no-image-b.png")) AS ProductImage'),
                DB::raw('(SELECT CASE
                       WHEN EXISTS (SELECT 1 FROM tbl_products_variation WHERE ProductID = P.ProductID AND DFlag = 0)
                       THEN (SELECT PRate FROM tbl_products_variation WHERE ProductID = P.ProductID AND DFlag = 0 ORDER BY SRate ASC LIMIT 1)
                       ELSE P.PRate
                     END) AS PRate'),
                DB::raw('(SELECT CASE
                       WHEN EXISTS (SELECT 1 FROM tbl_products_variation WHERE ProductID = P.ProductID AND DFlag = 0) THEN MIN(PV.SRate)
                       ELSE P.SRate
                     END
                     FROM tbl_products_variation AS PV
                     WHERE PV.ProductID = P.ProductID AND PV.DFlag = 0) AS SRate'))
                ->when($req->price_filter, function ($query) use ($req) {
                    $priceRange = explode(',', $req->price_filter);
                    return $query->whereRaw('(SELECT CASE
                           WHEN EXISTS (SELECT 1 FROM tbl_products_variation WHERE ProductID = P.ProductID AND DFlag = 0) THEN MIN(PV.SRate)
                           ELSE P.SRate
                         END
                         FROM tbl_products_variation AS PV
                         WHERE PV.ProductID = P.ProductID AND PV.DFlag = 0) BETWEEN ? AND ?', $priceRange);
                })
                ->when($req->has('sorting_by') && in_array($req->sorting_by, ['SRate', 'ProductName']), function ($query) use ($req) {
                    $sortingKey = $req->sorting_by;
                    $direction = $req->has('sorting_direction') && in_array($req->sorting_direction, ['asc', 'desc']) ? $req->sorting_direction : 'asc';
                    return $query->orderBy($sortingKey, $direction);
                })
                ->paginate($perPage, ['*'], 'page', $pageNo);

            $result->getCollection()->map(function ($item) {
                $item->wishlist = false;
                $item->PRate = Helper::formatAmount($item->PRate);
                $item->SRate = Helper::formatAmount($item->SRate);
                $item->unit = DB::table('tbl_products_variation')
                    ->where('tbl_products_variation.ProductID', $item->ProductID)
                    ->where('tbl_products_variation.DFlag', 0)
                    ->where('tbl_products_variation.SRate', function ($query) use ($item) {
                        $query->select(DB::raw('min(SRate)'))
                            ->from('tbl_products_variation')
                            ->where('ProductID', $item->ProductID)
                            ->where('DFlag', 0);
                    })
                    ->leftJoin('tbl_products_variation_details as D', function ($join) {
                        $join->on('tbl_products_variation.VariationID', '=', 'D.VariationID');
                    })
                    ->leftJoin('tbl_attributes_details as AD', function ($join) {
                        $join->on('AD.ValueID', '=', 'D.AttributeValueID')
                            ->on('AD.AttrID', '=', 'D.AttributeID');
                    })
                    ->leftJoin('tbl_attributes as A', 'A.AttrID', '=', 'AD.AttrID')
                    ->pluck('AD.Values')
                    ->first() ?? ($item->UName ? "In $item->UName" : '-');
                return $item;
            });

            return response()->json([
                'status' => true,
                'data' => $result->items(),
                'CurrentPage' => $result->currentPage(),
                'LastPage' => $result->lastPage(),
            ]);
        } catch (Exception $e) {
            logger($e);
            return $this->errorResponse($e, "Can't get products list", 500);
        }
    }

    public function getProduct(Request $req)
    {
        try {
            $validatedData = Validator::make($req->all(), [
                'ProductID' => 'required|string|exists:tbl_products,ProductID'
            ]);

            if ($validatedData->fails()) {
                return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
            }

            $product = DB::table('tbl_products as P')
                ->join('tbl_product_category_type as PCT', 'PCT.PCTID', 'P.CTID')
                ->join('tbl_product_category as PC', 'PC.PCID', 'P.CID')
                ->join('tbl_product_subcategory as PSC', 'PSC.PSCID', 'P.SCID')
                ->join('tbl_uom as U', 'U.UID', 'P.UID')
                ->where('P.ActiveStatus', 'Active')
                ->where('P.DFlag', 0)
                ->where('PCT.ActiveStatus', 'Active')
                ->where('PCT.DFlag', 0)
                ->where('PC.ActiveStatus', 'Active')
                ->where('PC.DFlag', 0)
                ->where('PSC.ActiveStatus', 'Active')
                ->where('PSC.DFlag', 0)
                ->where('P.ProductID', $req->ProductID)
                ->first();

            if (!$product) {
                return $this->errorResponse('Product not found', 'Product not found', 404);
            }

            $reviews = ProductReview::with('customerDetails')->where('ProductID', $product->ProductID)
                ->orderBy('CreatedOn', 'desc')
                ->get()
                ->map(function ($review) {
                    $review->CreatedOn = date('M d, Y', strtotime($review->CreatedOn));
                    $review->isHelpful = false;
                    return $review;
                });

            $ratings = ProductReview::where('ProductID', $product->ProductID)
                ->avg('rating');

            $relatedProductIds = unserialize($product->RelatedProducts);

            if (!is_array($relatedProductIds)) {
                $relatedProductIds = explode(',', $relatedProductIds);
            }

            $relatedProducts = DB::table('tbl_products as P')
                ->leftJoin('tbl_uom as U', 'U.UID', 'P.UID')
                ->leftJoin('tbl_products_variation', 'tbl_products_variation.ProductID', '=', 'P.ProductID')
                ->whereIn('P.ProductID', $relatedProductIds)
                ->where('P.ActiveStatus', 'Active')
                ->where('P.DFlag', 0)
                ->distinct()
                ->select('P.ProductID', 'P.ProductName', 'U.UName', 'U.UCode', 'U.UID',
                    DB::raw("CONCAT('" . config('app.url') . "/', COALESCE(P.ProductImage, 'assets/images/no-image-b.png')) as ProductImage"),
                    DB::raw('COALESCE((SELECT PRate FROM tbl_products_variation WHERE tbl_products_variation.ProductID = P.ProductID ORDER BY SRate LIMIT 1), P.PRate) as PRate'),
                    DB::raw('COALESCE((SELECT SRate FROM tbl_products_variation WHERE tbl_products_variation.ProductID = P.ProductID ORDER BY SRate LIMIT 1), P.SRate) as SRate'),
                    DB::raw('false AS IsInWishlist')
                )
                ->get();

            $relatedProducts->transform(function ($item) {
                $item->PRate = Helper::formatAmount($item->PRate);
                $item->SRate = Helper::formatAmount($item->SRate);
                $item->unit = DB::table('tbl_products_variation')
                    ->where('tbl_products_variation.ProductID', $item->ProductID)
                    ->where('tbl_products_variation.DFlag', 0)
                    ->where('tbl_products_variation.SRate', function ($query) use ($item) {
                        $query->select(DB::raw('min(SRate)'))
                            ->from('tbl_products_variation')
                            ->where('ProductID', $item->ProductID)
                            ->where('DFlag', 0);
                    })
                    ->leftJoin('tbl_products_variation_details as D', function ($join) {
                        $join->on('tbl_products_variation.VariationID', '=', 'D.VariationID');
                    })
                    ->leftJoin('tbl_attributes_details as AD', function ($join) {
                        $join->on('AD.ValueID', '=', 'D.AttributeValueID')
                            ->on('AD.AttrID', '=', 'D.AttributeID');
                    })
                    ->leftJoin('tbl_attributes as A', 'A.AttrID', '=', 'AD.AttrID')
                    ->pluck('AD.Values')
                    ->first() ?? ($item->UName ? "In $item->UName" : '-');
                return $item;
            });


            $result = (object)[
                'ProductName' => $product->ProductName,
                'ProductID' => $product->ProductID,
                'PCTName' => $product->PCTName,
                'PCTID' => $product->PCTID,
                'PCName' => $product->PCName,
                'PCID' => $product->PCID,
                'PSCName' => $product->PSCName,
                'PSCID' => $product->PSCID,
                'UName' => $product->UName,
                'UCode' => $product->UCode,
                'UID' => $product->UID,
                'ProductImage' => Helper::apiCheckImageExistsUrl($product->ProductImage ?? ''),
                'PRate' => Helper::formatAmount(DB::table('tbl_products_variation')->where('ProductID', $product->ProductID)->where('DFlag', 0)->exists() ?
                    DB::table('tbl_products_variation')->where('ProductID', $product->ProductID)->where('DFlag', 0)->orderBy('SRate')->value('PRate') :
                    $product->PRate),
                'SRate' => Helper::formatAmount(DB::table('tbl_products_variation')->where('ProductID', $product->ProductID)->where('DFlag', 0)->exists() ?
                    DB::table('tbl_products_variation')->where('ProductID', $product->ProductID)->where('DFlag', 0)->min('SRate') :
                    $product->SRate),
                'unit' => DB::table('tbl_products_variation')
                    ->where('tbl_products_variation.ProductID', $product->ProductID)
                    ->where('tbl_products_variation.DFlag', 0)
                    ->where('tbl_products_variation.SRate', function ($query) use ($product) {
                        $query->select(DB::raw('min(SRate)'))
                            ->from('tbl_products_variation')
                            ->where('ProductID', $product->ProductID)
                            ->where('DFlag', 0);
                    })
                    ->leftJoin('tbl_products_variation_details as D', function ($join) {
                        $join->on('tbl_products_variation.VariationID', '=', 'D.VariationID');
                    })
                    ->leftJoin('tbl_attributes_details as AD', function ($join) {
                        $join->on('AD.ValueID', '=', 'D.AttributeValueID')
                            ->on('AD.AttrID', '=', 'D.AttributeID');
                    })
                    ->leftJoin('tbl_attributes as A', 'A.AttrID', '=', 'AD.AttrID')
                    ->pluck('AD.Values')
                    ->first() ?? ($product->UName ? "In $product->UName" : '-'),
                'reviews' => $reviews,
                'RelatedProducts' => $relatedProducts,
                'ratings' => round($ratings),
                'IsInWishlist' => 0
            ];

            $pGallery = DB::table('tbl_products_gallery')
                ->select('gImage')
                ->where('ProductID', $product->ProductID)
                ->get();

            foreach ($pGallery as $galleryItem) {
                $galleryItem->gImage = Helper::apiCheckImageExistsUrl($galleryItem->gImage);
            }

            $result->gallery = $pGallery;

            $variations = DB::table('tbl_products_variation')
                ->select('VariationID', 'UUID', 'ProductID', 'Slug', 'Title', 'PRate', 'SRate')
                ->where('ProductID', $product->ProductID)
                ->where('DFlag', 0)
                ->get();

            foreach ($variations as $variation) {
                $sql = "SELECT D.DetailID, D.ProductID, D.VariationID, D.AttributeID, A.AttrName, D.AttributeValueID, AD.Values, D.DFlag FROM tbl_products_variation_details as D LEFT JOIN tbl_attributes_details as AD ON AD.ValueID=D.AttributeValueID and AD.AttrID=D.AttributeID LEFT JOIN tbl_attributes as A On A.AttrID=AD.AttrID ";
                $sql .= " Where D.ProductID='" . $product->ProductID . "' and D.VariationID='" . $variation->VariationID . "'";
                $AttributeDetails = DB::select($sql);

                $variation->PRate = Helper::formatAmount($variation->PRate);
                $variation->SRate = Helper::formatAmount($variation->SRate);
                $variation->unit = isset($AttributeDetails[0]) ? ($AttributeDetails[0]->Values) : (($product->UName ? "In $product->UName" : '-') ?? '-');
            }

            $result->variation = $variations;

            return $this->successResponse($result, "Product found!");
        } catch (\Exception $e) {
            logger($e);
            return $this->errorResponse($e, "Can't get product data", 500);
        }
    }

    public function getCustomerProducts(Request $req)
    {
        try {
            $pageNo = $req->PageNo ?? 1;
            $perPage = 15;
            $customerID = $req->auth_customer->CustomerID;
            $lang = optional($req->auth_customer)->language ?? 'en';

            $products = DB::table('tbl_products as P')
                ->leftjoin('tbl_product_category_type as PCT', 'PCT.PCTID', 'P.CTID')->leftjoin('tbl_product_category as PC', 'PC.PCID', 'P.CID')
                ->leftjoin('tbl_product_subcategory as PSC', 'PSC.PSCID', 'P.SCID')->leftjoin('tbl_uom as U', 'U.UID', 'P.UID')
                ->leftJoin('tbl_wishlists as W', function ($join) use ($customerID) {
                    $join->on('W.product_id', '=', 'P.ProductID')
                        ->where('W.customer_id', '=', $customerID);
                })
                ->where('P.ActiveStatus', 'Active')->where('P.DFlag', 0)
                ->where('PCT.ActiveStatus', 'Active')->where('PCT.DFlag', 0)
                ->where('PC.ActiveStatus', 'Active')
                ->where('PC.DFlag', 0)->where('PSC.ActiveStatus', 'Active')->where('PSC.DFlag', 0)
                ->when($req->PID, function ($query) use ($req) {
                    $PID = $req->PID;
                    $PIDs = is_array($PID) ? $PID : [$PID];
                    return $query->whereIn('P.ProductID', $PIDs);
                })
                ->when($req->PCTID, function ($query) use ($req) {
                    $PCTID = $req->PCTID;
                    $PCTIDs = is_array($PCTID) ? $PCTID : [$PCTID];
                    return $query->whereIn('P.CTID', $PCTIDs);
                })
                ->when($req->PCID, function ($query) use ($req) {
                    $PCID = $req->PCID;
                    $PCIDs = is_array($PCID) ? $PCID : [$PCID];
                    return $query->whereIn('P.CID', $PCIDs);
                })
                ->when($req->PSCID, function ($query) use ($req) {
                    $PSCID = $req->PSCID;
                    $PSCIDs = is_array($PSCID) ? $PSCID : [$PSCID];
                    return $query->whereIn('P.SCID', $PSCIDs);
                })
                ->when($req->has('SearchText') && !empty($req->SearchText), function ($query) use ($req) {
                    $search = $req->SearchText;
                    return $query->where(function ($q) use ($search) {
                        $q->where('P.ProductName', 'like', '%' . $search . '%')
                            ->orWhere('P.ProductNameInTranslation', 'like', '%' . $search . '%');
                    });
                });

            $result = $products->select('P.ProductName', 'P.ProductNameInTranslation', 'P.ProductID', 'PCT.PCTName', 'PCT.PCTNameInTranslation',
                'PCT.PCTID', 'PC.PCName', 'PC.PCNameInTranslation', 'PC.PCID', 'PSC.PSCName', 'PSC.PSCNameInTranslation', 'PSC.PSCID', 'U.UName', 'U.UNameInTranslation', 'U.UCode', 'U.UID',
                DB::raw('CONCAT("' . config('app.url') . '/", COALESCE(NULLIF(P.ProductImage, ""), "assets/images/no-image-b.png")) AS ProductImage'),
                DB::raw('(SELECT CASE
                       WHEN EXISTS (SELECT 1 FROM tbl_products_variation WHERE ProductID = P.ProductID AND DFlag = 0)
                       THEN (SELECT PRate FROM tbl_products_variation WHERE ProductID = P.ProductID AND DFlag = 0 ORDER BY SRate ASC LIMIT 1)
                       ELSE P.PRate
                     END) AS PRate'),
                DB::raw('(SELECT CASE
                       WHEN EXISTS (SELECT 1 FROM tbl_products_variation WHERE ProductID = P.ProductID AND DFlag = 0) THEN MIN(PV.SRate)
                       ELSE P.SRate
                     END
                     FROM tbl_products_variation AS PV
                     WHERE PV.ProductID = P.ProductID AND PV.DFlag = 0) AS SRate'),
                DB::raw('IF(W.product_id IS NOT NULL, true, false) AS IsInWishlist'))
                ->when($req->price_filter, function ($query) use ($req) {
                    $priceRange = explode(',', $req->price_filter);
                    return $query->whereRaw('(SELECT CASE
                           WHEN EXISTS (SELECT 1 FROM tbl_products_variation WHERE ProductID = P.ProductID AND DFlag = 0) THEN MIN(PV.SRate)
                           ELSE P.SRate
                         END
                         FROM tbl_products_variation AS PV
                         WHERE PV.ProductID = P.ProductID AND PV.DFlag = 0) BETWEEN ? AND ?', $priceRange);
                })
                ->when($req->has('sorting_by') && in_array($req->sorting_by, ['SRate', 'ProductName']), function ($query) use ($req) {
                    $sortingKey = $req->sorting_by;
                    $direction = $req->has('sorting_direction') && in_array($req->sorting_direction, ['asc', 'desc']) ? $req->sorting_direction : 'asc';
                    return $query->orderBy($sortingKey, $direction);
                })
                ->distinct()
                ->paginate($perPage, ['*'], 'page', $pageNo);

            $result->getCollection()->map(function ($item) use ($lang) {
                $ProductNameInTranslation = json_decode($item->ProductNameInTranslation);
                $item->ProductName = $ProductNameInTranslation->$lang ?? $item->ProductName;
                unset($item->ProductNameInTranslation);
                $PCTNameInTranslation = json_decode($item->PCTNameInTranslation);
                $item->PCTName = $PCTNameInTranslation->$lang ?? $item->PCTName;
                unset($item->PCTNameInTranslation);
                $PCNameInTranslation = json_decode($item->PCNameInTranslation);
                $item->PCName = $PCNameInTranslation->$lang ?? $item->PCName;
                unset($item->PCNameInTranslation);
                $PSCNameInTranslation = json_decode($item->PSCNameInTranslation);
                $item->PSCName = $PSCNameInTranslation->$lang ?? $item->PSCName;
                unset($item->PSCNameInTranslation);
                $item->PRate = Helper::formatAmount($item->PRate);
                $item->SRate = Helper::formatAmount($item->SRate);
                $item->unit = DB::table('tbl_products_variation')
                    ->where('tbl_products_variation.ProductID', $item->ProductID)
                    ->where('tbl_products_variation.DFlag', 0)
                    ->where('tbl_products_variation.SRate', function ($query) use ($item) {
                        $query->select(DB::raw('min(SRate)'))
                            ->from('tbl_products_variation')
                            ->where('ProductID', $item->ProductID)
                            ->where('DFlag', 0);
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
                    if (isset($item->unit->valuesInTranslation)) {
                        $valuesInTranslation = json_decode($item->unit->valuesInTranslation, true);
                        if (isset($valuesInTranslation[$lang])) {
                            $item->unit = $valuesInTranslation[$lang];
                        } else {
                            $item->unit = $item->unit->Values ?? $item->UName ?? '-';
                        }
                    } elseif (isset($item->UNameInTranslation)) {
                        $UNameInTranslation = json_decode($item->UNameInTranslation, true);
                        if (isset($UNameInTranslation[$lang])) {
                            $item->unit = $UNameInTranslation[$lang];
                        } else {
                            $item->unit = $item->UName ?? '-';
                        }
                    } else {
                        $item->unit = $item->UName ?? '-';
                    }
                unset($item->UName);
                unset($item->UNameInTranslation);
                unset($item->UCode);
                unset($item->UID);
                return $item;
            });

            return response()->json([
                'status' => true,
                'data' => $result->items(),
                'CurrentPage' => $result->currentPage(),
                'LastPage' => $result->lastPage(),
            ]);
        } catch (Exception $e) {
            logger($e);
            return $this->errorResponse($e, "Can't get products list", 500);
        }
    }

    public function getCustomerProduct(Request $req)
    {
        try {
            $lang = optional($req->auth_customer)->language ?? 'en';
            $CustomerID = $req->auth_customer->CustomerID;
            $validatedData = Validator::make($req->all(), [
                'ProductID' => 'required|string'
            ]);

            if ($validatedData->fails()) {
                return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
            }

            $product = DB::table('tbl_products as P')
                ->join('tbl_product_category_type as PCT', 'PCT.PCTID', 'P.CTID')
                ->join('tbl_product_category as PC', 'PC.PCID', 'P.CID')
                ->join('tbl_product_subcategory as PSC', 'PSC.PSCID', 'P.SCID')
                ->join('tbl_uom as U', 'U.UID', 'P.UID')
                ->where('P.ActiveStatus', 'Active')
                ->where('P.DFlag', 0)
                ->where('PCT.ActiveStatus', 'Active')
                ->where('PCT.DFlag', 0)
                ->where('PC.ActiveStatus', 'Active')
                ->where('PC.DFlag', 0)
                ->where('PSC.ActiveStatus', 'Active')
                ->where('PSC.DFlag', 0)
                ->where('P.ProductID', $req->ProductID)
                ->first();

            if (!$product) {
                return $this->errorResponse('Product not found', 'Product not found', 404);
            }

            $relatedProductIds = unserialize($product->RelatedProducts);

            if (!is_array($relatedProductIds)) {
                $relatedProductIds = explode(',', $relatedProductIds);
            }

            $relatedProducts = DB::table('tbl_products as P')
                ->leftJoin('tbl_wishlists', function ($join) use ($CustomerID) {
                    $join->on('P.ProductID', '=', 'tbl_wishlists.product_id')
                        ->where('tbl_wishlists.customer_id', '=', $CustomerID);
                })
                ->leftJoin('tbl_uom as U', 'U.UID', 'P.UID')
                ->leftJoin('tbl_products_variation', 'tbl_products_variation.ProductID', '=', 'P.ProductID')
                ->whereIn('P.ProductID', $relatedProductIds)
                ->where('P.ActiveStatus', 'Active')
                ->where('P.DFlag', 0)
                ->distinct()
                ->select('P.ProductID', 'P.ProductName', 'P.ProductNameInTranslation', 'U.UName', 'U.UCode', 'U.UID',
                    DB::raw("CONCAT('" . config('app.url') . "/', COALESCE(P.ProductImage, 'assets/images/no-image-b.png')) as ProductImage"),
                    DB::raw('COALESCE((SELECT PRate FROM tbl_products_variation WHERE tbl_products_variation.ProductID = P.ProductID ORDER BY SRate LIMIT 1), P.PRate) as PRate'),
                    DB::raw('COALESCE((SELECT SRate FROM tbl_products_variation WHERE tbl_products_variation.ProductID = P.ProductID ORDER BY SRate LIMIT 1), P.SRate) as SRate'),
                    DB::raw('IF(tbl_wishlists.product_id IS NOT NULL, "true", "false") as isInWishlist')
                )->get();

            $relatedProducts->transform(function ($item) use ($lang, $product) {
                $item->ProductName = json_decode($item->ProductNameInTranslation)->$lang ?? $item->ProductName;
                $item->PRate = Helper::formatAmount($item->PRate);
                $item->SRate = Helper::formatAmount($item->SRate);
                $relatedProductsUnit = DB::table('tbl_products_variation')
                    ->where('tbl_products_variation.ProductID', $item->ProductID)
                    ->where('tbl_products_variation.DFlag', 0)
                    ->where('tbl_products_variation.SRate', function ($query) use ($item) {
                        $query->select(DB::raw('min(SRate)'))
                            ->from('tbl_products_variation')
                            ->where('ProductID', $item->ProductID)
                            ->where('DFlag', 0);
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
                if (isset($relatedProductsUnit->valuesInTranslation)) {
                    $valuesInTranslation = json_decode($relatedProductsUnit->valuesInTranslation, true);
                    if (isset($valuesInTranslation[$lang])) {
                        $item->unit = $valuesInTranslation[$lang];
                    } else {
                        $item->unit = $relatedProductsUnit->Values ?? $product->UName ?? '-';
                    }
                } elseif (isset($product->UNameInTranslation)) {
                    $UNameInTranslation = json_decode($product->UNameInTranslation, true);
                    if (isset($UNameInTranslation[$lang])) {
                        $item->unit = $UNameInTranslation[$lang];
                    } else {
                        $item->unit = $product->UName ?? '-';
                    }
                } else {
                    $item->unit = $product->UName ?? '-';
                }
                unset($item->ProductNameInTranslation);
                unset($item->UName);
                unset($item->UCode);
                unset($item->UID);
                return $item;
            });

            $pGallery = DB::table('tbl_products_gallery')
                ->select('gImage')
                ->where('ProductID', $product->ProductID)
                ->get();

            foreach ($pGallery as $galleryItem) {
                $galleryItem->gImage = Helper::apiCheckImageExistsUrl($galleryItem->gImage);
            }

            $variations = DB::table('tbl_products_variation')
                ->select('VariationID', 'ProductID', 'PRate', 'SRate')
                ->where('ProductID', $product->ProductID)
                ->where('DFlag', 0)
                ->get();

            foreach ($variations as $variation) {
                $sql = "SELECT D.DetailID, D.ProductID, D.VariationID, D.AttributeID, A.AttrName, D.AttributeValueID, AD.Values, AD.ValuesInTranslation, D.DFlag FROM tbl_products_variation_details as D LEFT JOIN tbl_attributes_details as AD ON AD.ValueID=D.AttributeValueID and AD.AttrID=D.AttributeID LEFT JOIN tbl_attributes as A On A.AttrID=AD.AttrID ";
                $sql .= " Where D.ProductID='" . $product->ProductID . "' and D.VariationID='" . $variation->VariationID . "'";
                $AttributeDetails = DB::select($sql);
                $variation->PRate = Helper::formatAmount($variation->PRate);
                $variation->SRate = Helper::formatAmount($variation->SRate);
                if (isset($AttributeDetails[0]) && ($AttributeDetails[0]->ValuesInTranslation)) {
                    $vValuesInTranslation = json_decode($AttributeDetails[0]->ValuesInTranslation, true);
                    if (isset($vValuesInTranslation[$lang])) {
                        $variation->unit = $vValuesInTranslation[$lang];
                    } else {
                        $variation->unit = $AttributeDetails[0]->Values ?? $product->UName ?? '-';
                    }
                } elseif (isset($product->UNameInTranslation)) {
                    $UNameInTranslation = json_decode($product->UNameInTranslation, true);
                    if (isset($UNameInTranslation[$lang])) {
                        $variation->unit = $UNameInTranslation[$lang];
                    } else {
                        $variation->unit = $product->UName ?? '-';
                    }
                } else {
                    $variation->unit = $product->UName ?? '-';
                }
            }

            $reviews = ProductReview::with('customerDetails')->where('ProductID', $product->ProductID)
                ->orderBy('CreatedOn', 'desc')
                ->get()
                ->map(function ($review) use ($CustomerID, $product) {
                    $review->CreatedOn = date('M d, Y', strtotime($review->CreatedOn));
                    $review->isHelpful = DB::table('tbl_review_likes')
                        ->where('ProductID', $product->ProductID)
                        ->where('CustomerID', $CustomerID)
                        ->where('ReviewID', $review->ReviewID)
                        ->exists();
                    return $review;
                });
            $ratings = ProductReview::where('ProductID', $product->ProductID)->avg('rating');

            $productUnit = DB::table('tbl_products_variation')
                ->where('tbl_products_variation.ProductID', $product->ProductID)
                ->where('tbl_products_variation.DFlag', 0)
                ->where('tbl_products_variation.SRate', function ($query) use ($product) {
                    $query->select(DB::raw('min(SRate)'))
                        ->from('tbl_products_variation')
                        ->where('ProductID', $product->ProductID)
                        ->where('DFlag', 0);
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
                    $productUnit = $valuesInTranslation[$lang];
                } else {
                    $productUnit = $productUnit->Values ?? $product->UName ?? '-';
                }
            } elseif (isset($product->UNameInTranslation)) {
                $UNameInTranslation = json_decode($product->UNameInTranslation, true);
                if (isset($UNameInTranslation[$lang])) {
                    $productUnit = $UNameInTranslation[$lang];
                } else {
                    $productUnit = $product->UName ?? '-';
                }
            } else {
                $productUnit = $product->UName ?? '-';
            }
            $result = (object)[
                'ProductName' => json_decode($product->ProductNameInTranslation)->$lang ?? $product->ProductName,
                'ProductID' => $product->ProductID,
                'PCTName' => json_decode($product->PCTNameInTranslation)->$lang ?? $product->PCTName,
                'PCTID' => $product->PCTID,
                'PCName' => json_decode($product->PCNameInTranslation)->$lang ?? $product->PCName,
                'PCID' => $product->PCID,
                'PSCName' => json_decode($product->PSCNameInTranslation)->$lang ?? $product->PSCName,
                'PSCID' => $product->PSCID,
                'ProductImage' => Helper::apiCheckImageExistsUrl($product->ProductImage ?? ''),
                'PRate' => Helper::formatAmount(DB::table('tbl_products_variation')->where('ProductID', $product->ProductID)->where('DFlag', 0)->exists() ?
                    DB::table('tbl_products_variation')->where('ProductID', $product->ProductID)->where('DFlag', 0)->orderBy('SRate')->value('PRate') :
                    $product->PRate),
                'SRate' => Helper::formatAmount(DB::table('tbl_products_variation')->where('ProductID', $product->ProductID)->where('DFlag', 0)->exists() ?
                    DB::table('tbl_products_variation')->where('ProductID', $product->ProductID)->where('DFlag', 0)->min('SRate') :
                    $product->SRate),
                'unit' => $productUnit,
                'IsInWishlist' => DB::table('tbl_wishlists')->where('customer_id', $CustomerID)->where('product_id', $product->ProductID)->exists(),
                'RelatedProducts' => $relatedProducts,
                'gallery' => $pGallery,
                'variation' => $variations,
                'reviews' => $reviews,
                'ratings' => round($ratings)
            ];

            return $this->successResponse($result, "Product found!");
        } catch (\Exception $e) {
            logger($e);
            return $this->errorResponse($e, "Can't get product data", 500);
        }
    }

    public function checkCoupon(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'coupon_code' => 'required|exists:tbl_coupons,coupon_code',
        ]);

        if ($validatedData->fails()) {
            return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
        }

        $coupon = Coupon::where('coupon_code', $request->coupon_code)
            ->where('DFlag', 0)
            ->where('ActiveStatus', 'Active')
            ->first(['COID', 'type', 'value']);

        if($coupon) {
            if ($coupon->type === 'Percentage') {
                $coupon->value = $coupon->value . '%';
            } else {
                $coupon->value = Helper::formatAmount($coupon->value);
            }
            return $this->successResponse($coupon, "Valid Coupon!");
        }else{
            return $this->errorResponse([], "In-Valid Coupon!");
        }
    }
}
