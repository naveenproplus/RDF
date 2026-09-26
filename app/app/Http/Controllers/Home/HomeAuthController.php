<?php
namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use DocNum;
use general;
use SSP;
use DB;
use Auth;
use ValidUnique;
use logs;
use Helper;
use activeMenuNames;
use docTypes;
use cruds;
use PHPUnit\TextUI\Help;

class HomeAuthController extends Controller{
	private $generalDB;
    private $tmpDB;
    private $Company;
    private $PCategories;
	private $UserID;
	private $ReferID;
	private $FileTypes;
	private $UserData;
	private $logDB;

	public function __construct(){
		$this->generalDB=Helper::getGeneralDB();
		$this->tmpDB=Helper::getTmpDB();
		$this->logDB=Helper::getLogDB();
        $this->PCategories= DB::Table('tbl_product_category')->where('ActiveStatus','Active')->where('DFlag',0)->select('PCName','PCID','PCImage')->get()->toArray();
		$this->FileTypes=Helper::getFileTypes(array("category"=>array("Images","Documents")));
		$CompanyData= DB::table('tbl_company_settings')->select('KeyName','KeyValue')->get();
		$Company= [];
		foreach ($CompanyData as $item) {
			$Company[$item->KeyName] = $item->KeyValue;
		}
		$this->Company = $Company;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
			$this->UserData = Helper::getUserInfo(Auth()->user()->UserID);
			$this->UserID=auth()->user()->UserID;
			$this->ReferID=auth()->user()->ReferID;
			return $next($request);
		});
    }
	
    public function Home(Request $req){
		$CustomerID = $this->ReferID;
		$FormData['Company']=$this->Company;
		$PCatagories = DB::Table('tbl_product_category')->where('ActiveStatus','Active')->where('DFlag',0)->select('PCName','PCID','PCImage')->get()->toArray();

		shuffle($PCatagories);
		foreach($PCatagories as $row){
			$row->PCImage = $row->PCImage ? url('/').'/'.$row->PCImage :url('/') . '/'.'assets/images/no-image-b.png';
			$row->PSCData = DB::table('tbl_product_subcategory')->where('ActiveStatus','Active')->where('DFlag',0)->where('PCID',$row->PCID)->select('PSCID','PSCName','PSCImage')->get();
			/* foreach($row->PSCData as $item){
				$item->PSCImage = $item->PSCImage ? url('/').'/'.$item->PSCImage :url('/') . '/'.'assets/images/no-image-b.png';
				$item->ProductData = DB::table('tbl_products')->where('ActiveStatus','Active')->where('DFlag',0)->where('CID',$row->PCID)->where('SCID',$item->PSCID)->select('ProductID','ProductName','ProductImage')->get();
				foreach($item->ProductData as $data){
					$data->ProductImage = $data->ProductImage ? url('/').'/'.$data->ProductImage :url('/') . '/'.'assets/images/no-image-b.png';
					$data->ProductImage = $data->ProductImage ? 'https://rpc.prodemo.in/'.$data->ProductImage :url('/') . '/'.'assets/images/no-image-b.png';
					$data->gImages=DB::table('tbl_products_gallery')->where('ProductID',$data->ProductID)->select(DB::raw('CONCAT("' . url('/') . '/", gImage) AS gImage'))->get();
				}
			} */
		}
		$RecentProducts = DB::table('tbl_products as P')->leftJoin('tbl_product_subcategory as PSC','PSC.PSCID','P.SCID')->where('P.ActiveStatus','Active')->where('P.DFlag',0)->select('P.ProductID','P.ProductName','P.ProductImage','PSC.PSCName')->get()->toArray();
		shuffle($RecentProducts);
		$randomProducts = array_slice($RecentProducts, 0, 10);
		foreach($randomProducts as $data){
			$data->ProductImage = url('/').'/'.Helper::checkProductImageExists($data->ProductImage);
		}
		$FormData['PCategories']=$PCatagories;
		$FormData['RecentProducts']=$randomProducts;
		shuffle($randomProducts);
		$FormData['HotProducts']=$randomProducts;
		$FormData['isRegister']=false;
		$FormData['Cart']=$this->getCart();

		$FormData['ShippingAddress']=DB::table('tbl_customer_address as CA')->where('CustomerID',$CustomerID)
		->join($this->generalDB.'tbl_countries as C','C.CountryID','CA.CountryID')
		->join($this->generalDB.'tbl_states as S', 'S.StateID', 'CA.StateID')
		->join($this->generalDB.'tbl_districts as D', 'D.DistrictID', 'CA.DistrictID')
		->join($this->generalDB.'tbl_taluks as T', 'T.TalukID', 'CA.TalukID')
		->join($this->generalDB.'tbl_cities as CI', 'CI.CityID', 'CA.CityID')
		->join($this->generalDB.'tbl_postalcodes as PC', 'PC.PID', 'CA.PostalCodeID')
		->select('CA.SLNo as AID', 'CA.Address', 'CA.isDefault', 'CA.CountryID', 'C.CountryName', 'CA.StateID', 'S.StateName', 'CA.DistrictID', 'D.DistrictName', 'CA.TalukID', 'T.TalukName', 'CA.CityID', 'CI.CityName', 'CA.PostalCodeID', 'PC.PostalCode')
		->get();
		// return $FormData['ShippingAddress'];
		return view('home.home',$FormData);
    }
    public function Register(Request $req){
		
		$FormData['Company']=$this->Company;
		$FormData['UserData']=$this->UserData['data'];
		$FormData['PCategories']=$this->PCategories;
		$FormData['isEdit']=false;
		$FormData['isRegister']=true;
		$FormData['Cart']=$this->getCart();
		return view('home.register',$FormData);
    }
	public function Checkout(Request $req){
		$CustomerID = $this->ReferID;
		
		$FormData['Company']=$this->Company;
		$FormData['UserData']=$this->UserData['data'];
		$FormData['PCategories']=$this->PCategories;
		$FormData['isEdit']=false;
		$FormData['isRegister']=false;
		$FormData['ShippingAddress']=DB::table('tbl_customer_address as CA')->where('CustomerID',$CustomerID)
		->join($this->generalDB.'tbl_countries as C','C.CountryID','CA.CountryID')
		->join($this->generalDB.'tbl_states as S', 'S.StateID', 'CA.StateID')
		->join($this->generalDB.'tbl_districts as D', 'D.DistrictID', 'CA.DistrictID')
		->join($this->generalDB.'tbl_taluks as T', 'T.TalukID', 'CA.TalukID')
		->join($this->generalDB.'tbl_cities as CI', 'CI.CityID', 'CA.CityID')
		->join($this->generalDB.'tbl_postalcodes as PC', 'PC.PID', 'CA.PostalCodeID')
		->select('CA.SLNo as AID', 'CA.Address', 'CA.isDefault', 'CA.CountryID', 'C.CountryName', 'CA.StateID', 'S.StateName', 'CA.DistrictID', 'D.DistrictName', 'CA.TalukID', 'T.TalukName', 'CA.CityID', 'CI.CityName', 'CA.PostalCodeID', 'PC.PostalCode')
		->get();
		$FormData['CustomerData']=DB::table('tbl_customer as CU')->where('CustomerID',$CustomerID)
		->join($this->generalDB.'tbl_countries as C','C.CountryID','CU.CountryID')
		->join($this->generalDB.'tbl_states as S', 'S.StateID', 'CU.StateID')
		->join($this->generalDB.'tbl_districts as D', 'D.DistrictID', 'CU.DistrictID')
		->join($this->generalDB.'tbl_taluks as T', 'T.TalukID', 'CU.TalukID')
		->join($this->generalDB.'tbl_cities as CI', 'CI.CityID', 'CU.CityID')
		->join($this->generalDB.'tbl_postalcodes as PC', 'PC.PID', 'CU.PostalCodeID')
		->select('CU.Address', 'CU.CountryID', 'C.CountryName', 'CU.StateID', 'S.StateName', 'CU.DistrictID', 'D.DistrictName', 'CU.TalukID', 'T.TalukName', 'CU.CityID', 'CI.CityName', 'CU.PostalCodeID', 'PC.PostalCode','CU.MobileNo1','CU.CustomerName')
		->first();
		$FormData['DeliveryAddress']=DB::table('tbl_customer_address as CA')->where('CustomerID',$CustomerID)->where('CA.isDefault',1)
		->join($this->generalDB.'tbl_countries as C','C.CountryID','CA.CountryID')
		->join($this->generalDB.'tbl_states as S', 'S.StateID', 'CA.StateID')
		->join($this->generalDB.'tbl_districts as D', 'D.DistrictID', 'CA.DistrictID')
		->join($this->generalDB.'tbl_taluks as T', 'T.TalukID', 'CA.TalukID')
		->join($this->generalDB.'tbl_cities as CI', 'CI.CityID', 'CA.CityID')
		->join($this->generalDB.'tbl_postalcodes as PC', 'PC.PID', 'CA.PostalCodeID')
		->select('CA.SLNo as AID', 'CA.Address', 'CA.isDefault', 'CA.CountryID', 'C.CountryName', 'CA.StateID', 'S.StateName', 'CA.DistrictID', 'D.DistrictName', 'CA.TalukID', 'T.TalukName', 'CA.CityID', 'CI.CityName', 'CA.PostalCodeID', 'PC.PostalCode')
		->first();
		$FormData['Cart']=$this->getCart();
		if($FormData['Cart'])
		return view('home.checkout',$FormData);
    }
	public function PlaceOrder(Request $req){	
        DB::beginTransaction();
        $status=false;
        $CustomerID=$this->ReferID;
        try {
            $CustomerData = DB::table('tbl_customer')->where('CustomerID',$CustomerID)->first();
            $EnqID = DocNum::getDocNum(docTypes::Enquiry->value,$this->logDB,Helper::getCurrentFY());
            $BuildingImage = "";
            if($req->BuildingImage != null) {
                $dir = "uploads/transaction/enquiry/" . $EnqID . "/";
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                if ($req->hasFile('BuildingImage')) {
                    $file = $req->file('BuildingImage');
                    $fileName = md5($file->getClientOriginalName() . time());
                    $fileName1 = $fileName . "." . $file->getClientOriginalExtension();
                    $file->move($dir, $fileName1);
                    $BuildingImage = $dir . $fileName1;
                } else if (Helper::isJSON($req->BuildingImage) == true) {
                    $Img = json_decode($req->BuildingImage);
                    if (file_exists($Img->uploadPath)) {
                        $fileName1 = $Img->fileName != "" ? $Img->fileName : Helper::RandomString(10) . "png";
                        copy($Img->uploadPath, $dir . $fileName1);
                        $BuildingImage = $dir . $fileName1;
                        // unlink($Img->uploadPath);
                    }
                }
            }
            $data=[
                'EnqID' => $EnqID,
                'EnqNo' =>DocNum::getInvNo("Quote-Enquiry"),
                'EnqDate' => date('Y-m-d'),
                'EnqExpiryDate' => date('Y-m-d', strtotime('+15 days')),
                'CustomerID' => $CustomerID,
                'ReceiverName' => $req->ReceiverName,
                'ReceiverMobNo' => $req->ReceiverMobNo,
                'ExpectedDeliveryDate' => $req->ExpectedDeliveryDate,
                'Address' => $req->Address,
                'CountryID' => $req->CountryID,
                'StateID' => $req->StateID,
                'DistrictID' => $req->DistrictID,
                'TalukID' => $req->TalukID,
                'CityID' => $req->CityID,
                'PostalCodeID' => $req->PostalCodeID,
                'DAddress' => $req->Address,
                'DCountryID' => $req->CountryID,
                'DStateID' => $req->StateID,
                'DDistrictID' => $req->DistrictID,
                'DTalukID' => $req->TalukID,
                'DCityID' => $req->CityID,
                'DPostalCodeID' => $req->PostalCodeID,
                'StageID' => $req->StageID,
                'BuildingMeasurementID' => $req->BuildingMeasurementID,
                'BuildingMeasurement' => $req->BuildingMeasurement,
                'BuildingImage' => $BuildingImage,
                'CreatedOn' => date('Y-m-d H:i:s'),
                'CreatedBy' => $CustomerID,
            ];
            $status=DB::table($this->logDB.'tbl_enquiry')->insert($data);
            if($status){
                $ProductData = json_decode($req->ProductData,true);
                if($ProductData){
					foreach($ProductData as $item){
						$EnquiryDetailID = DocNum::getDocNum(docTypes::EnquiryDetails->value,$this->logDB,Helper::getCurrentFY());
						$data1=[
							'DetailID' => $EnquiryDetailID,
							'EnqID'=>$EnqID,
							'CID'=>$item['PCID'],
							'SCID'=>$item['PSCID'],
							'ProductID'=>$item['ProductID'],
							'Qty'=>$item['Qty'],
							'UOMID'=>$item['UID'],
							'CreatedOn'=>date('Y-m-d H:i:s'),
							'CreatedBy'=>$CustomerID,
						];
						$status = DB::table($this->logDB.'tbl_enquiry_details')->insert($data1);
						if($status){
							DocNum::updateDocNum(docTypes::EnquiryDetails->value,$this->logDB);
						}
					}
				}
                DocNum::updateDocNum(docTypes::Enquiry->value,$this->logDB);
            }
        }catch(Exception $e) {
            $status=false;
        }
        if($status==true){
            DB::commit();
            DocNum::updateInvNo("Quote-Enquiry");
            DB::table('tbl_customer_cart')->where('CustomerID',$CustomerID)->delete();
            return response()->json(['status' => true,'message' => "Order Placed Successfully"]);
        }else{
            DB::rollback();
            return response()->json(['status' => false,'message' => "Order Placing Failed!"]);
        }
    }
    public function Profile(Request $req){
		$CustomerID = $this->ReferID;
		$FormData['Company']=$this->Company;
		$FormData['UserData']=$this->UserData['data'];
		$FormData['PCategories']=$this->PCategories;
		$FormData['isEdit']=true;
		$FormData['isRegister']=true;
		$FormData['Cart']=$this->getCart();
		$FormData['EditData'] = DB::table('tbl_customer')->where('DFlag',0)->Where('CustomerID',$CustomerID)->first();
		if($FormData['EditData']){
			$FormData['EditData']->CustomerImage = $FormData['EditData']->CustomerImage ? url('/').'/'.$FormData['EditData']->CustomerImage : url('/') . '/'.'assets/images/no-image-b.png';
			$FormData['EditData']->PostalCode = DB::table($this->generalDB.'tbl_postalcodes as P')->where('PID',$FormData['EditData']->PostalCodeID)->value('PostalCode');
			$FormData['EditData']->SAddress = DB::table('tbl_customer_address as CA')->where('CustomerID',$CustomerID)
			->join($this->generalDB.'tbl_countries as C','C.CountryID','CA.CountryID')
			->join($this->generalDB.'tbl_states as S', 'S.StateID', 'CA.StateID')
			->join($this->generalDB.'tbl_districts as D', 'D.DistrictID', 'CA.DistrictID')
			->join($this->generalDB.'tbl_taluks as T', 'T.TalukID', 'CA.TalukID')
			->join($this->generalDB.'tbl_cities as CI', 'CI.CityID', 'CA.CityID')
			->join($this->generalDB.'tbl_postalcodes as PC', 'PC.PID', 'CA.PostalCodeID')
			->select('CA.SLNo as AID', 'CA.Address', 'CA.isDefault', 'CA.CountryID', 'C.CountryName', 'CA.StateID', 'S.StateName', 'CA.DistrictID', 'D.DistrictName', 'CA.TalukID', 'T.TalukName', 'CA.CityID', 'CI.CityName', 'CA.PostalCodeID', 'PC.PostalCode')
			->get();
			// return $FormData['EditData'];
			return view('home.register',$FormData);
		}else{
			return view('errors.403');
		}
    }
	public function Save(Request $req){ 
		$OldData=array();$NewData=array();$CustomerID="";
			
		$rules=array(
			'MobileNo1' =>['required','max:10',new ValidUnique(array("TABLE"=>"tbl_customer","WHERE"=>" MobileNo1='".$req->MobileNo1."'  "),"This Mobile Number is already taken.")],
		);
		$message =[];
		$validator = Validator::make($req->all(), $rules,$message);

		if ($validator->fails()) {
			return array('status'=>false,'message'=>"Customer Create Failed",'errors'=>$validator->errors());
		}

		DB::beginTransaction();
		$status=false;
		try {
			$SAddress=json_decode($req->SAddress,true);
			$CustomerID=DocNum::getDocNum(docTypes::Customer->value,"",Helper::getCurrentFY());
			$CustomerImage="";
			$dir="uploads/user-and-permissions/customers/".$CustomerID."/";
			if (!file_exists( $dir)) {mkdir( $dir, 0777, true);}
			if($req->hasFile('CustomerImage')){
				$file = $req->file('CustomerImage');
				$fileName=md5($file->getClientOriginalName() . time());
				$fileName1 =  $fileName. "." . $file->getClientOriginalExtension();
				$file->move($dir, $fileName1);  
				$CustomerImage=$dir.$fileName1;
			}else if(Helper::isJSON($req->CustomerImage)==true){
				$Img=json_decode($req->CustomerImage);
				if(file_exists($Img->uploadPath)){
					$fileName1=$Img->fileName!=""?$Img->fileName:Helper::RandomString(10)."png";
					copy($Img->uploadPath,$dir.$fileName1);
					$CustomerImage=$dir.$fileName1;
					unlink($Img->uploadPath);
				}
			}
			
			$data=array(
				"CustomerID"=>$CustomerID,
				"CustomerName"=>$req->CustomerName,
				'CustomerImage'=>$CustomerImage,
				"MobileNo1"=>$req->MobileNo1,
				"MobileNo2"=>$req->MobileNo2,
				"Email"=>$req->Email,
				"CusTypeID"=>$req->CusTypeID,
				"Address"=>$req->Address,
				"PostalCodeID"=>$req->PostalCodeID,
				"CityID"=>$req->CityID,
				"TalukID"=>$req->TalukID,
				"DistrictID"=>$req->DistrictID,
				"StateID"=>$req->StateID,
				"CountryID"=>$req->CountryID,
				"CreatedBy"=>$this->UserID,
				"CreatedOn"=>date("Y-m-d H:i:s")
			);
			$status=DB::Table('tbl_customer')->insert($data);
			if($status){
				foreach($SAddress as $row){
					$AID=DocNum::getDocNum(docTypes::CustomerAddress->value,"",Helper::getCurrentFY());
					$tmp=array(
						"SLNO"=>$AID,
						"CustomerID"=>$CustomerID,
						"Address"=>$row['Address'],
						"PostalCodeID"=>$row['PostalCodeID'],
						"CityID"=>$row['CityID'],
						"TalukID"=>$row['TalukID'],
						"DistrictID"=>$row['DistrictID'],
						"StateID"=>$row['StateID'],
						"CountryID"=>$row['CountryID'],
						"isDefault"=>$row['isDefault'],
						"CreatedOn"=>date("Y-m-d H:i:s")
					);
					$status=DB::Table('tbl_customer_address')->insert($tmp);
					if($status==true){
						DocNum::updateDocNum(docTypes::CustomerAddress->value);
					}
				}
			}
			if($status){
                $CustomerName = $req->CustomerName;
                $nameParts = explode(' ', $CustomerName, 2);
                $firstName = $nameParts[0] ?? '';
                $lastName = $nameParts[1] ?? '';

                $Udata=array(
                    "ReferID"=>$CustomerID,
                    "Name" => $CustomerName,
                    "FirstName" => $firstName,
                    "LastName" => $lastName,
                    "ProfileImage" => $CustomerImage,
                    "MobileNumber"=>$req->MobileNo1,
					"Address"=>$req->Address,
					"PostalCodeID"=>$req->PostalCodeID,
					"CityID"=>$req->CityID,
					"TalukID"=>$req->TalukID,
					"DistrictID"=>$req->DistrictID,
					"StateID"=>$req->StateID,
					"CountryID"=>$req->CountryID,
					"UpdatedBy"=>$CustomerID,
                );
				$status=DB::Table('users')->where('UserID',$this->UserID)->update($Udata);
            }
		}catch(Exception $e) {
			$status=false;
		}
		if($status==true){
			DocNum::updateDocNum(docTypes::Customer->value);
			$NewData=(array)DB::table('tbl_customer as C')->join('tbl_customer_address as CA','CA.CustomerID','C.CustomerID')->where('CA.CustomerID',$CustomerID)->get();
			$logData=array("Description"=>"New Customer Created","ModuleName"=>"Customer","Action"=>"Add","ReferID"=>$CustomerID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
			logs::Store($logData);
			DB::commit();
			return array('status'=>true,'message'=>"Customer Created Successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"Customer Create Failed");
		}
	}
	public function Update(Request $req){
		$CustomerID = $this->ReferID;
		$OldData=DB::table('tbl_customer_address as CA')->join('tbl_customer as C','C.CustomerID','CA.CustomerID')->where('CA.CustomerID',$CustomerID)->get();
		$NewData=array();
		
		$rules=array(
			'MobileNo1' =>['required','max:10',new ValidUnique(array("TABLE"=>"tbl_customer","WHERE"=>" MobileNo1='".$req->MobileNo1."' and CustomerID <> '".$CustomerID."' "),"This Mobile Number is already taken.")],
		);

		$message=array();
		$validator = Validator::make($req->all(), $rules,$message);

		if ($validator->fails()) {
			return array('status'=>false,'message'=>"Customer Update Failed",'errors'=>$validator->errors());
		}
		DB::beginTransaction();
		$status=false;
		$currCustomerImage=array();
		$images=array();
		try {
			$SAddress=json_decode($req->SAddress,true);
			$CustomerImage="";
			$dir="uploads/user-and-permissions/customers/".$CustomerID."/";
			if (!file_exists( $dir)) {mkdir( $dir, 0777, true);}
			if($req->hasFile('CustomerImage')){
				$file = $req->file('CustomerImage');
				$fileName=md5($file->getClientOriginalName() . time());
				$fileName1 =  $fileName. "." . $file->getClientOriginalExtension();
				$file->move($dir, $fileName1);  
				$CustomerImage=$dir.$fileName1;
			}else if(Helper::isJSON($req->CustomerImage)==true){
				$Img=json_decode($req->CustomerImage);
				if(file_exists($Img->uploadPath)){
					$fileName1=$Img->fileName!=""?$Img->fileName:Helper::RandomString(10)."png";
					copy($Img->uploadPath,$dir.$fileName1);
					$CustomerImage=$dir.$fileName1;
					unlink($Img->uploadPath);
				}
			}
			if(file_exists($CustomerImage)){
				$images=Helper::ImageResize($CustomerImage,$dir);
			}
			if(($CustomerImage!="" || intval($req->removeCustomerImage)==1) && Count($OldData)>0){ 
				$currCustomerImage=$OldData[0]->Images!=""?unserialize($OldData[0]->Images):array();
			}
			$data=array(
				"CustomerName"=>$req->CustomerName,
				"MobileNo1"=>$req->MobileNo1,
				"MobileNo2"=>$req->MobileNo2,
				"Email"=>$req->Email,
				"CusTypeID"=>$req->CusTypeID,
				"Address"=>$req->Address,
				"PostalCodeID"=>$req->PostalCodeID,
				"CityID"=>$req->CityID,
				"TalukID"=>$req->TalukID,
				"DistrictID"=>$req->DistrictID,
				"StateID"=>$req->StateID,
				"CountryID"=>$req->CountryID,
				"UpdatedBy"=>$this->UserID,
				"UpdatedOn"=>date("Y-m-d H:i:s")
			);
			if($CustomerImage!=""){
				$data['CustomerImage']=$CustomerImage;
				$data['Images']=serialize($images);
			}else if(intval($req->removeCustomerImage)==1){
				$data['CustomerImage']="";
				$data['Images']=serialize(array());
			}
			$status=DB::Table('tbl_customer')->where('CustomerID',$CustomerID)->update($data);
			if($status){
				$AIDs=[];
				foreach($SAddress as $row){
					if($row['AID']){
						$AIDs[] = $row['AID'];
						$data=array(
							"Address"=>$row['Address'],
							"PostalCodeID"=>$row['PostalCodeID'],
							"CityID"=>$row['CityID'],
							"TalukID"=>$row['TalukID'],
							"DistrictID"=>$row['DistrictID'],
							"StateID"=>$row['StateID'],
							"CountryID"=>$row['CountryID'],
							"isDefault"=>$row['isDefault'],
							"UpdatedOn"=>date("Y-m-d H:i:s")
						);
						$status=DB::Table('tbl_customer_address')->where('CustomerID',$CustomerID)->where('SLNO',$row['AID'])->update($data);
					}else{
						$AID=DocNum::getDocNum(docTypes::CustomerAddress->value,"",Helper::getCurrentFY());
						$AIDs[] = $AID;
						$tmp=array(
							"SLNO"=>$AID,
							"CustomerID"=>$CustomerID,
							"Address"=>$row['Address'],
							"PostalCodeID"=>$row['PostalCodeID'],
							"CityID"=>$row['CityID'],
							"TalukID"=>$row['TalukID'],
							"DistrictID"=>$row['DistrictID'],
							"StateID"=>$row['StateID'],
							"CountryID"=>$row['CountryID'],
							"isDefault"=>$row['isDefault'],
							"CreatedOn"=>date("Y-m-d H:i:s")
						);
						$status=DB::Table('tbl_customer_address')->insert($tmp);
						if($status==true){
							DocNum::updateDocNum(docTypes::CustomerAddress->value);
						}
					}
				}
			}
			if(count($AIDs)>0){
				DB::table('tbl_customer_address')->where('CustomerID',$CustomerID)->whereNotIn('SLNO',$AIDs)->delete();
			}
			if($status){
                $CustomerName = $req->CustomerName;
                $nameParts = explode(' ', $CustomerName, 2);
                $firstName = $nameParts[0] ?? '';
                $lastName = $nameParts[1] ?? '';

                $Udata=array(
                    "Name" => $CustomerName,
                    "FirstName" => $firstName,
                    "LastName" => $lastName,
                    "ProfileImage" => $CustomerImage,
                    "MobileNumber"=>$req->MobileNo1,
					"Address"=>$req->Address,
					"PostalCodeID"=>$req->PostalCodeID,
					"CityID"=>$req->CityID,
					"TalukID"=>$req->TalukID,
					"DistrictID"=>$req->DistrictID,
					"StateID"=>$req->StateID,
					"CountryID"=>$req->CountryID,
					"UpdatedBy"=>$CustomerID,
					"UpdatedOn"=>date("Y-m-d H:i:s")
                );
				$status=DB::Table('users')->where('UserID',$this->UserID)->where('ReferID',$this->ReferID)->update($Udata);
            }
			
		}catch(Exception $e) {
			$status=false;
		}
		if($status==true){
			$NewData=DB::table('tbl_customer_address as CA')->join('tbl_customer as C','C.CustomerID','CA.CustomerID')->where('CA.CustomerID',$CustomerID)->get();
			$logData=array("Description"=>"Customer Updated ","ModuleName"=>"Customer","Action"=>"Update","ReferID"=>$CustomerID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
			logs::Store($logData);
			DB::commit();
			foreach($currCustomerImage as $KeyName=>$Img){
				Helper::removeFile($Img['url']);
			}
			return array('status'=>true,'message'=>"Customer Updated Successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"Customer Update Failed");
		}
	}
	public function getCart(){
        $Cart = DB::table('tbl_customer_cart as C')->join('tbl_products as P','P.ProductID','C.ProductID')->join('tbl_product_category as PC', 'PC.PCID', 'P.CID')->join('tbl_product_subcategory as PSC', 'PSC.PSCID', 'P.SCID')->join('tbl_uom as U', 'U.UID', 'P.UID')
        ->where('C.CustomerID', $this->ReferID)->where('P.ActiveStatus', 'Active')->where('P.DFlag', 0)->where('PC.ActiveStatus', 'Active')->where('PC.DFlag', 0)->where('PSC.ActiveStatus', 'Active')->where('PSC.DFlag', 0)
        ->select('P.ProductName','P.ProductID','C.Qty', 'PC.PCName', 'PC.PCID', 'PSC.PSCName','U.UName','U.UCode','U.UID', 'PSC.PSCID','P.ProductImage')->get();

        foreach ($Cart as $item) {
            $item->ProductImage = url('/').'/'.Helper::checkProductImageExists($item->ProductImage);
        }

        return $Cart;
    }
	public function AddCart(Request $req){ 
        DB::beginTransaction();
        $status=false;
        try {
            $isProductExists = DB::table('tbl_customer_cart')->where('CustomerID',$this->ReferID)->where('ProductID',$req->ProductID)->exists();
            if($isProductExists){
                return response()->json(['status' => false,'message' => "Product already exists!"]);
            }else{
                $data=array(
                    "CustomerID"=>$this->ReferID,
                    "ProductID"=>$req->ProductID,
                );
                $status=DB::Table('tbl_customer_cart')->insert($data);
            }
        }catch(Exception $e) {
            $status=false;
        }
        if($status==true){
            DB::commit();
            return response()->json(['status' => true,'message' => "Product added to Cart Successfully",'data' => $this->getCart()]);
        }else{
            DB::rollback();
            return response()->json(['status' => false,'message' => "Product add to Cart Failed!"]);
        }
    }
    public function UpdateCart(Request $req){ 
        DB::beginTransaction();
        $status=false;
        try {
            $status=DB::Table('tbl_customer_cart')->where('CustomerID',$this->ReferID)->where('ProductID',$req->ProductID)->update(['Qty'=>$req->Qty]);
        }catch(Exception $e) {
            $status=false;
        }
        if($status==true){
            DB::commit();
            return response()->json(['status' => true,'message' => "Product Update Successfully"]);
        }else{
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => "Product Update Failed!",
            ]);
        }
    }
    public function DeleteCart(Request $req){ 
        DB::beginTransaction();
        $status=false;
        try {
            $status=DB::Table('tbl_customer_cart')->where('CustomerID',$this->ReferID)->where('ProductID',$req->ProductID)->delete();
        }catch(Exception $e) {
            $status=false;
        }
        if($status==true){
            DB::commit();
            return response()->json(['status' => true,'message' => "Product Deleted Successfully",'data' => $this->getCart()]);
        }else{
            DB::rollback();
            return response()->json(['status' => false,'message' => "Product Deleted Failed!"]);
        }
    }
	
}
