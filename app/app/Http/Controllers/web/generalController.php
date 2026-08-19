<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Helper;
use DB;
use DocNum;
use docTypes;
use ValidDB;
use ValidUnique;
use Auth;
class generalController extends Controller{
	private $FileTypes;
	private $generalDB;
    public function __construct(){
		$this->generalDB=Helper::getGeneralDB();
		$this->FileTypes=Helper::getFileTypes(array("category"=>array("Images")));
	}
	private function getUserID(){
		if(Auth::Check()){
			return auth()->user()->UserID;
		}
		return "";
	}
    private function getThemesOption(){
		$UserID=$this->getUserID();
    	$return=array("button-size"=>"btn-sm","table-size"=>"table-sm","input-size"=>"","switch-size"=>"");
    	$result=DB::Table('tbl_user_theme')->where('UserID',$UserID)->get();
    	if(count($result)>0){
    		for($i=0;$i<count($result);$i++){
    			$return[$result[$i]->Theme_option]=$result[$i]->Theme_Value;
    		}
    	}
    	return $return;
    }
	//General
	public function getCountry(request $req){
        return Helper::getCountry();
	}
	public function getState(request $req){
        return Helper::getState(array("CountryID"=>$req->CountryID));
	}
	public function getDistrict(request $req){
        return Helper::getDistrict(array("CountryID"=>$req->CountryID,"StateID"=>$req->StateID));
	}
	public function getTaluk(request $req){
        return Helper::getTaluk(array("CountryID"=>$req->CountryID,"StateID"=>$req->StateID,"DistrictID"=>$req->DistrictID));
	}
	public function getCity(request $req){
        return Helper::getCity(array("CountryID"=>$req->CountryID,"StateID"=>$req->StateID,"DistrictID"=>$req->DistrictID,"TalukID"=>$req->TalukID,"PostalID"=>$req->PostalID,"PostalCode"=>$req->PostalCode));
	}
	public function getPostalCode(request $req){
        return Helper::getPostalCode(array("CountryID"=>$req->CountryID,"StateID"=>$req->StateID,"DistrictID"=>$req->DistrictID));
	}
	public function getGender(request $req){
        return Helper::getGender();
	}
	public function getNewGender(Request $req){
		$Theme=$this->getThemesOption();
		return view("app.modals.gender",array("Theme"=>$Theme));
	}
	public function getNewCountry(Request $req){
		$Theme=$this->getThemesOption();
		return view("app.modals.country",array("Theme"=>$Theme));
	}
	public function getNewState(Request $req){
		$Theme=$this->getThemesOption();
		$Country=Helper::getCountry();
		return view("app.modals.state",array("Theme"=>$Theme,"Country"=>$Country,"CountryID"=>$req->CountryID));
	}
	public function getNewDistrict(Request $req){
		$Theme=$this->getThemesOption();
		$Country=Helper::getCountry();
		$State=Helper::getState();
		return view("app.modals.district",array("Theme"=>$Theme,"Country"=>$Country,"State"=>$State,"CountryID"=>$req->CountryID,"StateID"=>$req->StateID));
	}
	public function getNewTaluk(Request $req){
		$Theme=$this->getThemesOption();
		$Country=Helper::getCountry();
		$State=Helper::getState(["CountryID"=>$req->CountryID]);
		$District=Helper::getDistrict(["CountryID"=>$req->CountryID,"StateID"=>$req->StateID]);
		return view("app.modals.taluk",array("Theme"=>$Theme,"Country"=>$Country,"State"=>$State,"District"=>$District,"CountryID"=>$req->CountryID,"StateID"=>$req->StateID,"DistrictID"=>$req->DistrictID));
	}
	public function getNewPostalCode(Request $req){
		$Theme=$this->getThemesOption();
		$Country=Helper::getCountry();
		$State=Helper::getState(["CountryID"=>$req->CountryID]);
		$District=Helper::getDistrict(["CountryID"=>$req->CountryID,"StateID"=>$req->StateID]);
		return view("app.modals.postal-code",array("Theme"=>$Theme,"Country"=>$Country,"State"=>$State,"District"=>$District,"CountryID"=>$req->CountryID,"StateID"=>$req->StateID,"DistrictID"=>$req->DistrictID));
	}
	public function getNewCity(Request $req){
		$Theme=$this->getThemesOption();
		$Country=Helper::getCountry();
		$State=Helper::getState(["CountryID"=>$req->CountryID]);
		$District=Helper::getDistrict(["CountryID"=>$req->CountryID,"StateID"=>$req->StateID]);
		$Taluk=Helper::getTaluk(["CountryID"=>$req->CountryID,"StateID"=>$req->StateID,"DistrictID"=>$req->DistrictID]);
		$PostalCode=Helper::getPostalCode(["CountryID"=>$req->CountryID,"StateID"=>$req->StateID,"DistrictID"=>$req->DistrictID]);
		return view("app.modals.city",array("Theme"=>$Theme,"Country"=>$Country,"State"=>$State,"District"=>$District,"Taluk"=>$Taluk,"PostalCode"=>$PostalCode,"CountryID"=>$req->CountryID,"StateID"=>$req->StateID,"DistrictID"=>$req->DistrictID,"TalukID"=>$req->TalukID,"PID"=>$req->PID));
	}

	public function getTax(request $req){
		return DB::Table('tbl_tax')->where('DFlag',0)->where('ActiveStatus','Active')->get();
	}
	public function getUOM(request $req){
		return DB::Table('tbl_uom')->where('DFlag',0)->where('ActiveStatus','Active')->get();
	}
	public function createCountry(Request $req){
		$OldData=$NewData=array();$CID="";
		$rules=array(
			'ShortName' =>['required','min:2','max:6',new ValidUnique(array("TABLE"=>$this->generalDB."tbl_countries","WHERE"=>" sortname='".$req->ShortName."' "),"This Short Name is already taken.")],
			'CountryName' =>['required','min:3','max:100',new ValidUnique(array("TABLE"=>$this->generalDB."tbl_countries","WHERE"=>" CountryName='".$req->CountryName."' "),"This Country Name is already taken.")],
			'CallingCode' =>'required|numeric',
			'PhoneLength' =>'required|numeric',
		);
		$message=array(
		);
		$validator = Validator::make($req->all(), $rules,$message);

		if ($validator->fails()) {
			return array('status'=>false,'message'=>"Country Create Failed",'errors'=>$validator->errors());
		}
		DB::beginTransaction();

		$status=false;
		$UserID=Auth::check()?auth()->user()->UserID:"";
		try{
			$CID=DocNum::getDocNum(docTypes::Country->value);
			$data=array(
				"CountryID"=>$CID,
				"sortname"=>$req->ShortName,
				"CountryName"=>$req->CountryName,
				"PhoneCode"=>$req->CallingCode,
				"PhoneLength"=>$req->PhoneLength,
				"CreatedBy"=>$UserID,
				"CreatedOn"=>date("Y-m-d H:i:s")
			);
			$status=DB::table($this->generalDB.'tbl_countries')->insert($data);
		}catch(Exception $e) {
			$status=false;
		}
		if($status==true){
			DocNum::updateDocNum(docTypes::Country->value);
			DB::commit();
			return array('status'=>true,'message'=>"Country Create Successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"Country Create Failed");
		}
	}
	public function createState(Request $req){
		$OldData=$NewData=array();$CID="";
		$ValidDB=array();
		$ValidDB['Country']['TABLE']=$this->generalDB."tbl_countries";
		$ValidDB['Country']['ErrMsg']="Country name  does not exist";
		$ValidDB['Country']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->CountryID);
		$ValidDB['Country']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>1);
		$ValidDB['Country']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);

		$rules=array(
			'CountryID' =>['required',$ValidDB['Country']],
			'StateName' =>['required','min:3','max:100',new ValidUnique(array("TABLE"=>$this->generalDB."tbl_states","WHERE"=>" StateName='".$req->StateName."' and CountryID='".$req->CountryID."' "),"This State Name is already taken.")],
			'StateCode' =>['required',new ValidUnique(array("TABLE"=>$this->generalDB."tbl_states","WHERE"=>" StateCode='".$req->StateCode."' and CountryID='".$req->CountryID."' "),"This State Code is already taken.")],
		);
		$message=array(
		);
		$validator = Validator::make($req->all(), $rules,$message);

		if ($validator->fails()) {
			return array('status'=>false,'message'=>"State Create Failed",'errors'=>$validator->errors());
		}
		DB::beginTransaction();

		$status=false;
		try{
			$UserID=Auth::check()?auth()->user()->UserID:"";
			$StateID=DocNum::getDocNum(docTypes::States->value);
			$data=array(
				"StateID"=>$StateID,
				"CountryID"=>$req->CountryID,
				"StateName"=>$req->StateName,
				"StateCode"=>$req->StateCode,
				"CreatedBy"=>$UserID,
				"CreatedOn"=>date("Y-m-d H:i:s")
			);
			$status=DB::table($this->generalDB.'tbl_states')->insert($data);
		}catch(Exception $e) {
			$status=false;
		}
		if($status==true){
			DocNum::updateDocNum(docTypes::States->value);
			DB::commit();
			return array('status'=>true,'message'=>"State Create Successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"State Create Failed");
		}
	}
	public function createDistrict(Request $req){
		$OldData=$NewData=array();$CID="";
		$ValidDB=array();
		$ValidDB['Country']['TABLE']=$this->generalDB."tbl_countries";
		$ValidDB['Country']['ErrMsg']="Country name  does not exist";
		$ValidDB['Country']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->CountryID);
		$ValidDB['Country']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>1);
		$ValidDB['Country']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);

		$ValidDB['State']['TABLE']=$this->generalDB."tbl_states";
		$ValidDB['State']['ErrMsg']="State name  does not exist";
		$ValidDB['State']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->CountryID);
		$ValidDB['State']['WHERE'][]=array("COLUMN"=>"StateID","CONDITION"=>"=","VALUE"=>$req->StateID);
		$ValidDB['State']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>1);
		$ValidDB['State']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);
		$rules=array(
			'CountryID' =>['required',$ValidDB['Country']],
			'StateID' =>['required',$ValidDB['State']],
			'DistrictName' =>['required','min:3','max:100',new ValidUnique(array("TABLE"=>$this->generalDB."tbl_districts","WHERE"=>" DistrictName='".$req->DistrictName."' and CountryID='".$req->CountryID."' and StateID='".$req->StateID."' "),"This District is already taken.")],
		);
		$message=array(
		);
		$validator = Validator::make($req->all(), $rules,$message);

		if ($validator->fails()) {
			return array('status'=>false,'message'=>"District Create Failed",'errors'=>$validator->errors());
		}
		DB::beginTransaction();

		$status=false;
		try{
			$UserID=Auth::check()?auth()->user()->UserID:"";
			$DistrictID=DocNum::getDocNum(docTypes::Districts->value);
			$data=array(
				"DistrictID"=>$DistrictID,
				"CountryID"=>$req->CountryID,
				"StateID"=>$req->StateID,
				"DistrictName"=>$req->DistrictName,
				"CreatedBy"=>$UserID,
				"CreatedOn"=>date("Y-m-d H:i:s")
			);
			$status=DB::table($this->generalDB.'tbl_districts')->insert($data);
		}catch(Exception $e) {
			$status=false;
		}
		if($status==true){
			DocNum::updateDocNum(docTypes::Districts->value);
			DB::commit();
			return array('status'=>true,'message'=>"District Create Successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"District Create Failed");
		}
	}
	public function createTaluk(Request $req){
		$OldData=$NewData=array();$CID="";
		$ValidDB=array();
		$ValidDB['Country']['TABLE']=$this->generalDB."tbl_countries";
		$ValidDB['Country']['ErrMsg']="Country name  does not exist";
		$ValidDB['Country']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->CountryID);
		$ValidDB['Country']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>1);
		$ValidDB['Country']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);

		$ValidDB['State']['TABLE']=$this->generalDB."tbl_states";
		$ValidDB['State']['ErrMsg']="State name  does not exist";
		$ValidDB['State']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->CountryID);
		$ValidDB['State']['WHERE'][]=array("COLUMN"=>"StateID","CONDITION"=>"=","VALUE"=>$req->StateID);
		$ValidDB['State']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>1);
		$ValidDB['State']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);

		$ValidDB['District']['TABLE']=$this->generalDB."tbl_districts";
		$ValidDB['District']['ErrMsg']="District name  does not exist";
		$ValidDB['District']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->CountryID);
		$ValidDB['District']['WHERE'][]=array("COLUMN"=>"StateID","CONDITION"=>"=","VALUE"=>$req->StateID);
		$ValidDB['District']['WHERE'][]=array("COLUMN"=>"DistrictID","CONDITION"=>"=","VALUE"=>$req->DistrictID);
		$ValidDB['District']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>1);
		$ValidDB['District']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);
		$rules=array(
			'CountryID' =>['required',$ValidDB['Country']],
			'StateID' =>['required',$ValidDB['State']],
			'DistrictID' =>['required',$ValidDB['District']],
			'TalukName' =>['required','min:3','max:100',new ValidUnique(array("TABLE"=>$this->generalDB."tbl_taluks","WHERE"=>" TalukName='".$req->TalukName."' and CountryID='".$req->CountryID."' and StateID='".$req->StateID."' and DistrictID='".$req->DistrictID."' "),"This Taluk Name is already taken.")],
		);
		$message=array(
		);
		$validator = Validator::make($req->all(), $rules,$message);

		if ($validator->fails()) {
			return array('status'=>false,'message'=>"Taluk Create Failed",'errors'=>$validator->errors());
		}
		DB::beginTransaction();

		$status=false;
		try{
			$UserID=Auth::check()?auth()->user()->UserID:"";
			$TalukID=DocNum::getDocNum(docTypes::Taluks->value);
			$data=array(
				"TalukID"=>$TalukID,
				"CountryID"=>$req->CountryID,
				"StateID"=>$req->StateID,
				"DistrictID"=>$req->DistrictID,
				"TalukName"=>$req->TalukName,
				"CreatedBy"=>$UserID,
				"CreatedOn"=>date("Y-m-d H:i:s")
			);
			$status=DB::table($this->generalDB.'tbl_taluks')->insert($data);
		}catch(Exception $e) {
			$status=false;
		}
		if($status==true){
			DocNum::updateDocNum(docTypes::Taluks->value);
			DB::commit();
			return array('status'=>true,'message'=>"Taluk Create Successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"Taluk Create Failed");
		}
	}
	public function createCity(Request $req){
		$OldData=$NewData=array();$CID="";
		$ValidDB=array();
		$ValidDB['Country']['TABLE']=$this->generalDB."tbl_countries";
		$ValidDB['Country']['ErrMsg']="Country name does not exist";
		$ValidDB['Country']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->CountryID);
		$ValidDB['Country']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>1);
		$ValidDB['Country']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);

		$ValidDB['State']['TABLE']=$this->generalDB."tbl_states";
		$ValidDB['State']['ErrMsg']="State name does not exist";
		$ValidDB['State']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->CountryID);
		$ValidDB['State']['WHERE'][]=array("COLUMN"=>"StateID","CONDITION"=>"=","VALUE"=>$req->StateID);
		$ValidDB['State']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>1);
		$ValidDB['State']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);

		$ValidDB['District']['TABLE']=$this->generalDB."tbl_districts";
		$ValidDB['District']['ErrMsg']="District name does not exist";
		$ValidDB['District']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->CountryID);
		$ValidDB['District']['WHERE'][]=array("COLUMN"=>"StateID","CONDITION"=>"=","VALUE"=>$req->StateID);
		$ValidDB['District']['WHERE'][]=array("COLUMN"=>"DistrictID","CONDITION"=>"=","VALUE"=>$req->DistrictID);
		$ValidDB['District']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>1);
		$ValidDB['District']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);

		$ValidDB['Taluk']['TABLE']=$this->generalDB."tbl_taluks";
		$ValidDB['Taluk']['ErrMsg']="Taluk name does not exist";
		$ValidDB['Taluk']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->CountryID);
		$ValidDB['Taluk']['WHERE'][]=array("COLUMN"=>"StateID","CONDITION"=>"=","VALUE"=>$req->StateID);
		$ValidDB['Taluk']['WHERE'][]=array("COLUMN"=>"DistrictID","CONDITION"=>"=","VALUE"=>$req->DistrictID);
		$ValidDB['Taluk']['WHERE'][]=array("COLUMN"=>"TalukID","CONDITION"=>"=","VALUE"=>$req->TalukID);
		$ValidDB['Taluk']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>1);
		$ValidDB['Taluk']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);

		$ValidDB['PostalCode']['TABLE']=$this->generalDB."tbl_postalcodes";
		$ValidDB['PostalCode']['ErrMsg']="Postal Code does not exist";
		$ValidDB['PostalCode']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->CountryID);
		$ValidDB['PostalCode']['WHERE'][]=array("COLUMN"=>"StateID","CONDITION"=>"=","VALUE"=>$req->StateID);
		$ValidDB['PostalCode']['WHERE'][]=array("COLUMN"=>"DistrictID","CONDITION"=>"=","VALUE"=>$req->DistrictID);
		$ValidDB['PostalCode']['WHERE'][]=array("COLUMN"=>"PID","CONDITION"=>"=","VALUE"=>$req->PID);
		$ValidDB['PostalCode']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>1);
		$ValidDB['PostalCode']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);

		$rules=array(
			'CountryID' =>['required',$ValidDB['Country']],
			'StateID' =>['required',$ValidDB['State']],
			'DistrictID' =>['required',$ValidDB['District']],
			'TalukID' =>['required',$ValidDB['Taluk']],
			'PID' =>['required',$ValidDB['PostalCode']],
			'CityName' =>['required','min:3','max:100',new ValidUnique(array("TABLE"=>$this->generalDB."tbl_cities","WHERE"=>" CityName='".$req->CityName."' and CountryID='".$req->CountryID."' and StateID='".$req->StateID."' and DistrictID='".$req->DistrictID."' and TalukID='".$req->TalukID."' "),"This City Name is already taken.")],
		);
		$message=array();
		$validator = Validator::make($req->all(), $rules,$message);

		if ($validator->fails()) {
			return array('status'=>false,'message'=>"City Create Failed",'errors'=>$validator->errors());
		}
		DB::beginTransaction();

		$status=false;
		try{
			$UserID=Auth::check()?auth()->user()->UserID:"";
			$CityID=DocNum::getDocNum(docTypes::City->value);
			$data=array(
				"CityID"=>$CityID,
				"CountryID"=>$req->CountryID,
				"StateID"=>$req->StateID,
				"DistrictID"=>$req->DistrictID,
				"TalukID"=>$req->TalukID,
				"PostalID"=>$req->PID,
				"CityName"=>$req->CityName,
				"CreatedBy"=>$UserID,
				"CreatedOn"=>date("Y-m-d H:i:s")
			);
			$status=DB::table($this->generalDB.'tbl_cities')->insert($data);
		}catch(Exception $e) {
			$status=false;
		}
		if($status==true){
			DocNum::updateDocNum(docTypes::City->value);
			DB::commit();
			return array('status'=>true,'message'=>"City Create Successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"City Create Failed");
		}
	}
	public function createPostalCode(Request $req){
		$OldData=$NewData=array();$CID="";
		$ValidDB=array();
		$ValidDB['Country']['TABLE']=$this->generalDB."tbl_countries";
		$ValidDB['Country']['ErrMsg']="Country name  does not exist";
		$ValidDB['Country']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->CountryID);
		$ValidDB['Country']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>1);
		$ValidDB['Country']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);

		$ValidDB['State']['TABLE']=$this->generalDB."tbl_states";
		$ValidDB['State']['ErrMsg']="State name  does not exist";
		$ValidDB['State']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->CountryID);
		$ValidDB['State']['WHERE'][]=array("COLUMN"=>"StateID","CONDITION"=>"=","VALUE"=>$req->StateID);
		$ValidDB['State']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>1);
		$ValidDB['State']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);

		$ValidDB['District']['TABLE']=$this->generalDB."tbl_districts";
		$ValidDB['District']['ErrMsg']="District name  does not exist";
		$ValidDB['District']['WHERE'][]=array("COLUMN"=>"CountryID","CONDITION"=>"=","VALUE"=>$req->CountryID);
		$ValidDB['District']['WHERE'][]=array("COLUMN"=>"StateID","CONDITION"=>"=","VALUE"=>$req->StateID);
		$ValidDB['District']['WHERE'][]=array("COLUMN"=>"DistrictID","CONDITION"=>"=","VALUE"=>$req->DistrictID);
		$ValidDB['District']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>1);
		$ValidDB['District']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);
		$rules=array(
			'CountryID' =>['required',$ValidDB['Country']],
			'StateID' =>['required',$ValidDB['State']],
			'DistrictID' =>['required',$ValidDB['District']],
			'PostalCode' =>['required','min:3','max:100',new ValidUnique(array("TABLE"=>$this->generalDB."tbl_postalcodes","WHERE"=>" PostalCode='".$req->PostalCode."' and CountryID='".$req->CountryID."' and StateID='".$req->StateID."' and DistrictID='".$req->DistrictID."' "),"This Postal Code is already taken.")],
		);
		$message=array(
		);
		$validator = Validator::make($req->all(), $rules,$message);

		if ($validator->fails()) {
			return array('status'=>false,'message'=>"PostalCode Create Failed",'errors'=>$validator->errors());
		}
		DB::beginTransaction();

		$status=false;
		try{
			$UserID=Auth::check()?auth()->user()->UserID:"";
			$PID=DocNum::getDocNum(docTypes::PostalCodes->value);
			$data=array(
				"PID"=>$PID,
				"CountryID"=>$req->CountryID,
				"StateID"=>$req->StateID,
				"DistrictID"=>$req->DistrictID,
				"PostalCode"=>$req->PostalCode,
				"CreatedBy"=>$UserID,
				"CreatedOn"=>date("Y-m-d H:i:s")
			);
			$status=DB::table($this->generalDB.'tbl_postalcodes')->insert($data);
		}catch(Exception $e) {
			$status=false;
		}
		if($status==true){
			DocNum::updateDocNum(docTypes::PostalCodes->value);
			DB::commit();
			return array('status'=>true,'message'=>"Postal Code Create Successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"Postal Code Create Failed");
		}
	}
	public function createGender(Request $req){
		$OldData=$NewData=array();$GenderID="";
		$rules=array(
			'Gender' =>['required','min:3','max:100',new ValidUnique(array("TABLE"=>$this->generalDB."tbl_genders","WHERE"=>" Gender='".$req->Gender."' "),"This Gender is already taken.")],
		);
		$message=array(
		);
		$validator = Validator::make($req->all(), $rules,$message);

		if ($validator->fails()) {
			return array('status'=>false,'message'=>"Gender Create Failed",'errors'=>$validator->errors());
		}
		DB::beginTransaction();

		$status=false;
		$UserID=Auth::check()?auth()->user()->UserID:"";
		try{
			$GenderID=DocNum::getDocNum(docTypes::Gender->value);
			$data=array(
				"GID"=>$GenderID,
				"Gender"=>$req->Gender,
				"CreatedBy"=>$UserID,
				"CreatedOn"=>date("Y-m-d H:i:s")
			);
			$status=DB::table($this->generalDB.'tbl_genders')->insert($data);
		}catch(Exception $e) {
			$status=false;
		}
		if($status==true){
			DocNum::updateDocNum(docTypes::Gender->value);
			DB::commit();
			return array('status'=>true,'message'=>"Gender Create Successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"Gender Create Failed");
		}
	}
	public function getNewTax(Request $req){
		$Theme=$this->getThemesOption();
		return view("app.modals.tax",array("Theme"=>$Theme));
	}
	public function getNewUOM(Request $req){
		$Theme=$this->getThemesOption();
        $languages = Language::active()->get();
		return view("app.modals.uom",array("Theme"=>$Theme, "languages" => $languages));
	}
	public function tmpUploadImage(Request $req){
		//remove yesterday folder
		$dir="uploads/tmp/".date("Ymd",strtotime("-1 days"))."/";
		if (file_exists( $dir)) {
			$files = glob($dir."*"); // get all file names
			foreach($files as $file){ // iterate files
				if(is_file($file)) {
					unlink($file); // delete file
				}
			}
			rmdir($dir);
		}

		$dir="uploads/tmp/".date("Ymd")."/";
		if (!file_exists( $dir)) {mkdir( $dir, 0777, true);}

		if($req->hasFile('image')){
			$file=$req->file('image');
			$ext=$file->getClientOriginalExtension();
			$rnd=Helper::RandomString(10)."_".date("YmdHis");
			$tname=md5($file->getClientOriginalName() . time());
			$fileName=$tname. "." . $file->getClientOriginalExtension();
			$fileName1 =  $tname. "-tmp." . $file->getClientOriginalExtension();
			$file->move($dir, $fileName1);
			return array("uploadPath"=>$dir.$fileName1,"fileName"=>$fileName,"ext"=>$ext,"referData"=>$req->referData);
		}elseif($req->image!=""){
			$rnd=Helper::RandomString(10)."_".date("YmdHis");
			$fileName = $rnd.".png";
			$fileName1 = $rnd."-tmp.png";
			$imgData = $this->getImageData($req->image);
			file_put_contents($dir.$fileName1, $imgData);
			return array("uploadPath"=>$dir.$fileName1,"fileName"=>$fileName,"ext"=>"png","referData"=>$req->referData);
		}
		return array("uploadPath"=>"","fileName"=>"","referData"=>$req->referData);
	}

	private function getImageData($base64){
		$base64_str = substr($base64, strpos($base64, ",")+1);
		$image = base64_decode($base64_str);
		return $image;
	}
	public function themeUpdate(Request $req){
		$UserID=auth()->user()->UserID;
		try {
			$Theme=json_decode($req->Theme,true);
			if(is_array($Theme)){
				foreach ($Theme as $key => $value) {
					$result=DB::table('tbl_user_theme')->where('UserID',$UserID)->where('Theme_option',$key)->get();
					if(count($result)>0){
						$data=array($key=>$value);
						DB::table('tbl_user_theme')->where('UserID',$UserID)->where('Theme_option',$key)->update(array("Theme_Value" => $value));
					}else{
						DB::table('tbl_user_theme')->insert(array('UserID'=>$UserID,'Theme_option'=>$key,"Theme_Value" => $value));
					}
				}
			}

		} catch (Exception $e) {

		}
	}
	public function getFinancialYear(Request $req){
		return Helper::getFinancialYears();
	}
	public function updateActiveFinancialYear(Request $req){
		$FYID=$req->FYID;
		return Helper::ActivateFinancialYear($FYID);
	}

	public function getVehicleType(request $req){
        return Helper::getVehicleType();
	}
	public function getVehicleBrand(request $req){
        return Helper::getVehicleBrand(array("VehicleTypeID"=>$req->VehicleTypeID));
	}
	public function getVehicleModel(request $req){
        return Helper::getVehicleModel(array("VehicleTypeID"=>$req->VehicleTypeID,"VehicleBrandID"=>$req->VehicleBrandID));
	}

	public function getNewVehicleType(Request $req){
		$Theme=$this->getThemesOption();
		return view("app.modals.vehicle-type",array("Theme"=>$Theme));
	}
	public function getNewVehicleBrand(Request $req){
		$Theme=$this->getThemesOption();
		$VehicleType=Helper::getVehicleType();
		return view("app.modals.vehicle-brand",array("Theme"=>$Theme,"VehicleType"=>$VehicleType,"VehicleTypeID"=>$req->VehicleTypeID));
	}
	public function getNewVehicleModel(Request $req){
		$Theme=$this->getThemesOption();
		$VehicleType=Helper::getVehicleType();
		$VehicleBrand=Helper::getVehicleBrand();
		return view("app.modals.vehicle-model",array("Theme"=>$Theme,"VehicleType"=>$VehicleType,"VehicleBrand"=>$VehicleBrand,"VehicleTypeID"=>$req->VehicleTypeID,"VehicleBrandID"=>$req->VehicleBrandID));
	}

	public function createVehicleType(Request $req){
		$OldData=$NewData=array();$VehicleTypeID="";
		$rules=array(
			'VehicleType' =>['required','min:3','max:100',new ValidUnique(array("TABLE"=>$this->generalDB."tbl_vehicle_type","WHERE"=>" VehicleType='".$req->VehicleType."' "),"This Vehicle Type is already taken.")],
		);
		$message=array();
		$validator = Validator::make($req->all(), $rules,$message);
		if ($validator->fails()) {
			return array('status'=>false,'message'=>"Vehicle Type Create Failed",'errors'=>$validator->errors());
		}
		DB::beginTransaction();

		$status=false;
		$UserID=Auth::check()?auth()->user()->UserID:"";
		try{
			$VehicleTypeID=DocNum::getDocNum(docTypes::VehicleType->value);
			$data=array(
				"VehicleTypeID"=>$VehicleTypeID,
				"VehicleType"=>$req->VehicleType,
				"CreatedBy"=>$UserID,
				"CreatedOn"=>date("Y-m-d H:i:s")
			);
			$status=DB::table($this->generalDB.'tbl_vehicle_type')->insert($data);
		}catch(Exception $e) {
			$status=false;
		}
		if($status==true){
			DocNum::updateDocNum(docTypes::VehicleType->value);
			DB::commit();
			return array('status'=>true,'message'=>"Vehicle Type Create Successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"Vehicle Type Create Failed");
		}
	}
	public function createVehicleBrand(Request $req){
		$OldData=$NewData=array();$VehicleBrandID="";
		$ValidDB=array();
		$ValidDB['VehicleType']['TABLE']=$this->generalDB."tbl_vehicle_type";
		$ValidDB['VehicleType']['ErrMsg']="Vehicle Type does not exist";
		$ValidDB['VehicleType']['WHERE'][]=array("COLUMN"=>"VehicleTypeID","CONDITION"=>"=","VALUE"=>$req->VehicleTypeID);
		$ValidDB['VehicleType']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>1);
		$ValidDB['VehicleType']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);

		$rules=array(
			'VehicleTypeID' =>['required',$ValidDB['VehicleType']],
			'VehicleBrandName' =>['required','min:3','max:100',new ValidUnique(array("TABLE"=>$this->generalDB."tbl_vehicle_brand","WHERE"=>" VehicleBrandName='".$req->VehicleBrandName."' and VehicleTypeID='".$req->VehicleTypeID."' "),"This VehicleBrandName is already taken.")],
		);
		$message=array();
		$validator = Validator::make($req->all(), $rules,$message);

		if ($validator->fails()) {
			return array('status'=>false,'message'=>"Vehicle Brand Create Failed",'errors'=>$validator->errors());
		}
		DB::beginTransaction();

		$status=false;
		try{
			$UserID=Auth::check()?auth()->user()->UserID:"";
			$VehicleBrandID=DocNum::getDocNum(docTypes::VehicleBrand->value);
			$data=array(
				"VehicleBrandID"=>$VehicleBrandID,
				"VehicleBrandName"=>$req->VehicleBrandName,
				"VehicleTypeID"=>$req->VehicleTypeID,
				"CreatedBy"=>$UserID,
				"CreatedOn"=>date("Y-m-d H:i:s")
			);
			$status=DB::table($this->generalDB.'tbl_vehicle_brand')->insert($data);
		}catch(Exception $e) {
			$status=false;
		}
		if($status==true){
			DocNum::updateDocNum(docTypes::VehicleBrand->value);
			DB::commit();
			return array('status'=>true,'message'=>"Vehicle Brand Create Successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"Vehicle Brand Create Failed");
		}
	}
	public function createVehicleModel(Request $req){
		$OldData=$NewData=array();$VehicleBrandID="";
		$ValidDB=array();
		$ValidDB['VehicleType']['TABLE']=$this->generalDB."tbl_vehicle_type";
		$ValidDB['VehicleType']['ErrMsg']="Vehicle Type does not exist";
		$ValidDB['VehicleType']['WHERE'][]=array("COLUMN"=>"VehicleTypeID","CONDITION"=>"=","VALUE"=>$req->VehicleTypeID);
		$ValidDB['VehicleType']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>1);
		$ValidDB['VehicleType']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);

		$ValidDB['VehicleBrand']['TABLE']=$this->generalDB."tbl_vehicle_brand";
		$ValidDB['VehicleBrand']['ErrMsg']="Vehicle Brand does not exist";
		$ValidDB['VehicleBrand']['WHERE'][]=array("COLUMN"=>"VehicleBrandID","CONDITION"=>"=","VALUE"=>$req->VehicleBrandID);
		$ValidDB['VehicleBrand']['WHERE'][]=array("COLUMN"=>"VehicleTypeID","CONDITION"=>"=","VALUE"=>$req->VehicleTypeID);
		$ValidDB['VehicleBrand']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>1);
		$ValidDB['VehicleBrand']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);

		$rules=array(
			'VehicleTypeID' =>['required',$ValidDB['VehicleType']],
			'VehicleBrandID' =>['required',$ValidDB['VehicleBrand']],
			'VehicleModel' =>['required','min:3','max:100',new ValidUnique(array("TABLE"=>$this->generalDB."tbl_vehicle_model","WHERE"=>" VehicleModel='".$req->VehicleModel."' and VehicleBrandID='".$req->VehicleBrandID."' and VehicleTypeID='".$req->VehicleTypeID."' "),"This Vehicle Model is already taken.")],
		);
		$message=array();
		$validator = Validator::make($req->all(), $rules,$message);

		if ($validator->fails()) {
			return array('status'=>false,'message'=>"Vehicle Model Create Failed",'errors'=>$validator->errors());
		}
		DB::beginTransaction();

		$status=false;
		try{
			$UserID=Auth::check()?auth()->user()->UserID:"";
			$VehicleModelID=DocNum::getDocNum(docTypes::VehicleModel->value);
			$data=array(
				"VehicleModelID"=>$VehicleModelID,
				"VehicleModel"=>$req->VehicleModel,
				"VehicleTypeID"=>$req->VehicleTypeID,
				"VehicleBrandID"=>$req->VehicleBrandID,
				"CreatedBy"=>$UserID,
				"CreatedOn"=>date("Y-m-d H:i:s")
			);
			$status=DB::table($this->generalDB.'tbl_vehicle_model')->insert($data);
		}catch(Exception $e) {
			$status=false;
		}
		if($status==true){
			DocNum::updateDocNum(docTypes::VehicleModel->value);
			DB::commit();
			return array('status'=>true,'message'=>"Vehicle Model Create Successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"Vehicle Model Create Failed");
		}
	}

	//Banks
	public function getBankType(request $req){
        return Helper::getBankType();
	}
	public function getBank(request $req){
        return Helper::getBanks(array("TypeOfBankID"=>$req->TypeOfBankID));
	}
	public function getBankBranch(request $req){
        return Helper::getBankBranch(array("BankID"=>$req->BankID));
	}
	public function getBankAccountType(request $req){
        return Helper::getBankAccountType(array());
	}

	public function getNewBankType(Request $req){
		$Theme=$this->getThemesOption();
		return view("app.modals.bank-type",array("Theme"=>$Theme));
	}
	public function getNewBank(Request $req){
		$Theme=$this->getThemesOption();
		$BankType=Helper::getBankType();
		return view("app.modals.Bank",array("Theme"=>$Theme,"BankType"=>$BankType,"BankTypeID"=>$req->BankTypeID));
	}
	public function getNewBankBranch(Request $req){
		$Theme=$this->getThemesOption();
		$BankType=Helper::getBankType();
		$Bank=Helper::getBanks(["OptGroup"=>1]);
		// return $Bank;
		return view("app.modals.bank-branch",array("Theme"=>$Theme,"BankType"=>$BankType,"Bank"=>$Bank,"BankTypeID"=>$req->BankTypeID,"BankID"=>$req->BankID));
	}

	public function createBankType(Request $req){
		$OldData=$NewData=array();$BankTypeID="";
		$rules=array(
			'BankType' =>['required','min:3','max:100',new ValidUnique(array("TABLE"=>$this->generalDB."tbl_type_of_bank","WHERE"=>" TypeOfBank='".$req->BankType."' "),"This Bank Type is already taken.")],
		);
		$message=array();
		$validator = Validator::make($req->all(), $rules,$message);
		if ($validator->fails()) {
			return array('status'=>false,'message'=>"Bank Type Create Failed",'errors'=>$validator->errors());
		}
		DB::beginTransaction();

		$status=false;
		$UserID=Auth::check()?auth()->user()->UserID:"";
		try{
			$BankTypeID=DocNum::getDocNum(docTypes::BankType->value);
			$data=array(
				"SLNO"=>$BankTypeID,
				"TypeOfBank"=>$req->BankType,
				"CreatedBy"=>$UserID,
				"CreatedOn"=>date("Y-m-d H:i:s")
			);
			$status=DB::table($this->generalDB.'tbl_type_of_bank')->insert($data);
		}catch(Exception $e) {
			$status=false;
		}
		if($status==true){
			DocNum::updateDocNum(docTypes::BankType->value);
			DB::commit();
			return array('status'=>true,'message'=>"Bank Type Create Successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"Bank Type Create Failed");
		}
	}
	public function createBank(Request $req){
		$OldData=$NewData=array();$BankID="";
		$ValidDB=array();
		$ValidDB['BankType']['TABLE']=$this->generalDB."tbl_type_of_bank";
		$ValidDB['BankType']['ErrMsg']="Bank Type does not exist";
		$ValidDB['BankType']['WHERE'][]=array("COLUMN"=>"SLNO","CONDITION"=>"=","VALUE"=>$req->BankTypeID);
		$ValidDB['BankType']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>"Active");
		$ValidDB['BankType']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);

		$rules=array(
			'BankTypeID' =>['required',$ValidDB['BankType']],
			'BankName' =>['required','min:3','max:100',new ValidUnique(array("TABLE"=>$this->generalDB."tbl_banklist","WHERE"=>" NameOfBanks='".$req->BankName."' and TypeOfBank='".$req->BankTypeID."' "),"This Bank Name is already taken.")],
		);
		$message=array();
		$validator = Validator::make($req->all(), $rules,$message);

		if ($validator->fails()) {
			return array('status'=>false,'message'=>"Bank Create Failed",'errors'=>$validator->errors());
		}
		DB::beginTransaction();

		$status=false;
		try{
			$UserID=Auth::check()?auth()->user()->UserID:"";
			$BankID=DocNum::getDocNum(docTypes::Bank->value);
			$data=array(
				"SLNO"=>$BankID,
				"NameOfBanks"=>$req->BankName,
				"TypeOfBank"=>$req->BankTypeID,
				"CreatedBy"=>$UserID,
				"CreatedOn"=>date("Y-m-d H:i:s")
			);
			$status=DB::table($this->generalDB.'tbl_banklist')->insert($data);
		}catch(Exception $e) {
			$status=false;
		}
		if($status==true){
			DocNum::updateDocNum(docTypes::Bank->value);
			DB::commit();
			return array('status'=>true,'message'=>"Bank Create Successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"Bank Create Failed");
		}
	}
	public function createBankBranch(Request $req){
		$OldData=$NewData=array();$BankBranchID="";
		$ValidDB=array();

		$ValidDB['Bank']['TABLE']=$this->generalDB."tbl_banklist";
		$ValidDB['Bank']['ErrMsg']="Bank Name does not exist";
		$ValidDB['Bank']['WHERE'][]=array("COLUMN"=>"BankID","CONDITION"=>"=","VALUE"=>$req->BankID);
		$ValidDB['Bank']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>1);
		$ValidDB['Bank']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);

		$rules=array(
			'BankID' =>['required',$ValidDB['Bank']],
			'BankBranch' =>['required','min:3','max:100',new ValidUnique(array("TABLE"=>$this->generalDB."tbl_bank_branches","WHERE"=>" BranchName='".$req->BankBranch."' and BankID='".$req->BankID."'"),"This Bank Branch is already taken.")],
		);
		$message=array();
		$validator = Validator::make($req->all(), $rules,$message);

		if ($validator->fails()) {
			return array('status'=>false,'message'=>"Bank Branch Create Failed",'errors'=>$validator->errors());
		}
		DB::beginTransaction();

		$status=false;
		try{
			$UserID=Auth::check()?auth()->user()->UserID:"";
			$BankBranchID=DocNum::getDocNum(docTypes::BankBranch->value);
			$data=array(
				"SLNO"=>$BankBranchID,
				"BranchName"=>$req->BankBranch,
				"IFSCCode"=>$req->IFSCCode,
				"MICR"=>$req->MICR,
				"email"=>$req->BranchEmail,
				"BankID"=>$req->BankID,
				"CreatedBy"=>$UserID,
				"CreatedOn"=>date("Y-m-d H:i:s")
			);
			$status=DB::table($this->generalDB.'tbl_bank_branches')->insert($data);
		}catch(Exception $e) {
			$status=false;
		}
		if($status==true){
			DocNum::updateDocNum(docTypes::BankBranch->value);
			DB::commit();
			return array('status'=>true,'message'=>"Bank Branch Create Successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"Bank Branch Create Failed");
		}
	}

	//Address
	public function getNewAddress(Request $req){
		// return $req;
		$Theme=$this->getThemesOption();
		$FormData=json_decode($req->data,true);
		return view("app.modals.Address",array("Theme"=>$Theme,"data"=>$FormData));
	}
}
