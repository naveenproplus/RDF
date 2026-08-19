<?php

namespace App\Http\Controllers\api;

use App\helper\helper;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\general;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;

class GeneralAPIController extends Controller{
    use ApiResponse;
	private $generalDB;
    private $tmpDB;
    private $FileTypes;


    public function __construct(){
		$this->generalDB=Helper::getGeneralDB();
		$this->tmpDB=Helper::getTmpDB();
		$this->FileTypes=Helper::getFileTypes(array("category"=>array("Images","Documents")));
    }

    public function getGoogleAuthSecret(request $req){
		$return = [
			'status' => true,
			'data' => DB::table('tbl_socialite_providers')->where('ActiveStatus', 'Active')->select('ProviderName','ClientID','ClientSecret')->first(),
		];
        return $return;
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
    public function getState(request $req){
		$return = [
			'status' => true,
			'data' => Helper::getState(array("CountryID"=>$req->CountryID)),
		];
        return $return;
	}
    public function getDistrict(request $req){
		$return = [
			'status' => true,
			'data' => Helper::getDistrict(array("CountryID"=>$req->CountryID,"StateID"=>$req->StateID)),
		];
        return $return;
	}
    public function getTaluk(request $req){
		$return = [
			'status' => true,
			'data' => Helper::getTaluk(array("CountryID"=>$req->CountryID,"StateID"=>$req->StateID,"DistrictID"=>$req->DistrictID)),
		];
        return $return;
	}
	public function getCity(Request $req){
		$data = Helper::getCity(["CountryID" => $req->CountryID,"StateID" => $req->StateID,"DistrictID" => $req->DistrictID,"TalukID" => $req->TalukID,"PostalID" => $req->PostalID,"PostalCode" => $req->PostalCode,]);

		if (isset($data['error'])) {
			$return = [
				'status' => false,
				'message' => $data['error'],
				'data' => [],
			];
		} else {
			$return = [
				'status' => true,
				'data' => $data,
			];
		}
		return $return;
	}
	public function getPostalCode(request $req){
		$return = [
			'status' => true,
			'data' => Helper::getPostalCode(array("CountryID"=>$req->CountryID,"StateID"=>$req->StateID,"DistrictID"=>$req->DistrictID,"TalukID"=>$req->TalukID)),
		];
        return $return;
	}
	public function getVehicleType(request $req){
		$return = [
			'status' => true,
			'data' => Helper::getVehicleType()
		];
        return $return;
	}
	public function getVehicleBrand(request $req){
		$return = [
			'status' => true,
			'data' => Helper::getVehicleBrand(array("VehicleTypeID"=>$req->VehicleTypeID))
		];
        return $return;
	}
	public function getVehicleModel(request $req){
		$return = [
			'status' => true,
			'data' => Helper::getVehicleModel(array("VehicleTypeID"=>$req->VehicleTypeID,"VehicleBrandID"=>$req->VehicleBrandID))
		];
        return $return;
	}


	public function GetCategory(request $req){
		$PCatagories = DB::Table('tbl_product_category')->where('ActiveStatus','Active')->where('DFlag',0)->select('PCName','PCID')->get()->toArray();
		shuffle($PCatagories);

		$return = [
			'status' => true,
			'data' => $PCatagories
		];
        return $return;
	}
	public function GetSubCategory(request $req){
		$PCID = $req->PCID;
		$PCIDArray = json_decode($PCID, true);
		$PCIDArray = is_array($PCIDArray) ? $PCIDArray : [$PCID];
		$return = [
			'status' => true,
			'data' => DB::table('tbl_product_subcategory')->where('ActiveStatus', 'Active')->where('DFlag', 0)->whereIn('PCID', $PCIDArray)->get(),
		];

		return $return;
	}


	public function GetProducts(request $req){
		$return = [
			'status' => true,
			'data' => DB::Table('tbl_products')->where('ActiveStatus','Active')->where('DFlag',0)->where('CID',$req->PCID)->where('SCID',$req->PSCID)->get(),
		];
        return $return;
	}
	public function getVendorType(request $req){
		$return = [
			'status' => true,
			'data' => DB::Table('tbl_vendor_type')->where('ActiveStatus','Active')->where('DFlag',0)->get(),
		];
        return $return;
	}

	public function tmpFileUpload(Request $req){

		//remove yesterday folder
		/* $dir="uploads/tmp/".date("Ymd",strtotime("-1 days"))."/";
		if (file_exists( $dir)) {
			$files = glob($dir."*"); // get all file names
			foreach($files as $file){ // iterate files
				if(is_file($file)) {
					unlink($file); // delete file
				}
			}
			rmdir($dir);
		} */
		$dir="uploads/tmp/".date("Ymd")."/";
		if (!file_exists( $dir)) {mkdir( $dir, 0777, true);}

		$allowedImageExtensions = ['jpg', 'jpeg', 'png'];
		$allowedDocExtensions = ['pdf', 'doc', 'docx', 'txt','jpg', 'jpeg'];
		$maxFileSize = 10 * 1024 * 1024; // 10 MB

		if ($req->hasFile('image')) {
			$file = $req->file('image');
			$ext = strtolower($file->getClientOriginalExtension());
			// return $ext;
			$size = $file->getSize();

			if (!in_array(strtolower($ext), $allowedImageExtensions)) {
				return array('status' => false, 'message' => 'Image upload failed', 'errors' => 'Invalid image extension. Allowed extensions: ' . implode(', ', $allowedImageExtensions));
			} elseif ($size > $maxFileSize) {
				return array('status' => false, 'message' => 'Image upload failed', 'errors' => 'Image size exceeds the maximum allowed size of ' . ($maxFileSize / 1024 / 1024) . ' MB.');
			} else {
				$rnd=Helper::RandomString(10)."_".date("YmdHis");
				$tname=md5($file->getClientOriginalName() . time());
				$fileName=$tname. "." . $ext;
				$fileName1 =  $tname. "-tmp." . $ext;
				$file->move($dir, $fileName1);
				return array("uploadPath"=>$dir.$fileName1,"fileName"=>$fileName,"ext"=>$ext,"referData"=>$req->referData);
			}
		} elseif ($req->image != "") {
			$rnd = Helper::RandomString(10) . "_" . date("YmdHis");
			$originalExtension = strtolower(pathinfo($req->image, PATHINFO_EXTENSION));
			$fileName = $rnd . "." . $originalExtension;
			$fileName1 = $rnd . "-tmp." . $originalExtension;
			$imgData = $this->getImageData($req->image);
			file_put_contents($dir . $fileName1, $imgData);
			return array("uploadPath" => $dir . $fileName1, "fileName" => $fileName, "ext" => $originalExtension, "referData" => $req->referData);
		}

		if ($req->hasFile('doc')) {
			$file = $req->file('doc');
			$ext = strtolower($file->getClientOriginalExtension());
			$size = $file->getSize();

			if (!in_array(strtolower($ext), $allowedDocExtensions)) {
				return array('status' => false, 'message' => 'Document upload failed', 'errors' => 'Invalid document extension. Allowed extensions: ' . implode(', ', $allowedDocExtensions));
			} elseif ($size > $maxFileSize) {
				return array('status' => false, 'message' => 'Document upload failed', 'errors' => 'Document size exceeds the maximum allowed size of ' . ($maxFileSize / 1024 / 1024) . ' MB.');
			} else {
				$rnd = Helper::RandomString(10) . "_" . date("YmdHis");
				$tname = md5($file->getClientOriginalName() . time());
				$fileName = $tname . "." . $file->getClientOriginalExtension();
				$fileName1 = $tname . "-tmp." . $file->getClientOriginalExtension();
				$file->move($dir, $fileName1);
				return array("uploadPath" => $dir . $fileName1,"fileName" => $fileName,"ext" => $ext,"referData" => $req->referData);
			}
		} elseif ($req->doc != "") {
			$rnd = Helper::RandomString(10) . "_" . date("YmdHis");
			$originalExtension = strtolower(pathinfo($req->doc, PATHINFO_EXTENSION));
			$fileName = $rnd . "." . $originalExtension;
			$fileName1 = $rnd . "-tmp." . $originalExtension;
			file_put_contents($dir . $fileName1, $req->doc);

			return array("uploadPath" => $dir . $fileName1, "fileName" => $fileName, "ext" => $originalExtension, "referData" => $req->referData);
		}
		return array("uploadPath" => "", "fileName" => "", "referData" => $req->referData);
	}
	private function getImageData($base64){
		$base64_str = substr($base64, strpos($base64, ",")+1);
		$image = base64_decode($base64_str);
		return $image;
	}

    public function getStages(){
        $Stages = DB::Table($this->generalDB.'tbl_stages')->where('ActiveStatus','Active')->where('DFlag',0)
            ->select('StageID','StageName','Description')->get();
        return response()->json(['status' => true,'data' => $Stages]);
    }

    public function getSupportType(request $req){
        $return = [
            'status' => true,
            'data' => DB::Table('tbl_support_type')->where('ActiveStatus',1)->where('DFlag',0)->select('SLNO as SupportTypeID', 'SupportType')->get(),
        ];
        return $return;
    }

    public function getCMS(request $req){
        $lang = optional($req->auth_customer)->language ?? 'en';
        $query = DB::Table('tbl_page_content');
        if ($req->has('PageName') && !empty($req->PageName)) {
            $query->where('PageName',$req->PageName);
        }
        $CMS = $query->select('PageName', 'PageContent', 'PageContentInTranslation')->where('DFlag',0)->where('ActiveStatus',1)->get();
        $CMS->transform(function ($content) use ($lang) {
            $content->PageContent = json_decode($content->PageContentInTranslation)->$lang ?? $content->PageContent;
            unset($content->PageContentInTranslation);
            return $content;
        });
        return [
            'status' => true,
            'data' => $CMS,
        ];
    }

    public function getBannerImages(request $req){
        $BannerImages = DB::Table('tbl_banner_images')->where('BannerType','Mobile')->where('DFlag',0)->select('BannerType', DB::raw('CONCAT("' . url('/') . '/", BannerImage) AS BannerImage'))->get();
        $return = [
            'status' => true,
            'data' => $BannerImages,
        ];
        return $return;
    }

    public function translate(Request $request)
    {
        $sourceText = $request->input('sourceText');
        $sourceLang = $request->input('sourceLang', 'auto');
        $targetLang = $request->input('targetLang', 'ta');
        return Helper::translate($sourceText, $targetLang, $sourceLang);
    }

    public function getLanguages()
    {
        return Language::active()->select("name", "name_in_english", "code")->get();
    }
}
