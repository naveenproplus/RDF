<?php

namespace App\Http\Controllers\web\users;

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
use Hash;
use cruds;
use ValidUnique;
use ValidDB;
use logs;
use Helper;
use activeMenuNames;
use docTypes;
class userController extends Controller{
	private $general;
	private $UserID;
	private $ActiveMenuName;
	private $PageTitle;
	private $CRUD;
	private $Settings;
    private $Menus;
    public function __construct(){
		$this->ActiveMenuName=activeMenuNames::Users->value;
		$this->PageTitle="Users";
        $this->middleware('auth');
		$this->middleware(function ($request, $next) {
			$this->UserID=auth()->user()->UserID;
			$this->general=new general($this->UserID,$this->ActiveMenuName);
			$this->Menus=$this->general->loadMenu();
			$this->CRUD=$this->general->getCrudOperations($this->ActiveMenuName);
			$this->Settings=$this->general->getSettings();
			return $next($request);
		});
    }
	public function index(Request $req){
		if($this->general->isCrudAllow($this->CRUD,"view")==true){
			$FormData=$this->general->UserInfo;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
			$FormData['menus']=$this->Menus;
			$FormData['crud']=$this->CRUD;
			return view('app.users.users.view',$FormData);
		}elseif($this->general->isCrudAllow($this->CRUD,"Add")==true){
			return Redirect::to('/users-and-permissions/users/create');
		}else{
			return view('errors.403');
		}
	}

	public function restoreView(Request $req){
		if($this->general->isCrudAllow($this->CRUD,"restore")==true){
			$FormData=$this->general->UserInfo;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
			$FormData['menus']=$this->Menus;
			$FormData['crud']=$this->CRUD;
			return view('app.users.users.trash',$FormData);
		}elseif($this->general->isCrudAllow($this->CRUD,"view")==true){
			return Redirect::to('/users-and-permissions/users');
		}elseif($this->general->isCrudAllow($this->CRUD,"Add")==true){
			return Redirect::to('/users-and-permissions/users/create');
		}else{
			return view('errors.403');
		}
	}
	public function Create(Request $req){
		if($this->general->isCrudAllow($this->CRUD,"Add")==true){
			$FormData=$this->general->UserInfo;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
			$FormData['menus']=$this->Menus;
			$FormData['crud']=$this->CRUD;
			$FormData['URCrud']=$this->general->getCrudOperations(activeMenuNames::UserRoles->value);
			$FormData['isEdit']=false;
			return view('app.users.users.user',$FormData);
		}elseif($this->general->isCrudAllow($this->CRUD,"view")==true){
			return Redirect::to('/users-and-permissions/users');
		}else{
			return view('errors.403');
		}
	}

	public function Edit(Request $req,$UserID=null){
		if($this->general->isCrudAllow($this->CRUD,"edit")==true){
			$generalDB=Helper::getGeneralDB();
			$sql="Select U.UserID,U.FirstName,U.LastName,U.DOB,U.GenderID,U.Address,U.CityID,U.StateID,U.TalukID,U.DistrictID,U.CountryID,U.PostalCodeID,P.PostalCode,U.EMail,U.MobileNumber,U.RoleID,U.ProfileImage,U.ActiveStatus,U.isLogin From  users AS U LEFT JOIN ".$generalDB."tbl_postalcodes as P ON P.PID=U.PostalCodeID";
			$sql.=" Where U.DFlag=0 and U.UserID='".$UserID."'";
			$FormData=$this->general->UserInfo;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
			$FormData['isEdit']=true;
			$FormData['menus']=$this->Menus;
			$FormData['crud']=$this->CRUD;
			$FormData['URCrud']=$this->general->getCrudOperations(activeMenuNames::UserRoles->value);
			$FormData['UserID']=$UserID;
			$FormData['EditData']=DB::SELECT($sql);
			if(count($FormData['EditData'])>0){
				return view('app.users.users.user',$FormData);
			}else{
				return view('errors.400');
			}
		}else{
			return view('errors.403');
		}
	}
    public function getUserRoles(request $req){
        return DB::Table('tbl_user_roles')->where('ActiveStatus',1)->where('DFlag',0)->where('isShow',1)->get();
    }
	public function Save(Request $req){
		$generalDB=Helper::getGeneralDB();
		if($this->general->isCrudAllow($this->CRUD,"add")==true){

			if($req->PostalCodeID==$req->PostalCode){
				$req->PostalCodeID = $this->general->CreatePostalCode($req->PostalCode,$req->Country,$req->State);
			}
			$OldData=$NewData=array();$UserID="";
			$ValidDB=array();
			//Cities
			$ValidDB['City']['TABLE']=$generalDB."tbl_cities";
			$ValidDB['City']['ErrMsg']="City name does  not exist";
			$ValidDB['City']['WHERE'][]=array("COLUMN"=>"CityID","CONDITION"=>"=","VALUE"=>$req->City);
			$ValidDB['City']['WHERE'][]=array("COLUMN"=>"StateID","CONDITION"=>"=","VALUE"=>$req->State);
			$ValidDB['City']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->Country);

			//States
			$ValidDB['State']['TABLE']=$generalDB."tbl_states";
			$ValidDB['State']['ErrMsg']="State name does  not exist";
			$ValidDB['State']['WHERE'][]=array("COLUMN"=>"StateID","CONDITION"=>"=","VALUE"=>$req->State);
			$ValidDB['State']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->Country);

			//Country
			$ValidDB['Country']['TABLE']=$generalDB."tbl_countries";
			$ValidDB['Country']['ErrMsg']="Country name  does not exist";
			$ValidDB['Country']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->Country);

			//Postal Code
			$ValidDB['PostalCode']['TABLE']=$generalDB."tbl_postalcodes";
			$ValidDB['PostalCode']['ErrMsg']="Postal Code  does not exist";
			$ValidDB['PostalCode']['WHERE'][]=array("COLUMN"=>"PID","CONDITION"=>"=","VALUE"=>$req->PostalCodeID);
			$ValidDB['PostalCode']['WHERE'][]=array("COLUMN"=>"StateID","CONDITION"=>"=","VALUE"=>$req->State);
			$ValidDB['PostalCode']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->Country);

			//Gender
			$ValidDB['Gender']['TABLE']=$generalDB."tbl_genders";
			$ValidDB['Gender']['ErrMsg']="Gender  does not exist";
			$ValidDB['Gender']['WHERE'][]=array("COLUMN"=>"GID","CONDITION"=>"=","VALUE"=>$req->Gender);


			//User Roles
			$ValidDB['UserRole']['TABLE']="tbl_user_roles";
			$ValidDB['UserRole']['ErrMsg']="User Role  does not exist";
			$ValidDB['UserRole']['WHERE'][]=array("COLUMN"=>"RoleID","CONDITION"=>"=","VALUE"=>$req->UserRole);
			$ValidDB['UserRole']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);
			$ValidDB['UserRole']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>1);

			$rules=array(
				'FirstName' =>'required|min:3|max:50',
				'LastName' =>'max:50',
				'MobileNumber' =>['required',new ValidUnique(array("TABLE"=>"users","WHERE"=>" MobileNumber='".$req->MobileNumber."' "),"This Mobile Number is already taken.")],
				'EMail'=>['required','email',new ValidUnique(array("TABLE"=>"users","WHERE"=>" EMail='".$req->EMail."' "),"This E-Mail is already taken.")],
				'Gender'=>['required',new ValidDB($ValidDB['Gender'])],
				'State'=>['required',new ValidDB($ValidDB['State'])],
				'City'=>['required',new ValidDB($ValidDB['City'])],
				'Country'=>['required',new ValidDB($ValidDB['Country'])],
				'PostalCodeID'=>['required',new ValidDB($ValidDB['PostalCode'])],
				'Password' =>'required|min:3|max:20',
				'ConfirmPassword' =>'required|min:3|max:20|same:Password',
			);
			$message=array(
				'PostalCodeID.required'=>"Postal Code is required"
			);
			if($req->ProfileImage!=""){$rules['PImage']='mimes:jpeg,jpg,png,gif,bmp,webp';}

			$validator = Validator::make($req->all(), $rules,$message);

			if ($validator->fails()) {
				return array('status'=>false,'message'=>"User Create Failed",'errors'=>$validator->errors());
			}
			DB::beginTransaction();
			$status=false;
			$ProfileImage="";
			try{

				$UserID=DocNum::getDocNum(docTypes::Users->value);
				$dir="uploads/users/";
				if (!file_exists( $dir)) {mkdir( $dir, 0777, true);}
				if($req->hasFile('ProfileImage')){
					$file = $req->file('ProfileImage');
					$fileName=md5($file->getClientOriginalName() . time());
					$fileName1 =  $fileName. "." . $file->getClientOriginalExtension();
					$file->move($dir, $fileName1);
					$ProfileImage=$dir.$fileName1;
				}else if(Helper::isJSON($req->ProfileImage)==true){
					$Img=json_decode($req->ProfileImage);
					if(file_exists($Img->uploadPath)){
						$fileName1=$Img->fileName!=""?$Img->fileName:Helper::RandomString(10)."png";
						copy($Img->uploadPath,$dir.$fileName1);
						$ProfileImage=$dir.$fileName1;
						unlink($Img->uploadPath);
					}
				}

				$Name =  $req->FirstName." ".$req->LastName;
				$password=$req->Password;
				$pwd1=Hash::make($password);
				$pwd2=Helper::EncryptDecrypt("encrypt",$password);

				$data=array(
					"UserID"=>$UserID,
					"Name"=>$Name,
					"FirstName"=>$req->FirstName,
					"LastName"=>$req->LastName,
					"UserName"=>$req->EMail,
					"MobileNumber"=>$req->MobileNumber,
					"Password"=>$pwd1,
					"Password1"=>$pwd2,
					"RoleID"=>$req->UserRole,
					"GenderID"=>$req->Gender,
					"Address"=>$req->Address,
					"CityID"=>$req->City,
					"TalukID"=>$req->Taluk,
					"DistrictID"=>$req->District,
					"StateID"=>$req->State,
					"CountryID"=>$req->Country,
					"PostalCodeID"=>$req->PostalCodeID,
					"Email"=>$req->EMail,
					"ActiveStatus"=>$req->ActiveStatus,
					"isLogin"=>$req->loginStatus,
					"ProfileImage"=>$ProfileImage,
					"CreatedOn"=>date("Y-m-d H:i:s"),
					"CreatedBy"=>$this->UserID
				);
				$status=DB::Table('users')->insert($data);
			}catch(Exception $e) {
				$status=false;
			}
			if($status==true){
				DB::commit();
				DocNum::updateDocNum(docTypes::Users->value);
				$NewData=DB::Table('users')->where('UserID',$UserID)->get();
				$logData=array("Description"=>"New User Created ","ModuleName"=>$this->ActiveMenuName,"Action"=>cruds::ADD->value,"ReferID"=>$UserID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				return array('status'=>true,'message'=>"User Create Successfully");
			}else{
				if($ProfileImage!=""){
					if(file_exists($ProfileImage)){
						unlink($ProfileImage);
					}
				}
				DB::rollback();
				return array('status'=>false,'message'=>"User Create Failed");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}

	public function Update(Request $req,$UserID){
		$generalDB=Helper::getGeneralDB();
		if($this->general->isCrudAllow($this->CRUD,"edit")==true){

			if($req->PostalCodeID==$req->PostalCode){
				$req->PostalCodeID = $this->general->CreatePostalCode($req->PostalCode,$req->Country,$req->State);
			}
			$OldData=DB::table('users')->where('UserID',$UserID)->get();$NewData=array();
			$ValidDB=array();
			//Cities
			$ValidDB['City']['TABLE']=$generalDB."tbl_cities";
			$ValidDB['City']['ErrMsg']="City name does  not exist";
			$ValidDB['City']['WHERE'][]=array("COLUMN"=>"CityID","CONDITION"=>"=","VALUE"=>$req->City);
			$ValidDB['City']['WHERE'][]=array("COLUMN"=>"StateID","CONDITION"=>"=","VALUE"=>$req->State);
			$ValidDB['City']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->Country);

			//States
			$ValidDB['State']['TABLE']=$generalDB."tbl_states";
			$ValidDB['State']['ErrMsg']="State name does  not exist";
			$ValidDB['State']['WHERE'][]=array("COLUMN"=>"StateID","CONDITION"=>"=","VALUE"=>$req->State);
			$ValidDB['State']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->Country);

			//Country
			$ValidDB['Country']['TABLE']=$generalDB."tbl_countries";
			$ValidDB['Country']['ErrMsg']="Country name  does not exist";
			$ValidDB['Country']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->Country);

			//Postal Code
			$ValidDB['PostalCode']['TABLE']=$generalDB."tbl_postalcodes";
			$ValidDB['PostalCode']['ErrMsg']="Postal Code  does not exist";
			$ValidDB['PostalCode']['WHERE'][]=array("COLUMN"=>"PID","CONDITION"=>"=","VALUE"=>$req->PostalCodeID);
			$ValidDB['PostalCode']['WHERE'][]=array("COLUMN"=>"StateID","CONDITION"=>"=","VALUE"=>$req->State);
			$ValidDB['PostalCode']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->Country);

			//Gender
			$ValidDB['Gender']['TABLE']=$generalDB."tbl_genders";
			$ValidDB['Gender']['ErrMsg']="Gender  does not exist";
			$ValidDB['Gender']['WHERE'][]=array("COLUMN"=>"GID","CONDITION"=>"=","VALUE"=>$req->Gender);


			//Gender
			$ValidDB['UserRole']['TABLE']="tbl_user_roles";
			$ValidDB['UserRole']['ErrMsg']="User Role  does not exist";
			$ValidDB['UserRole']['WHERE'][]=array("COLUMN"=>"RoleID","CONDITION"=>"=","VALUE"=>$req->UserRole);
			$ValidDB['UserRole']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);
			$ValidDB['UserRole']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>'Active');

			$rules=array(
				'FirstName' =>'required|min:3|max:50',
				'LastName' =>'max:50',
				'MobileNumber' =>['required',new ValidUnique(array("TABLE"=>"users","WHERE"=>" MobileNumber='".$req->MobileNumber."'  and UserID<>'".$UserID."' "),"This Mobile Number is already taken.")],
				'EMail'=>['required','email:filter',new ValidUnique(array("TABLE"=>"users","WHERE"=>" EMail='".$req->EMail."' and UserID<>'".$UserID."'"),"This E-Mail is already taken.")],
				'Gender'=>['required',new ValidDB($ValidDB['Gender'])],
				'State'=>['required',new ValidDB($ValidDB['State'])],
				'City'=>['required',new ValidDB($ValidDB['City'])],
				'Country'=>['required',new ValidDB($ValidDB['Country'])],
				'PostalCodeID'=>['required',new ValidDB($ValidDB['PostalCode'])],
			);
			$message=array(
				'PostalCodeID.required'=>"Postal Code is required"
			);
			if($req->ProfileImage!=""){$rules['PImage']='mimes:jpeg,jpg,png,gif,bmp,webp';}


			$validator = Validator::make($req->all(), $rules,$message);

			if ($validator->fails()) {
				return array('status'=>false,'message'=>"User Update Failed",'errors'=>$validator->errors());
			}
			DB::beginTransaction();
			$status=false;
			$ProfileImage="";
			$currProfileImage="";
			try{
				$dir="uploads/users/";
				if (!file_exists( $dir)) {mkdir( $dir, 0777, true);}
				if($req->hasFile('ProfileImage')){
					$file = $req->file('ProfileImage');
					$fileName=md5($file->getClientOriginalName() . time());
					$fileName1 =  $fileName. "." . $file->getClientOriginalExtension();
					$file->move($dir, $fileName1);
					$ProfileImage=$dir.$fileName1;

					$result=DB::Table('users')->where('UserID',$UserID)->get();
					if(count($result)>0){
						$CPImage=$result[0]->ProfileImage;
					}
				}else if(Helper::isJSON($req->ProfileImage)==true){
					$Img=json_decode($req->ProfileImage);
					if(file_exists($Img->uploadPath)){
						$fileName1=$Img->fileName!=""?$Img->fileName:Helper::RandomString(10)."png";
						copy($Img->uploadPath,$dir.$fileName1);
						$ProfileImage=$dir.$fileName1;
						unlink($Img->uploadPath);
					}
				}
				if(($ProfileImage!="" || intval($req->removeProfileImage)==1) && Count($OldData)>0){
					$currProfileImage=$OldData[0]->ProfileImage;
				}

				$Name =  $req->FirstName." ".$req->LastName;

				$data=array(
					"Name"=>$Name,
					"FirstName"=>$req->FirstName,
					"LastName"=>$req->LastName,
					"UserName"=>$req->EMail,
					"MobileNumber"=>$req->MobileNumber,
					"RoleID"=>$req->UserRole,
					"GenderID"=>$req->Gender,
					"Address"=>$req->Address,
					"CityID"=>$req->City,
					"TalukID"=>$req->Taluk,
					"DistrictID"=>$req->District,
					"StateID"=>$req->State,
					"CountryID"=>$req->Country,
					"PostalCodeID"=>$req->PostalCodeID,
					"Email"=>$req->EMail,
					"ActiveStatus"=>$req->ActiveStatus,
					"isLogin"=>$req->loginStatus,
					"UpdatedOn"=>date("Y-m-d H:i:s"),
					"UpdatedBy"=>$this->UserID
				);
				if($ProfileImage!=""){
					$data["ProfileImage"]=$ProfileImage;
				}else if(intval($req->removeProfileImage)==1){
					$data['ProfileImage']="";
				}
				$status=DB::Table('users')->where('UserID',$UserID)->update($data);

			}catch(Exception $e) {
				$status=false;
			}
			if($status==true){
				DB::commit();

				$NewData=DB::Table('users')->where('UserID',$UserID)->get();
				$logData=array("Description"=>"User Updated ","ModuleName"=>$this->ActiveMenuName,"Action"=>cruds::UPDATE->value,"ReferID"=>$UserID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				Helper::removeFile($currProfileImage);
				return array('status'=>true,'message'=>"User Update Successfully");
			}else{
				if($ProfileImage!=""){
					if(file_exists($ProfileImage)){
						unlink($ProfileImage);
					}
				}
				DB::rollback();
				return array('status'=>false,'message'=>"User Update Failed");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}

	public function Delete(Request $req,$UserID){
		$OldData=$NewData=array();
		if($this->general->isCrudAllow($this->CRUD,"delete")==true){
			DB::beginTransaction();
			$status=false;
			try{
				$OldData=DB::table('users')->where('UserID',$UserID)->get();
				$status=DB::table('users')->where('UserID',$UserID)->update(array("DFlag"=>1,"DeletedBy"=>$this->UserID,"DeletedOn"=>date("Y-m-d H:i:s")));
			}catch(Exception $e) {

			}
			if($status==true){
				DB::commit();
				$logData=array("Description"=>"User has been Deleted ","ModuleName"=>$this->ActiveMenuName,"Action"=>cruds::DELETE->value,"ReferID"=>$UserID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				return array('status'=>true,'message'=>"User Deleted Successfully");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"User Delete Failed");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}
	public function Restore(Request $req,$UserID){
		$OldData=$NewData=array();
		if($this->general->isCrudAllow($this->CRUD,"restore")==true){
			DB::beginTransaction();
			$status=false;
			try{
				$OldData=DB::table('users')->where('UserID',$UserID)->get();
				$status=DB::table('users')->where('UserID',$UserID)->update(array("DFlag"=>0,"UpdatedBy"=>$this->UserID,"UpdatedOn"=>date("Y-m-d H:i:s")));
			}catch(Exception $e) {

			}
			if($status==true){
				DB::commit();
				$NewData=DB::table('users')->where('UserID',$UserID)->get();
				$logData=array("Description"=>"User has been Restored ","ModuleName"=>$this->ActiveMenuName,"Action"=>cruds::RESTORE->value,"ReferID"=>$UserID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				return array('status'=>true,'message'=>"User Restored Successfully");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"User Restore Failed");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}

	public function TableView(Request $request){
		$generalDB=Helper::getGeneralDB();
		if($this->general->isCrudAllow($this->CRUD,"view")==true){
			$columns = array(
                array( 'db' => 'U.Name', 'dt' => '0' ),
                array( 'db' => 'U.MobileNumber', 'dt' => '1',
					'formatter' => function( $d, $row ) {
						$MobileNumber=$row['PhoneCode']!=""?"+".$row['PhoneCode']." ":"";
						$MobileNumber.=$d;
						return $MobileNumber;
					}
				),
                array( 'db' => 'U.EMail', 'dt' => '2' ),
                array(
					'db' => 'U.Address',
					'dt' => '3',
					'formatter' => function( $d, $row ) {
						$Address=trim($d);
						$Address.=substr($Address,strlen($Address)-1)!=","?", ":" ";
						$Address.=$row['CityName'];
						$Address.=substr($Address,strlen($Address)-1)!=","?", ":" ";
						$Address.=$row['StateName'];
						$Address.=substr($Address,strlen($Address)-1)!=","?", ":" ";
						$Address.=$row['CountryName'];
						$Address.=$Address!=""?" - ":"";
						$Address.=$row['PostalCode'];

						return $Address;
					}
				),
                array( 'db' => 'U.Password', 'dt' => '4','formatter' => function( $d, $row ) { return '<span id="pwd-'.$row['UserID'].'">**********</span>';} ),
                array( 'db' => 'UR.RoleName', 'dt' => '5' ),
				array(
						'db' => 'U.ActiveStatus',
						'dt' => '6',
						'formatter' => function( $d, $row ) {
							if($d=="Active"){
								return "<span class='badge badge-success m-1'>Active</span>";
							}else{
								return "<span class='badge badge-danger m-1'>Inactive</span>";
							}
						}
                    ),
				array(
						'db' => 'U.UserID',
						'dt' => '7',
						'formatter' => function( $d, $row ) {
							$html='';
							if($this->general->isCrudAllow($this->CRUD,"ShowPwd")==true){
								$html.='<button type="button" data-id="'.$d.'" class="btn  btn-outline-info  '.$this->general->UserInfo['Theme']['button-size'].' m-5 btnPassword" data-original-title="Show Password"><i class="fa fa-key" aria-hidden="true"></i></button>';
							}
							if($this->general->isCrudAllow($this->CRUD,"edit")==true){
								$html.='<button type="button" data-id="'.$d.'" class="btn  btn-outline-success  '.$this->general->UserInfo['Theme']['button-size'].' m-5 btnEdit" data-original-title="Edit"><i class="fa fa-pencil"></i></button>';
							}
							if($this->general->isCrudAllow($this->CRUD,"delete")==true){
								$html.='<button type="button" data-id="'.$d.'" class="btn  btn-outline-danger  '.$this->general->UserInfo['Theme']['button-size'].' m-5 btnDelete" data-original-title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></button>';
							}
							return $html;
						}
					),
				array( 'db' => 'CI.CityName', 'dt' => '8' ),
				array( 'db' => 'S.StateName', 'dt' => '9' ),
				array( 'db' => 'C.CountryName', 'dt' => '10' ),
				array( 'db' => 'PC.PostalCode', 'dt' => '11' ),
				array( 'db' => 'C.PhoneCode', 'dt' => '12' ),
			);
			$columns1 = array(
                array( 'db' => 'Name', 'dt' => '0' ),
                array(
					'db' => 'MobileNumber',
					'dt' => '1',
					'formatter' => function( $d, $row ) {
						$MobileNumber=$row['PhoneCode']!=""?"+".$row['PhoneCode']." ":"";
						$MobileNumber.=$d;
						return $MobileNumber;
					}
				),
                array( 'db' => 'EMail', 'dt' => '2' ),
                array(
					'db' => 'Address',
					'dt' => '3',
					'formatter' => function( $d, $row ) {
						$Address=trim($d);
						$Address.=substr($Address,strlen($Address)-1)!=","?", ":" ";
						$Address.=$row['CityName'];
						$Address.=substr($Address,strlen($Address)-1)!=","?", ":" ";
						$Address.=$row['StateName'];
						$Address.=substr($Address,strlen($Address)-1)!=","?", ":" ";
						$Address.=$row['CountryName'];
						$Address.=$Address!=""?" - ":"";
						$Address.=$row['PostalCode'];

						return $Address;
					}
				),
                array( 'db' => 'Password', 'dt' => '4','formatter' => function( $d, $row ) {  return '<span id="pwd-'.$row['UserID'].'">**********</span>';} ),
                array( 'db' => 'RoleName', 'dt' => '5' ),
				array(
						'db' => 'ActiveStatus',
						'dt' => '6',
						'formatter' => function( $d, $row ) {
							if($d=="Active"){
								return "<span class='badge badge-success m-1'>Active</span>";
							}else{
								return "<span class='badge badge-danger m-1'>Inactive</span>";
							}
						}
                    ),
				array(
						'db' => 'UserID',
						'dt' => '7',
						'formatter' => function( $d, $row ) {
							$html='';
							if($this->general->isCrudAllow($this->CRUD,"ShowPwd")==true){
								$html.='<button type="button" data-id="'.$d.'" class="btn  btn-outline-info '.$this->general->UserInfo['Theme']['button-size'].' m-5 btnPassword" data-original-title="Show Password"><i class="fa fa-key" aria-hidden="true"></i></button>';
							}
							if($this->general->isCrudAllow($this->CRUD,"edit")==true){
								$html.='<button type="button" data-id="'.$d.'" class="btn  btn-outline-success '.$this->general->UserInfo['Theme']['button-size'].' m-5 btnEdit" data-original-title="Edit"><i class="fa fa-pencil"></i></button>';
							}
							if($this->general->isCrudAllow($this->CRUD,"delete")==true){
								$html.='<button type="button" data-id="'.$d.'" class="btn  btn-outline-danger '.$this->general->UserInfo['Theme']['button-size'].' m-5 btnDelete" data-original-title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></button>';
							}
							return $html;
						}
					),
					array( 'db' => 'CityName', 'dt' => '8' ),
					array( 'db' => 'StateName', 'dt' => '9' ),
					array( 'db' => 'CountryName', 'dt' => '10' ),
					array( 'db' => 'PostalCode', 'dt' => '11' ),
					array( 'db' => 'PhoneCode', 'dt' => '12' )
			);
			$data=array();
			$data['POSTDATA']=$request;
			$data['TABLE']=' users as U LEFT JOIN tbl_user_roles as UR ON UR.RoleID=U.RoleID LEFT JOIN '.$generalDB.'tbl_countries as C ON C.CountryID=U.CountryID LEFT JOIN '.$generalDB.'tbl_genders as G ON G.GID=U.GenderID  LEFT JOIN '.$generalDB.'tbl_states as S ON S.StateID=U.StateID LEFT JOIN '.$generalDB.'tbl_cities as CI ON CI.CityID=U.CityID LEFT JOIN '.$generalDB.'tbl_postalcodes as PC ON PC.PID=U.PostalCodeID';
			$data['PRIMARYKEY']='U.UserID';
			$data['COLUMNS']=$columns;
			$data['COLUMNS1']=$columns1;
			$data['GROUPBY']=null;
			$data['WHERERESULT']=null;
			$data['WHEREALL']=" U.DFlag=0 and U.isShow=1 ";
			return SSP::SSP( $data);
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}
	public function RestoreTableView(Request $request){
		$generalDB=Helper::getGeneralDB();
		if($this->general->isCrudAllow($this->CRUD,"restore")==true){
			$columns = array(
				array( 'db' => 'U.Name', 'dt' => '0' ),
                array(
					'db' => 'U.MobileNumber',
					'dt' => '1',
					'formatter' => function( $d, $row ) {
						$MobileNumber=$row['PhoneCode']!=""?"+".$row['PhoneCode']." ":"";
						$MobileNumber.=$d;
						return $MobileNumber;
					}
				),
                array( 'db' => 'U.EMail', 'dt' => '2' ),
                array(
					'db' => 'U.Address',
					'dt' => '3',
					'formatter' => function( $d, $row ) {
						$Address=trim($d);
						$Address.=substr($Address,strlen($Address)-1)!=","?", ":" ";
						$Address.=$row['CityName'];
						$Address.=substr($Address,strlen($Address)-1)!=","?", ":" ";
						$Address.=$row['StateName'];
						$Address.=substr($Address,strlen($Address)-1)!=","?", ":" ";
						$Address.=$row['CountryName'];
						$Address.=$Address!=""?" - ":"";
						$Address.=$row['PostalCode'];

						return $Address;
					}
				),
                array( 'db' => 'U.Password', 'dt' => '4','formatter' => function( $d, $row ) { return '<span id="pwd-'.$row['UserID'].'">**********</span>';} ),
                array( 'db' => 'UR.RoleName', 'dt' => '5' ),
				array(
						'db' => 'U.ActiveStatus',
						'dt' => '6',
						'formatter' => function( $d, $row ) {
							if($d=="Active"){
								return "<span class='badge badge-success m-1'>Active</span>";
							}else{
								return "<span class='badge badge-danger m-1'>Inactive</span>";
							}
						}
                    ),
				array(
						'db' => 'U.UserID',
						'dt' => '7',
						'formatter' => function( $d, $row ) {
							$html='<button type="button" data-id="'.$d.'" class="btn btn-outline-success '.$this->general->UserInfo['Theme']['button-size'].'  m-2 btnRestore"> <i class="fa fa-repeat" aria-hidden="true"></i> </button>';
							return $html;
						}
					),
				array( 'db' => 'CI.CityName', 'dt' => '8' ),
				array( 'db' => 'S.StateName', 'dt' => '9' ),
				array( 'db' => 'C.CountryName', 'dt' => '10' ),
				array( 'db' => 'PC.PostalCode', 'dt' => '11' ),
				array( 'db' => 'C.PhoneCode', 'dt' => '12' ),
			);
			$columns1 = array(
				array( 'db' => 'Name', 'dt' => '0' ),
                array(
					'db' => 'MobileNumber',
					'dt' => '1',
					'formatter' => function( $d, $row ) {
						$MobileNumber=$row['PhoneCode']!=""?"+".$row['PhoneCode']." ":"";
						$MobileNumber.=$d;
						return $MobileNumber;
					}
				),
                array( 'db' => 'EMail', 'dt' => '2' ),
                array(
					'db' => 'Address',
					'dt' => '3',
					'formatter' => function( $d, $row ) {
						$Address=trim($d);
						$Address.=substr($Address,strlen($Address)-1)!=","?", ":" ";
						$Address.=$row['CityName'];
						$Address.=substr($Address,strlen($Address)-1)!=","?", ":" ";
						$Address.=$row['StateName'];
						$Address.=substr($Address,strlen($Address)-1)!=","?", ":" ";
						$Address.=$row['CountryName'];
						$Address.=$Address!=""?" - ":"";
						$Address.=$row['PostalCode'];

						return $Address;
					}
				),
                array( 'db' => 'Password', 'dt' => '4','formatter' => function( $d, $row ) { return '<span id="pwd-'.$row['UserID'].'">**********</span>';} ),
                array( 'db' => 'RoleName', 'dt' => '5' ),
				array(
						'db' => 'ActiveStatus',
						'dt' => '6',
						'formatter' => function( $d, $row ) {
							if($d=="Active"){
								return "<span class='badge badge-success m-1'>Active</span>";
							}else{
								return "<span class='badge badge-danger m-1'>Inactive</span>";
							}
						}
                    ),
				array(
						'db' => 'UserID',
						'dt' => '7',
						'formatter' => function( $d, $row ) {
							$html='<button type="button" data-id="'.$d.'" class="btn btn-outline-success '.$this->general->UserInfo['Theme']['button-size'].'  m-2 btnRestore"> <i class="fa fa-repeat" aria-hidden="true"></i> </button>';
							return $html;
						}
					),
				array( 'db' => 'CityName', 'dt' => '8' ),
				array( 'db' => 'StateName', 'dt' => '9' ),
				array( 'db' => 'CountryName', 'dt' => '10' ),
				array( 'db' => 'PostalCode', 'dt' => '11' ),
				array( 'db' => 'PhoneCode', 'dt' => '12' ),
			);
			$data=array();
			$data['POSTDATA']=$request;
			$data['TABLE']=' users as U LEFT JOIN tbl_user_roles as UR ON UR.RoleID=U.RoleID LEFT JOIN '.$generalDB.'tbl_countries as C ON C.CountryID=U.CountryID LEFT JOIN '.$generalDB.'tbl_genders as G ON G.GID=U.GenderID  LEFT JOIN '.$generalDB.'tbl_states as S ON S.StateID=U.StateID LEFT JOIN '.$generalDB.'tbl_cities as CI ON CI.CityID=U.CityID LEFT JOIN '.$generalDB.'tbl_postalcodes as PC ON PC.PID=U.PostalCodeID';
			$data['PRIMARYKEY']='U.UserID';
			$data['COLUMNS']=$columns;
			$data['COLUMNS1']=$columns1;
			$data['GROUPBY']=null;
			$data['WHERERESULT']=null;
			$data['WHEREALL']=" U.DFlag=1    and U.isShow=1";
			return SSP::SSP( $data);
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}

	public function getPassword(request $req){
		//return Helper::EncryptDecrypt("decrypt","90TU2cUQSZTVkFmMLl3KNhnYGFXeT90L");
		$password="";
		$result=DB::Table('users')->where('UserID',$req->UserID)->get();
		if(count($result)>0){ //return $result[0]->password1;
			$password=Helper::EncryptDecrypt("DECRYPT",$result[0]->password1);
		}
		return array("password"=>$password);
	}
	public function getValidate(Request $req,$Type){
		if($Type=="mobile-number"){
			$sql="Select * From users where MobileNumber='".$req->MobileNumber."'";
			if($req->UserID!=""){$sql.=" and UserID<>'".$req->UserID."'";}
			$result=DB::Select($sql);
			if(count($result)>0){
				return array("status"=>false,"message"=>"This mobile number is already used");
			}else{
				return array("status"=>true,"message"=>"Mobile Number Avaiable");
			}
		}else if($Type=="email"){
			$sql="Select * From users where email='".$req->email."'";
			if($req->UserID!=""){$sql.=" and UserID<>'".$req->UserID."'";}
			$result=DB::Select($sql);
			if(count($result)>0){
				return array("status"=>false,"message"=>"This email address is already used");
			}else{
				return array("status"=>true,"message"=>"Email address Avaiable");
			}
		}
	}
}
