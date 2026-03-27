<?php

namespace App\Http\Controllers\web;


use App\enums\cruds;
use App\enums\docTypes;
use App\helper\helper;
use App\Http\Controllers\Controller;
use App\Mail\OrderMail;
use App\Models\BusyIntegration;
use App\Models\CustomerOrderTrack;
use App\Models\DocNum;
use App\Models\Order;
use App\Rules\ValidDB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use App\Models\general;
use App\Models\ServerSideProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Rules\ValidUnique;
use Illuminate\Validation\Rule;
use logs;
use App\enums\activeMenuNames;
use Saloon\XmlWrangler\XmlReader;
use SimpleXMLElement;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller{
	private $general;
	private $DocNum;
	private $UserID;
	private $ActiveMenuName;
	private $PageTitle;
	private $CRUD;
	private $logs;
	private $Settings;
    private $Menus;
	private $AdminID;
	private $generalDB;
	private $supportDB;
    private $LoginType;

    public function __construct(){
		$this->ActiveMenuName=activeMenuNames::Orders->value;
        $this->PageTitle="Orders";
        $this->middleware('auth');
		$this->middleware(function ($request, $next) {
			$this->UserID=auth()->user()->UserID;
			$this->general=new general($this->UserID,$this->ActiveMenuName);
			$this->Menus=$this->general->loadMenu();
			$this->CRUD=$this->general->getCrudOperations($this->ActiveMenuName);
			$this->Settings=$this->general->getSettings();
			$this->generalDB=Helper::getGeneralDB();
            $this->supportDB=Helper::getSupportDB();
			return $next($request);
		});
    }
	public function OrderView(Request $req){
        if($this->general->isCrudAllow($this->CRUD,"view")==true){
            $FormData=$this->general->UserInfo;
            $FormData['menus']=$this->Menus;
            $FormData['crud']=$this->CRUD;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
            $FormData['PageName']="Orders";
            $FormData['crud']=$this->CRUD;
            $FormData['users']=DB::table("users")->where("DFlag",0)->where("ActiveStatus",1)->get();
            $FormData['customers']=DB::table("tbl_customer")->where("DFlag",0)->where("ActiveStatus",1)->get();
            return view('app.order.order',$FormData);
        }else{
            return view('errors.403');
        }
    }

    public function BusyOrderView(Request $req){
        if($this->general->isCrudAllow($this->CRUD,"view")==true){
            $FormData=$this->general->UserInfo;
            $FormData['menus']=$this->Menus;
            $FormData['crud']=$this->CRUD;
            $FormData['ActiveMenuName']=$this->ActiveMenuName;
            $FormData['PageTitle']=$this->PageTitle;
            $FormData['PageName']="Busy Orders";
            $FormData['crud']=$this->CRUD;
//            $FormData['users']=DB::table("users")->where("DFlag",0)->where("ActiveStatus",1)->get();
//            $FormData['customers']=DB::table("tbl_customer")->where("DFlag",0)->where("ActiveStatus",1)->get();
            return view('app.order.busy',$FormData);
        }else{
            return view('errors.403');
        }
    }
    public function SupportDetailsView(Request $req,$SupportID){
		$FormData=$this->general->UserInfo;
		$FormData['ActiveMenuName']=$this->ActiveMenuName;
		$FormData['PageTitle']=$this->PageTitle;
		$FormData['PageName']="Account Settings Support Details";
		$FormData['menus']=$this->Menus;
        $FormData['crud']=$this->CRUD;
        $FormData['SID']=$SupportID;
        $FormData['TUInfo']=$this->ticketUserInfo($SupportID);
        $FormData['Support']=DB::table($this->supportDB."tbl_support")->where("SupportID",$SupportID)->get();
        if(count($FormData['Support'])>0){
            $FormData['SupportDetails']=$this->getSupportDetails(array("SupportID"=>$SupportID));
            return view('app.support.support_details',$FormData);
        }else{
            return view('errors.404');
        }

    }
    private function ticketUserInfo($SupportID){
        $return=array(
            "Name"=>"",
            "MobileNumber"=>""
        );
//        $sql="SELECT U.LoginType FROM ".$this->supportDB."tbl_support as S LEFT JOIN tbl_customer as U ON U.CustomerID=S.CreatedBy  Where S.SupportID='".$SupportID."'";
//        $result=DB::SELECT($sql);
//        if(count($result)>0){
//            if(intval($result[0]->LoginType)==2){
                $sql="SELECT U.CustomerID, U.CustomerName as UserName, U.MobileNo1 as MobileNumber FROM ".$this->supportDB."tbl_support as S LEFT JOIN tbl_customer as U ON U.CustomerID=S.UserID LEFT JOIN tbl_customer as UI ON UI.CustomerID=S.UserID LEFT JOIN ".$this->generalDB."tbl_countries as C ON C.CountryID=UI.CountryID Where S.SupportID='".$SupportID."'";
                $tmp=DB::SELECT($sql);
                if(count($tmp)>0){
                    $return=array(
                        "Name"=>$tmp[0]->UserName,
                        "MobileNumber"=>$tmp[0]->MobileNumber
                    );
                }
//            }else{
//                $sql="SELECT U.CustomerID, U.CustomerName as UserName,CASE WHEN IFNULL(UI.MobileNo1,'')<>'' THEN CONCAT('+',C.PhoneCode,' ',UI.MobileNo1) ELSE '' END as MobileNumber FROM ".$this->supportDB."tbl_support as S LEFT JOIN users as U ON U.CustomerID=S.UserID LEFT JOIN tbl_customer as UI ON UI.CustomerID=S.UserID LEFT JOIN ".$this->generalDB."tbl_countries as C ON C.CountryID=UI.CountryID Where S.SupportID='".$SupportID."'";
//                $tmp=DB::SELECT($sql);
//                if(count($tmp)>0){
//                    $return=array(
//                        "Name"=>$tmp[0]->UserName,
//                        "MobileNumber"=>$tmp[0]->MobileNumber
//                    );
//                }
//            }
//        }
        return $return;
    }
	public function NewTicket(Request $req){
		if($this->general->isCrudAllow($this->CRUD,"add")==true){
            // $sql="SELECT U.CustomerID, U.CustomerName as UserName,CASE WHEN IFNULL(UI.MobileNo1,'')<>'' THEN CONCAT('+',C.PhoneCode,' ',UI.MobileNo1) ELSE '' END as MobileNumber FROM  users as U  LEFT JOIN users as UI ON UI.CustomerID=U.CustomerID LEFT JOIN ".$this->generalDB."tbl_countries as C ON C.CountryID=UI.CountryID Where U.LoginType=2 and U.DFlag=0";
            $FormData=$this->general->UserInfo;
            $FormData['PageName']="Account Settings New Support";
            $FormData['Customers']=DB::table('tbl_customer')->where('DFlag',0)->get();
            $FormData['Vendors']=DB::table('users')->where('DFlag',0)->where('LoginType','Vendor')->get();
            $FormData['SupportType']=DB::Table('tbl_support_type')->where('isAll',0)->where('ActiveStatus',1)->where('DFlag',0)->get();
            $FormData['SaveButtonID']=md5(date("YmdHis"));
            return view('app.support.new',$FormData);
        }else{
			return view('errors.403');
		}
    }
    public function SaveTicket(Request $req){
		if($this->general->isCrudAllow($this->CRUD,"add")==true){
            $UInfo=$this->general->UserInfo;
            $OldData=$NewData=array();$SupportID="";
            $ValidDB=array();
			$ValidDB['UserID']['TABLE']="tbl_customer";
			$ValidDB['UserID']['ErrMsg']="Customer does not exist";
			$ValidDB['UserID']['WHERE'][]=array("COLUMN"=>"CustomerID","CONDITION"=>"=","VALUE"=>$req->UserID);
			$ValidDB['UserID']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);
            $rules=array(
                'UserID'=>['required',new ValidDB($ValidDB['UserID'])],
                'Subject' =>'required|min:3|max:100',
                'Priority' =>'required',
                'SupportType' =>'required',
                'Description' => 'required|min:3',
                'Attachment1' => 'mimes:jpeg,jpg,png,gif,webp,bmp,pdf,doc,docx',
                'Attachment2' => 'mimes:jpeg,jpg,png,gif,webp,bmp,pdf,doc,docx',
                'Attachment3' => 'mimes:jpeg,jpg,png,gif,webp,bmp,pdf,doc,docx',
                'Attachment4' => 'mimes:jpeg,jpg,png,gif,webp,bmp,pdf,doc,docx'
            );
			$message=array(
                'UserID.required'=>'Customer is required'
			);
            $validator = Validator::make($req->all(), $rules,$message);

            if ($validator->fails()) {
                return array('status'=>false,'message'=>"New support ticket request has been submit failed",'errors'=>$validator->errors());
            }
            DB::beginTransaction();
            $status=false;
            try{
                $SupportID=DocNum::getDocNum(docTypes::Support->value,"",Helper::getCurrentFY());
                $data=array(
                    "SupportID"=>$SupportID,
                    "UserID"=>$req->UserID,
                    "Subject"=>$req->Subject,
                    "Priority"=>$req->Priority,
                    "SupportType"=>$req->SupportType,
                    "Status"=>1,
                    "DFlag"=>0,
                    "CreatedOn"=>date("Y-m-d H:i:s"),
                    "CreatedBy"=>$this->UserID,
                );
                $status=DB::Table($this->supportDB."tbl_support")->insert($data);
                if($status==true){
                    $status=$this->SaveSupportDetail($req,$SupportID);
                }
//                if($status==true){
//                    $status1=$this->sendMail($req->SupportType,$SupportID);
//                    if($status1==false){
//                        DB::rollback();
//                        $status=false;
//                        return $status1;
//                    }else{
//                        $status=true;
//                    }
//                }
            }catch(Exception $e) {
                logger($e);
                $status=false;
            }
            if($status==true){
                DocNum::updateDocNum(docTypes::Support->value);
                $NewData=DB::Table($this->supportDB."tbl_support")->where('SupportID',$SupportID)->get();
                $logData=array("Description"=>"New Support request has been submit successfully ","ModuleName"=>"Support","Action"=>"Add","ReferID"=>$SupportID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
                logs::Store($logData);


                DB::commit();
                return array('status'=>true,'message'=>"New support ticket request has been submit successfully");
            }else{
                DB::rollback();
                return array('status'=>false,'message'=>"New support ticket request has been submit  Failed");
            }
        }else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
        }
    }
    private function SaveSupportDetail($req,$SupportID){
        try{
            $SLNO=DocNum::getDocNum(docTypes::SupportDetails->value,"",Helper::getCurrentFY());
            $data=array(
                "SLNO"=>$SLNO,
                "SupportID"=>$SupportID,
                "UserID"=>$req->UserID,
                "Description"=>$req->Description,
                "DFlag"=>0,
                "CreatedOn"=>date("Y-m-d H:i:s"),
                "CreatedBy"=>$this->UserID
            );
            $status=DB::Table($this->supportDB."tbl_support_details")->insert($data);
            if($status==true){
                DocNum::updateDocNum(docTypes::SupportDetails->value);
                for($i=1;$i<=4;$i++){
                    if ($req->hasFile('Attachment'.$i)) {
                        if($status==true){
                            $status=$this->SaveAttachments(request()->file('Attachment'.$i),$SupportID,$SLNO);
                        }
                    }
                }

            }
//            if($status){
//                $status=DB::Table($this->supportDB."tbl_support")->update(array('status'=>1,"UpdatedOn"=>date("Y-m-d H:i:s")));
//            }
            if($status){
                $ReferID = DB::table($this->supportDB.'tbl_support as S')->leftJoin('tbl_customer as U','U.CustomerID','S.UserID')->where('S.SupportID',$SupportID)->value('U.CustomerID');
                $this->sendNotification($SupportID,$ReferID);
            }
//            if($status==true){
//                $status1=$this->sendMail($req->SupportType,$SupportID);
//                if($status1==false){
//                    DB::rollback();
//                    $status=false;
//                    return $status1;
//                }else{
//                    $status=true;
//                }
//            }
		}catch(Exception $e) {
        }
        return $status;
    }
    public function sendNotification($SupportID,$UserID){
        $Title = "Admin Respond your Ticket";
        $Message = "Admin has responded to your ticket. Check now for updates and further instructions.";
        $status = Helper::saveNotification($UserID,$Title,$Message,'Support',$SupportID);
    }

    private function SaveAttachments($file,$SupportID,$ReferID){
        //DB::beginTransaction();
        $status=false;
        try{
            $dir="uploads/support/".$SupportID."/";
            if (!file_exists( $dir)) {mkdir( $dir, 0777, true);}
            $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
            $filepath=$dir.$fileName;
            $FileSize=$file->getSize();
            $AttachmentID=DocNum::getDocNum(docTypes::SupportAttachments->value,"",Helper::getCurrentFY());
            $data=array("AttachmentID"=>$AttachmentID,"ReferID"=>$ReferID,"Module"=>"Support","FileSize"=>$FileSize,"UploadDir"=>$dir,"UploadFileName"=>$fileName,"UploadFileExt"=>$file->getClientOriginalExtension(),"UploadUrl"=>$filepath,"UserID"=>$this->UserID,"CreatedOn"=>date("Y-m-d H:i:s"));
            $status=DB::table($this->supportDB."tbl_attachment")->insert($data);
            if($status==true){
                $file->move($dir,$fileName);
                DocNum::updateDocNum(docTypes::SupportAttachments->value);
            }
        }catch(Exception $e){

        }
		return $status;
    }
    public function DeleteSupport(Request $req,$SupportID){
        $OldData=$NewData=array();
        DB::beginTransaction();
		$status=false;
		try{
			$OldData=DB::table($this->supportDB."tbl_support")->where('SupportID',$SupportID)->get();
			$status=DB::table($this->supportDB."tbl_support")->where('SupportID',$SupportID)->update(array("DFlag"=>1,"DeletedOn"=>date("Y-m-d H:i:s"),"DeletedBy"=>$this->UserID));
		}catch(Exception $e) {

		}
		if($status==true){
			DB::commit();
			$logData=array("Description"=>"Support Deleted ","ModuleName"=>"Support","Action"=>"Delete","ReferID"=>$SupportID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
			logs::Store($logData);

			return array('status'=>true,'message'=>"Deleted Successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"Delete Failed");
		}
    }
    public function ActivateSupport(Request $req,$SupportID){
        $OldData=$NewData=array();
        DB::beginTransaction();
		$status=false;
		try{
			$OldData=DB::table($this->supportDB."tbl_support")->where('SupportID',$SupportID)->get();
			$status=DB::table($this->supportDB."tbl_support")->where('SupportID',$SupportID)->update(array("Status"=>1,"DFlag"=>0,"UpdatedOn"=>date("Y-m-d H:i:s"),"UpdatedBy"=>$this->UserID));
		}catch(Exception $e) {

		}
		if($status==true){
            DB::commit();
            $NewData=DB::table($this->supportDB."tbl_support")->where('SupportID',$SupportID)->get();
			$logData=array("Description"=>"Support Reopened ","ModuleName"=>"Support","Action"=>"Update","ReferID"=>$SupportID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
			logs::Store($logData);

			return array('status'=>true,'message'=>"Reopened Successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"Reopen Failed");
		}
    }
    public function DeactivateSupport(Request $req,$SupportID){
        $OldData=$NewData=array();
        DB::beginTransaction();
		$status=false;
		try{
			$OldData=DB::table($this->supportDB."tbl_support")->where('SupportID',$SupportID)->get();
			$status=DB::table($this->supportDB."tbl_support")->where('SupportID',$SupportID)->update(array("Status"=>0,"DFlag"=>0,"UpdatedOn"=>date("Y-m-d H:i:s"),"UpdatedBy"=>$this->UserID));
		}catch(Exception $e) {

		}
		if($status==true){
            DB::commit();
            $NewData=DB::table($this->supportDB."tbl_support")->where('SupportID',$SupportID)->get();
			$logData=array("Description"=>"Support Closed ","ModuleName"=>"Support","Action"=>"Update","ReferID"=>$SupportID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
			logs::Store($logData);
            $ReferID = DB::table($this->supportDB.'tbl_support as S')->leftJoin('tbl_customer as U','U.CustomerID','S.UserID')->where('S.SupportID',$SupportID)->value('U.CustomerID');

            $Title = "Admin closed ticket";
            $Message = "Admin closed your ticket.";
            $status = Helper::saveNotification($ReferID,$Title,$Message,'Support',$SupportID);
			return array('status'=>true,'message'=>"Closed Successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"Close Failed");
		}
    }
    private function getSupportDetails($data){
        $sql="SELECT D.SLNO,D.UserID, CASE
        WHEN IFNULL(U.CustomerName, '') = '' AND IFNULL(U.MobileNo1, '') = '' THEN 'Support Team'
        WHEN IFNULL(U.CustomerName, '') = '' THEN U.MobileNo1
        ELSE U.CustomerName
    END AS Name,U.Email,UI.CustomerImage,D.SupportID,D.Description,D.DeliveryStatus,D.ReadStatus,D.DFlag,D.CreatedOn,D.UpdatedOn,D.DeletedOn,D.CreatedBy,D.UpdatedBy,D.DeletedBy From ".$this->supportDB."tbl_support_details as D left join ".$this->supportDB."tbl_support as H On H.SupportID=D.SupportID Left join tbl_customer as U ON U.CustomerID=D.UserID LEFT Join tbl_customer AS UI ON UI.CustomerID=U.CustomerID Where 1=1";
//        Here if the MobileNo1 also null then set the value as "Support Team"
        if(is_array($data)){
            if(array_key_exists("SLNO",$data)){ $sql.=" and D.SLNO='".$data['SLNO']."'";}
            if(array_key_exists("UserID",$data)){ $sql.=" and D.UserID='".$data['UserID']."'";}
            if(array_key_exists("SupportID",$data)){ $sql.=" and D.SupportID='".$data['SupportID']."'";}
        }
        $sql.=" Order By  D.SLNO";
        $result=DB::Select($sql);
        for($i=0;$i<count($result);$i++){
            $result[$i]->Attachments=$this->getAttachment(array("ReferID"=>$result[$i]->SLNO,"Module"=>"Support"));
        }
        return $result;
    }
    private function getAttachment($data){
        $sql="Select AttachmentID,ReferID,Module,UploadDir,UploadFileName,UploadFileExt,UploadUrl,DFlag From ".$this->supportDB."tbl_attachment where DFlag=0 ";
        if(is_array($data)){
            if(array_key_exists("AttachmentID",$data)){$sql.=" and AttachmentID='".$data['AttachmentID']."'";}
            if(array_key_exists("ReferID",$data)){$sql.=" and ReferID='".$data['ReferID']."'";}
            if(array_key_exists("Module",$data)){$sql.=" and Module='".$data['Module']."'";}
        }
        return DB::Select($sql);
    }
    public function SupportDetailsSave(Request $req){
        $SupportID=$req->SID;
        DB::beginTransaction();
        $status=$this->SaveSupportDetail($req,$SupportID);

		if($status==true){
            DB::table($this->supportDB."tbl_support")->where('SupportID',$SupportID)->update(array('Status'=>1,"DFlag"=>0,"UpdatedOn"=>date("Y-m-d H:i:s")));
            DB::commit();

			return array('status'=>true,'message'=>"submit successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"submit  Failed");
		}
    }
    public function getDetails(Request $req){
        $data=array("UserID"=>$this->UserID);
        if(isset($req->SupportID)){$data['SupportID']=$req->SupportID;}
        return $this->getSupportDetails($data);
    }
	public function TableView(Request $request){
			$ServerSideProcess=new ServerSideProcess();
			$columns = array(
				array( 'db' => 'O.OrderID', 'dt' => '0' ),
				array( 'db' => 'O.OrderNo', 'dt' => '1' ),
				array( 'db' => 'O.CustomerName', 'dt' => '2'),
				array( 'db' => 'O.TotalAmount', 'dt' => '3'),
				array( 'db' => 'O.TrackStatus', 'dt' => '4' ),
				array( 'db' => 'O.OrderDate', 'dt' => '5'),
				array( 'db' => 'O.OrderID', 'dt' => '6')
			);
			$columns1 = array(
				array( 'db' => 'OrderID', 'dt' => '0'),
				array( 'db' => 'OrderNo', 'dt' => '1'),
				array( 'db' => 'CustomerName', 'dt' => '2'),
//				array( 'db' => 'CreatedBy', 'dt' => '1','formatter' =>function($d,$row){
//                    $customer =DB::Table("tbl_customer")->Where("CustomerID", $row['CreatedBy'])->first();
//                    $html= $customer->CustomerName ?? $customer->nick_name;
//                    return $html;
//
//                } ),
				array( 'db' => 'TotalAmount', 'dt' => '3'),
				array( 'db' => 'TrackStatus', 'dt' => '4', 'formatter' => function( $d, $row ) {
                    $Status="<span class='badge block badge-secondary mr-2'> Pending </span>";
                    $result=DB::Table("tbl_order")->Where("OrderID",$row['OrderID'])->get();
                    if(count($result)>0){
                        if($result[0]->DFlag==1){
                            $Status="<span class='badge block badge-danger mr-2'> Deleted </span>";
                        }else{
                            if($d== "Order Confirmed"){
                                $Status="<span class='badge block badge-warning mr-2'> Order Confirmed </span>";
                            }elseif($d=="Shipped"){
                                $Status="<span class='badge block badge-secondary mr-2'> Shipped </span>";
                            }elseif($d=="Delivered"){
                                $Status="<span class='badge block badge-success mr-2'> Delivered </span>";
                            }else {
                                $Status="<span class='badge block badge-info mr-2'> Payment Pending </span>";
                            }
                        }
                    }
					return $Status;
				}),
				array( 'db' => 'OrderDate', 'dt' => '5','formatter' => function( $d, $row ) {
                    return date("d - M - Y",strtotime($d));
                } ),
                array(
                    'db' => 'OrderID',
                    'dt' => '6',
                    'formatter' => function ($d, $row) {
                        $html = '';
                        if ($this->general->isCrudAllow($this->CRUD, "edit")) {
                            if($row['TrackStatus'] === "Order Confirmed"){
                                $html .= '<button type="button" data-id="' . $d . '" class="btn  btn-outline-success ' . $this->general->UserInfo['Theme']['button-size'] . ' mr-10 btnEdit" data-original-title="Edit"><i class="fa fa-pencil"></i></button>';
                            } else {
                                $html .= '<button type="button" data-id="' . $d . '" class="btn  btn-outline-success ' . $this->general->UserInfo['Theme']['button-size'] . ' mr-10 btnEdit" data-original-title="View"><i class="fa fa-eye"></i></button>';
                            }
                        }
                        if($row['TrackStatus']) {
                            $html .= '<a href="' . route('generatePackingLabel', $d) . '" target="_blank"><button type="button" data-id="' . $d . '" class="btn  btn-outline-success ' . $this->general->UserInfo['Theme']['button-size'] . ' mr-10" data-original-title="Packing Slip"><i class="fa fa-paper-plane"></i></button></a>';
                        }
                        return $html;
                    }
                )
            );
        $Where=intval($this->LoginType)==2?" O.CreatedBy='".$this->UserID."'":" 1=1 ";
        $Where.=" and date(O.CreatedOn)>='".date("Y-m-d",strtotime($request->FromDate))."'";
        $Where.=" and date(O.CreatedOn)>='".date("Y-m-d",strtotime($request->FromDate))."'";
        $Where.=" and O.PaymentID != ''";
        if($request->User!=""){
            $Where.=" and CreatedBy='".$request->User."'";
        }
//        if($request->Status){
//            $Where.=" and O.TrackStatus='". $request->Status ."' and O.DFlag='0'";
//        } elseif($request->Status === "Payment Pending"){
//            $Where.=" and O.PaymentID='' and O.DFlag='0'"; // check PaymentID == null here
//        }

        if ($request->Status) {
            if ($request->Status === "Payment Pending") {
                $Where .= " and O.PaymentID='' and O.DFlag='0'";
            } else {
                $Where .= " and O.TrackStatus='". $request->Status ."' and O.DFlag='0'";
            }
        }
        $data=array();
        $data['POSTDATA']=$request;
        $data['TABLE']="tbl_order as O";
        $data['PRIMARYKEY']='O.OrderID';
        $data['COLUMNS']=$columns;
        $data['COLUMNS1']=$columns1;
        $data['GROUPBY']=null;
        $data['WHERERESULT']=null;
        $data['WHEREALL']=$Where;
        return $ServerSideProcess->SSP( $data);
	}

    /**
     * @throws Exception
     */

    public function Edit($OrderID)
    {
        if ($this->general->isCrudAllow($this->CRUD, "edit")) {
            $FormData = $this->general->UserInfo;
            $FormData['menus'] = $this->Menus;
            $FormData['crud'] = $this->CRUD;
            $FormData['ActiveMenuName'] = $this->ActiveMenuName;
            $FormData['PageTitle'] = $this->PageTitle;
            $FormData['isEdit'] = true;
            $CompanyStateID = DB::table('tbl_company_settings')->where('KeyName', 'StateID')->value('KeyValue');
            $CompanyState = DB::table($this->generalDB.'tbl_states')->where('StateID', $CompanyStateID)->value('StateName');
            $taxes = [];
            $orderDetails = Order::with('orderDetails')
                ->where('OrderID', $OrderID)
                ->get();
            $orderDetails->transform(function ($order) use ($CompanyState, &$taxes) {
                $isIGST = !($order->State == $CompanyState);
                if ($order->orderDetails) {
                    $order->orderDetails->transform(function ($detail) use ($isIGST, &$taxes) {
                        $detail->PRate = Helper::addRupeesSymbol($detail->PRate);
                        $detail->SRate = Helper::addRupeesSymbol($detail->SRate);
                        $detail->Amount = Helper::addRupeesSymbol($detail->Amount);

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
                                ->select('AD.Values')
                                ->first();
                        } else {
                            $productUnit = DB::table('tbl_products')
                                ->where('tbl_products.ProductID', $detail->ProductID)
                                ->leftJoin('tbl_uom as U', 'U.UID', '=', 'tbl_products.UID')
                                ->select('U.UName')
                                ->first();
                        }

                        $detail->Unit = $productUnit->Values ?? $productUnit->UName ?? '-';
                        return $detail;
                    });
                }

                $order->taxes = $taxes;
                $order->SubTotal = Helper::addRupeesSymbol($order->SubTotal);
                $order->DiscountAmount = Helper::addRupeesSymbol($order->DiscountAmount);
                $order->ShippingCharge = Helper::addRupeesSymbol($order->ShippingCharge);
                $order->TotalAmountInString = Helper::addRupeesSymbol($order->TotalAmount);
                $order->OrderDate = Carbon::parse($order->OrderDate)->format('D, M d, Y');
                $order->orderTrackDetails->sortBy('orderBy');
                $order->orderTrackDetails->transform(function ($orderTrack) {
                    $orderTrack->StatusDate = $orderTrack->StatusDate ? Carbon::parse($orderTrack->StatusDate)->format('D, M d, Y') : null;
                    return $orderTrack;
                });
                return $order;
            });

            logger("orderDetails");
            logger($orderDetails);
            $FormData['EditData'] = $orderDetails;
            if (count($FormData['EditData']) > 0) {
                return view('app.order.create', $FormData);
            } else {
                return view('errors.403');
            }
        } elseif ($this->general->isCrudAllow($this->CRUD, "view")) {
            return Redirect::to('/admin/master/product/coupon');
        } else {
            return view('errors.403');
        }
    }
    public function busyShow($BusyID)
    {
        if ($this->general->isCrudAllow($this->CRUD, "edit")) {
            $FormData = $this->general->UserInfo;
            $FormData['menus'] = $this->Menus;
            $FormData['crud'] = $this->CRUD;
            $FormData['ActiveMenuName'] = $this->ActiveMenuName;
            $FormData['PageTitle'] = $this->PageTitle;
            $FormData['isEdit'] = true;
            libxml_use_internal_errors(true);
            $orderDetails = BusyIntegration::getSingleSale($BusyID);
            if (!mb_check_encoding($orderDetails, 'UTF-8')) {
                $orderDetails = mb_convert_encoding($orderDetails, 'UTF-8');
            }
            $reader = XmlReader::fromString($orderDetails);
            $orderNo = $reader->value('Sale.VchNo')->first();
            $partyName = $reader->value('Sale.BillingDetails.PartyName')->first();
            $address1 = $reader->value('Sale.BillingDetails.Address1')->first();
            $address2 = $reader->value('Sale.BillingDetails.Address2')->first();
            $address3 = ''; // Initialize with empty string to handle potential encoding errors
            try {
                $address3 = $reader->value('Sale.BillingDetails.Address3')->first();
            } catch (\VeeWee\Xml\Exception\RuntimeException $e) {
                $address3 = '';
            }
            $date = $reader->value('Sale.Date')->first();
            $subTotal = $reader->value('Sale.tmpCostOfGoods')->first();
            $discountAmount = $reader->xpathValue('//BSDetail[BSName="Discount"]/Amt')->first();
            $shippingCharge = $reader->xpathValue('//BSDetail[BSName="Shipping Charge"]/Amt')->first() ?? 0;
            $totalAmount = $reader->value('Sale.tmpTotalAmt')->first();
            $completeAddress = "{$address1}, {$address2}, {$address3}";

            $itemDetails = [];
            $taxes = [];
            $items = $reader->value('Sale.ItemEntries.ItemDetail')->get();
            foreach ($items as $item) {
                $itemName = $item['ItemName'];
                $qty = $item['Qty'];
                $price = $item['Price'];
                $gstPercent = $item['STPercent'] ? $item['STPercent'] * 2 : 0;
                $gstAmount = $item['STAmount'] ?? 0;

                if (isset($item['TaxBeforeSurcharge1']) && isset($item['TaxBeforeSurcharge'])) {
                    if (!isset($taxes[$gstPercent])) {
                        $taxes[$gstPercent] = ['SGST' => 0, 'CGST' => 0, 'Total' => 0];
                    }
                    $taxes[$gstPercent]['SGST'] += $item['TaxBeforeSurcharge'];
                    $taxes[$gstPercent]['CGST'] += $item['TaxBeforeSurcharge1'];
                    $taxes[$gstPercent]['Total'] += $gstAmount;
                } else {
                    if (!isset($taxes[$gstPercent])) {
                        $taxes[$gstPercent] = ['IGST' => 0, 'Total' => 0];
                    }
                    $taxes[$gstPercent]['IGST'] += $item['TaxBeforeSurcharge'] ?? 0;
                    $taxes[$gstPercent]['Total'] += $gstAmount;
                }
                $itemDetail = new \stdClass();
                $itemDetail->ProductName = $itemName;
                $itemDetail->Qty = $qty;
                $itemDetail->SRate = $price;
//                $itemDetail->GSTPercent = $gstPercent;
//                $itemDetail->GSTAmount = $gstAmount;
                $itemDetail->Amount = $qty * $price;

                $itemDetails[] = $itemDetail;
            }

            $order = (object)[
                'OrderNo' => $orderNo,
                'CustomerName' => $partyName,
                'CompleteAddress' => $completeAddress,
                'OrderDate' => date('Y-m-d', strtotime($date)),
                'SubTotal' => $subTotal,
                'DiscountAmount' => $discountAmount,
                'ShippingCharge' => $shippingCharge,
                'TotalAmount' => $totalAmount,
                'BusySaleID' => $BusyID,
                'productCount' => count($itemDetails),
                'orderDetails' => $itemDetails,
                'taxes' => $taxes, // Add the taxes to the order object
            ];

            if ($order->orderDetails) {
                foreach($order->orderDetails as $detail) {
                    $detail->SRate = Helper::addRupeesSymbol($detail->SRate);
                    $detail->Amount = Helper::addRupeesSymbol($detail->Amount);
//                    $detail->GSTAmount = Helper::addRupeesSymbol($detail->GSTAmount);
                }
            }
            $order->SubTotal = Helper::addRupeesSymbol($order->SubTotal);
            $order->DiscountAmount = Helper::addRupeesSymbol($order->DiscountAmount);
            $order->ShippingCharge = Helper::addRupeesSymbol($order->ShippingCharge);
            $order->TotalAmountInString = Helper::addRupeesSymbol($order->TotalAmount);
            $order->OrderDate = Carbon::parse($order->OrderDate)->format('D, M d, Y');
            $FormData['EditData'] = $order;

            return view('app.order.busy_show', $FormData);
        } elseif ($this->general->isCrudAllow($this->CRUD, "view")) {
            return Redirect::to('/admin/master/product/coupon');
        } else {
            return view('errors.403');
        }
    }

    public function update(Request $req, $OrderID)
    {
        if ($this->general->isCrudAllow($this->CRUD, "edit")) {
            $req->validate([
                'courierName' => ['required'],
                'courierTrackNo' => ['required']
            ]);
            DB::beginTransaction();
            try {
                $OldData = Order::where('OrderID', $OrderID)->first();
                if (!$OldData->PaymentID) {
                    return array('status' => false, 'message' => "Payment not completed. So, This Request can't be processed!");
                }
                $data = array(
                    "TrackStatus" => "Shipped",
                    "courierName" => $req->courierName,
                    "courierTrackNo" => $req->courierTrackNo,
                    "UpdatedBy" => $this->UserID,
                    "UpdatedOn" => date("Y-m-d H:i:s")
                );
                $Order = Order::where('OrderID', $OrderID)->update($data);
                $Description = "Your Order Has Been Shipped, You Will Receive Shipped Within 2 To 3 Days";
                $Title = "Shipment update";
                $Message = "Your Order shipped successfully";
                CustomerOrderTrack::where('OrderID', $OrderID)->where('Status', "Shipped")
                    ->update(["Description" => $Description, "StatusDate" => Carbon::now(), "UpdatedBy" => $this->UserID]);
//                [$orderDetails, $logo, $companyDetails, $locationDetails] = $this->generateMailData($OrderID);
//                Mail::to($Order->Email)->send(new OrderMail("Shipment", 'orderDetails', 'companyDetails', 'locationDetails', 'logo'));

                Helper::saveNotification($OldData->CreatedBy, $Title, $Message, 'Order', $OrderID);
                $NewData = Order::where('OrderID', $OrderID)->get();
                $logData = array("Description" => "Order Track Status Updated", "ModuleName" => $this->ActiveMenuName, "Action" => cruds::UPDATE->value, "ReferID" => $OrderID, "OldData" => $OldData, "NewData" => $NewData, "UserID" => $this->UserID, "IP" => $req->ip());
                logController::Store($logData);
                DB::commit();
                return array('status' => true, 'message' => "Track Status Updated Successfully");
            } catch (Exception $e) {
                logger($e);
                DB::rollback();
                return array('status' => false, 'message' => "Track Status Update Failed");
            }
        } else {
            return array('status' => false, 'message' => 'Access denied');
        }
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

    public function generatePackingLabel($OrderID)
    {
        $order = Order::with('orderDetails')
            ->where('OrderID', $OrderID)
            ->first();
        if ($order) {
            $order->OrderDate = Carbon::parse($order->OrderDate)->format('M d, Y');
            $order->CustomerName = Helper::translate($order->CustomerName, 'en');
            $order->CompleteAddress = Helper::translate($order->CompleteAddress, 'en');
            if ($order->orderDetails) {
                $order->orderDetails->transform(function ($detail) {
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
                            ->select('AD.Values')
                            ->first();
                    } else {
                        $productUnit = DB::table('tbl_products')
                            ->where('tbl_products.ProductID', $detail->ProductID)
                            ->leftJoin('tbl_uom as U', 'U.UID', '=', 'tbl_products.UID')
                            ->select('U.UName')
                            ->first();
                    }

                    $detail->weight = $productUnit->Values ?? $productUnit->UName ?? '-';
                    return $detail;
                });
            }
        }

        $pdf = PDF::loadView('packing_label', compact('order'));
        $pdf->setPaper([0, 0, 283.46, 425.20]); // 100mm x 150mm in points (1mm = 2.8346 points)
        return $pdf->stream("Packing slip " . $OrderID . ".pdf");
    }
}
