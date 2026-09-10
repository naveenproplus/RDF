<?php

namespace App\Http\Controllers\api\customer;

use App\helper\helper;
use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    use ApiResponse;
    public function addWishlist(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validatedData = Validator::make($request->all(), [
                'product_id' => 'required|string|exists:tbl_products,ProductID',
                'product_variation_id' => 'nullable|string|exists:tbl_products_variation,VariationID',
            ]);

            if ($validatedData->fails()) {
                return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
            }

            $product_id = $request->product_id;
            $product_variation_id = $request->product_variation_id;
            $customer_id = $request->auth_customer->CustomerID;

            $wishlist = Wishlist::firstOrCreate(['customer_id' => $customer_id, 'product_id' => $product_id,
                'product_variation_id' => $product_variation_id]);
            DB::commit();
            if ($wishlist->wasRecentlyCreated) {
                return $this->successResponse([], "Wishlist item added successfully");
            }
            return $this->errorResponse([], "The selected product is already in your wishlist", 422);
        } catch (Exception $e) {
            logger($e);
            DB::rollBack();
            return $this->errorResponse($e, "Wishlist item adding failed", 422);
        }
    }
    public function removeWishlist(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validatedData = Validator::make($request->all(), [
                'product_id' => 'required|string|exists:tbl_products,ProductID',
                'product_variation_id' => 'nullable|string|exists:tbl_products_variation,VariationID',
            ]);

            if ($validatedData->fails()) {
                return $this->errorResponse($validatedData->errors(), 'Validation Error', 422);
            }

            $product_id = $request->product_id;
            $product_variation_id = $request->product_variation_id;
            $customer_id = $request->auth_customer->CustomerID;

            $products = Wishlist::where('customer_id', $customer_id)
                ->where('product_id', $product_id);
            if (isset($product_variation_id)){
                $deleted = $products->where('product_variation_id', $product_variation_id)->delete();
            } else {
                $deleted = $products->where('product_variation_id', null)->delete();
            }
            DB::commit();
            if ($deleted) {
                return $this->successResponse([], "Wishlist item removed successfully");
            } else {
                return $this->errorResponse([], "This product was not listed in your wishlist", 422);
            }
        } catch (Exception $e) {
            logger($e);
            DB::rollBack();
            return $this->errorResponse($e, "Wishlist item removing failed", 422);
        }
    }

    public function my_wishlist(Request $request): JsonResponse
    {
        try {
            $lang = optional($request->auth_customer)->language ?? 'en';
            $customerId = $request->auth_customer->CustomerID;
            $searchText = $request->input('SearchText', '');

            $wishlistProducts = Wishlist::where('customer_id', $customerId)->get();

            $formattedWishlist = [];
            foreach ($wishlistProducts as $wishlistProduct) {
                $productQuery = DB::table('tbl_products as P')->where('P.ProductID', $wishlistProduct->product_id)
                    ->leftJoin('tbl_uom as U', 'U.UID', 'P.UID')
                    ->where('P.ActiveStatus', 'Active')
                    ->where('P.DFlag', 0);

                if (!empty($searchText)) {
                        $productQuery->where('ProductName', 'like', "%$searchText%");
                }

                $productDetails = $productQuery->first();
                if ($productDetails) {
                    $productUnit = DB::table('tbl_products_variation')
                        ->where('tbl_products_variation.ProductID', $productDetails->ProductID)
                        ->where('tbl_products_variation.SRate', function ($query) use ($productDetails) {
                            $query->select(DB::raw('min(SRate)'))
                                ->from('tbl_products_variation')
                                ->where('ProductID', $productDetails->ProductID);
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
                            $productUnit = $productUnit->Values ?? $productDetails->UName ?? '-';
                        }
                    } elseif (isset($productDetails->UNameInTranslation)) {
                        $UNameInTranslation = json_decode($productDetails->UNameInTranslation, true);
                        if (isset($UNameInTranslation[$lang])) {
                            $productUnit = $UNameInTranslation[$lang];
                        } else {
                            $productUnit = $productDetails->UName ?? '-';
                        }
                    } else {
                        $productUnit = $productDetails->UName ?? '-';
                    }
                    $formattedWishlist[] = [
                        'product_id' => $productDetails->ProductID,
                        'Title' => json_decode($productDetails->ProductNameInTranslation)->$lang ?? $productDetails->ProductName,
                        'PRate' => Helper::formatAmount(DB::table('tbl_products_variation')->where('ProductID', $productDetails->ProductID)->exists() ?
                            DB::table('tbl_products_variation')->where('ProductID', $productDetails->ProductID)->orderBy('SRate')->value('PRate') :
                            $productDetails->PRate),
                        'SRate' => Helper::formatAmount(DB::table('tbl_products_variation')->where('ProductID', $productDetails->ProductID)->exists() ?
                            DB::table('tbl_products_variation')->where('ProductID', $productDetails->ProductID)->min('SRate') :
                            $productDetails->SRate),
                        'unit' => $productUnit,
                        'ProductImage' => Helper::apiCheckImageExistsUrl($productDetails->ProductImage ?? ''),
                    ];
                }
            }

            return $this->successResponse($formattedWishlist, "Wishlist items fetched successfully");

        } catch (Exception $e) {
            logger($e);
            return $this->errorResponse($e, "Wishlist item fetch failed", 422);
        }
    }

}
