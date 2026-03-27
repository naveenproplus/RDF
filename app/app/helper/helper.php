<?php
namespace App\helper;
use App\Models\DocNum;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Session;
class helper{
    public static function formatAmount($amount)
    {
        return '₹ ' . number_format($amount, 2, '.', ',');
    }
    public static function addRupeesSymbol($amount)
    {
        return '₹ ' . $amount;
    }
	public static function getMainDB(){
		return config('app.db_main').".";
	}
	public static function getGeneralDB(){
		return config('app.db_general').".";
	}
	public static function getLogDB(){
		return config('app.db_log').".";
	}
	public static function getStockDB(){
		return "rdf_stock_fy_2324.";
	}
	public static function getTmpDB(){
		return config('app.db_tmp').".";
	}
	public static function getSupportDB(){
		return config('app.db_support').".";
	}
	public static function getDBPrefix(){
		return config('app.db_prefix');
	}
	public static function getVendorDB($VendorID,$UserID){
		$VendorDB = DB::table('tbl_vendors_database')->where('VendorID', $VendorID)->value('DBName');
		if (!$VendorDB) {
			if (self::generateVendorDB($VendorID, $UserID)) {
				$VendorDB = DB::table('tbl_vendors_database')->where('VendorID', $VendorID)->value('DBName');
			}
		}
		return $VendorDB.'.';
	}
	public static function getStockTable($VendorID) {
		$StockDB = self::getStockDB();

		$vendorIDParts = explode('-', $VendorID);

		$tableName = 'tbl_' . implode('_',$vendorIDParts);

		$status = DB::statement("CREATE TABLE IF NOT EXISTS ".$StockDB."tbl_docnum (
			`SLNO` INT AUTO_INCREMENT PRIMARY KEY,
			`DocType` VARCHAR(50) NULL,
			`Prefix` VARCHAR(5) NULL,
			`Length` INT(11) NULL,
			`CurrNum` INT(11) NULL,
			`Suffix` VARCHAR(10),
			`Year` VARCHAR(10) NULL
		)");

		$status = DB::statement("CREATE TABLE IF NOT EXISTS ".$StockDB.$tableName." (
			`DetailID` VARCHAR(50) PRIMARY KEY,
			`Date` DATE,
			`VendorID` VARCHAR(50) NULL,
			`StockPointID` VARCHAR(50) NULL,
			`ProductID` VARCHAR(50) NULL,
			`Qty` DOUBLE,
			`CreatedBy` VARCHAR(50) NULL,
			`CreatedOn` TIMESTAMP NULL
		)");

		$VendorIDexists=DB::table($StockDB.'tbl_docnum')->where('DocType', $VendorID)->exists();
		if(!$VendorIDexists){
			$status = DB::table($StockDB . 'tbl_docnum')->insert([
				'DocType' => $VendorID,
				'Prefix' => "VSU",
				'Length' => 10,
				'CurrNum' => 1,
			]);
		}
		return $StockDB.$tableName;
	}

	public static function generateVendorDB($VendorID,$UserID){
		$DBPrefix="";
		$FYName="";
		$VendorDBName="";
		$ActiveFY=self::getActiveFinancialYear();
		if($ActiveFY->FromDate=="" && $ActiveFY->ToDate==""){
			$t=self::getCurrentFYDates();
			$ActiveFY->FromDate=date("Y-m-d",strtotime($t->FromDate));
			$ActiveFY->ToDate=date("Y-m-d",strtotime($t->ToDate));
		}
		$FYName="fy_".date("y",strtotime($ActiveFY->FromDate)).date("y",strtotime($ActiveFY->ToDate));
		$DBPrefix=self::getDBPrefix();
		$VendorUniqueID=self::generateUniqueVendorID($VendorID);
		$t=DB::table('tbl_vendors')->where('VendorID',$VendorID)->exists();
		if($t){
			$VendorDBName = $DBPrefix.'v_'.$VendorUniqueID.'_'.$FYName;
			$sql = "CREATE DATABASE IF NOT EXISTS $VendorDBName";
			$status = DB::statement($sql);
			$status = true;
			if($status){
				$isVendorIDExists=DB::table('tbl_vendors_database')->where('VendorID', $VendorID)->exists();
				if(!$isVendorIDExists){
					$data = [
						'VendorID' => $VendorID,
						'DBName' => $VendorDBName,
						'VendorUniqueID' => $VendorUniqueID,
						'CreatedBy' => $UserID,
						'CreatedOn' => now(),
					];
					$status = DB::table('tbl_vendors_database')->insert($data);
					if($status){
						return $status;
					}
				}else{
					return ['status' =>false,'message' =>'Vendor Name Exists!'];
				}
			}
		}
	}

	public static function generateUniqueVendorID($VendorID) {
		$isVendorIDExists=DB::table('tbl_vendors_database')->where('VendorID', $VendorID)->exists();
		if(!$isVendorIDExists){
			do {
				$randomID = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 4);
				$isExists = DB::table('tbl_vendors_database')->where('VendorUniqueID', $randomID)->exists();
			} while ($isExists);
			return $randomID;
		}
	}

	public static function generateFinancialYearName($FromDate,$ToDate){
		return "FY-".date("y",strtotime($FromDate)).date("y",strtotime($ToDate));
	}
	public static function getLogTableName(){
		$LogDB=self::getLogDB();
		$MainDB=self::getMainDB();
		$return=$MainDB."tbl_log";
		$ActiveFY=self::getActiveFinancialYear();
		if($ActiveFY->FromDate=="" && $ActiveFY->ToDate==""){
			$t=self::getCurrentFYDates();
			$ActiveFY->FromDate=date("Y-m-d",strtotime($t->FromDate));
			$ActiveFY->ToDate=date("Y-m-d",strtotime($t->ToDate));
		}
		$FYName=self::generateFinancialYearName($ActiveFY->FromDate,$ActiveFY->ToDate);
		$FYName=str_replace("-","_",$FYName);
		$FYName=str_replace(" ","_",$FYName);
		$TableName=$FYName!=""?"tbl_log_".$FYName:"tbl_log";
		if(self::checkTableExists($LogDB,$TableName)==false){
			$sql="CREATE TABLE IF NOT EXISTS ".$LogDB.$TableName." (LogID varchar(50) PRIMARY Key,ReferID varchar(50),Description varchar(255),ModuleName varchar(150),Action varchar(100) ,OldData text,NewData text,IPAddress varchar(100),UserID varchar(50),LogTime timestamp NOT NULL DEFAULT current_timestamp);";
			DB::Statement($sql);
			return self::getLogTableName();
		}
		return $LogDB.$TableName;
	}
	public static function getFinancialYears(){
		$sql="Select SLNo,DBName,FromDate,ToDate,FYName,isCurrent From tbl_financial_year Where ActiveStatus='Active' Order By FromDate";
		return DB::SELECT($sql);
	}
	public static function getCurrentFYDates(){
		$FromDate="";$ToDate="";
		if(date("m")<=4){
			$FromDate="01-Apr-".date("Y",strtotime('-1 year'));
			$ToDate="31-Mar-".date("Y");
		}else{
			$FromDate="01-Apr-".date("Y");
			$ToDate="31-Mar-".date("Y",strtotime("1 year"));
		}
		$FromDate=date("Y-m-d",strtotime($FromDate));
		$ToDate=date("Y-m-d",strtotime($ToDate));

		return json_decode(json_encode(array("FromDate"=>$FromDate,"ToDate"=>$ToDate)));
	}
	public static function getCurrentFY(){
		$FromDate="";$ToDate="";
		if(date("m")<=4){
			$FromDate="01-Apr-".date("Y",strtotime('-1 year'));
			$ToDate="31-Mar-".date("Y");
		}else{
			$FromDate="01-Apr-".date("Y");
			$ToDate="31-Mar-".date("Y",strtotime("1 year"));
		}
		$FromDate=date("y",strtotime($FromDate));
		$ToDate=date("y",strtotime($ToDate));

		return $FromDate.$ToDate;
	}
	public static function getFinancialYearDetails($FYID){
		if($FYID==""){
			$t=self::getCurrentFYDates();
			$FromDate=date("Y-m-d",strtotime($t->FromDate));
			$ToDate=date("Y-m-d",strtotime($t->ToDate));
		}
		$data=array("DBName"=>"","FromDate"=>"","ToDate"=>"","FYName"=>"","FYID"=>"");
		$sql="Select SLNo,DBName,FromDate,ToDate,FYName,isCurrent From  tbl_financial_year Where SLNO='".$FYID."'";
		$result=DB::SELECT($sql);
		if(count($result)>0){
			$data['DBName']=$result[0]->DBName;
			$data['FromDate']=$result[0]->FromDate;
			$data['ToDate']=$result[0]->ToDate;
			$data['FYName']=$result[0]->FYName;
			$data['FYID']=$result[0]->SLNo;
			$data['isCurrent']=$result[0]->isCurrent;
		}
		$data=json_encode($data);

		return json_decode($data);
	}
	public static function ActivateFinancialYear($FYID){
		$keyName=config('app.name')."_financial_year";
		Session::put($keyName,$FYID);
	}
	public static function getActiveFinancialYear(){
		$keyName=config('app.name')."_financial_year";
		$FYID="";
		if (Session::has($keyName)){
			$FYID=Session::get($keyName);
		}else{
			$t=self::getCurrentFYDates();
			$sql="Select SLNo,DBName,FromDate,ToDate,FYName,isCurrent From tbl_financial_year Where FromDate='".date("Y-m-d",strtotime($t->FromDate))."' and ToDate='".date("Y-m-d",strtotime($t->ToDate))."'";
			$result=DB::SELECT($sql);
			if(count($result)>0){
				$FYID=$result[0]->SLNo;
				Session::put($keyName,$FYID);
			}
		}
		return self::getFinancialYearDetails($FYID);
	}
	public static function GDEnabled(){
		if (extension_loaded('gd')) {
			return true;
		}else{
			return false;
		}
	}
    public static function isJSON($string):bool{
		try {
			if($string!=""){
				json_decode($string, true, 512, JSON_THROW_ON_ERROR);
			}else{
				return false;
			}

		} catch (JsonException) {
			return false;
		}

    	return true;
	}
	public static function removeFile($url){
		if(file_exists($url)){
			unlink($url);
		}
	}
	public static function EncryptDecrypt($action, $string){
		$output = false;$action=strtoupper($action);
		$encrypt_method = "AES-256-CBC";
		$secret_key = 'gKWRyB9FZ34jQn1CjSl8';
		$secret_iv = 'wVHvDuqDaXkr0PXROT0E2E3wGJEYcwfFcAi8qgnPOcq2pZcUEjn7wruspR1Z';
		$key = hash('sha256', $secret_key);
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr(hash('sha256', $secret_iv), 0, 16);
		if($action=='ENCRYPT'){
			$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
			$output = strrev(base64_encode($output));
		}
		elseif($action=='DECRYPT'){
			$output = openssl_decrypt(base64_decode(strrev($string)), $encrypt_method, $key, 0, $iv);;
		}
		return $output;
	}
	public static function RandomString($len){
		$validCharacters = "AaBbCcDdEeFfGgHhIiJjKkLlMmNnPpQqRrSsTtUuXxYyVvWwZz1234567890";
		$validCharNumber = strlen($validCharacters);
		$result ="";
		for ($i = 0; $i < $len; $i++){
			$index = mt_rand(0, $validCharNumber - 1);
			$result .= $validCharacters[$index];
		}
		return $result;
	}
	public static function getOTP($len){
		$validCharacters = "1234567890";
		$validCharNumber = strlen($validCharacters);
		$result ="";
		for ($i = 0; $i < $len; $i++){
			$index = mt_rand(0, $validCharNumber - 1);
			$result .= $validCharacters[$index];
		}
		return $result;
    }
	public static function getDateDifferenceinDays($Date1,$Date2){
		$date1=date_create(date("Y-m-d",strtotime($Date1)));
		$date2=date_create(date("Y-m-d",strtotime($Date2)));
		$diff=date_diff($date1,$date2);
		return $diff->format("%a")+1;
	}
	public static function getDateDifference($Date1,$Date2){
        $start=strtotime($Date1);
        $end=strtotime($Date2);
        $min=($end - $start) / 60;
        return self::MinsToGeneral($min);
	}
	public static function getDateDifferenceInMins($Date1,$Date2){
        $start=strtotime($Date1);
        $end=strtotime($Date2);
        $min=($end - $start) / 60;
        return $min;
	}
	public static function HoursToMins($Duration){
		$t=explode(":",$Duration);
		$mins=intval($t[0])*60;
		if(count($t)>1){
			$mins+$t[1];
		}
		return $mins;
	}
	public static function LPad($String,$Length,$PadString){
		return str_pad($String, $Length, $PadString, STR_PAD_LEFT);
	}
	public static function RPad($String,$Length,$PadString){
		return str_pad($String, $Length, $PadString);
	}
	public static function NumberFormat($Value,$Decimal){
		if($Decimal!="auto"){
			return number_format($Value,$Decimal,".","");
		}else{
			return $Value;
		}
	}
	public static function NumberSteps($Decimal){
		$Value="1";
		if($Decimal!="auto"){
			if($Decimal==0){
				return 1;
			}else{
				return "0.".str_pad($Value,$Decimal,"0",STR_PAD_LEFT);
			}
		}else{
			return $Value;
		}
	}
	public static function checkTableExists($DBName,$TableName){
		$DBName=$DBName==""?self::getMainDB():$DBName;
		$DBName=str_replace(".","",$DBName);
        $sql="SELECT * FROM information_schema.tables WHERE table_schema = '".$DBName."' AND table_name = '".$TableName."' LIMIT 1;";
        $result=DB::SELECT($sql);
        if(count($result)>0){
            return true;
        }
        return false;
	}
	public static function addDocNum($DBName, $Doctype, $Prefix, $Length, $CurrNum) {
		$isDocTypeExists = DB::table($DBName . 'tbl_docnum')->where('DocType', $Doctype)->exists();
		if (!$isDocTypeExists) {
			$status = DB::table($DBName . 'tbl_docnum')->insert([
				'DocType' => $Doctype,
				'Prefix' => $Prefix,
				'Length' => $Length,
				'CurrNum' => $CurrNum,
			]);
		} else {
			$status = true;
		}
		return $status;
	}
	public static function getCountry($data=array()){
		$generalDB=self::getGeneralDB();
		$sql="Select * From ".$generalDB."tbl_countries Where ActiveStatus='Active' and DFlag=0 ";
		if(is_array($data)){
			if(array_key_exists("CountryID",$data)){if($data['CountryID']!=""){$sql.=" and CountryID='".$data['CountryID']."'";}}
			if(array_key_exists("sortname",$data)){if($data['sortname']!=""){$sql.=" and sortname='".$data['sortname']."'";}}
			if(array_key_exists("CountryName",$data)){if($data['CountryName']!=""){$sql.=" and CountryName='".$data['CountryName']."'";}}
			if(array_key_exists("PhoneCode",$data)){if($data['PhoneCode']!=""){$sql.=" and PhoneCode='".$data['PhoneCode']."'";}}

		}
		$sql.=" Order By CountryName asc";
		return DB::SELECT($sql);
	}
	public static function getState($data=array()){
		$generalDB=self::getGeneralDB();
		$sql="Select * From ".$generalDB."tbl_states Where ActiveStatus='Active' and DFlag=0 ";
		if(is_array($data)){
			if (array_key_exists("CountryID", $data)) {
				if (is_array($data['CountryID']) && !empty($data['CountryID'])) {
					$sql .= " AND CountryID IN ('" . implode("','", $data['CountryID']) . "')";
				} elseif ($data['CountryID'] != "") {
					$sql .= " AND CountryID='" . $data['CountryID'] . "'";
				}
			}
			if (array_key_exists("StateID", $data)) {
				if (is_array($data['StateID']) && !empty($data['StateID'])) {
					$sql .= " AND StateID IN ('" . implode("','", $data['StateID']) . "')";
				} elseif ($data['StateID'] != "") {
					$sql .= " AND StateID='" . $data['StateID'] . "'";
				}
			}
			if(array_key_exists("StateName",$data)){if($data['StateName']!=""){$sql.=" and StateName='".$data['StateName']."'";}}
		}
		$sql.=" Order By StateName asc";
		return DB::SELECT($sql);
	}
	public static function getDistrict($data=array()){
		$generalDB=self::getGeneralDB();
		$sql="Select * From ".$generalDB."tbl_districts Where ActiveStatus='Active' and DFlag=0 ";
		if(is_array($data)){
			if (array_key_exists("CountryID", $data)) {
				if (is_array($data['CountryID']) && !empty($data['CountryID'])) {
					$sql .= " AND CountryID IN ('" . implode("','", $data['CountryID']) . "')";
				} elseif ($data['CountryID'] != "") {
					$sql .= " AND CountryID='" . $data['CountryID'] . "'";
				}
			}
			if (array_key_exists("StateID", $data)) {
				if (is_array($data['StateID']) && !empty($data['StateID'])) {
					$sql .= " AND StateID IN ('" . implode("','", $data['StateID']) . "')";
				} elseif ($data['StateID'] != "") {
					$sql .= " AND StateID='" . $data['StateID'] . "'";
				}
			}
			if (array_key_exists("DistrictID", $data)) {
				if (is_array($data['DistrictID']) && !empty($data['DistrictID'])) {
					$sql .= " AND DistrictID IN ('" . implode("','", $data['DistrictID']) . "')";
				} elseif ($data['DistrictID'] != "") {
					$sql .= " AND DistrictID='" . $data['DistrictID'] . "'";
				}
			}
			if(array_key_exists("DistrictName",$data)){if($data['DistrictName']!=""){$sql.=" and DistrictName='".$data['DistrictName']."'";}}

		}
		$sql.=" Order By DistrictName asc";
		return DB::SELECT($sql);
	}
	public static function getTaluk($data=array()){
		$generalDB=self::getGeneralDB();
		$sql="Select * From ".$generalDB."tbl_taluks Where ActiveStatus='Active' and DFlag=0 ";
		if(is_array($data)){
			if(array_key_exists("CountryID",$data)){if($data['CountryID']!=""){$sql.=" and CountryID='".$data['CountryID']."'";}}
			if(array_key_exists("StateID",$data)){if($data['StateID']!=""){$sql.=" and StateID='".$data['StateID']."'";}}
			if(array_key_exists("DistrictID",$data)){if($data['DistrictID']!=""){$sql.=" and DistrictID='".$data['DistrictID']."'";}}
			if(array_key_exists("TalukID",$data)){if($data['TalukID']!=""){$sql.=" and TalukID='".$data['TalukID']."'";}}
			if(array_key_exists("TalukName",$data)){if($data['TalukName']!=""){$sql.=" and TalukName='".$data['TalukName']."'";}}

		}
		$sql.=" Order By TalukName asc";
		return DB::SELECT($sql);
	}
	public static function getCity($data=array()){
		// return $data;
		$generalDB=self::getGeneralDB();
		$sql="Select * From ".$generalDB."tbl_cities Where ActiveStatus='Active' and DFlag=0 ";
		if(is_array($data)){
			if(array_key_exists("CountryID",$data)){if($data['CountryID']!=""){$sql.=" and CountryID='".$data['CountryID']."'";}}
			if(array_key_exists("StateID",$data)){if($data['StateID']!=""){$sql.=" and StateID='".$data['StateID']."'";}}
			if(array_key_exists("DistrictID",$data)){if($data['DistrictID']!=""){$sql.=" and DistrictID='".$data['DistrictID']."'";}}
			if(array_key_exists("TalukID",$data)){if($data['TalukID']!=""){$sql.=" and TalukID='".$data['TalukID']."'";}}
			if(array_key_exists("PostalID",$data)){if($data['PostalID']!=""){$sql.=" and PostalID='".$data['PostalID']."'";}}
			if(array_key_exists("CityID",$data)){if($data['CityID']!=""){$sql.=" and CityID='".$data['CityID']."'";}}
			if(array_key_exists("CityName",$data)){if($data['CityName']!=""){$sql.=" and CityName='".$data['CityName']."'";}}
			if(array_key_exists("PostalCode",$data)){
				if($data['PostalCode']!=""){
					$postalData = self::getPostalCode(["PostalCode" => $data['PostalCode']]);
					if (count($postalData) > 0) {
						$postalID = $postalData[0]->PID;
						$sql .= " AND PostalID='" . $postalID . "'";
					}else{
						return ["error" => "Postal Code does not exist"];
					}
				}
			}
		}
		$sql.=" Order By CityName asc ";
		return DB::SELECT($sql);
	}
	public static function getPostalCode($data=array()){
		$generalDB=self::getGeneralDB();
		$sql="Select * From ".$generalDB."tbl_postalcodes Where ActiveStatus='Active' and DFlag=0 ";
		if(is_array($data)){
			if(array_key_exists("CountryID",$data)){if($data['CountryID']!=""){$sql.=" and CountryID='".$data['CountryID']."'";}}
			if(array_key_exists("StateID",$data)){if($data['StateID']!=""){$sql.=" and StateID='".$data['StateID']."'";}}
			if(array_key_exists("DistrictID",$data)){if($data['DistrictID']!=""){$sql.=" and DistrictID='".$data['DistrictID']."'";}}
			if(array_key_exists("PostalCodeID",$data)){if($data['PostalCodeID']!=""){$sql.=" and PID='".$data['PostalCodeID']."'";}}
			if(array_key_exists("PostalCode",$data)){if($data['PostalCode']!=""){$sql.=" and PostalCode='".$data['PostalCode']."'";}}

		}
		$sql.=" Order By PostalCode asc ";
		return DB::SELECT($sql);
	}
	public static function getBankType($data=array()){
		$generalDB=self::getGeneralDB();
		$sql="Select * From ".$generalDB."tbl_type_of_bank Where ActiveStatus='Active' and DFlag=0 ";
		$sql.=" Order By TypeOfBank asc ";
		return DB::SELECT($sql);
	}
	public static function getBanks($data=array()){
		$generalDB=self::getGeneralDB();
		$sql="Select *,b.TypeOfBank as TypeBankID,b.SLNO as BankID From ".$generalDB."tbl_banklist as b LEFT JOIN " . $generalDB . "tbl_type_of_bank as tob ON tob.SLNO = b.TypeOfBank Where b.ActiveStatus='Active' and b.DFlag=0 ";
		if(is_array($data)){
			// if(array_key_exists("BankID",$data)){if($data['BankID']!=""){$sql.=" and b.SLNO='".$data['BankID']."'";}}
			if(array_key_exists("TypeOfBankID",$data)){if($data['TypeOfBankID']!=""){$sql.=" and b.TypeOfBank='".$data['TypeOfBankID']."'";}}
		}
		$sql.=" Order By b.NameOfBanks asc ";
		$result = DB::SELECT($sql);
		if(array_key_exists("OptGroup",$data)){if($data['OptGroup'] == 1){
			$return = [];
			for($i = 0; $i < count($result); $i++){
				$return[$result[$i]->TypeOfBank][]=$result[$i];
			}
			return $return;
		}}else{
			return $result;
		}
	}
	public static function getBankBranch($data=array()){
		$generalDB=self::getGeneralDB();
		$sql="Select * From ".$generalDB."tbl_bank_branches Where ActiveStatus='Active' and DFlag=0 ";
		if(is_array($data)){
			if(array_key_exists("BankID",$data)){if($data['BankID']!=""){$sql.=" and BankID='".$data['BankID']."'";}}
			if(array_key_exists("BranchID",$data)){if($data['BranchID']!=""){$sql.=" and SLNO='".$data['BranchID']."'";}}

		}
		$sql.=" Order By BranchName asc ";
		return DB::SELECT($sql);
	}
	public static function getBankAccountType($data=array()){
		$generalDB=self::getGeneralDB();
		$sql="Select * From ".$generalDB."tbl_bank_account_type Where ActiveStatus='Active' and DFlag=0 ";
		if(is_array($data)){
			if(array_key_exists("AccountTypeID",$data)){if($data['AccountTypeID']!=""){$sql.=" and SLNO='".$data['AccountTypeID']."'";}}
		}
		$sql.=" Order By AccountType asc ";
		return DB::SELECT($sql);
	}
	public static function getGender($data=array()){
		$generalDB=self::getGeneralDB();
		$sql="Select * From ".$generalDB."tbl_genders Where ActiveStatus='Active' and DFlag=0 ";
		if(is_array($data)){
			if(array_key_exists("GID",$data)){if($data['GID']!=""){$sql.=" and GID='".$data['GID']."'";}}
		}
		$sql.=" Order By Gender asc ";
		return DB::SELECT($sql);
	}
	public static function getFileTypes($data=array()){
		$generalDB=self::getGeneralDB();
		$return=array();
		$sql="Select * From ".$generalDB."tbl_file_types Where ActiveStatus='Active' and CategoryActiveStatus='Active' and DFlag=0 ";
		if(is_array($data)){
			if(array_key_exists("category",$data)){
				$sql.=" and Category In ('".implode("','",$data['category'])."')";
			}
		}
		$result=DB::Select($sql);
		for($i=0;$i<count($result);$i++){
			$return["category"][$result[$i]->Category][]=str_replace("*.","",$result[$i]->FileExtension);
			$return["All"][]=str_replace("*","",$result[$i]->FileExtension);
		}
		return $return;
	}
	public static function getResizePercentages(){
		$generalDB=self::getGeneralDB();
		$result=DB::Table($generalDB."tbl_image_resize")->where('ActiveStatus','Active')->where('DFlag',0)->get();
		return $result;
	}
	private static function readImage($url){
		if (file_exists($url)) {
			// Get image information
			$imageInfo = getimagesize($url);

			// Check if the image type is supported
			$supportedTypes = [
				IMAGETYPE_PNG,
				IMAGETYPE_JPEG,
				IMAGETYPE_GIF,
				IMAGETYPE_BMP,
				IMAGETYPE_WEBP,
			];
			if ($imageInfo && in_array($imageInfo[2], $supportedTypes)) {
				// Attempt to create the image resource directly from URL
				$image = call_user_func('imagecreatefrom' . image_type_to_extension($imageInfo[2], false), $url);

				// Check if the image resource was created successfully
				if ($image !== false) {
					return $image;
				} else {
					return null;
				}
			} else {
				return null;
			}
		}
		return null;
	}
    private static function imgSave($ext,$file,$UploadPath){
        if($ext=="gif"){return  imagegif($file,$UploadPath);}
        elseif($ext=="jpg"){return  imagejpeg($file,$UploadPath);}
        elseif($ext=="jpeg"){return  imagejpeg($file,$UploadPath);}
        elseif($ext=="png"){return  imagepng($file,$UploadPath);}
        elseif($ext=="avif"){imageavif($file,$UploadPath);}
        elseif($ext=="bmp"){return  imagebmp($file,$UploadPath);}
        elseif($ext=="wbmp"){return  imagewbmp($file,$UploadPath);}
        elseif($ext=="webp"){return  imagewebp($file,$UploadPath);}
    }
	private static function ResizeImage($FileUrl,$destination_dir,$new_width,$new_height,$fileName=""){
		$uploadPath="";
		//if(file_exists($FileUrl)){
			$ext = pathinfo($FileUrl, PATHINFO_EXTENSION);
			if($fileName==""){
				$fileName = basename($FileUrl, ".".$ext)."_".$new_width."x".$new_height.".".$ext;
			}


			if(SELF::GDEnabled()){
				list($width, $height) = getimagesize($FileUrl);
				$dst = imagecreatetruecolor($new_width, $new_height);
				$src=SELF::readImage($FileUrl);
				if($src!=null){
					imagecopyresized($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

					if (!file_exists( $destination_dir)) {mkdir( $destination_dir, 0777, true);}
					$uploadPath=$destination_dir.$fileName;
					self::imgSave($ext,$dst,$uploadPath);
				}
			}
		//}
		return $uploadPath;
	}
	public static function ImageResize($FileUrl,$destination_dir){
		list($imgWidth, $imgHeight) = getimagesize($FileUrl);
		$ResizePercentages=self::getResizePercentages();
		$resizedImgUrls=array();
		$resizedImgUrls['100']=array("width"=>$imgWidth,"height"=>$imgHeight,"url"=>$FileUrl);
		foreach($ResizePercentages as $index=>$percentage){
			$newWidth=($imgWidth*$percentage->Percentage)/100;
			$newHeight=($imgHeight*$percentage->Percentage)/100;

			$ext = pathinfo($FileUrl, PATHINFO_EXTENSION);
			$fileName = basename($FileUrl, ".".$ext)."_".$percentage->Percentage.".".$ext;
			$uploadUrl=self::ResizeImage($FileUrl,$destination_dir,$newWidth,$newHeight,$fileName);
			$resizedImgUrls[$percentage->Percentage]=array("width"=>$newWidth,"height"=>$newHeight,"url"=>$uploadUrl);
		}
		return $resizedImgUrls;
	}
	public static function generateSlug($string) {
		// Remove special characters
		$string = preg_replace('/[^a-zA-Z0-9\s]/', '', $string);

		// Convert to lowercase
		$string = strtolower($string);

		// Replace spaces with dashes
		$string = str_replace(' ', '-', $string);

		// Remove consecutive dashes
		$string = preg_replace('/-+/', '-', $string);

		// Trim dashes from the beginning and end
		$string = trim($string, '-');

		return $string;
	}
	public static function checkProductImageExists($url){
		$image=file_exists($url)?$url:"assets/images/no-images.jpg";
		return $image;
	}

    public static function apiCheckImageExistsUrl($url)
    {
        return config('app.url') . "/" . (file_exists($url) ? $url : "assets/images/no-images.jpg");
    }

	public static function getVehicleType($data=array()){
		$generalDB=self::getGeneralDB();
		$sql="Select * From ".$generalDB."tbl_vehicle_type Where ActiveStatus='Active' and DFlag=0 ";
		if(is_array($data)){
			if(array_key_exists("VehicleTypeID",$data)){if($data['VehicleTypeID']!=""){$sql.=" and VehicleTypeID='".$data['VehicleTypeID']."'";}}
			if(array_key_exists("VehicleBrandID",$data)){if($data['VehicleBrandID']!=""){$sql.=" and VehicleBrandID='".$data['VehicleBrandID']."'";}}
			if(array_key_exists("VehicleBrandName",$data)){if($data['VehicleBrandName']!=""){$sql.=" and VehicleBrandName='".$data['VehicleBrandName']."'";}}
		}
		$sql.=" Order By VehicleType asc";
		return DB::SELECT($sql);
	}
	public static function getVehicleModel($data=array()){
		$generalDB=self::getGeneralDB();
		$sql="Select * From ".$generalDB."tbl_vehicle_model Where ActiveStatus='Active' and DFlag=0 ";
		if(is_array($data)){
			if(array_key_exists("VehicleTypeID",$data)){if($data['VehicleTypeID']!=""){$sql.=" and VehicleTypeID='".$data['VehicleTypeID']."'";}}
			if(array_key_exists("VehicleBrandID",$data)){if($data['VehicleBrandID']!=""){$sql.=" and VehicleBrandID='".$data['VehicleBrandID']."'";}}
			if(array_key_exists("VehicleModelID",$data)){if($data['VehicleModelID']!=""){$sql.=" and VehicleModelID='".$data['VehicleModelID']."'";}}
			if(array_key_exists("VehicleModel",$data)){if($data['VehicleModel']!=""){$sql.=" and VehicleModel='".$data['VehicleModel']."'";}}
		}
		$sql.=" Order By VehicleModel asc";
		return DB::SELECT($sql);
	}
	public static function getVehicleBrand($data=array()){
		$generalDB=self::getGeneralDB();
		$sql="Select * From ".$generalDB."tbl_vehicle_brand Where ActiveStatus='Active' and DFlag=0 ";
		if(is_array($data)){
			if(array_key_exists("VehicleTypeID",$data)){if($data['VehicleTypeID']!=""){$sql.=" and VehicleTypeID='".$data['VehicleTypeID']."'";}}
			if(array_key_exists("VehicleBrandName",$data)){if($data['VehicleBrandName']!=""){$sql.=" and VehicleBrandName='".$data['VehicleBrandName']."'";}}
		}
		$sql.=" Order By VehicleBrandName asc";
		return DB::SELECT($sql);
	}
	/* public static function getUserInfo($UserID){
		$generalDB=self::getGeneralDB();
		$return=array();
		$sql="Select U.ID,U.UserID,U.ReferID,U.LoginType,U.RoleID,UR.RoleName,U.Name,U.EMail,U.FirstName,U.LastName,U.DOB,U.GenderID,G.Gender,U.Address,U.CityID,CI.CityName,U.StateID,S.StateName,U.CountryID,CO.CountryName,CO.PhoneCode,U.PostalCodeID,PC.PostalCode,U.EMail,U.MobileNumber,U.ProfileImage,U.ActiveStatus,U.DFlag From users AS U  left join ".$generalDB."tbl_cities AS CI On CI.CityID=U.CityID Left Join ".$generalDB."tbl_countries AS CO ON CO.CountryID=U.CountryID LEFT JOIN ".$generalDB."tbl_states as S On S.StateID=U.StateID  Left Join ".$generalDB."tbl_postalcodes as PC On PC.PID=U.PostalCodeID Left Join ".$generalDB."tbl_genders as G On G.GID=U.GenderID Left join tbl_user_roles as UR ON UR.RoleID=U.RoleID Where U.UserID='".$UserID."'";
		$return=DB::select($sql);
		for($i=0;$i<count($return);$i++){
            if (!file_exists($return[$i]->ProfileImage)) {
				$return[$i]->ProfileImage="";
			}
			if(($return[$i]->ProfileImage=="")||($return[$i]->ProfileImage==null)){
                if(strtolower($return[$i]->Gender)=="female"){
                    $return[$i]->ProfileImage="assets/images/female-icon.png";
                }else{
                    $return[$i]->ProfileImage="assets/images/male-icon.png";
                }
			}
            $return[$i]->ActiveStatus=intval($return[$i]->ActiveStatus)==1?'Active':'Inactive';
			$return[$i]->ProfileImage=url('/')."/".$return[$i]->ProfileImage;
        }
        if(count($return)>0){
            return array("status"=>true,"data"=>$return[0]);
        }else{
            return array("status"=>false,"data"=>$return);
        }
		return $return;
    } */
	public static function getUserInfo($UserID){
		$generalDB = self::getGeneralDB();

		$userInfo = DB::table('users as U')
			->select('U.ID', 'U.UserID', 'U.ReferID', 'U.LoginType', 'U.RoleID', 'UR.RoleName', 'U.Name', 'U.EMail', 'U.FirstName', 'U.LastName', 'U.DOB', 'U.GenderID', 'G.Gender', 'U.Address', 'U.CityID', 'CI.CityName', 'U.StateID', 'S.StateName', 'U.CountryID', 'CO.CountryName', 'CO.PhoneCode', 'U.PostalCodeID', 'PC.PostalCode', 'U.MobileNumber', 'U.ProfileImage', 'U.ActiveStatus', 'U.DFlag')
			->leftJoin($generalDB . 'tbl_cities AS CI', 'CI.CityID', '=', 'U.CityID')
			->leftJoin($generalDB . 'tbl_countries AS CO', 'CO.CountryID', '=', 'U.CountryID')
			->leftJoin($generalDB . 'tbl_states AS S', 'S.StateID', '=', 'U.StateID')
			->leftJoin($generalDB . 'tbl_postalcodes AS PC', 'PC.PID', '=', 'U.PostalCodeID')
			->leftJoin($generalDB . 'tbl_genders AS G', 'G.GID', '=', 'U.GenderID')
			->leftJoin('tbl_user_roles AS UR', 'UR.RoleID', '=', 'U.RoleID')
			->where('U.UserID', $UserID)
			->first();

		if ($userInfo) {
			// $userInfo->ActiveStatus = intval($userInfo->ActiveStatus) == 1 ? 'Active' : 'Inactive';

			if (!empty($userInfo->ProfileImage) && filter_var($userInfo->ProfileImage, FILTER_VALIDATE_URL)) {
				// Profile image is already a valid URL
			} elseif (empty($userInfo->ProfileImage) || !file_exists($userInfo->ProfileImage)) {
				$userInfo->ProfileImage = strtolower($userInfo->Gender) == "female" ? "assets/images/female-icon.png" : "assets/images/male-icon.png";
			} else {
				$userInfo->ProfileImage = url('/') . "/" . $userInfo->ProfileImage;
			}

			return ["status" => true, "data" => $userInfo];
		} else {
			return ["status" => false, "data" => []];
		}
	}

    public static function generateCoupon(){
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $couponCode = '';

        for ($i = 0; $i < 8; $i++) {
            $couponCode .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $couponCode;
    }

    public static function saveNotification($ReferID,$Title,$Message,$Route,$RouteID){
        $UserID = DB::table('tbl_customer')->where('CustomerID',$ReferID)->value('CustomerID');
        $NID = DocNum::getDocNum("Notification", Helper::getMainDB(),Carbon::now()->year);
        $Ndata = [
            'NID'=> $NID,
            'CustomerID'=> $UserID,
            'Title'=> $Title,
            'Message'=> $Message,
            'Route'=> $Route,
            'RouteID'=> $RouteID
        ];
        $status = DB::table('tbl_notifications')->insert($Ndata);
        if($status){
            DocNum::updateDocNum("Notification");
            self::sendNotification($UserID,$Title,$Message);
        }
        return $status;
    }

    /**
     * @throws \JsonException
     */
    public static function sendNotification($UserID, $title, $body): string
    {
        // Fetch all tokens from DB
        $result = DB::table('tbl_customer')
            ->where([
                ['ActiveStatus', '=', 1],
                ['DFlag', '=', 0],
                ['CustomerID', '=', $UserID]
            ])
            ->pluck('fcmToken')
            ->filter()
            ->toArray();
        if (empty($result)) return 'No valid tokens found.';

        $serviceAccountPath = storage_path('app/firebase/firebase_credentials.json');
        $projectId = config('app.firebase_project_id');

        $accessToken = self::generateAccessToken($serviceAccountPath);
        if (!$accessToken) return 'Failed to generate access token.';

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $success = 0;
        $fail = 0;

        foreach ($result as $token) {
            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => compact('title', 'body'),
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            if ($response->successful()) {
                $success++;
            } else {
                $fail++;
            }
        }

        return "Notifications sent. Success: {$success}, Failed: {$fail}";
    }

    /**
     * @throws \JsonException
     */
    public static function generateAccessToken($serviceAccountPath)
    {
        $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true, 512, JSON_THROW_ON_ERROR);

        $now = time();
        $expires = $now + 3600;
        $jwtHeader = self::base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $jwtClaimSet = self::base64UrlEncode(json_encode([
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $expires,
            'iat' => $now,
        ], JSON_THROW_ON_ERROR));

        $unsignedJwt = $jwtHeader . '.' . $jwtClaimSet;
        $signature = '';
        openssl_sign($unsignedJwt, $signature, $serviceAccount['private_key'], 'sha256');
        $signedJwt = $unsignedJwt . '.' . self::base64UrlEncode($signature);

        // Exchange JWT for access token
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $signedJwt,
        ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        } else {
            return null;
        }
    }

    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function shortenValue($value) {
        $abbreviations = ["", "K", "M", "B", "T"]; // Add more if needed

        $abbrevIndex = 0;
        while ($value >= 1000 && $abbrevIndex < count($abbreviations) - 1) {
            $value /= 1000;
            $abbrevIndex++;
        }

        return round($value, 1) . $abbreviations[$abbrevIndex];
    }

    public static function translate($sourceText, $targetLang, $sourceLang = 'auto')
    {
        try {
            $url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=$sourceLang&tl=$targetLang&dt=t&q=" . urlencode($sourceText);
            $response = Http::get($url);
            if ($response->successful()) {
                $responseData = $response->json();
                return $responseData[0][0][0] ?? '';
            } else {
                logger([
                    'error' => 'Translation API error',
                    'status' => $response->status(),
                ]);
                return $sourceText;
            }
        } catch (\Exception $e) {
            logger($e);
            return $sourceText;
        }
    }
}
