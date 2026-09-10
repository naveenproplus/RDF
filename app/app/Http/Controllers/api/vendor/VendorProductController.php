<?php

namespace App\Http\Controllers\api\vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\JsonResponse;
use DB;
use Auth;
use cruds;
use Helper;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;
use activeMenuNames;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use ValidUnique;
use ValidDB;
use DocNum;
use general;
use docTypes;
use Exception;
use logs;
use Illuminate\Pagination\Paginator;

class VendorProductController extends Controller
{
	use ResponseTrait;
	private $general;
	private $DocNum;
	private $UserID;
	private $ActiveMenuName;
	private $PageTitle;
	private $CRUD;
	private $Settings;
	private $Menus;

	public function __construct()
	{
		// 	$this->ActiveMenuName=activeMenuNames::VendorProductMapping->value;
		// 	$this->PageTitle="Vendor Product Mapping";
		//     // $this->middleware('auth');
		$this->middleware(function ($request, $next) {
			// $this->UserID=auth()->user()->UserID;
			$this->general = new general($this->UserID, $this->ActiveMenuName);
			// $this->Menus=$this->general->loadMenu();
			// $this->CRUD=$this->general->getCrudOperations($this->ActiveMenuName);
			$this->Settings = $this->general->getSettings();
			return $next($request);
		});
	}

	public function index(Request $req)
	{
		$perPage = $req->input('per_page');
		$nextPage = $req->input('next_page');

		if ($nextPage) {
			Paginator::currentPageResolver(function () use ($nextPage) {
				return $nextPage;
			});
		}

		$offset = ($nextPage - 1) * $perPage;

		$VendorDB = Helper::getVendorDB($req->VendorID, "2");
		$query  = DB::table($VendorDB . 'tbl_vendors_product_mapping')
					->join('tbl_product_category', 'tbl_vendors_product_mapping.PCID', '=', 'tbl_product_category.PCID')
					->join('tbl_product_subcategory', 'tbl_vendors_product_mapping.PSCID', '=', 'tbl_product_subcategory.PSCID')
					->where('tbl_vendors_product_mapping.status',1)
					->select(
						'tbl_vendors_product_mapping.*',
						'tbl_product_category.PCName as category_name',
						'tbl_product_subcategory.PSCName as subcategory_name'
					);

		$vendorData = $query->skip($offset)->take($perPage)->get();

		$totalRecords = $query->count();

		$nextPage = $totalRecords > $offset + $perPage ? $nextPage + 1 : null;


		return response()->json([
			'data' => $vendorData,
			'next_page' => $nextPage,
			'total_records' => $totalRecords
		]);
	}

	public function update(Request $req)
	{
		// dd($req);
		$OldData = $NewData = [];
		DB::beginTransaction();
		try {
			$VendorDB = Helper::getVendorDB($req->VendorID, "2");
			// dd($VendorDB);
			$status = DB::statement("CREATE TABLE IF NOT EXISTS {$VendorDB}tbl_docnum (SLNO INT AUTO_INCREMENT PRIMARY KEY,DocType VARCHAR(50) NULL,Prefix VARCHAR(5) NULL,Length INT(11) NULL,CurrNum INT(11) NULL,Suffix VARCHAR(10),Year VARCHAR(10) NULL)");
			if ($status) {
				Helper::addVendorDocNum($VendorDB, 'tbl_docnum', docTypes::VendorProductMapping->value, "VPM", 8, 1);
				$status = DB::statement("CREATE TABLE IF NOT EXISTS {$VendorDB}tbl_vendors_product_mapping (DetailID VARCHAR(50) PRIMARY KEY,VendorID VARCHAR(50) NULL,ProductID VARCHAR(50) NULL,PCID VARCHAR(50) NULL,PSCID VARCHAR(50) NULL,Status INT(1) DEFAULT 1,VendorPrice DOUBLE,CreatedBy VARCHAR(50) NULL,CreatedOn TIMESTAMP NULL,UpdatedBy VARCHAR(50) NULL,UpdatedOn TIMESTAMP NULL)");
			}
			$OldData = DB::table($VendorDB . 'tbl_vendors_product_mapping')->where('VendorID', $req->VendorID)->get();
			$ProductData = $req->ProductData;
			$ProductIDs = [];
			foreach ($ProductData as $data) {
				$ProductIDs[] = $data['ProductID'];
				$t = DB::Table($VendorDB . 'tbl_vendors_product_mapping')->where('VendorID', $req->VendorID)->Where('ProductID', $data['ProductID'])->first();
				if (!$t) {
					$DetailID = DocNum::getDocNum(docTypes::VendorProductMapping->value, $VendorDB);
					$tdata = array(
						"DetailID" => $DetailID,
						"VendorID" => $req->VendorID,
						"ProductID" => $data['ProductID'],
						"VendorPrice" => $data['VendorPrice'],
						"PCID" => $data['PCID'],
						"PSCID" => $data['PSCID'],
						"CreatedBy" => $this->UserID,
						"CreatedOn" => date("Y-m-d H:i:s")
					);
					$status = DB::Table($VendorDB . 'tbl_vendors_product_mapping')->insert($tdata);
					if ($status) {
						DocNum::updateDocNum(docTypes::VendorProductMapping->value, $VendorDB);
					}
				} else {
					$ExistingPrice = DB::Table($VendorDB . 'tbl_vendors_product_mapping')->where('VendorID', $req->VendorID)->Where('ProductID', $data['ProductID'])->value('VendorPrice');
					if ($ExistingPrice != $data['VendorPrice']) {
						$status = DB::Table($VendorDB . 'tbl_vendors_product_mapping')->where('VendorID', $req->VendorID)->Where('ProductID', $data['ProductID'])->update(['VendorPrice' => $data['VendorPrice'], "UpdatedBy" => $this->UserID, "UpdatedOn" => date("Y-m-d H:i:s")]);
					}
				}
			}
			if (count($ProductIDs) > 0) {
				DB::Table($VendorDB . 'tbl_vendors_product_mapping')->where('VendorID', $req->VendorID)->WhereIn('ProductID', $ProductIDs)->update(['Status' => 1, 'UpdatedOn' => date('Y-m-d H:i:s'), 'UpdatedBy' => $this->UserID]);
				DB::Table($VendorDB . 'tbl_vendors_product_mapping')->where('VendorID', $req->VendorID)->WhereNotIn('ProductID', $ProductIDs)->update(['Status' => 0, 'UpdatedOn' => date('Y-m-d H:i:s'), 'UpdatedBy' => $this->UserID]);
			}
			$status = true;
		} catch (Exception $e) {
			$status = false;
		}
		if ($status == true) {
			// DB::commit();
			// $NewData=DB::table($VendorDB.'tbl_vendors_product_mapping')->where('VendorID',$req->VendorID)->get();
			// $logData=array("Description"=>"Vendor Product Mapped","Action"=>cruds::UPDATE->value,"ReferID"=>$req->VendorID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$req->VendorID,"IP"=>$req->ip());
			// logs::Store($logData);
			return $this->responseSuccess(null, 'Vendor Product Mapped Successfully !');
		} else {
			DB::rollback();
			return $this->responseError(null, 'Vendor Product Mapping Failed !', Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

	public function delete(Request $req)
	{
		try {
			$VendorDB = Helper::getVendorDB($req->VendorID, $this->UserID);
			$query  = DB::table($VendorDB . 'tbl_vendors_product_mapping')->where('ProductID', $req->ProductID)->where('status',1)->first();

			if ($query) {

				DB::table($VendorDB . 'tbl_vendors_product_mapping')
					->where('ProductID', $req->ProductID)
					->update(['status' => 0]);


				$updatedRecord = DB::table($VendorDB . 'tbl_vendors_product_mapping')
					->where('ProductID', $req->ProductID)
					->first();

				return $this->responseSuccess($updatedRecord, 'Product deleted Successfully!');
			} else {
				return $this->responseError('Record not found', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
			}
		} catch (\Exception $e) {
			return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}


	public function getProducts(Request $req)
	{
		try {
			$data = DB::table('tbl_products as P')->join('tbl_product_category as PC', 'PC.PCID', 'P.CID')
				->join('tbl_product_subcategory as PSC', 'PSC.PSCID', 'P.SCID')
				->join('tbl_uom as UOM', 'UOM.UID', 'P.UID')
				->where('P.DFlag', 0)->where('P.ActiveStatus', 'Active')
				->where('PC.ActiveStatus', 'Active')->where('PC.DFlag', 0)
				->where('PSC.ActiveStatus', 'Active')->where('PSC.DFlag', 0)
				->whereIn('P.CID', $req->PCID)->whereIn('P.SCID', $req->PSCID)->get();

			$data = $data->map(function ($item) {
				$item->Images = unserialize($item->Images);
				unset($item->Attributes);
				unset($item->AdditionalSetting);
				return $item;
			});

			return $this->responseSuccess($data, 'Product List Fetch Successfully !');
		} catch (\Exception $e) {
			return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}
}
