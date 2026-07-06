<?php
namespace App\helper;

use App\enums\activeMenuNames;
use App\enums\cruds;
use App\helper\helper;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\DocNum;
use App\enums\docTypes;
use Illuminate\Support\Facades\Validator;
use logs;
use Illuminate\Support\Facades\Session;
use App\Rules\ValidDB;
use App\Rules\ValidUnique;
use stdClass;

class ProductHelper {
    private static $ProcessStatus=0;
    public static function save($req,$UserID){
        self::checkTmpProductTables();
        if($req->saveType=="main"){
            $tmpDBName=Helper::getTmpDB();
            DB::statement("Delete From ".$tmpDBName."tbl_products Where Date(CreatedOn)<'".date("Y-m-d")."'");
            DB::statement("Delete From ".$tmpDBName."tbl_products_variation Where Date(CreatedOn)<'".date("Y-m-d")."'");
            return self::variationProductSave($req,$UserID);
        }elseif($req->saveType=="Variable"){
            return self::variationSave($req,$UserID);
        }elseif($req->saveType=="confirm"){
            return self::SaveConfirmation($req,$UserID);
        }else{
            return array('status'=>false,'message'=>"Product Create Failed");
        }
    }
    public static function getSaveProcessStatus($UserID){
		$tmpDBName=Helper::getTmpDB();
        try {
            $status = true;
            while ($status) {
                $percentage = 0;
                $t=DB::connection('mysql2')->Table($tmpDBName."tbl_product_save_status")->where('UserID',$UserID)->get();
                if(count($t)>0){
                    $percentage=$t[0]->Percentage;
                }
                echo "data: {$percentage}\n\n";
                ob_flush();
                flush();
                if (floatval($percentage) >= 100) {
                    $status = false;
                }
            }

            return ['status' => 'completed']; // Indicate completion
        } catch (\Exception $e) {
            // Handle the exception, log it, or take appropriate action
            error_log("Error in getSaveProcessStatus: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'An error occurred'];
        }
    }
    private static function CompletedPercentage($TotalImagesCount,$Completed,$tmpDBName,$UserID){
        $TotalImagesCount=$TotalImagesCount==0?100:$TotalImagesCount;
        $percentage = ($Completed * 100) / $TotalImagesCount;
        $percentage = number_format($percentage, 2);
        $t=DB::connection('mysql2')->Table($tmpDBName."tbl_product_save_status")->where('UserID',$UserID)->get();
        if(count($t)>0){
            DB::connection('mysql2')->Table($tmpDBName."tbl_product_save_status")->where('UserID',$UserID)->update(array("Percentage"=>$percentage));
        }else{
            $status=DB::connection('mysql2')->Table($tmpDBName."tbl_product_save_status")->insert(array("UserID"=>$UserID,"Percentage"=>$percentage));
        }
    }
    private static function SaveConfirmation($req,$UserID){
        ini_set('memory_limit', '-1');
        $tmpProductID=$req->ProductID;
        $TotalImagesCount=$req->TotalImagesCount;
        $ProductID="";
        $Completed=0;
        $TotalImagesCount+=20;
		$tmpDBName=Helper::getTmpDB();
        self::CompletedPercentage($TotalImagesCount,0,$tmpDBName,$UserID);
		DB::beginTransaction();
		$status=false;
		$images=array();
		$ProductImage="";
		$ProductBrochure="";
		$galleryImages=array();
        $RemoveImg=array();
        $uploadingImgs=array();// if save failed than upload images remove
        $isDeleteProductImage=false;
        $isDeleteProductBrochure=false;
        try {
            $result=DB::Table($tmpDBName."tbl_products")->where('ProductID',$tmpProductID)->get();
            if(count($result)){
                //Checking Product ID Exists or not for save or update
                $isNewProductID=true;
                $ProductID=DocNum::getDocNum(docTypes::Product->value,"",Helper::getCurrentFY());
                $t=DB::Table("tbl_products")->where('ProductID',$tmpProductID)->get();
                if(count($t)>0){
                    $isNewProductID=false;
                    $ProductID=$tmpProductID;
                }

                $tmpImage=json_decode(json_encode(unserialize($result[0]->Images)));
                $tmpDoc=json_decode(json_encode(unserialize($result[0]->ProductBrochure)));
                $gallery=json_decode(json_encode(unserialize($result[0]->gallery)));

                //uploaded images move to products folder from temp folder
                $dir="uploads/master/product/products/".$ProductID."/";
                $gDir=$dir."gallery/";
                if (!file_exists( $dir)) {mkdir( $dir, 0777, true);}
                if (!file_exists( $gDir)) {mkdir( $gDir, 0777, true);}

                if ($tmpImage !== null) {

                    if (isset($tmpImage->isDeleted) && !empty((array)$tmpImage->isDeleted)) {
                        $isDeleteProductImage=intval($tmpImage->isDeleted)==1?true:false;
                    }
                    if (isset($tmpImage->data) && !empty((array)$tmpImage->data)) {
                        $Completed++;
                        self::CompletedPercentage($TotalImagesCount,$Completed,$tmpDBName,$UserID);
                        if($tmpImage->data->referData->isTemp =="1" && file_exists($tmpImage->data->uploadPath) ){
                            $fileName1=$tmpImage->data->fileName!=""?$tmpImage->data->fileName:Helper::RandomString(10)."png";
                            copy($tmpImage->data->uploadPath,$dir.$fileName1);
                            $ProductImage=$dir.$fileName1;
                            $images=helper::ImageResize($ProductImage,$dir);

                            $RemoveImg[]=$tmpImage->data->uploadPath;
                            $uploadingImgs[]=$ProductImage;

                            foreach($images as $tindex=>$turl){
                                $uploadingImgs[]=$turl['url'];
                            }
                        }
                    }
                }
                if ($tmpDoc !== null) {
                    if (isset($tmpDoc->isDeleted) && !empty((array)$tmpDoc->isDeleted)) {
                        $isDeleteProductBrochure=intval($tmpDoc->isDeleted)==1?true:false;
                    }
                    if (isset($tmpDoc->data) && !empty((array)$tmpDoc->data)) {
                        $Completed++;
                        self::CompletedPercentage($TotalImagesCount,$Completed,$tmpDBName,$UserID);
                        if(file_exists($tmpDoc->data->uploadPath) ){
                            $fileName1=$tmpDoc->data->fileName!=""?$tmpDoc->data->fileName:Helper::RandomString(10)."pdf";
                            copy($tmpDoc->data->uploadPath,$dir.$fileName1);
                            $ProductBrochure=$dir.$fileName1;

                            $RemoveImg[]=$tmpDoc->data->uploadPath;
                            $uploadingImgs[]=$ProductBrochure;

                            foreach($images as $tindex=>$turl){
                                $uploadingImgs[]=$turl['url'];
                            }
                        }
                    }
                }
                foreach($gallery as $ImgID=>$gData){
                    $Completed++;
                    self::CompletedPercentage($TotalImagesCount,$Completed,$tmpDBName,$UserID);
                    if($gData->referData->isTemp =="1" && file_exists($gData->uploadPath) ){
                        $fileName1=$gData->fileName!=""?$gData->fileName:Helper::RandomString(10)."png";
                        copy($gData->uploadPath,$gDir.$fileName1);

                        $t=array("gImage"=>$gDir.$fileName1,"ImgID"=>$ImgID,"Images"=>array());
                        $t['Images']=helper::ImageResize($gDir.$fileName1,$gDir);
                        $galleryImages[]=$t;

                        $RemoveImg[]=$gData->uploadPath;
                        foreach($t['Images'] as $tindex=>$turl){
                            $uploadingImgs[]=$turl['url'];
                        }
                    }
                }
                //get to previous uploaded image if new image upload or removed
                if($ProductImage!="" || $isDeleteProductImage==true){
                    $t=DB::Table("tbl_products")->where('ProductID',$tmpProductID)->get();
                    if(count($t)>0){
                        $RemoveImg[]=$t[0]->ProductImage;
                        $tGallery=unserialize($t[0]->Images);
                        foreach($tGallery as $index=>$tval){
                            $RemoveImg[]=$tval['url'];
                        }
                    }
                }
                //get to previous uploaded doc if new doc upload or removed
                if($ProductBrochure!="" || $isDeleteProductBrochure == true){
                    $t=DB::Table("tbl_products")->where('ProductID',$tmpProductID)->get();
                    if(count($t)>0){
                        $RemoveImg[]=$t[0]->ProductBrochure;
                    }
                }
                //product information save to main table
                if( $isNewProductID==false){
                    $data=array(
                        "Slug"=>Helper::generateSlug($result[0]->ProductName),
                        "ProductName"=>$result[0]->ProductName,
                        "ProductNameInTranslation"=>$result[0]->ProductNameInTranslation,
                        "ProductType"=>$result[0]->ProductType,
                        "ProductCode"=>$result[0]->ProductCode,
//                        "Stages"=>$result[0]->Stages,
                        "RelatedProducts"=>$result[0]->RelatedProducts,
                        "VideoURL"=>$result[0]->VideoURL ?? '',
                        "SKU"=>$result[0]->SKU,
                        "HSNSAC"=>$result[0]->HSNSAC,
                        "CTID"=>$result[0]->CTID,
                        "CID"=>$result[0]->CID,
                        "SCID"=>$result[0]->SCID,
                        "UID"=>$result[0]->UID,
                       "TaxType"=>$result[0]->TaxType,
                       "TaxID"=>$result[0]->TaxID,
                        "PRate"=>$result[0]->PRate,
                        "SRate"=>$result[0]->SRate,
//                        "Decimals"=>$result[0]->Decimals,
                        "Attributes"=>$result[0]->Attributes,
                        "Description"=>$result[0]->Description,
                        "ShortDescription"=>$result[0]->ShortDescription,
                        "ActiveStatus"=>$result[0]->ActiveStatus,
                        "UpdatedBy"=>$UserID,
                        "UpdatedOn"=>date("Y-m-d H:i:s")
                    );
                    if($ProductImage!=""){
                        $data['ProductImage']=$ProductImage;
                        $data['Images']=serialize($images);
                    }else if($isDeleteProductImage){
                        $data['ProductImage']="";
                        $data['Images']=serialize(array());
                    }
                    if($ProductBrochure!=""){
                        $data['ProductBrochure']=$ProductBrochure;
                    }else if($isDeleteProductBrochure){
                        $data['ProductBrochure']="";
                    }
                    $status=DB::Table('tbl_products')->where('ProductID',$ProductID)->update($data);
                }else{
                    $data=array(
                        "ProductID"=>$ProductID,
                        "Slug"=>Helper::generateSlug($result[0]->ProductName),
                        "ProductName"=>$result[0]->ProductName,
                        "ProductNameInTranslation"=>$result[0]->ProductNameInTranslation,
                        "ProductType"=>$result[0]->ProductType,
                        "ProductCode"=>$result[0]->ProductCode,
//                        "Stages"=>$result[0]->Stages,
                        "RelatedProducts"=>$result[0]->RelatedProducts,
                        "VideoURL"=>$result[0]->VideoURL ?? '',
                        "SKU"=>$result[0]->SKU,
                        "HSNSAC"=>$result[0]->HSNSAC,
                        "CTID"=>$result[0]->CTID,
                        "CID"=>$result[0]->CID,
                        "SCID"=>$result[0]->SCID,
                        "UID"=>$result[0]->UID,
                       "TaxType"=>$result[0]->TaxType,
                       "TaxID"=>$result[0]->TaxID,
                        "PRate"=>$result[0]->PRate,
                        "SRate"=>$result[0]->SRate,
//                        "Decimals"=>$result[0]->Decimals,
                        "Attributes"=>$result[0]->Attributes,
                        'ProductImage'=>$ProductImage,
                        'ProductBrochure'=>$ProductBrochure,
                        "Images"=>serialize($images),
                        "Description"=>$result[0]->Description,
                        "ShortDescription"=>$result[0]->ShortDescription,
                        "ActiveStatus"=>$result[0]->ActiveStatus,
                        "CreatedBy"=>$UserID,
                        "CreatedOn"=>date("Y-m-d H:i:s")
                    );
                    $status=DB::Table('tbl_products')->insert($data);
                    DocNum::updateDocNum(docTypes::Product->value);
                }
                //attributes information save to attributes tabe
                if($status){
                    $Attributes=json_decode(json_encode(unserialize($result[0]->Attributes)));
                    $tmpAIDs=array();
                    foreach($Attributes as $AttributeID=>$Attribute){
                        $tmpAIDs[]=$AttributeID;
                        $t=DB::Table('tbl_products_attributes')->where('AttributeID',$AttributeID)->where('ProductID',$ProductID)->get();
                        if(count($t)>0){
                            $tdata=array(
                                "DetailID"=>DocNum::getDocNum(docTypes::ProductAttributes->value,"",Helper::getCurrentFY()),
                                "ProductID"=>$ProductID,
                                "AttributeID"=>$AttributeID,
                                "isVariation"=>($Attribute->isVariation==true||$Attribute->isVariation=="true")?1:0,
                                "AttributeValues"=> serialize((array)$Attribute),
                                "DFlag"=>0,
                                "UpdatedBy"=>$UserID,
                                "UpdatedOn"=>date("Y-m-d H:i:s")
                            );
                            $status=DB::Table('tbl_products_attributes')->where('AttributeID',$AttributeID)->where('ProductID',$ProductID)->update($tdata);
                        }else{
                            $tdata=array(
                                "DetailID"=>DocNum::getDocNum(docTypes::ProductAttributes->value,"",Helper::getCurrentFY()),
                                "ProductID"=>$ProductID,
                                "AttributeID"=>$AttributeID,
                                "isVariation"=>($Attribute->isVariation==true||$Attribute->isVariation=="true")?1:0,
                                "AttributeValues"=> serialize((array)$Attribute),
                                "CreatedBy"=>$UserID,
                                "CreatedOn"=>date("Y-m-d H:i:s")
                            );
                            $status = DB::Table('tbl_products_attributes')->insert($tdata);
                            DocNum::updateDocNum(docTypes::ProductAttributes->value);
                        }
                    }
                    if($status && count($tmpAIDs)>0){
                        $sql="Select * From tbl_products_attributes Where ProductID='".$ProductID."' and  AttributeID not in('".implode("','",$tmpAIDs)."') and DFlag=0";
                        $t=DB::SELECT($sql);
                        if(count($t)>0){
                            $sql="Update tbl_products_attributes Set DFlag=1,DeletedOn='".date("Y-m-d H:i:s")."', DeletedBy='".$UserID."' Where ProductID='".$ProductID."' and AttributeID not in('".implode("','",$tmpAIDs)."') and DFlag=0 ";
                            $status=DB::UPDATE($sql);
                        }
                    }
                    $Stages=unserialize($result[0]->Stages);
                    $StageIDs=[];
                    foreach($Stages as $StageID){
                        $StageIDs[] = $StageID;
                        $t=DB::Table('tbl_products_stages')->where('ProductID',$ProductID)->where('StageID',$StageID)->first();
                        if(!$t){
                            $tdata=array(
                                "DetailID"=>DocNum::getDocNum(docTypes::ProductStages->value,"",Helper::getCurrentFY()),
                                "ProductID"=>$ProductID,
                                "StageID"=>$StageID,
                                "CreatedBy"=>$UserID,
                                "CreatedOn"=>date("Y-m-d H:i:s")
                            );
                            $status = DB::Table('tbl_products_stages')->insert($tdata);
                            DocNum::updateDocNum(docTypes::ProductStages->value);
                        }
                    }
                    if (!empty($StageIDs)) {
                        DB::Table('tbl_products_stages')->where('ProductID',$ProductID)->WhereIn('StageID',$StageIDs)->update(['DFlag'=>0,'UpdatedOn'=>date('Y-m-d H:i:s'),'UpdatedBy'=>$UserID]);
                        DB::Table('tbl_products_stages')->where('ProductID',$ProductID)->WhereNotIn('StageID',$StageIDs)->update(['DFlag'=>1,'UpdatedOn'=>date('Y-m-d H:i:s'),'UpdatedBy'=>$UserID]);
                        $status=true;
                    }
                }
                //galleries save to Main Gallery table
                if(count($galleryImages)>0 && $status==true){
                    for($i=0;$i<count($galleryImages);$i++){
                        if($status){
                            $tdata=array(
                                "SLNO"=>DocNum::getDocNum(docTypes::ProductGallery->value,"",Helper::getCurrentFY()),
                                "ProductID"=>$ProductID,
                                "ImgID"=>$galleryImages[$i]['ImgID'],
                                "gImage"=>$galleryImages[$i]['gImage'],
                                "Images"=>serialize($galleryImages[$i]['Images']),
                                "CreatedBy"=>$UserID,
                                "CreatedOn"=>date("Y-m-d H:i:s")

                            );
                            $status=DB::Table('tbl_products_gallery')->insert($tdata);
                            if($status){
                                DocNum::updateDocNum(docTypes::ProductGallery->value);
                            }
                        }
                    }
                }
                //variation details save
                $Variations=DB::Table($tmpDBName."tbl_products_variation")->where('ProductID',$tmpProductID)->get();
                $tmpVariationIDs=array();
                foreach($Variations as $index =>$Variation){
                    if($status){

                        $tmpVariationIDs[]=$Variation->UUID;
                        $vDir=$dir."variation/".$Variation->UUID."/";
                        $vGDir=$vDir."gallery/";
                        if (!file_exists( $vDir)) {mkdir( $vDir, 0777, true);}
                        if (!file_exists( $vGDir)) {mkdir( $vGDir, 0777, true);}
                        $Variation->Attributes=json_decode(json_encode(unserialize($Variation->Attributes)));
                        $Variation->Images=json_decode(json_encode(unserialize($Variation->Images)));

                        $Images=json_decode(json_encode(array("Cover"=>array("isDeleted"=>0,"Cover"=>"","Images"=>array()),"gallery"=>array())));
                        if ($Variation->Images !== null) {
                            if (isset($Variation->Images->cover) && !empty((array)$Variation->Images->cover)) {
                                $coverImage= $Variation->Images->cover;
                                if (isset($coverImage->isDeleted) && !empty((array)$coverImage->isDeleted)) {
                                    $Images->Cover->isDeleted=intval($coverImage->isDeleted)==1;
                                }
                                if (isset($coverImage->data) && !empty((array)$coverImage->data)) {
                                    $Completed++;
                                    self::CompletedPercentage($TotalImagesCount,$Completed,$tmpDBName,$UserID);
                                    if($coverImage->data->referData->isTemp =="1" && file_exists($coverImage->data->uploadPath) ){
                                        $fileName1=$coverImage->data->fileName!=""?$coverImage->data->fileName:Helper::RandomString(10)."png";
                                        copy($coverImage->data->uploadPath,$vDir.$fileName1);
                                        $Images->Cover->Cover=$vDir.$fileName1;
                                        $Images->Cover->Images=helper::ImageResize($Images->Cover->Cover,$vDir);
                                        $RemoveImg[]=$coverImage->data->uploadPath;
                                        $uploadingImgs[]=$Images->Cover->Cover;

                                        foreach($Images->Cover->Images as $tindex=>$turl){
                                            $uploadingImgs[]=$turl['url'];
                                        }
                                    }
                                }
                            }
                            if (isset($Variation->Images->gallery) && !empty((array)$Variation->Images->gallery)) {
                                $gallery=$Variation->Images->gallery;
                                foreach($gallery as $ImgID=>$gData){
                                    $Completed++;
                                    self::CompletedPercentage($TotalImagesCount,$Completed,$tmpDBName,$UserID);
                                    if($gData->referData->isTemp =="1" && file_exists($gData->uploadPath) ){
                                        $fileName1=$gData->fileName!=""?$gData->fileName:Helper::RandomString(10)."png";
                                        copy($gData->uploadPath,$vGDir.$fileName1);

                                        $t=array("gImage"=>$vGDir.$fileName1,"ImgID"=>$ImgID,"Images"=>array());
                                        $t['Images']=helper::ImageResize($vGDir.$fileName1,$vGDir);
                                        $Images->gallery[]=$t;

                                        $RemoveImg[]=$gData->uploadPath;
                                        foreach($t['Images'] as $tindex=>$turl){
                                            $uploadingImgs[]=$turl['url'];
                                        }
                                    }
                                }
                            }
                        }
                        $VariationID=DocNum::getDocNum(docTypes::ProductVariation->value,"",Helper::getCurrentFY());

                        $t=DB::Table("tbl_products_variation")->Where('ProductID',$ProductID)->where('UUID',$Variation->UUID)->get();
                        if(count($t)>0){
                            $VariationID=$t[0]->VariationID;
                            $tdata=array(
                                "Slug"=>$Variation->Slug,
                                "Title"=>$Variation->Title,
                                "SKU"=>$Variation->SKU,
                                "PRate"=>$Variation->PRate,
                                "SRate"=>$Variation->SRate,
                                "Attributes"=>serialize($Variation->Attributes),
                                "CombinationID"=>$Variation->CombinationID,
                                "DFlag"=>0,
                                "UpdatedBy"=>$UserID,
                                "UpdatedOn"=>date("Y-m-d H:i:s")
                            );
                            if($Images->Cover->Cover!=""){
                                $tdata['VImage']=$Images->Cover->Cover;
                                $tdata['Images']=serialize($Images->Cover->Images);
                            }else if(intval($Images->Cover->isDeleted)==1){
                                $tdata['VImage']="";
                                $tdata['Images']=serialize(array());
                            }
                            $status=DB::Table("tbl_products_variation")->Where('ProductID',$ProductID)->where('UUID',$Variation->UUID)->update($tdata);
                        }else{
                            $tdata=array(
                                "VariationID"=>$VariationID,
                                "ProductID"=>$ProductID,
                                "UUID"=>$Variation->UUID,
                                "Slug"=>$Variation->Slug,
                                "Title"=>$Variation->Title,
                                "SKU"=>$Variation->SKU,
                                "PRate"=>$Variation->PRate,
                                "SRate"=>$Variation->SRate,
                                "VImage"=>$Images->Cover->Cover,
                                "Images"=>serialize($Images->Cover->Images),
                                "Attributes"=>serialize($Variation->Attributes),
                                "CombinationID"=>$Variation->CombinationID,
                                "DFlag"=>0,
                                "createdBy"=>$UserID,
                                "CreatedOn"=>date("Y-m-d H:i:s")
                            );
                            $status=DB::Table("tbl_products_variation")->insert($tdata);
                            if($status){
                                DocNum::updateDocNum(docTypes::ProductVariation->value);
                            }
                        }
                        if($status){
                            $tmpDetailIDs=array();
                            foreach($Variation->Attributes->ValueIDs as $index=>$ValueID){
                                if($status){
                                    $AID="";
                                    $t=DB::Table('tbl_attributes_details')->where('ValueID',$ValueID)->get();
                                    if(count($t)>0){
                                        $AID=$t[0]->AttrID;
                                    }
                                    $t=DB::Table('tbl_products_variation_details')->where('ProductID',$ProductID)->where('VariationID',$VariationID)->where('AttributeID',$AID)->where('AttributeValueID',$ValueID)->get();
                                    if(count($t)>0){
                                        $tmpDetailIDs[]=$t[0]->DetailID;
                                        $addMin=Helper::getOTP(1);
                                        $tdata=array(
                                            "DFlag"=>0,
                                            "UpdatedOn"=>date("Y-m-d H:i:s",strtotime($addMin." Minutes")),
                                            "UpdatedBy"=>$UserID
                                        );
                                        $status=DB::Table('tbl_products_variation_details')->where('DetailID',$t[0]->DetailID)->update($tdata);
                                    }else{
                                        $DetailID=DocNum::getDocNum(docTypes::ProductVariationDetails->value,"",Helper::getCurrentFY());
                                        $tmpDetailIDs[]=$DetailID;
                                        $tdata=array(
                                            "DetailID"=>$DetailID,
                                            "VariationID"=>$VariationID,
                                            "ProductID"=>$ProductID,
                                            "UUID"=>$Variation->UUID,
                                            "AttributeID"=>$AID,
                                            "AttributeValueID"=>$ValueID,
                                            "DFlag"=>0,
                                            "CreatedOn"=>date("Y-m-d H:i:s"),
                                            "CreatedBy"=>$UserID
                                        );
                                        $status=DB::Table('tbl_products_variation_details')->insert($tdata);
                                        if($status){
                                            DocNum::updateDocNum(docTypes::ProductVariationDetails->value);
                                        }
                                    }
                                }
                            }
                            if($status && count($tmpDetailIDs)>0){
                                $sql="Delete From tbl_products_variation_details Where ProductID='".$ProductID."' and VariationID='".$VariationID."' and DetailID not in('".implode("','",$tmpDetailIDs)."')";
                                DB::DELETE($sql);
                            }
                        }
                        if(count($Images->gallery)>0 && $status==true){
                            for($i=0;$i<count($Images->gallery);$i++){
                                if($status){
                                    $tdata=array(
                                        "SLNO"=>DocNum::getDocNum(docTypes::ProductVariationGallery->value,"",Helper::getCurrentFY()),
                                        "ProductID"=>$ProductID,
                                        "VariationID"=>$VariationID,
                                        "UUID"=>$Variation->UUID,
                                        "ImgID"=>$Images->gallery[$i]['ImgID'],
                                        "gImage"=>$Images->gallery[$i]['gImage'],
                                        "Images"=>serialize($Images->gallery[$i]['Images']),
                                        "CreatedBy"=>$UserID,
                                        "CreatedOn"=>date("Y-m-d H:i:s")
                                    );
                                    $status=DB::Table('tbl_products_variation_gallery')->insert($tdata);
                                    if($status){
                                        DocNum::updateDocNum(docTypes::ProductVariationGallery->value);
                                    }
                                }
                            }
                        }
                    }

                }
                if($status==true && count($tmpVariationIDs)>0){
                    $sql="Delete From tbl_products_variation Where ProductID='".$ProductID."' and UUID not in('".implode("','",$tmpVariationIDs)."')";
                    DB::DELETE($sql);
                }
                //Delete  uploaded Main && Variation Gallery Images
                $DeletedImages=json_decode($req->deletedImages);
                if($status){
                    if (isset($DeletedImages->product) && !empty((array)$DeletedImages->product)) {
                        foreach($DeletedImages->product as $index=>$ImgID){
                            $t=DB::Table('tbl_products_gallery')->where('ProductID',$ProductID)->where('ImgID',$ImgID)->get();
                            if(count($t)>0){
                                $RemoveImg[]=$t[0]->gImage;
                                $tGallery=unserialize($t[0]->Images);
                                foreach($tGallery as $index=>$tval){
                                    $RemoveImg[]=$tval['url'];
                                }
                                DB::Table('tbl_products_gallery')->where('ProductID',$ProductID)->where('ImgID',$ImgID)->delete();
                            }
                        }
                    }
                    if (isset($DeletedImages->variation) && !empty((array)$DeletedImages->variation)) {
                        foreach($DeletedImages->variation as $index=>$gData){
                            $t=DB::Table('tbl_products_variation_gallery')->where('ProductID',$ProductID)->where('ProductID',$ProductID)->Where('UUID',$gData->uuid)->where('ImgID',$gData->ImgID)->get();
                            if(count($t)>0){
                                $RemoveImg[]=$t[0]->gImage;
                                $tGallery=unserialize($t[0]->Images);
                                foreach($tGallery as $index=>$tval){
                                    $RemoveImg[]=$tval['url'];
                                }
                                DB::Table('tbl_products_variation_gallery')->where('ProductID',$ProductID)->where('ProductID',$ProductID)->Where('UUID',$gData->uuid)->where('ImgID',$gData->ImgID)->delete();
                            }
                        }
                    }
                }
            }else{
                $status=false;
            }
        }catch(Exception $e) {
            logger($e);
			$status=false;
		}
        DB::connection('mysql2')->Table($tmpDBName."tbl_product_save_status")->where('UserID',$UserID)->delete();
        if($status==true){
			DB::commit();
			foreach($RemoveImg as $KeyName=>$Img){
				Helper::removeFile($Img);
			}
			return array('status'=>true,'message'=>"Product Saved successfully","ProductID"=>$ProductID);
		}else{
			DB::rollback();
			foreach($uploadingImgs as $KeyName=>$Img){
				Helper::removeFile($Img);
			}
			return array('status'=>false,'message'=>"Product save Failed");
		}
    }

    private static function variationProductSave($req, $UserID)
    {
        $tmpDBName = Helper::getTmpDB();
        DB::Table($tmpDBName . 'tbl_products')->where('ProductID', $req->ProductID)->delete();
        DB::Table($tmpDBName . 'tbl_products_variation')->where('ProductID', $req->ProductID)->delete();
        $ValidDB = array();
        //Category Type
        $ValidDB['CategoryType']['TABLE'] = "tbl_product_category_type";
        $ValidDB['CategoryType']['ErrMsg'] = "Category Type does not exist";
        $ValidDB['CategoryType']['WHERE'][] = array("COLUMN" => "PCTID", "CONDITION" => "=", "VALUE" => $req->CategoryType);
        $ValidDB['CategoryType']['WHERE'][] = array("COLUMN" => "DFlag", "CONDITION" => "=", "VALUE" => 0);
        $ValidDB['CategoryType']['WHERE'][] = array("COLUMN" => "ActiveStatus", "CONDITION" => "=", "VALUE" => 'Active');
        //Category
        $ValidDB['Category']['TABLE'] = "tbl_product_category";
        $ValidDB['Category']['ErrMsg'] = "Category does not exist";
        $ValidDB['Category']['WHERE'][] = array("COLUMN" => "PCID", "CONDITION" => "=", "VALUE" => $req->Category);
        $ValidDB['Category']['WHERE'][] = array("COLUMN" => "DFlag", "CONDITION" => "=", "VALUE" => 0);
        $ValidDB['Category']['WHERE'][] = array("COLUMN" => "ActiveStatus", "CONDITION" => "=", "VALUE" => 'Active');
        //Sub Category
        $ValidDB['SubCategory']['TABLE'] = "tbl_product_subcategory";
        $ValidDB['SubCategory']['ErrMsg'] = "Sub Category does not exist";
        $ValidDB['SubCategory']['WHERE'][] = array("COLUMN" => "PSCID", "CONDITION" => "=", "VALUE" => $req->SubCategory);
        $ValidDB['SubCategory']['WHERE'][] = array("COLUMN" => "PCID", "CONDITION" => "=", "VALUE" => $req->Category);
        $ValidDB['SubCategory']['WHERE'][] = array("COLUMN" => "DFlag", "CONDITION" => "=", "VALUE" => 0);
        $ValidDB['SubCategory']['WHERE'][] = array("COLUMN" => "ActiveStatus", "CONDITION" => "=", "VALUE" => 'Active');
        //UOM
        $ValidDB['UOM']['TABLE'] = "tbl_uom";
        $ValidDB['UOM']['ErrMsg'] = "unit of measurement does not exist";
        $ValidDB['UOM']['WHERE'][] = array("COLUMN" => "UID", "CONDITION" => "=", "VALUE" => $req->UID);
        $ValidDB['UOM']['WHERE'][] = array("COLUMN" => "DFlag", "CONDITION" => "=", "VALUE" => 0);
        $ValidDB['UOM']['WHERE'][] = array("COLUMN" => "ActiveStatus", "CONDITION" => "=", "VALUE" => 'Active');
        //Tax
		$ValidDB['Tax']['TABLE']="tbl_tax";
		$ValidDB['Tax']['ErrMsg']="Tax does not exist";
		$ValidDB['Tax']['WHERE'][]=array("COLUMN"=>"TaxID","CONDITION"=>"=","VALUE"=>$req->TaxID);
		$ValidDB['Tax']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);
		$ValidDB['Tax']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>'Active');
        $rules = array(
            'ProductName' => ['required', 'min:2', 'max:150', new ValidUnique(array("TABLE" => "tbl_products", "WHERE" => " ProductName='" . $req->ProductName . "'  and ProductID<>'" . $req->ProductID . "' "), "This Product Name is already taken.")],
            'SKU' => ['required', 'min:2', 'max:50', new ValidUnique(array("TABLE" => "tbl_products", "WHERE" => " SKU='" . $req->SKU . "'  and ProductID<>'" . $req->ProductID . "' "), "This SKU is already taken.")],
            'ProductNameInTranslation' => 'required',
            'CategoryType' => ['required', new ValidDB($ValidDB['CategoryType'])],
            'Category' => ['required', new ValidDB($ValidDB['Category'])],
            'SubCategory' => ['required', new ValidDB($ValidDB['SubCategory'])],
            'UID' => ['required', new ValidDB($ValidDB['UOM'])],
			'TaxID'=>['required',new ValidDB($ValidDB['Tax'])],
			'TaxType'=>'required|in:Include,Exclude',
            'RegularPrice' => 'required|numeric|min:0',
            'SalesPrice' => 'required|numeric|min:0',
//			'Decimals'=>'required|in:auto, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9',
        );
        $message = array(
            'UOM.required' => "Unit of measurement is required",
        );
        $validator = Validator::make($req->all(), $rules, $message);

        if ($validator->fails()) {
            return array('status' => false, 'message' => "Product Create Failed", 'errors' => $validator->errors());
        }
        DB::beginTransaction();
        $images = array();
        $ProductImage = "";
        $ProductBrochure = "";
        $Stages = "";
        $galleryImages = array();
        try {
            $ProductID = $req->ProductID != "" ? $req->ProductID : date("YmdHis") . "-" . Helper::RandomString(20);
            $tmpImage = json_decode($req->ProductImage, true);
//			$tmpDoc=json_decode($req->ProductBrochure,true);
            $gallery = json_decode($req->gallery);


            if ($tmpImage !== null) {
                if (isset($tmpImage->data) && !empty((array)$tmpImage->data)) {
                    if ($tmpImage->data->referData->isTemp == "1" && file_exists($tmpImage->data->uploadPath)) {
                        $ProductImage = $tmpImage->data->uploadPath;
                    }
                }
            }
//            if ($tmpDoc !== null) {
//                if (isset($tmpDoc->data) && !empty((array)$tmpDoc->data)) {
//                    if($tmpDoc->data->referData->isBrochure =="1" && file_exists($tmpDoc->data->uploadPath) ){
//                        $ProductBrochure=$tmpDoc->data->uploadPath;
//                    }
//                }
//            }
            foreach ($gallery as $ImgID => $gData) {
                if ($gData->referData->isTemp == "1" && file_exists($gData->uploadPath)) {
                    $fileName1 = $gData->fileName != "" ? $gData->fileName : Helper::RandomString(10) . "png";

                    $t = array("gImage" => $gData->uploadPath, "ImgID" => $ImgID, "Images" => array());
                    $galleryImages[] = $t;
                }
            }
            $data = [
                "ProductID" => $ProductID,
                "Slug" => Helper::generateSlug($req->ProductName),
                "ProductName" => $req->ProductName,
                "ProductNameInTranslation" => $req->ProductNameInTranslation,
                "ProductType" => $req->ProductType,
                "ProductCode" => $req->ProductCode,
                'Stages' => serialize([]),
                'RelatedProducts' => serialize($req->RelatedProducts),
                "VideoURL" => $req->VideoURL ?? '',
                "SKU" => $req->SKU,
                "HSNSAC" => $req->HSNSAC,
                "CTID" => $req->CategoryType,
                "CID" => $req->Category,
                "SCID" => $req->SubCategory,
                "UID" => $req->UID,
				"TaxType"=>$req->TaxType,
				"TaxID"=>$req->TaxID,
                "PRate" => $req->RegularPrice,
                "SRate" => $req->SalesPrice,
//				"Decimals"=>$req->Decimals,
                "Description" => $req->Description,
                "ShortDescription" => $req->ShortDescription,
                "Attributes" => serialize(json_decode($req->Attributes, true)),
                'Images' => serialize(json_decode($req->ProductImage, true)),
                'ProductBrochure' => serialize(json_decode($req->ProductBrochure, true)),
                'gallery' => serialize(json_decode($req->gallery, true)),
                "ActiveStatus" => $req->ActiveStatus,
                "CreatedBy" => $UserID,
                "CreatedOn" => date("Y-m-d H:i:s")
            ];
            $savedProduct = DB::Table('tbl_products')->where("ProductID", $ProductID)->first();
            if ($savedProduct) {
                $newTranslations = json_decode($req->ProductNameInTranslation);
                $existingTranslations = json_decode($savedProduct->ProductNameInTranslation);
                if (!$existingTranslations) {
                    $existingTranslations = new stdClass();
                }
                foreach ($newTranslations as $lang => $value) {
                    $existingTranslations->$lang = $value;
                }
                $data['ProductNameInTranslation'] = json_encode($existingTranslations);
            }
            DB::Table($tmpDBName . 'tbl_products')->insert($data);
            DB::commit();
            return array('status' => true, "ProductID" => $ProductID);
        } catch (Exception $e) {
            logger($e);
            DB::rollback();
            foreach ($images as $Img) {
                Helper::removeFile($Img['url']);
            }
            return array('status' => false, 'message' => "Product Create Failed");
        }
    }

    private static function variationSave($req, $UserID)
    {
        $tmpDBName = Helper::getTmpDB();
        DB::beginTransaction();
        $images = array();
        $Image = "";
        $galleryImages = array();
        try {
            $variationID = date("YmdHis") . "-" . Helper::RandomString(20);
            $tmpImage = json_decode($req->images, true);
            $Attributes = array();
            $Attributes["data"] = json_decode($req->data, true);
            $Attributes["ValueIDs"] = json_decode($req->ValueIDs, true);
            $data = [
                "VariationID" => $variationID,
                "UUID" => $req->UUID,
                "ProductID" => $req->ProductID,
                "Slug" => Helper::generateSlug($req->title),
                "Title" => $req->title,
                "SKU" => $req->SKU,
                "PRate" => $req->PurchasePrice,
                "SRate" => $req->SalesPrice,
                "Images" => serialize($tmpImage),
                "Attributes" => serialize($Attributes),
                "CombinationID" => implode("-", json_decode($req->ValueIDs, true)),
                "DFlag" => 0,
                "CreatedBy" => $UserID,
                "CreatedOn" => date("Y-m-d H:i:s")
            ];
            DB::Table($tmpDBName . 'tbl_products_variation')->insert($data);
            DB::commit();
            return array('status' => true, 'message' => "variation Saved Successfully");
        } catch (Exception $e) {
            logger($e);
            DB::rollback();
            //Helper::removeFile($BrandLogo);
            foreach ($images as $KeyName => $Img) {
                Helper::removeFile($Img['url']);
            }
            return array('status' => false, 'message' => "Product Create Failed");
        }
    }
	private static function checkTmpProductTables(){
		$tmpDBName=Helper::getTmpDB();
		if(!Helper::checkTableExists($tmpDBName, "tbl_products")){
			$sql="CREATE TABLE ".$tmpDBName."tbl_products (ProductID varchar(50) Primary Key,Slug varchar(160) DEFAULT NULL,ProductName varchar(150) DEFAULT NULL,ProductType enum('Simple','Variable') DEFAULT 'Simple',SKU varchar(50) DEFAULT NULL, HSNSAC varchar(50) DEFAULT NULL,ProductCode varchar(50) DEFAULT NULL,VideoURL text DEFAULT NULL,CID varchar(50) DEFAULT NULL,SCID varchar(50) DEFAULT NULL,UID varchar(50) DEFAULT NULL,TaxType enum('Exclude','Include') DEFAULT 'Exclude',TaxID varchar(50) DEFAULT NULL,PRate double DEFAULT 0,SRate double DEFAULT 0,Decimals enum('auto','0','1','2','3','4','5','6','7','8','9') DEFAULT 'auto',Description text DEFAULT NULL,ShortDescription text DEFAULT NULL,Attributes text,Images text DEFAULT NULL,gallery text DEFAULT NULL,ActiveStatus enum('Active','Inactive') DEFAULT 'Active',DFlag int(1) DEFAULT 0,CreatedOn timestamp NULL DEFAULT current_timestamp(),CreatedBy varchar(50) DEFAULT NULL,UpdatedOn timestamp NULL DEFAULT NULL,UpdatedBy varchar(50) DEFAULT NULL,DeletedOn timestamp NULL DEFAULT NULL,DeletedBy varchar(50) DEFAULT NULL)";
			DB::Statement($sql);
		}
		if(!Helper::checkTableExists($tmpDBName, "tbl_products_variation")){
            $sql=" CREATE TABLE ".$tmpDBName."tbl_products_variation (VariationID varchar(50) Primary Key,UUID varchar(100) DEFAULT NULL,ProductID varchar(50) DEFAULT NULL,Slug text DEFAULT NULL,Title varchar(150) DEFAULT NULL,PRate double DEFAULT 0,SRate double DEFAULT 0,Images text DEFAULT NULL,Attributes text DEFAULT NULL,CombinationID text DEFAULT NULL,DFlag int(1) DEFAULT 0,CreatedOn timestamp NULL DEFAULT current_timestamp(),CreatedBy varchar(50) DEFAULT NULL,UpdatedOn timestamp NULL DEFAULT NULL,UpdatedBy varchar(50) DEFAULT NULL,DeletedOn timestamp NULL DEFAULT NULL,DeletedBy varchar(50) DEFAULT NULL)";
			DB::Statement($sql);
		}
		if(!Helper::checkTableExists($tmpDBName, "tbl_product_save_status")){
            $sql=" CREATE TABLE ".$tmpDBName."tbl_product_save_status (UserID varchar(50) Primary Key,Percentage Double Default 0)";
			DB::Statement($sql);
		}
	}
	/* private static function simpleProductSave($req,$UserID){
		$OldData=array();$NewData=array();$ProductID="";

		$ValidDB=array();
		//Category
		$ValidDB['Category']['TABLE']="tbl_product_category";
		$ValidDB['Category']['ErrMsg']="Category does not exist";
		$ValidDB['Category']['WHERE'][]=array("COLUMN"=>"PCID","CONDITION"=>"=","VALUE"=>$req->Category);
		$ValidDB['Category']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);
		$ValidDB['Category']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>'Active');
		//Sub Category
		$ValidDB['SubCategory']['TABLE']="tbl_product_subcategory";
		$ValidDB['SubCategory']['ErrMsg']="Sub Category does not exist";
		$ValidDB['SubCategory']['WHERE'][]=array("COLUMN"=>"PSCID","CONDITION"=>"=","VALUE"=>$req->SubCategory);
		$ValidDB['SubCategory']['WHERE'][]=array("COLUMN"=>"PCID","CONDITION"=>"=","VALUE"=>$req->Category);
		$ValidDB['SubCategory']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);
		$ValidDB['SubCategory']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>'Active');
		//UOM
		$ValidDB['UOM']['TABLE']="tbl_uom";
		$ValidDB['UOM']['ErrMsg']="unit of measurement does not exist";
		$ValidDB['UOM']['WHERE'][]=array("COLUMN"=>"UID","CONDITION"=>"=","VALUE"=>$req->UID);
		$ValidDB['UOM']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);
		$ValidDB['UOM']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>'Active');
		//Tax
		$ValidDB['Tax']['TABLE']="tbl_tax";
		$ValidDB['Tax']['ErrMsg']="Tax does not exist";
		$ValidDB['Tax']['WHERE'][]=array("COLUMN"=>"TaxID","CONDITION"=>"=","VALUE"=>$req->TaxID);
		$ValidDB['Tax']['WHERE'][]=array("COLUMN"=>"DFlag","CONDITION"=>"=","VALUE"=>0);
		$ValidDB['Tax']['WHERE'][]=array("COLUMN"=>"ActiveStatus","CONDITION"=>"=","VALUE"=>'Active');
		$rules=array(
			'ProductName' =>['required','min:2','max:150',new ValidUnique(array("TABLE"=>"tbl_products","WHERE"=>" ProductName='".$req->ProductName."' "),"This Product Name is already taken.")],
	        'SKU' => ['required', 'min:2', 'max:50', new ValidUnique(array("TABLE" => "tbl_products", "WHERE" => " SKU='" . $req->SKU . "'  and ProductID<>'" . $req->ProductID . "' "), "This HSN is already taken.")],
			'Category'=>['required',new ValidDB($ValidDB['Category'])],
			'SubCategory'=>['required',new ValidDB($ValidDB['SubCategory'])],
			'UID'=>['required',new ValidDB($ValidDB['UOM'])],
			'TaxID'=>['required',new ValidDB($ValidDB['Tax'])],
			'TaxType'=>'required|in:Include,Exclude',
			'RegularPrice'=>'required|numeric|min:0',
			'SalesPrice'=>'required|numeric|min:0',
			'Decimals'=>'required|in:auto, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9',
		);
		$message=array(
			'UOM.required'=>"Unit of measurement is required",
		);
		$validator = Validator::make($req->all(), $rules,$message);

		if ($validator->fails()) {
			return array('status'=>false,'message'=>"Product Create Failed1",'errors'=>$validator->errors());
		}
		DB::beginTransaction();
		$status=false;
		$images=array();
		$ProductImage="";
		$galleryImages=array();
		try {
			$ProductID=DocNum::getDocNum(docTypes::Product->value,"",Helper::getCurrentFY());

			$tmpImage=json_decode($req->ProductImage);
			$gallery=json_decode($req->gallery);

			$dir="uploads/master/product/products/".$ProductID."/";
			$gDir="uploads/master/product/products/".$ProductID."/gallery/";
			if (!file_exists( $dir)) {mkdir( $dir, 0777, true);}
			if (!file_exists( $gDir)) {mkdir( $gDir, 0777, true);}
            if ($tmpImage !== null) {
                if (isset($tmpImage->data) && !empty((array)$tmpImage->data)) {
                    if($tmpImage->data->referData->isTemp =="1" && file_exists($tmpImage->data->uploadPath) ){
                        $fileName1=$tmpImage->data->fileName!=""?$tmpImage->data->fileName:Helper::RandomString(10)."png";
                        copy($tmpImage->data->uploadPath,$dir.$fileName1);
                        $ProductImage=$dir.$fileName1;
                        unlink($tmpImage->data->uploadPath);
                    }
                }
            }
			foreach($gallery as $ImgID=>$gData){

				if($gData->referData->isTemp =="1" && file_exists($gData->uploadPath) ){
					$fileName1=$gData->fileName!=""?$gData->fileName:Helper::RandomString(10)."png";
					copy($gData->uploadPath,$gDir.$fileName1);

					$t=array("gImage"=>$gDir.$fileName1,"ImgID"=>$ImgID,"Images"=>array());
					$t['Images']=helper::ImageResize($gDir.$fileName1,$gDir);
					$galleryImages[]=$t;
				}
			}
			if(file_exists($ProductImage)){
				$images=helper::ImageResize($ProductImage,$dir);
			}
			$data=array(
				"ProductID"=>$ProductID,
				"Slug"=>Helper::generateSlug($req->ProductName),
				"ProductName"=>$req->ProductName,
				"ProductType"=>$req->ProductType,
				"VideoURL"=>$req->VideoURL ?? '',
				"ProductCode"=>$req->ProductCode,
				"SKU"=>$req->SKU,
				"HSNSAC"=>$req->HSNSAC,
				"CID"=>$req->Category,
				"SCID"=>$req->SubCategory,
				"UID"=>$req->UID,
				"TaxType"=>$req->TaxType,
				"TaxID"=>$req->TaxID,
				"PRate"=>$req->RegularPrice,
				"SRate"=>$req->SalesPrice,
				"Decimals"=>$req->Decimals,
				'ProductImage'=>$ProductImage,
				"Images"=>serialize($images),
				"Description"=>$req->Description,
				"ShortDescription"=>$req->ShortDescription,
				"ActiveStatus"=>$req->ActiveStatus,
				"CreatedBy"=>$UserID,
				"CreatedOn"=>date("Y-m-d H:i:s")
			);
			$status=DB::Table('tbl_products')->insert($data);
			if($status){
                $Attributes=json_decode($req->Attributes);
                foreach($Attributes as $AttributeID=>$Attribute){
                    $tdata=array(
                        "DetailID"=>DocNum::getDocNum(docTypes::ProductAttributes->value,"",Helper::getCurrentFY()),
                        "ProductID"=>$ProductID,
                        "AttributeID"=>$AttributeID,
                        "isVariation"=>($Attribute->isVariation==true||$Attribute->isVariation=="true")?1:0,
                        "AttributeValues"=> serialize((array)$Attribute)
                    );
                    $status=DB::Table('tbl_products_attributes')->insert($tdata);
                    if($status){
                        DocNum::updateDocNum(docTypes::ProductAttributes->value);
                    }
                }
            }
            //
			if(count($galleryImages)>0 && $status==true){
				for($i=0;$i<count($galleryImages);$i++){
					if($status){
						$tdata=array(
							"SLNO"=>DocNum::getDocNum(docTypes::ProductGallery->value,"",Helper::getCurrentFY()),
							"ProductID"=>$ProductID,
							"ImgID"=>$galleryImages[$i]['ImgID'],
							"gImage"=>$galleryImages[$i]['gImage'],
							"Images"=>serialize($galleryImages[$i]['Images']),
							"CreatedBy"=>$UserID,
							"CreatedOn"=>date("Y-m-d H:i:s")

						);
						$status=DB::Table('tbl_products_gallery')->insert($tdata);
						if($status){
							DocNum::updateDocNum(docTypes::ProductGallery->value);
						}
					}
				}
			}
		}catch(Exception $e) {
			$status=false;
		}

		if($status==true){
			DocNum::updateDocNum(docTypes::Product->value);
			DB::commit();
			$NewData=DB::table('tbl_products')->where('ProductID',$ProductID)->get();
			$logData=array("Description"=>"New Brand Created ","ModuleName"=>activeMenuNames::Products->value,"Action"=>cruds::ADD->value,"ReferID"=>$ProductID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$UserID,"IP"=>$req->ip());
			logs::Store($logData);
			return array('status'=>true,'message'=>"Product Saved Successfully","ProductID"=>$ProductID);
		}else{
			DB::rollback();
			//Helper::removeFile($BrandLogo);
			foreach($images as $KeyName=>$Img){
				Helper::removeFile($Img['url']);
			}
			return array('status'=>false,'message'=>"Product Create Failed");
		}
	} */
}
