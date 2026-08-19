@extends('layouts.app')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@icon/dashicons@0.9.0-alpha.4/dashicons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.css">
<style>
    .body{
        background:#f0f0f1;
    }
    .custom input,.custom select,.custom textarea{
        border-radius:0px !important;
    }
    .card{
        border-radius:0px !important;
        position: relative;
        border: 1px solid #c3c4c7;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
        background: #fff;
        border
    }
    .card .card-header{
        font-size: 14px;
        padding: 8px 10px;
        margin: 0;
        line-height: 1.4;
        position: relative;
        border-bottom: 1px solid #c3c4c7;
    }
    .card .card-header .options{
        position: absolute;
        right: 10px;
        top: 0px;
        display: flex;
        height: 100%;
        align-items: center;
        margin: 0px;
        font-size: 17px;
    }
    .card .card-header .options span{
        cursor: pointer;
    }
    .card .card-header .options span.trash:hover{
        color:var(--bs-danger);
    }
    .card .card-header .options span.add:hover{
        color:var(--bs-success);
    }
    .card .card-body{
        padding:5px;
        overflow:hidden;
    }
    .card .card-header h3{
        font-size: 14px;
        margin: 0;
        line-height: 1.4;
    }
    .woo-commerce-style > ul::after{
        content: "";
        display: block;
        width: 100%;
        height: 9999em;
        position: absolute;
        bottom: -9999em;
        left: 0;
        background-color: #fafafa;
        border-right: 1px solid #eee;
    }
    .woo-commerce-style ul.woo-commerce-style{
        margin: 0;
        width: 20%;
        float: left;
        line-height: 1em;
        padding: 0 0 10px;
        position: relative;
        background-color: #fafafa;
        border-right: 1px solid #eee;
        box-sizing: border-box;
    }

    .woo-commerce-style ul.woo-commerce-style li{
        margin: 0;
        padding: 0;
        display: block;
        position: relative;
    }
    .woo-commerce-style ul.woo-commerce-style li a{
        margin: 0;
        padding: 10px;
        display: block;
        box-shadow: none;
        text-decoration: none;
        line-height: 20px!important;
        border-bottom: 1px solid #eee;
        color:#2271b1;
        display: flex;
    align-items: center;
    }
    .woo-commerce-style ul.woo-commerce-style li a:before{
        font-family:"dashicons";
        speak: never;
        font-weight: 400;
        font-variant: normal;
        text-transform: none;
        line-height: 1;
        -webkit-font-smoothing: antialiased;
        content: "\eacc";
        text-decoration: none;
    }
    .woo-commerce-style ul.woo-commerce-style li.general_options a:before{
        content: "\eacc";
    }
    .woo-commerce-style ul.woo-commerce-style li.short_description_options a:before{
        content: "\eb31";
    }
    .woo-commerce-style ul.woo-commerce-style li.description_options a:before{
        content: "\eb31";
    }
    .woo-commerce-style ul.woo-commerce-style li.attribute_options a:before{
        content: "\eaa6";
    }
    .woo-commerce-style ul.woo-commerce-style li.variation_options a:before{
        content: "\eab8";
    }
    .woo-commerce-style ul.woo-commerce-style  li.active a{
        color: #555;
        position: relative;
        background-color: #eee;
    }
    .woo-commerce-style ul.woo-commerce-style  li a span{
        margin-left: 0.618em;
        margin-right: 0.618em;
    }
    .woo-commerce-style .tab-contents{
        float: left;
        width: 80%;
        display:none;
        padding:10px 20px 30px 20px;
        background:#fff;
    }
    .woo-commerce-style .tab-contents.active{
        display:block;
    }
    .woo-commerce-style #variations-tab[data-status="1"] #btnGenerateVariations{
        display:block;
    }
    .woo-commerce-style #variations-tab[data-status="0"] #btnGenerateVariations,
    .woo-commerce-style #variations-tab[data-status="1"] .no-attributes-found{
        display:none;
    }
    .woo-commerce-style #variations-tab[data-status="0"] .no-attributes-found{
        display:flex;
    }
    .vehicle_images_container{
        padding: 0 0 0 9px;
        position: relative;
    }
    .vehicle_images_container.main_product{
        min-height:250px;
    }
    .vehicle_images_container ul.vehicle_images{
        display:none;
    }
    .vehicle_images_container ul.vehicle_images:has(li){
        display:block;
    }
    .vehicle_images_container .no-gallery-images{
        display:flex;
        width:100%;
        height:250px;
        min-height:250px;
    }
    .vehicle_images_container ul.vehicle_images:has(li)+ .no-gallery-images{
        display:none;
    }
    .vehicle_images_container ul{
        margin: 0;
        padding: 0;
    }
    .vehicle_images_container ul:before{
        content: " ";
        display: table;
    }
    .vehicle_images_container ul li.image{
        width: 80px;
        height:80px;
        float: left;
        cursor: move;
        border: 1px solid #d5d5d5;
        margin: 9px 9px 0 0;
        background: #f7f7f7;
        border-radius: 2px;
        position: relative;
        box-sizing: border-box;
        display:flex;
        align-items:center;
        background:#fff;
        overflow: hidden;
    }
    .vehicle_images_container ul li.image img{
        width: 100%;
        height: auto;
        display: block;
    }
    .vehicle_images_container ul .actions {
        position: absolute;
        top:0;
        left:0;
        width:100%;
        height:100%;
        display:none;
        align-items:center;
        justify-content:center;
        background:rgba(255,255,255,1)

    }
    .vehicle_images_container ul li:hover .actions {
        display:flex;
    }
    .vehicle_images_container ul li:hover .actions li{
        margin:0px 2px;
    }
    .vehicle_images_container ul li:hover .actions li a{
        background: #999;
        /* width: 40px; */
        /* height: 40px; */
        font-size: 11px;
        padding: 1px 3px 3px;
        border-radius: 3px;
        color: #fff;
    }
    .vehicle_images_container ul li:hover .actions li a:hover{
        background:#a00;
    }
    .form-group label {
        margin-bottom:2px;
    }
    .input-group .select2{
        width:75% !important;
        max-width:75% !important;
    }
    ..dropify-wrapper{
        height:auto !important;
    }
    .accordion-item,
    .accordion-item:first-child,
    .accordion-item:last-child
    {
        border-left:0px;
        border-right:0px;
        border-radius:0px;
    }
    .accordion-button{
        font-weight:700;
    }
    .accordion-button:not(.collapsed){
        color: var(--bs-accordion-btn-color);
        background-color: var(--bs-accordion-btn-bg);
    }

    .accordion-item .accordion-header .options{
        position: absolute;
        right: 60px;
        top: 0px;
        display: flex;
        height: 100%;
        align-items: center;
        color: #8392a5;
        margin: 0px;
        font-size: 17px;
    }
    .accordion-item .accordion-header .options span{
        cursor: pointer;
    }
    .accordion-item .accordion-header .options span.trash:hover{
        color:var(--bs-danger);
    }
    .accordion-button{
        box-shadow: inset 0 calc(-1 * var(--bs-accordion-border-width)) 0 var(--bs-accordion-border-color);
    }
    .accordion-button:focus{
        box-shadow: inset 0 calc(-1 * var(--bs-accordion-border-width)) 0 var(--bs-accordion-border-color);
    }
    /*
    #btnGenerateVariations span{
        display: none;
    }
    #btnGenerateVariations[data-status="0"] span.generate-variation {
        display: block;
    }

    #btnGenerateVariations[data-status="1"] span.regenerate-variation {
        display: block;
    }*/
    #btnGenerateVariations[data-status="0"]::after {
        content: "Generate Variations";
    }

    #btnGenerateVariations[data-status="1"]::after {
        content: "Regenerate Variations";
    }
    #variations-tab .no-attributes-found{
        min-height:250px;
        width:100%;
        display:flex;
        align-items:center;
        justify-content:center;
        text-align:center;
    }
    #attributes-tab .no-sub-category-found{
        min-height:250px;
        width:100%;
        display:flex;
        align-items:center;
        justify-content:center;
        text-align:center;
    }

    .woo-commerce-style #attributes-tab[data-status="1"] .product-attributes{
        display:flex;
    }
    .woo-commerce-style #attributes-tab[data-status="0"] .product-attributes,
    .woo-commerce-style #attributes-tab[data-status="1"] .no-sub-category-found{
        display:none;
    }
    .woo-commerce-style #attributes-tab[data-status="0"] .no-sub-category-found{
        display:flex;
    }
</style>
<div class="container-fluid d-block d-sm-none">
    <div class="row">
        <div class="col-12 text-center fw-700 fs-16 mt-30">
            Sorry! screen resolution not supported
        </div>
    </div>
</div>
<div class="container-fluid d-none d-sm-block">
	<div class="page-header">
		<div class="row">
			<div class="col-sm-12">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="{{ url('/') }}" data-original-title="" title=""><i class="f-16 fa fa-home"></i></a></li>
					<li class="breadcrumb-item">Vendor Master</li>
					<li class="breadcrumb-item"><a href="{{url('/')}}/admin/master/vendor/manage-vendors">{{$PageTitle}}</a></li>
                    <li class="breadcrumb-item">@if($isEdit==true)Update @else Create @endif</li>
				</ol>
			</div>
		</div>
	</div>
</div>

<div class="container-fluid d-none d-sm-block pl-20 pr-20 custom">
	<div class="row d-flex justify-content-center">
		<div class="col-12 col-sm-9 col-md-9 col-lg-9 mb-10">
            <div class="row">
                <div class="col-12 p-0">
                    <div class="form-group">
                        <label for="txtVendorName">Vendor Name  <span class="required"> * </span></label>
                        <input type="text" class="form-control form-control-lg" id="txtVendorName" placeholder="Vendor Name" value="<?php if($isEdit){ echo $data->VendorName;} ?>">
                        <div class="errors err-sm" id="txtVendorName-err"></div>
                    </div>
                </div>
            </div>
            <div class="row d-none d-md-flex mt-20">
                <div class="col-12 p-0">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-18 col-sm-8 col-md-6 col-lg-5 col-xl-4 d-flex align-items-center">
                                    <div class=" fw-700 text-nowrap pr-10">Vendor Data  </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0 woo-commerce-style">
                            <ul class="woo-commerce-style">
                                <li class="general_options active"><a href="#general-tab"><span>General</span></a></li>
                                <li class="address_options "><a href="#address-tab"><span>Address</span></a></li>
                                <li class="transport_details_options "><a href="#transport-tab"><span>Transport Details</span></a></li>
                                <li class="supply_details_options  " ><a href="#supply-details-tab"><span>Supply Details</span></a></li>
                                <li class="stock_points_options " ><a href="#stock-points-tab"><span>Stock Points</span></a></li>
                                <li class="documents_options " ><a href="#vendor-documents-tab"><span>Documents</span></a></li>
                                <li class="service_location_options " ><a href="#service-location-tab"><span>Service Locations</span></a></li>
                            </ul>
                            <div class="tab-contents" id="general-tab">
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >GST Number <span class="required"> * </span></div></div>
                                    <div class="col-6 col-lg-8">
                                        <input type="text" id="txtGSTNo" class="form-control" placeholder="GST Number" value="<?php if($isEdit){ echo $data->GSTNo;} ?>">
                                        <div class="errors err-sm" id="txtGSTNo-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Vendor Category  <span class="required"> * </span></div></div>
                                    <div class="col-6 col-lg-8">
                                        <select class="form-control {{$Theme['input-size']}} select2" id="lstCategory" data-selected="<?php if($isEdit){ echo $data->CID;} ?>">
                                            <option value="">Select a Category</option>
                                        </select>
                                        <div class="errors err-sm" id="lstCategory-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Vendor Type  <span class="required"> * </span></div></div>
                                    <div class="col-6 col-lg-8">
                                        <select class="form-control {{$Theme['input-size']}} select2" id="lstVendorType"  data-selected="<?php if($isEdit){ echo $data->VendorTypeID;} ?>">
                                            <option value="">Select a Vendor Type</option>
                                        </select>
                                        <div class="errors err-sm" id="lstVendorType-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >E-Mail</div></div>
                                    <div class="col-6 col-lg-8">
                                        <input type="email" id="txtEmail" class="form-control" placeholder="Email" value="<?php if($isEdit){ echo $data->Email;} ?>">
                                        <div class="errors err-sm" id="txtEmail-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Mobile Number 1 <span class="required"> * </span></div></div>
                                    <div class="col-6 col-lg-8">
                                        <input type="text" id="txtMobileNumber1" class="form-control" placeholder="Mobile Number" value="<?php if($isEdit){ echo $data->MobileNumber1;} ?>">
                                        <div class="errors err-sm" id="txtMobileNumber1-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Mobile Number 2</div></div>
                                    <div class="col-6 col-lg-8">
                                        <input type="text" id="txtMobileNumber2" class="form-control" placeholder="Mobile Number" value="<?php if($isEdit){ echo $data->MobileNumber2;} ?>">
                                        <div class="errors err-sm" id="txtMobileNumber2-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Credit Days <span class="required"> * </span></div></div>
                                    <div class="col-6 col-lg-8">
                                        <div class="input-group">
                                            <input type="number" id="txtCreditDays" class="form-control" placeholder="Credit Days" value="<?php if($isEdit){ echo $data->CreditDays;} ?>">
                                            <span class="input-group-text">Days</span>
                                        </div>

                                        <div class="errors err-sm" id="txtCreditDays-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Credit Limit <span class="required"> * </span></div></div>
                                    <div class="col-6 col-lg-8">
                                        <input type="number" id="txtCreditLimit" class="form-control" placeholder="Credit Limit" value="<?php if($isEdit){ echo $data->CreditLimit;} ?>">
                                        <div class="errors err-sm" id="txtCreditLimit-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Commission <span class="required"> * </span></div></div>
                                    <div class="col-6 col-lg-8">
                                        <div class="input-group">
                                            <input type="number" min=0 max=100 step="{{NumberSteps($Settings['PERCENTAGE-DECIMALS'])}}" id="txtCommissionPercentage" class="form-control" placeholder="Commission Percentage" value="<?php if($isEdit){ echo $data->CommissionPercentage;} ?>">
                                            <span class="input-group-text"> % </span>
                                        </div>

                                        <div class="errors err-sm" id="txtCommissionPercentage-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Status</div></div>
                                    <div class="col-6 col-lg-8">
                                        <select class="form-control" id="lstActiveStatus">
                                            <option value="Active"   @if($isEdit) @if($data->ActiveStatus=="Active") selected @endif @endif>Active</option>
                                            <option value="Inactive"   @if($isEdit) @if($data->ActiveStatus=="Inactive") selected @endif @endif>Inactive</option>
                                        </select>
                                        <div class="errors err-sm" id="lstActiveStatus-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center">
                                    </div>
                                </div>
                            </div>
                            <div class="tab-contents" id="address-tab">
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Addres</div></div>
                                    <div class="col-6 col-lg-8">
                                        <textarea  id="txtAddress"  rows="3" class="form-control"><?php if($isEdit){ echo $data->Address;} ?></textarea>
                                        <div class="errors err-sm" id="txtAddress-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Postal Code</div></div>
                                    <div class="col-6 col-lg-8">
                                        <div class="input-group">
                                            <input type="text" id="txtPostalCode" class="form-control" placeholder="Postal Code" value="<?php if($isEdit){ echo $data->PostalCode;} ?>">
                                            <button type="button" class="btn btn-outline-dark" id="btnGSearchPostalCode">Search <i class="fa fa-search"></i></button>
                                        </div>
                                        <div class="errors err-sm" id="txtPostalCode-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >City</div></div>
                                    <div class="col-6 col-lg-8">
                                        <select class="form-control {{$Theme['input-size']}} select2" id="lstCity" data-selected="<?php if($isEdit){ echo $data->CityID;} ?>">
                                            <option value="">Select a City</option>
                                        </select>
                                        <div class="errors err-sm" id="lstCity-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Taluks</div></div>
                                    <div class="col-6 col-lg-8">
                                        <select class="form-control {{$Theme['input-size']}} select2" id="lstTaluk" data-selected="<?php if($isEdit){ echo $data->TalukID;} ?>">
                                            <option value="">Select a Taluk</option>
                                        </select>
                                        <div class="errors err-sm" id="lstTaluk-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Districts</div></div>
                                    <div class="col-6 col-lg-8">
                                        <select class="form-control {{$Theme['input-size']}} select2" id="lstDistricts" data-selected="<?php if($isEdit){ echo $data->DistrictID;} ?>">
                                            <option value="">Select a District</option>
                                        </select>
                                        <div class="errors err-sm" id="lstDistricts-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >States</div></div>
                                    <div class="col-6 col-lg-8">
                                        <select class="form-control {{$Theme['input-size']}} select2" id="lstState"  data-selected="<?php if($isEdit){ echo $data->StateID;} ?>">
                                            <option value="">Select a State</option>
                                        </select>
                                        <div class="errors err-sm" id="lstState-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Country</div></div>
                                    <div class="col-6 col-lg-8">
                                        <select class="form-control {{$Theme['input-size']}} select2" id="lstCountry" data-category-id="lstCategory" data-selected="<?php if($isEdit){ echo $data->CountryID;} ?>">
                                            <option value="">Select a Country</option>
                                        </select>
                                        <div class="errors err-sm" id="lstCountry-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                            </div>
                            <div class="tab-contents" id="transport-tab">
                                <div class="row justify-content-center mt-20 ">
                                    <div class="col-12 text-center">
                                        <button id="btnAddVehicle" class="btn btn-sm btn-outline-primary">Add Vehicle</button>
                                    </div>
                                </div>
                                <div class="col-12 mh-150">
                                    <div class="accordion accordion-flush" id="vehicleAccordion">
                                    </div>
                                </div>
                            </div>
                            <div class="tab-contents" id="supply-details-tab">
                                <div class="row mt-20 justify-content-center">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="lstPCategory">Product Category <span class="required"> * </span></label>
                                            <select class="form-control  {{$Theme['input-size']}} select2" id="lstPCategory">
                                                <option value="">Select a Product Category</option>
                                            </select>
                                            <div class="errors SupplyDetails err-sm" id="lstPCategory-err"></div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="lstPSubCategory"> Product Sub Category <span class="required"> * </span></label>
                                            <select size=1 class="form-control  {{$Theme['input-size']}} select2" id="lstPSubCategory" multiple>
                                                <option value="">Select a Product Sub Category</option>
                                            </select>
                                            <div class="errors SupplyDetails err-sm" id="lstPSubCategory-err"></div>
                                        </div>
                                    </div>
                                    <div class="col-sm-2 d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-sm btn-outline-info btnAddSupply mt-20" data-id="">Add</button>
                                    </div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-sm-12">
                                        <table class="table" id="tblSupplyDetails">
                                            <thead>
                                                <tr>
                                                    <th>Product Category</th>
                                                    <th>Product Sub Category</th>
                                                    <th>Action</th>
                                                    <th class="d-none">Data</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-contents" id="stock-points-tab">
                                <div class="row justify-content-center">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="txtSPName">Point Name</label>
                                            <input type="text" class="form-control" id="txtSPName">
                                            <div class="errors stock-points err-sm" id="txtSPName-err"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-15">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="txtSPAddress">Address</label>
                                            <input type="text" class="form-control" id="txtSPAddress">
                                            <div class="errors stock-points err-sm" id="txtSPAddress-err"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="txtSPPostalCode">Postal Code</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="txtSPPostalCode">
                                            <button type="button" class="btn btn-outline-dark" id="btnSPPostalCode"> <i class="fa fa-search"></i></button>
                                            </div>
                                            <div class="errors stock-points err-sm" id="txtSPPostalCode-err"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="lstSPCity">City</label>
                                            <select id="lstSPCity" class="form-control select2">
                                                <option value="">select a city</option>
                                            </select>
                                            <div class="errors stock-points err-sm" id="lstSPCity-err"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 pt-27 text-center d-flex ">
                                        <button id="btnCancelStockPoint" class="btn btn-sm btn-outline-dark mr-10" style="display:none" >Cancel</button>
                                        <button id="btnAddStockPoint" class="btn btn-sm btn-outline-primary">Add</button>
                                    </div>
                                </div>
                                <div class="row mt-15">
                                    <div class="col-12">
                                        <table class="table" id="tblStockPoint">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">SNo</th>
                                                    <th class="text-center">Name</th>
                                                    <th class="text-center">Address</th>
                                                    <th class="text-center">City</th>
                                                    <th class="text-center">Postal Code</th>
                                                    <th class="text-center">action</th>
                                                    <th class="text-center d-none">data</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-contents" id="vendor-documents-tab">
                                <div class="row mt-15 mb-15" id="divDocuments">
                                </div>
                                <div class="row justify-content-center mt-20 ">
                                    <div class="col-12 text-center">
                                        <button id="btnAddDocuments" class="btn btn-sm btn-outline-primary">Add Documents</button>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-contents" id="service-location-tab">
                                <div class="row justify-content-center mt-20">
                                    <div class="col-12 col-sm-8 col-md-7 col-lg-4">
                                        <select  class="form-control select2" id="lstSLCountry" data-selected="">
                                            <option value="">Select a Country</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-sm-8 col-md-7 col-lg-4">
                                        <select  class="form-control select2" id="lstSLState" data-selected="">
                                            <option value="">Select a State</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-10 mb-20">
                                    <div class="col-12">
                                        <div class="accordion" id="ServiceLocationAccordin">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-right">
                    <a href="{{url('/')}}/admin/master/vendor/manage-vendors" class="btn btn-outline-dark mr-10">Cancel</a>
                    <button type="button" class="btn btn-outline-success mr-10" id="btnSave">@if($isEdit)Update @else Save @endif</button>
                </div>
            </div>
		</div>
		<div class="col-12 col-sm-3 col-md-3 col-lg-3 pt-22">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header"> <h3>Vendor Logo</h3> </div>
                        <div class="card-body vendor-logo ">
                            <div class="row">
                                <div class="col-12">
                                    <input type="file" class="dropify"  data-remove="0" data-is-cover-image="1" id="txtVendorLogo" data-default-file="<?php if($isEdit){ echo url('/')."/".$data->Logo;} ?>"   data-allowed-file-extensions="<?php echo implode(" ",$FileTypes['category']['Images']) ?>" >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		</div>
	</div>
</div>
<div class="modal fade" id="SupplyPointsModal" tabindex="-1"  aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Supply Point</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="">Name</label>
                            <input type="text" class="form-control" id="txtMSPName">
                            <div class="errors err-sm"></div>
                        </div>
                    </div>
                    <div class="col-12 mt-10">
                        <div class="form-group">
                            <label for="">Address</label>
                            <textarea row="2" class="form-control" id="txtMSPAddress"></textarea>
                            <div class="errors err-sm"></div>
                        </div>
                    </div>
                    <div class="col-12 mt-10">
                        <div class="form-group">
                            <label for="txtMSPPostalCode">Postal Code <span class="required">*</span> <span class="addOption" id="btnReloadPostalCode"  title="Reload Postal Codes" ><i class="fa fa-refresh"></i></span>  <span class="addOption " id="btnAddPostalCode"  title="Add Postal Codes" ><i class="fa fa-plus"></i></span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="txtMSPPostalCode">
                                <span class="input-group-text"><i class="fa fa-search"></i></span>
                            </div>
                            <div class="errors err-sm"></div>
                        </div>
                    </div>
                    <div class="col-12 mt-10">
                        <div class="form-group">
                            <label for="">City</label>
                            <select class="form-control {{$Theme['input-size']}} mselect2" id="lstSubCategory" data-category-id="lstCategory" data-selected="">
                                <option value="">Select a City</option>
                            </select>
                            <div class="errors err-sm"></div>
                        </div>
                    </div>
                </div>
            </div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary">Save</button>
			</div>
		</div>
	</div>
</div>
<input type="file" data-uuid="" id="txtVehicleImages" class="d-none" multiple accept="<?php if(count($FileTypes['category']['Images'])>0) {echo ".".implode(",.",$FileTypes['category']['Images']);} ?>">
<input type="file" data-uuid="" id="txtDocuments" class="d-none" multiple accept="<?php if(count($FileTypes['category']['Documents'])>0) {echo ".".implode(",.",$FileTypes['category']['Documents']);} ?>">
@endsection
@section('scripts')
<script src="{{url('/')}}/assets/js/form-wizard/form-wizard-five.js"></script>
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.js"></script>
<!-- Image Crop Script Start -->

<script>
    var $image=null;
    $(document).ready(function() {
        var uploadedImageURL;
        var URL = window.URL || window.webkitURL;
        var $dataRotate = $('#dataRotate');
        var $dataScaleX = $('#dataScaleX');
        var $dataScaleY = $('#dataScaleY');
        var options = {
            aspectRatio: 16/9,
            preview: '.img-preview'
        };
        $image = $('#ImageCrop').cropper(options);
        $('#ImgCrop').modal({backdrop: 'static',keyboard: false});
        $('#ImgCrop').modal('hide');
        $(document).on('change', '.imageScrop', function() {
            let id = $(this).attr('id');
            let pType=$(this).attr('data-product-type')
            let uuid=$(this).attr('data-uuid')
            $('#'+id).attr('data-remove',0);
            if($('#'+id).attr('data-aspect-ratio')!=undefined){
                options.aspectRatio=$('#'+id).attr('data-aspect-ratio')
            }
            $image.attr('data-id', id);
            $image.attr('data-is-url', 0);
            $image.attr('data-product-type', pType);
            $image.attr('data-uuid', uuid);
            $('#ImgCrop').modal('show');
            var files = this.files;
            if (files && files.length) {
                file = files[0];
                if (/^image\/\w+$/.test(file.type)) {
                    uploadedImageName = file.name;
                    uploadedImageType = file.type;
                    if (uploadedImageURL) {
                        URL.revokeObjectURL(uploadedImageURL);
                    }
                    uploadedImageURL = URL.createObjectURL(file);
                    $image.cropper('destroy').attr('src', uploadedImageURL).cropper(options);
                } else {
                    window.alert('Please choose an image file.');
                }
            }
        });
        $(document).on('click','.actions a.crop',function(e){
            e.preventDefault();
            let aid=$(this).attr('data-attachment_id')
            let pType=$(this).attr('data-product-type')
            let uuid=$(this).attr('data-uuid')
            if($(this).attr('data-aspect-ratio')!=undefined){
                options.aspectRatio=$(this).attr('data-aspect-ratio')
            }
            let url=$(this).attr('href');
            if(url!="#"){
                $image.attr('data-is-url', 1);
                $image.attr('data-is-url', 1);
                $image.attr('data-attachment_id', aid);
                $image.attr('data-product-type', pType);
                $image.attr('data-uuid', uuid);
                $('#ImgCrop').modal('show');
                var request = new XMLHttpRequest();
                request.open('GET', url, true);
                request.responseType = 'blob';
                request.onload = function() {
                    var reader = new FileReader();
                    reader.onload =  function(e){
                        $image.cropper('destroy').attr('src', reader.result).cropper(options);
                    };
                    reader.readAsDataURL(request.response);
                };
                request.send();
            }
        });
        $('.docs-buttons').on('click', '[data-method]', function() {
            var $this = $(this);
            var data = $this.data();
            var cropper = $image.data('cropper');
            var cropped;
            var $target;
            var result;
            if (cropper && data.method) {
                data = $.extend({}, data);
                if (typeof data.target !== 'undefined') {
                    $target = $(data.target);
                    if (typeof data.option === 'undefined') {
                        try {
                            data.option = JSON.parse($target.val());
                        } catch (e) {
                            console.log(e.message);
                        }
                    }
                }
                cropped = cropper.cropped;
                switch (data.method) {
                    case 'rotate':
                        if (cropped && options.viewMode > 0) {
                            $image.cropper('clear');
                        }
                        break;
                    case 'getCroppedCanvas':
                        if (uploadedImageType === 'image/jpeg') {
                            if (!data.option) {
                                data.option = {};
                            }
                            data.option.fillColor = '#fff';
                        }
                        break;
                }
                result = $image.cropper(data.method, data.option, data.secondOption);
                switch (data.method) {
                    case 'rotate':
                        if (cropped && options.viewMode > 0) {
                            $image.cropper('crop');
                        }
                        break;
                    case 'scaleX':
                    case 'scaleY':
                        $(this).data('option', -data.option);
                        break;
                    case 'getCroppedCanvas':
                        if (result) {
                            $('#getCroppedCanvasModal').modal().find('.modal-body').html(result);
                            if (!$download.hasClass('disabled')) {
                                download.download = uploadedImageName;
                                $download.attr('href', result.toDataURL(uploadedImageType));
                            }
                        }
                        break;
                }
            }
        });
        $('#inputImage').change(function() {
            var files = this.files;
            var file;
            if (!$image.data('cropper')) {
                return;
            }
            if (files && files.length) {
                file = files[0];
                if (/^image\/\w+$/.test(file.type)) {
                    uploadedImageName = file.name;
                    uploadedImageType = file.type;
                    if (uploadedImageURL) {
                        URL.revokeObjectURL(uploadedImageURL);
                    }
                    uploadedImageURL = URL.createObjectURL(file);
                    $image.cropper('destroy').attr('src', uploadedImageURL).cropper(options);
                    $('#inputImage').val('');
                } else {
                    window.alert('Please choose an image file.');
                }
            }
        });
        $(document).on('click', '#btnUploadImage', function() {
            $('#inputImage').trigger('click')
        });
        $("#btnCropApply").on('click', function() {

        });
        $(document).on('click','#btnCropModelClose',function(){
            let  id = $image.attr('data-id');
            let isUrl=$image.attr('data-is-url');
            let aid=$image.attr('data-attachment_id');
            if(isUrl=="0"){
                $('#' + id).val("");
                $('#' + id).attr('src', "");
                $('#' + id).parent().find('img').attr('src', "");
                $('#' + id).parent().find('.dropify-clear').trigger('click');
            }
            $('#ImgCrop').modal('hide');
        });
    });
</script>
<!-- Form Wizard Script Start-->
<!-- Form Wizard Script End -->
<!-- Image Crop Script End -->
<script>
    $(document).ready(function(){
        let Images={
            vehicle:{},
            documents:{}
        };
        let vehiclesCount=1;
        let stockPointCount=1;
        let vehiclesData={};
        var ProductDetails=[];
        let tblStockPoint=null;
        let deletedImages={
            documents:[],
            vehicle:[],

        };
        let TotalImagesCount=0;
        const init=async()=>{
            showTabs();
            //InitStockPointTable();
            getVendorCategory();
            getVendorType();
            getCountry({},'lstCountry');
            getPCategory();
            getVendor();
            setTimeout(() => {
                $('#btnGSearchPostalCode').trigger('click')
            }, 100);
        }
        const showTabs=async()=>{
            $('.woo-commerce-style .tab-contents').hide('slow');
            let activeTabs=$('.woo-commerce-style ul.woo-commerce-style li.active a').attr('href');
            $('.woo-commerce-style '+activeTabs).show('slow')
            if(activeTabs=="#attributes-tab"){

                let cid=$('#lstCategory').val();
                let scid=$('#lstSubCategory').val();
                if(cid!="" && scid!=""){
                    $('.woo-commerce-style '+activeTabs).attr('data-status',1)
                }else{
                    $('.woo-commerce-style '+activeTabs).attr('data-status',0)
                }
            }else if(activeTabs=="#variations-tab"){
                $('.woo-commerce-style '+activeTabs).attr('data-status',0)
                if($('#variationAccordion .accordion-item').length>0){
                    $('.woo-commerce-style '+activeTabs).attr('data-status',1)
                }else{
                    let variationList=await getVariationList();
                    if(Object.keys(variationList).length>0){
                        $('.woo-commerce-style '+activeTabs).attr('data-status',1)
                    }
                }
            }else if(activeTabs=="#service-location-tab"){
                let Country = $("#lstCountry").val();
                let SLCountry = $("#lstSLCountry").val();
                let State = $("#lstState").val();
                let SLState = $("#lstSLState").val();
                if(Country && Country != SLCountry){
                    $("#lstSLCountry").attr("data-selected",Country);
                }
                if(State && State != SLState){
                    $("#lstSLState").attr("data-selected",State);
                }
                getCountry({},"lstSLCountry");
            }
        }
        const generateUUID=()=> {
            var d = new Date().getTime();
            var uuid = 'Ixxxxxxxx-xxxxxx-xx'.replace(/[xy]/g, function(c) {
                var r = (d + Math.random()*16)%16 | 0;
                d = Math.floor(d/16);
                return (c=='x' ? r : (r&0x3|0x8)).toString(16);
            });
            return uuid;
        }
        const generateVariationID=()=> {
            var d = new Date().getTime();
            var uuid = 'Ixxxxxxxxxx-xx'.replace(/[xy]/g, function(c) {
                var r = (d + Math.random()*16)%16 | 0;
                d = Math.floor(d/16);
                return (c=='x' ? r : (r&0x3|0x8)).toString(16);
            });
            return uuid;
        }
        const tmpImageUpload=async(formData)=>{
            $.ajax({
                type: "post",
                url: "{{url('/')}}/tmp/upload-image?random="+ Math.floor(1000 + Math.random() * 9000),
                headers: {'X-CSRF-Token': $('meta[name=_token]').attr('content')},
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                dataType:"json",
                async:true,
                error: function(e, x, settings, exception) {ajaxErrors(e, x, settings, exception);},
                success:function(response){
                    response.referData=JSON.parse(response.referData);
                    response.referData.isTemp=1;
                    let referData=response.referData;

                    if(referData.pType=="logo"){
                        if($('#' + response.referData.id).length>0){
                            try {
                                if(Images.productImage.data.referData.isTemp==0){
                                    TotalImagesCount++;
                                }
                            } catch (error) {
                                TotalImagesCount++;
                            }
                            $('#' + response.referData.id).attr('data-remove',0);
                            $('#' + response.referData.id).parent().find('img').attr('src', "{{url('/')}}/"+response.uploadPath)
                            Images.productImage.data=response;
                            Images.productImage.isDeleted=0;
                        }
                    }else if(referData.pType=="vehicle-images"){
                        if(Images.vehicle[referData.uuid]==undefined){
                            Images.vehicle[referData.uuid]={};
                        }
                        Images.vehicle[referData.uuid][referData.imgID]=response;
                        AddVehicleImages(referData.uuid,referData.imgID,response);

                    }else if(referData.pType=="vendor-documents"){
                        Images.documents[referData.imgID]=response;
                        AddVendorDocuments(referData.imgID,response);

                    }
                }
            });
        }
        const loadData=async(data)=>{
            let VehicleTypes=await getVehicleType();
            if(data.length>0){
                data=data[0];
                for(let item of data.Vehicles){
                    await AddVehicle(item.UUID,VehicleTypes);
                }
                for(let item of data.StockPoints){
                    let FormData = {
                        UUID :item.UUID,
                        PostalCode :item.PostalCode,
                        Name : item.PointName,
                        Address :item.Address,
                        CityID : item.CityID,
                        PostalID : item.PostalID,
                        CityName : item.CityName,
                        TalukID : item.TalukID,
                        DistrictID : item.DistrictID,
                        StateID : item.StateID,
                        CountryID : item.CountryID,
                    }
                    let UUID = generateUUID();
                    let index =  ($("#tblStockPoint tbody tr").length) + 1;
                    let html = `<tr data-uuid="${UUID}">`;
                    html += `<td>${index}</td>`;
                    html += `<td>${FormData.Name} </td>`;
                    html += `<td>${FormData.Address} </td>`;
                    html += `<td>${FormData.CityName} </td>`;
                    html += `<td>${FormData.PostalCode} </td>`;
                    html += `<td class="text-center align-middle"><button type="button" class="btn btn-outline-success btnEditStockPoint"><i class="fa fa-pencil"></i></button> <button type="button" class="btn btn-outline-danger btnDeleteStockPoint"><i class="fa fa-trash"></i></button></td>`;
                    html += `<td class="d-none tdata">${JSON.stringify(FormData)}</td>`;
                    html += "</tr>";
                    $('#tblStockPoint tbody').append(html);
                }
                for(let item of data.SupplyDetails){
                    let data={
                        PCID:item.PCID,
                        PCName:item.PCName,
                        PSCID:item.PSCID,
                        PSCName:item.PSCName
                    }
                    let html = '<tr >';
                        html += '<td>' + data.PCName + '</td>';
                        html += '<td data-id="' + data.PSCID + '">' + data.PSCName + '</td>';
                        html += '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btnDeleteSupply"><i class="fa fa-trash"></i></button></td>';
                        html += '<td class="d-none tdata">' + JSON.stringify({ PCID: data.PCID, PSCID: data.PSCID }) + '</td>';
                        html += '</tr>';
                        $('#tblSupplyDetails tbody').append(html);
                }
                for(let item of data.Vehicles){
                    let uuid=item.UUID;
                    $('#txtVehicleNumber-'+uuid).val(item.VNumber);
                    $('#lstVehicleType-'+uuid).attr('data-selected',item.VTypeID);
                    $('#lstVehicleBrand-'+uuid).attr('data-selected',item.VBrandID);
                    $('#lstVehicleModel-'+uuid).attr('data-selected',item.VModelID);
                    $('#txtVehicleLength-'+uuid).val(item.VLength);
                    $('#txtVehicleDepth-'+uuid).val(item.VDepth);
                    $('#txtVehicleWidth-'+uuid).val(item.VWidth);
                    $('#txtVehicleCapacity-'+uuid).val(item.VCapacity);
                    for(let image of item.Images){
                        item.gImage=image.gImage.replace("{{url('/')}}/","");
                        let tdata={
                            uploadPath:image.gImage,
                            fileName:image.fileName,
                            ext:image.ext,
                            referData:{isTemp:0,imgID:image.ImgID,pType:"vehicle-images"}
                        }
                        if(Images.vehicle[uuid]==undefined){
                            Images.vehicle[uuid]={};
                        }
                        Images.vehicle[uuid][image.ImgID]=tdata;
                        AddVehicleImages(uuid,image.ImgID,tdata);
                    }
                    setTimeout(() => {
                        $('#lstVehicleType-'+uuid).val(item.VTypeID).trigger('change');

                    }, 10);
                }
                for(let item of data.Documents){
                    item.documents=item.documents.replace("{{url('/')}}/","");
                    let tdata={
                        uploadPath:item.documents,
                        fileName:item.fileName,
                        ext:item.ext,
                        referData:{isTemp:0,imgID:item.ImgID,pType:"vendor-documents"}
                    }
                    Images.documents[item.ImgID]=tdata;
                    AddVendorDocuments(item.ImgID,tdata);
                }
                for(let item of data.ServiceLocations){
                    let ExistingStates = [];
                    $('#ServiceLocationAccordin .accordion-item').each(function(){
                        ExistingStates.push($(this).attr('data-state-id'));
                    });
                    if(ExistingStates.indexOf(item.StateID)== -1){
                        AddServiceLocations(item.CountryID,item.StateID,item.StateName,item.DistrictIDs);
                    }
                }
                setTimeout(() => {
                    $('.txtVehicleNumber').trigger('keyup');
                }, 10);
            }
        }
        const getVendor=async()=>{
            $.ajax({
                type:"post",
                url:"{{url('/')}}/admin/master/vendor/manage-vendors/get/vendor",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                data:{VendorID:"<?php if($isEdit){ echo $VendorID;} ?>"},
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    loadData(response);
                }
            });
        }
        const AddVehicle=async(tmpUUID=null,tmpVehicleTypes=null)=>{
            let uuid=tmpUUID!=null?tmpUUID:generateUUID();
            let VehicleTypes=tmpVehicleTypes!=null?tmpVehicleTypes:await getVehicleType();
            let html='';
                html+='<div class="accordion-item" data-uuid="'+uuid+'">';
                    html+='<h5 class="accordion-header" id="'+uuid+'-heading">';
                        html+='<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#'+uuid+'" aria-expanded="false" aria-controls="'+uuid+'"> <span class="vehicle-name">Vehicle '+vehiclesCount+'</span><span class="options"> <span class="vehicle-trash trash" data-uuid="'+uuid+'"><i class="fa fa-trash"></i></span></span> </button>';
                    html+='</h5>';
                    html+='<div id="'+uuid+'" class="accordion-collapse collapse" aria-labelledby="'+uuid+'-heading" data-bs-parent="#vehicleAccordion">';
                        html+='<div class="accordion-body">';
                            html+='<div class="row mt-10">';
                                html+='<div class="col-sm-3 mt-20">';
                                    html+='<div class="form-group">';
                                        html+='<label for="txtVehicleNumber-'+uuid+'"> Vehicle Number </label>';
                                        html+='<input type="text" class="form-control   txtVehicleNumber" data-uuid="'+uuid+'" id="txtVehicleNumber-'+uuid+'" value="">';
                                        html+='<div class="errors err-sm" id="txtVehicleNumber-'+uuid+'-err"></div>';
                                    html+='</div>';
                                html+='</div>';
                                html+='<div class="col-sm-3 mt-20">';
                                    html+='<div class="form-group">';
                                        html+='<label for="lstVehicleType-'+uuid+'"> Vehicle Type <span class="required"> * </span> <span  class="addOption btnReloadVehicleType"  title="Reload Vehicle Type" ><i class="fa fa-refresh"></i></span>   <span class="addOption btnAddVehicleType"  title="Add New Vehicle Type" ><i class="fa fa-plus"></i></span> </label>';
                                        html+='<select class="form-control   dselect2 lstVehicleType" data-uuid="'+uuid+'" id="lstVehicleType-'+uuid+'" data-selected="">';
                                            html+='<option value="">Select a Vehicle Type</option>';
                                            for(let item of VehicleTypes){
                                                html+='<option value="'+item.VehicleTypeID+'">'+item.VehicleType+'</option>';
                                            }
                                        html+='</select>';
                                        html+='<div class="errors err-sm" id="lstVehicleType-'+uuid+'-err"></div>';
                                    html+='</div>';
                                html+='</div>';
                                html+='<div class="col-sm-3 mt-20">';
                                    html+='<div class="form-group">';
                                        html+='<label for="lstVehicleBrand-'+uuid+'"> Vehicle Brand <span class="required"> * </span> <span  class="addOption btnReloadVehicleBrand"   title="Reload Vehicle Brand" ><i class="fa fa-refresh"></i></span>   <span class="addOption btnAddVehicleBrand"  title="Add New Vehicle Brand" ><i class="fa fa-plus"></i></span> </label>';
                                        html+='<select class="form-control   dselect2 lstVehicleBrand" data-uuid="'+uuid+'" id="lstVehicleBrand-'+uuid+'" data-vehicle-type-id="lstVehicleType-'+uuid+'" data-selected="">';
                                            html+='<option value="">Select a Vehicle Brand</option>';
                                        html+='</select>';
                                        html+='<div class="errors err-sm" id="lstVehicleBrand-'+uuid+'-err"></div>';
                                    html+='</div>';
                                html+='</div>';
                                html+='<div class="col-sm-3 mt-20">';
                                    html+='<div class="form-group">';
                                        html+='<label for="lstVehicleModel-'+uuid+'"> Vehicle Model <span class="required"> * </span> <span  class="addOption btnReloadVehicleModel" id="btnReloadVehicleModel-'+uuid+'"  title="Reload Vehicle Model" ><i class="fa fa-refresh"></i></span>   <span class="addOption btnAddVehicleModel" id="btnAddVehicleModel-'+uuid+'" title="Add New Vehicle Model" ><i class="fa fa-plus"></i></span> </label>';
                                        html+='<select class="form-control   dselect2 lstVehicleModel" data-uuid="'+uuid+'" id="lstVehicleModel-'+uuid+'" data-vehicle-type-id="lstVehicleType-'+uuid+'" data-vehicle-brand-id="lstVehicleBrand-'+uuid+'" data-selected="">';
                                            html+='<option value="">Select a Vehicle Model</option>';
                                        html+='</select>';
                                        html+='<div class="errors err-sm" id="lstVehicleModel-'+uuid+'-err"></div>';
                                    html+='</div>';
                                html+='</div>';
                                html+='<div class="col-sm-3 mt-20">';
                                    html+='<div class="form-group">';
                                        html+='<label class="txtVehicleLength-'+uuid+'"> Vehicle Length <span class="required"> * </span></label>';
                                        html+='<div class="input-group">';
                                            html+='<input type="number" class="form-control  " data-uuid="'+uuid+'" id="txtVehicleLength-'+uuid+'" value="">';
                                            html+='<span class="input-group-text">in meters</span>';
                                        html+='</div>';
                                        html+='<div class="errors err-sm" id="txtVehicleLength-'+uuid+'-err"></div>';
                                    html+='</div>';
                                html+='</div>';
                                html+='<div class="col-sm-3 mt-20">';
                                    html+='<div class="form-group">';
                                        html+='<label class="txtVehicleDepth-'+uuid+'"> Vehicle Depth <span class="required"> * </span></label>';
                                        html+='<div class="input-group">';
                                            html+='<input type="number" class="form-control  " data-uuid="'+uuid+'" id="txtVehicleDepth-'+uuid+'" value="">';
                                            html+='<span class="input-group-text">in meters</span>';
                                        html+='</div>';
                                        html+='<div class="errors err-sm" id="txtVehicleDepth-'+uuid+'-err"></div>';
                                    html+='</div>';
                                html+='</div>';
                                html+='<div class="col-sm-3 mt-20">';
                                    html+='<div class="form-group">';
                                        html+='<label class="txtVehicleWidth-'+uuid+'"> Vehicle Width <span class="required"> * </span></label>';
                                        html+='<div class="input-group">';
                                            html+='<input type="number" class="form-control  " data-uuid="'+uuid+'" id="txtVehicleWidth-'+uuid+'" value="">';
                                            html+='<span class="input-group-text">in meters</span>';
                                        html+='</div>';
                                        html+='<div class="errors err-sm" id="txtVehicleWidth-'+uuid+'-err"></div>';
                                    html+='</div>';
                                html+='</div>';
                                html+='<div class="col-sm-3 mt-20">';
                                    html+='<div class="form-group">';
                                        html+='<label for="txtVehicleCapacity-'+uuid+'"> Vehicle Capacity <span class="required"> * </span> </label>';
                                        html+='<div class="input-group">';
                                            html+='<input type="number" class="form-control  " data-uuid="'+uuid+'" id="txtVehicleCapacity-'+uuid+'" value="">';
                                            html+='<span class="input-group-text">in Kg</span>';
                                        html+='</div>';
                                        html+='<div class="errors err-sm" id="txtVehicleCapacity-'+uuid+'-err"></div>';
                                    html+='</div>';
                                html+='</div>';
                            html+='</div>';
                            html+='<div class="row mt-20">';
                                html+='<div class="col-12 text-center fw-600"> Vehicle Images</div>';
                                html+='<div class="col-12 mt-10">';
                                    html+='<div class="vehicle_images_container">';
                                        html+='<ul class="vehicle_images"  data-uuid="'+uuid+'" id="VImages-'+uuid+'">';
                                        html+='</ul>';
                                    html+='</div>';
                                html+='</div>';
                                html+='<div class="col-12 mt-10 text-center"><button class="btn btn-sm btn-outline-primary addVehicleImages"  data-uuid="'+uuid+'">Add Vehicle Images</button></div>';
                            html+='</div>';
                        html+='</div>';
                    html+='</div>';
                html+='</div>';
            $('#vehicleAccordion').append(html);
                setTimeout(() => {
                    $('#txtVImage-'+uuid).dropify();
                    $('.dselect2[data-uuid="'+uuid+'"]').select2();
                }, 10);
            vehiclesCount++;
        }
        const AddVehicleImages=async(uuid,imgID,tdata)=>{
            let html='';
            if($('ul#VImages-'+uuid+' li[data-attachment_id="'+imgID+'"]').length>0){
                    html+='<img src="{{url("/")}}/'+tdata.uploadPath+'">';
                    html+='<div class="actions">';
                        html+='<ul class="actions">';
                            html+='<li><a href="{{url("/")}}/'+tdata.uploadPath+'" data-uuid="'+uuid+'" data-attachment_id="'+imgID+'" data-lightbox="Product Gallery" class="view" title="view image"><i class="fa fa-eye" aria-hidden="true"></i></a></li>';
                            html+='<li><a href="#" class="delete" data-uuid="'+uuid+'" data-product-type="vehicle-images" data-attachment_id="'+imgID+'" title="Delete image"><i class="fa fa-trash" aria-hidden="true"></i></a></li>';
                        html+='</ul>';
                    html+='</div>';
                $('ul#VGalleries-'+uuid+' li[data-attachment_id="'+imgID+'"]').html(html);
            }else{
                html+='<li class="image" data-attachment_id="'+imgID+'" style="cursor: default;">';
                    html+='<img src="{{url("/")}}/'+tdata.uploadPath+'">';
                    html+='<div class="actions">';
                        html+='<ul class="actions">';
                            html+='<li><a href="{{url("/")}}/'+tdata.uploadPath+'" data-uuid="'+uuid+'" data-attachment_id="'+imgID+'" data-lightbox="Product Gallery" class="view" title="view image"><i class="fa fa-eye" aria-hidden="true"></i></a></li>';
                            html+='<li><a href="#" class="delete" data-product-type="vehicle-images" data-uuid="'+uuid+'" data-attachment_id="'+imgID+'" title="Delete image"><i class="fa fa-trash" aria-hidden="true"></i></a></li>';
                        html+='</ul>';
                    html+='</div>';
                html+='</li>';
                $('ul#VImages-'+uuid).append(html);
            }
        }
        const AddVendorDocuments=async(imgID,data)=>{
            let html='';
            if($('#divDocuments .vendor-documents[data-attachment_id="'+imgID+'"]').length>0){
                    html+='<div class="form-group">';
                        html+='<input type="file" class="dropify" data-id="'+imgID+'" id="txtVD-'+imgID+'" data-product-type="vendor-documents"  data-default-file="'+data.uploadPath+'" accept="<?php echo ".".implode(",.",$FileTypes['category']['Documents']) ?>" data-allowed-file-extensions="<?php echo implode(" ",$FileTypes['category']['Documents']) ?>" >';
                    html+='</div>';
                $('#divDocuments .vendor-documents[data-attachment_id="'+imgID+'"]').append(html);
            }else{
                html+='<div class="col-md-2  m-5 vendor-documents" data-attachment_id="'+imgID+'">';
                    html+='<div class="form-group">';
                        html+='<input type="file" class="dropify" data-id="'+imgID+'" id="txtVD-'+imgID+'" data-product-type="vendor-documents"  data-default-file="'+data.uploadPath+'" accept="<?php echo ".".implode(",.",$FileTypes['category']['Documents']) ?>" data-allowed-file-extensions="<?php echo implode(" ",$FileTypes['category']['Documents']) ?>" >';
                    html+='</div>';
                html+='</div>';
                $('#divDocuments').append(html);
            }
            setTimeout(() => {
                $('#txtVD-'+imgID).dropify();
            }, 10);
        }
        const updateVehicleAccordinTitle=async()=>{
            let rowIndex=1;
            $('#vehicleAccordion .accordion-item').each(function(index){
                let uuid1=$(this).attr('data-uuid');
                let Title=$('#txtVehicleNumber-'+uuid1).val();
                if(Title=="" || Title==undefined || Title==null ){
                    Title="Vehicle "+rowIndex;
                    rowIndex++;
                }
                $('#vehicleAccordion .accordion-item[data-uuid="'+uuid1+'"] .accordion-header button span.vehicle-name').html(Title);
            });
            vehiclesCount=rowIndex;
        }
        const InitStockPointTable=async()=>{
            if(tblStockPoint!=null){
                tblStockPoint.destroy();
            }
            tblStockPoint=$('#tblStockPoint').DataTable({
                info:false,
                searching:false,
                dom:'Bfrtip',
                buttons:[],
                responsive:true,
                ordering:false
            });
        }
        const getVendorCategory=async()=>{
            $('#lstCategory').select2('destroy');
            $('#lstCategory option').remove();
            $('#lstCategory').append('<option value="" selected>Select a Vendor Category</option>');
            $.ajax({
                type:"post",
                url:"{{url('/')}}/admin/master/vendor/manage-vendors/get/vendor-category",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    for(let Item of response){
                        let selected="";
                        if(Item.VCID==$('#lstCategory').attr('data-selected')){selected="selected";}
                        $('#lstCategory').append('<option '+selected+' value="'+Item.VCID+'">'+Item.VCName+' </option>');
                    }
                    if($('#lstCategory').val()!=""){
                        $('#lstCategory').trigger('change');
                    }
                }
            });
            $('#lstCategory').select2();
        }
        const getVendorType=async()=>{
            $('#lstVendorType').select2('destroy');
            $('#lstVendorType option').remove();
            $('#lstVendorType').append('<option value="" selected>Select a Vendor Type</option>');
            $.ajax({
                type:"post",
                url:"{{url('/')}}/admin/master/vendor/manage-vendors/get/vendor-type",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    for(let Item of response){
                        let selected="";
                        if(Item.VendorTypeID==$('#lstVendorType').attr('data-selected')){selected="selected";}
                        $('#lstVendorType').append('<option '+selected+' value="'+Item.VendorTypeID+'">'+Item.VendorType+' </option>');
                    }
                    if($('#lstVendorType').val()!=""){
                        $('#lstVendorType').trigger('change');
                    }
                }
            });
            $('#lstVendorType').select2();
        }
        const getCity=async(data)=>{
            return await new Promise((resolve,reject)=>{
                $.ajax({
                    type:"post",
                    url:"{{url('/')}}/get/city",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:data,
                    dataType:"json",
                    async:true,
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);resolve([])},
                    complete: function(e, x, settings, exception){},
                    success:function(response){
                        resolve(response)
                    }
                });
            });
        }
        const getTaluks=async(data,id)=>{
            $('#'+id).select2('destroy');
            $('#'+id+' option').remove();
            $('#'+id).append('<option value="">Select a Taluk</option>');
            $.ajax({
                type:"post",
                url:"{{url('/')}}/get/taluks",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                data:data,
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);resolve([])},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    for(let Item of response){
                        let selected="";
                        if(Item.TalukID==$('#'+id).attr('data-selected')){selected="selected";}
                        $('#'+id).append('<option '+selected+' data-taluk=""  value="'+Item.TalukID+'">'+Item.TalukName+' </option>');
                    }
                    if($('#'+id).val()!=""){
                        $('#'+id).trigger('change');
                    }
                }
            });
            $('#'+id).select2();
        }
        const getDistricts=async(data,id)=>{
            let Data = [];
            $('#'+id).select2('destroy');
            $('#'+id+' option').remove();
            $('#'+id).append('<option value="">Select a District</option>');
            $.ajax({
                type:"post",
                url:"{{url('/')}}/get/districts",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                data:data,
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);resolve([])},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    for(let Item of response){
                        let selected="";
                        if(Item.DistrictID==$('#'+id).attr('data-selected')){selected="selected";}
                        $('#'+id).append('<option '+selected+' data-taluk=""  value="'+Item.DistrictID+'">'+Item.DistrictName+' </option>');
                    }
                    if($('#'+id).val()!=""){
                        $('#'+id).trigger('change');
                    }
                    Data = response;
                }
            });
            $('#'+id).select2();
            return Data;
        }
        const getStates=async(data,id)=>{
            $('#'+id).select2('destroy');
            $('#'+id+' option').remove();
            $('#'+id).append('<option value="">Select a State</option>');
            $.ajax({
                type:"post",
                url:"{{url('/')}}/get/states",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                data:data,
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);resolve([])},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    for(let Item of response){
                        let selected="";
                        if(Item.StateID==$('#'+id).attr('data-selected')){selected="selected";}
                        $('#'+id).append('<option '+selected+'  value="'+Item.StateID+'">'+Item.StateName+' </option>');
                    }
                    if($('#'+id).val()!=""){
                        $('#'+id).trigger('change');
                    }
                }
            });
            $('#'+id).select2();
        }
        const getCountry=async(data,id)=>{
            $('#'+id).select2('destroy');
            $('#'+id+' option').remove();
            $('#'+id).append('<option value="">Select a Country</option>');
            $.ajax({
                type:"post",
                url:"{{url('/')}}/get/country",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                data:data,
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);resolve([])},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    for(let Item of response){
                        let selected="";
                        if(Item.CountryID==$('#'+id).attr('data-selected')){selected="selected";}
                        $('#'+id).append('<option '+selected+' value="'+Item.CountryID+'">'+Item.CountryName+' </option>');
                    }
                    if($('#'+id).val()!=""){
                        $('#'+id).trigger('change');
                    }
                }
            });
            $('#'+id).select2();
        }
        const getVehicleType=async()=>{
            return await new Promise((resolve,reject)=>{
                $.ajax({
                    type:"post",
                    url:"{{url('/')}}/get/vehicle-type",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    dataType:"json",
                    async:true,
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);resolve([])},
                    complete: function(e, x, settings, exception){},
                    success:function(response){
                        resolve(response)
                    }
                });
            });
        }
        const getVehicleBrand=async(data,id)=>{
            $('#'+id).select2('destroy');
            $('#'+id+' option').remove();
            $('#'+id).append('<option value="">Select a vehicle brand</option>');
            $.ajax({
                type:"post",
                url:"{{url('/')}}/get/vehicle-brand",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                data:data,
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);resolve([])},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    for(let Item of response){
                        let selected="";
                        if(Item.VehicleBrandID==$('#'+id).attr('data-selected')){selected="selected";}
                        $('#'+id).append('<option '+selected+' value="'+Item.VehicleBrandID+'">'+Item.VehicleBrandName+' </option>');
                    }
                    if($('#'+id).val()!=""){
                        $('#'+id).trigger('change');
                    }
                }
            });
            $('#'+id).select2();
        }
        const getVehicleModal=async(data,id)=>{
            $('#'+id).select2('destroy');
            $('#'+id+' option').remove();
            $('#'+id).append('<option value="">Select a vehicle model</option>');
            $.ajax({
                type:"post",
                url:"{{url('/')}}/get/vehicle-model",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                data:data,
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);resolve([])},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    for(let Item of response){
                        let selected="";
                        if(Item.VehicleModelID==$('#'+id).attr('data-selected')){selected="selected";}
                        $('#'+id).append('<option '+selected+' value="'+Item.VehicleModelID+'">'+Item.VehicleModel+' </option>');
                    }
                    if($('#'+id).val()!=""){
                        $('#'+id).trigger('change');
                    }
                }
            });
            $('#'+id).select2();
        }
        const stockPointValidation = async () => {
            $('.errors.stock-points').html('');
            let status = true;
            let uuid1=$("#txtSPName").attr('data-uuid');
                uuid1=(uuid1!="" && uuid1!=undefined)?uuid1: await generateUUID();
            let FormData = {
                UUID:uuid1,
                PostalCode : $("#txtSPPostalCode").val(),
                Name : $("#txtSPName").val(),
                Address : $("#txtSPAddress").val(),
                CityID : $("#lstSPCity").val(),
                PostalID : $("#lstSPCity option:selected").attr('data-postal'),
                CityName : $("#lstSPCity option:selected").attr('data-city-name'),
                TalukID : $("#lstSPCity option:selected").attr('data-taluk'),
                DistrictID : $("#lstSPCity option:selected").attr('data-district'),
                StateID : $("#lstSPCity option:selected").attr('data-state'),
                CountryID : $("#lstSPCity option:selected").attr('data-country'),
            }
            if(FormData.PostalCode == ""){
                $('#txtSPPostalCode-err').html('Postal Code is required');status = false;
            }
            if(FormData.Name == ""){
                $('#txtSPName-err').html('Point Name is required');status = false;
            }
            if(FormData.Address == ""){
                $('#txtSPAddress-err').html('Address is required');status = false;
            }
            if(FormData.CityID == ""){
                $('#txtSPCity-err').html('City is required');status = false;
            }
            // status = true;
            return { status, FormData };
        };
        const AddStockPoint = async (EditID,Index) => {
            let { status, FormData } = await stockPointValidation();
            if (status) {
                let UUID = generateUUID();
                let index = Index ? Index : ($("#tblStockPoint tbody tr").length) + 1;
                let html = `<tr data-uuid="${EditID ? EditID : UUID}">`;
                html += `<td>${index}</td>`;
                html += `<td>${FormData.Name} </td>`;
                html += `<td>${FormData.Address} </td>`;
                html += `<td>${FormData.CityName} </td>`;
                html += `<td>${FormData.PostalCode} </td>`;
                html += `<td class="text-center align-middle"><button type="button" class="btn btn-outline-success btnEditStockPoint"><i class="fa fa-pencil"></i></button> <button type="button" class="btn btn-outline-danger btnDeleteStockPoint"><i class="fa fa-trash"></i></button></td>`;
                html += `<td class="d-none tdata">${JSON.stringify(FormData)}</td>`;
                html += "</tr>";
                if(EditID){
                    $("#tblStockPoint tbody tr").each(function () {
                        let MatchID = $(this).attr("data-uuid");
                        if (MatchID === EditID) {
                            $(this).replaceWith(html);
                            return false;
                        }
                    });
                    $("#btnAddStockPoint").attr('data-edit-id', "").attr('data-index', "").html("Add");
                    $("#btnCancelStockPoint").removeClass('display:block').css('display', 'none');
                }else{
                    $('#tblStockPoint tbody').append(html);
                }
                //InitStockPointTable();
                clearStockPointDetails();
            }
        };
        const clearStockPointDetails=async()=>{
            $('#lstSPCity').attr("data-selected","");
            $("#txtSPPostalCode, #txtSPName, #txtSPAddress").val("");
            $('#lstSPCity').val("").trigger('change');
            getCity();
        }

        const getPCategory=async()=>{
            $('#lstPCategory').select2('destroy');
            $('#lstPCategory option').remove();
            $('#lstPCategory').append('<option value="" selected>Select a Product Category</option>');
            $.ajax({
                type:"post",
                url:"{{url('/')}}/admin/master/product/category/get/PCategory",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    for(let Item of response){
                        let selected="";
                        if(Item.PCID==$('#lstPCategory').attr('data-selected')){selected="selected";}
                        $('#lstPCategory').append('<option '+selected+' value="'+Item.PCID+'">'+Item.PCName+' </option>');
                    }
                }
            });
            $('#lstPCategory').select2();
            if($('#lstPCategory').val()!=""){
                $('#lstPCategory').trigger('change');
            }
        }
        const getPSubCategory=async()=>{
            let PCID = $('#lstPCategory').val();
            $('#lstPSubCategory').select2('destroy');
            $('#lstPSubCategory option').remove();
            $.ajax({
                type:"post",
                url:"{{url('/')}}/admin/master/product/sub-category/get/PSubCategory",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                dataType:"json",
                data : {PCID : PCID},
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                complete: function(e, x, settings, exception){},
                success: function (response) {
                    for (let Item of response) {
                        let isAlreadyInTable = isPSCIDInTable(Item.PSCID);
                        if (!isAlreadyInTable) {
                            $('#lstPSubCategory').append('<option value="' + Item.PSCID + '">' + Item.PSCName + ' </option>');
                        }
                    }
                }
            });
            $('#lstPSubCategory').select2();
        }
        const isPSCIDInTable=(PSCID)=> {
            let isExists = false;
            $('#tblSupplyDetails tbody tr').each(function (index) {
                let existingValue = $(this).find('td:eq(1)').attr('data-id');
                if (existingValue === PSCID.trim()) {
                    isExists = true;
                    return false;
                }
            });
            return isExists;
        }
        const formValidation=async()=>{
            let status=true;
            let isGeneral=false;
            let isAddress=false;
            let isVehicles=false;
            let data={}
            $('.errors').html('');
            data.VendorName=$('#txtVendorName').val();
            data.GSTNo=$('#txtGSTNo').val();
            data.CID=$('#lstCategory').val();
            data.VendorType=$('#lstVendorType').val();
            data.Email=$('#txtEmail').val();
            data.MobileNumber1=$('#txtMobileNumber1').val();
            data.MobileNumber2=$('#txtMobileNumber2').val();
            data.CreditDays=$('#txtCreditDays').val();
            data.CreditLimit=$('#txtCreditLimit').val();
            data.Address=$('#txtAddress').val();
            data.PostalCode=$('#lstCity option:selected').attr('data-postal');
            data.CityID=$('#lstCity').val();
            data.TalukID=$('#lstTaluk').val();
            data.DistrictID=$('#lstDistricts').val();
            data.StateID=$('#lstState').val();
            data.CountryID=$('#lstCountry').val();
            data.CommissionPercentage=$('#txtCommissionPercentage').val();
            if(data.VendorName==""){
                $('#txtVendorName-err').html('Vendor name is required.');status=false;
            }else if(data.VendorName.length<2){
                $('#txtVendorName-err').html('Vendor Name must be greater than 2 characters');status=false;
            }else if(data.VendorName.length>100){
                $('#txtVendorName-err').html('Vendor Name may not be greater than 100 characters');status=false;
            }
            if(data.GSTNo==""){
                $('#txtGSTNo-err').html('GST Number is required.');status=false;isGeneral=true;
            }
            if(data.CID==""){
                $('#lstCategory-err').html('Category is required.');status=false;isGeneral=true;
            }
            if(data.VendorType==""){
                $('#lstVendorType-err').html('Vendor Type is required.');status=false;isGeneral=true;
            }
            if(data.MobileNumber1==""){
                $('#txtMobileNumber1-err').html('Mobile Number is required.');status=false;isGeneral=true;
            }
            if(data.CreditDays==""){
                $('#txtCreditDays-err').html('Credit Days is required.');status=false;isGeneral=true;
            }else if($.isNumeric(data.CreditDays)==false){
                $('#txtCreditDays-err').html('Credit Days is must be numeric value');status=false;isGeneral=true;
            }else if(parseFloat(data.CreditDays)<0){
                $('#txtCreditDays-err').html('Credit Days is must be equal or  greater than 0.');status=false;isGeneral=true;
            }
            if(data.CreditLimit==""){
                $('#txtCreditLimit-err').html('Credit Limit is required.');status=false;isGeneral=true;
            }else if($.isNumeric(data.CommissionPercentage)==false){
                $('#txtCreditLimit-err').html('Credit Limit is must be numeric value');status=false;isGeneral=true;
            }else if(parseFloat(data.CommissionPercentage)<0){
                $('#txtCreditLimit-err').html('Credit Limit is must be equal or  greater than 0.');status=false;isGeneral=true;
            }
            if(data.CommissionPercentage==""){
                $('#txtCommissionPercentage-err').html('Commission Percentage is required.');status=false;isGeneral=true;
            }else if($.isNumeric(data.CommissionPercentage)==false){
                $('#txtCommissionPercentage-err').html('Commission Percentage is must be numeric value');status=false;isGeneral=true;
            }else if(parseFloat(data.CommissionPercentage)<0){
                $('#txtCommissionPercentage-err').html('Commission Percentage is must be equal or  greater than 0.');status=false;isGeneral=true;
            }else if(parseFloat(data.CommissionPercentage)>100){
                $('#txtCommissionPercentage-err').html('Commission Percentage is must be  not greater than 100.');status=false;isGeneral=true;
            }
            if(data.PostalCode==""){
                $('#txtPostalCode-err').html('Postal Code is required.');status=false;isAddress=true;
            }
            if(data.CityID==""){
                $('#lstCity-err').html('City is required.');status=false;isAddress=true;
            }
            if(data.TalukID==""){
                $('#lstTaluk-err').html('Taluk is required.');status=false;isAddress=true;
            }
            if(data.DistrictID==""){
                $('#lstDistricts-err').html('District is required.');status=false;isAddress=true;
            }
            if(data.StateID==""){
                $('#lstState-err').html('State is required.');status=false;isAddress=true;
            }
            if(data.CountryID==""){
                $('#lstCountry-err').html('Country is required.');status=false;isAddress=true;
            }
            if(data.Address==""){
                $('#txtAddress-err').html('Address is required.');status=false;
            }else if(data.Address.length<10){
                $('#txtAddress-err').html('Address must be greater than 10 characters');status=false;isAddress=true;
            }
            let errorUUID=null;
            $('#vehicleAccordion .accordion-item').each(function(index){/*
                let uuid=$(this).attr('data-uuid');
                let VNo=$('#txtVehicleNumber-'+uuid).val();
                let VType=$('#lstVehicleType-'+uuid).val();
                let VBrand=$('#lstVehicleBrand-'+uuid).val();
                let VModel=$('#lstVehicleModel-'+uuid).val();
                let VLength=$('#txtVehicleLength-'+uuid).val();
                let VDepth=$('#txtVehicleDepth-'+uuid).val();
                let VWidth=$('#txtVehicleWidth-'+uuid).val();
                let VCapacity=$('#txtVehicleCapacity-'+uuid).val();
                if (VType === "") {
                    $('#lstVehicleType-'+uuid+'-err').html('Vehicle Type is required');status = false;isVehicles=true;
                }
                if (VBrand=== "") {
                    $('#lstVehicleBrand-'+uuid+'-err').html('Vehicle Brand is required');status = false;isVehicles=true;
                }

                if (VModel === "") {
                    $('#lstVehicleModel-'+uuid+'-err').html('Vehicle Model is required');status = false;isVehicles=true;
                }
                if (VLength=== "") {
                    $('#txtVehicleLength-'+uuid+'-err').html('Vehicle Length is required');status = false;isVehicles=true;
                }
                if (VDepth === "") {
                    $('#txtVehicleDepth-'+uuid+'-err').html('Vehicle Depth is required');status = false;isVehicles=true;
                }
                if (VWidth === "") {
                    $('#txtVehicleWidth-'+uuid+'-err').html('Vehicle Width is required');status = false;isVehicles=true;
                }
                if (VNo === "") {
                    $('#txtVehicleNumber-'+uuid+'-err').html('Vehicle Number is required');status = false;isVehicles=true;
                }
                if (VCapacity === "") {
                    $('#txtVehicleCapacity-'+uuid+'-err').html('Vehicle Capacity is required');status = false;isVehicles=true;
                }
                errorUUID=(status==false && errorUUID==null)?uuid:errorUUID;*/
            });

            if(isGeneral==true && status==false){
                if($('.woo-commerce-style ul.woo-commerce-style li.general_options').hasClass('active')==false){
                    $('.woo-commerce-style ul.woo-commerce-style li').removeClass('active');
                    $('.woo-commerce-style ul.woo-commerce-style li.general_options').addClass('active');
                    showTabs();
                }
            }else if(isGeneral==false && isAddress==true && status==false){
                if($('.woo-commerce-style ul.woo-commerce-style li.address_options').hasClass('active')==false){
                    $('.woo-commerce-style ul.woo-commerce-style li').removeClass('active');
                    $('.woo-commerce-style ul.woo-commerce-style li.address_options').addClass('active');
                    showTabs();
                }

            }else if(isGeneral==false && isAddress==false && errorUUID!=null && isVehicles==true && status==false){
                if($('.woo-commerce-style ul.woo-commerce-style li.transport_details_options').hasClass('active')==false){
                    $('.woo-commerce-style ul.woo-commerce-style li').removeClass('active');
                    $('.woo-commerce-style ul.woo-commerce-style li.transport_details_options').addClass('active');
                    showTabs();
                    if( $('#'+errorUUID+'-heading button').hasClass('collapsed')){

                        $('#'+errorUUID+'-heading button').trigger('click');
                    }
                }
            }
            return status;
        }
        const getData=async()=>{
            let Vehicles=[];
            let supplyDetails=[];
            let stockPoints=[];
            let ServiceLocations=[];
            $('#vehicleAccordion .accordion-item').each(function(index){
                let t={};
                let tmpImages=[];
                let uuid=$(this).attr('data-uuid');
                if(Images.vehicle[uuid]!=undefined){
                    tmpImages=Images.vehicle[uuid];
                }
                t.uuid=uuid
                t.VNo=$('#txtVehicleNumber-'+uuid).val();
                t.VType=$('#lstVehicleType-'+uuid).val();
                t.VBrand=$('#lstVehicleBrand-'+uuid).val();
                t.VModel=$('#lstVehicleModel-'+uuid).val();
                t.VLength=$('#txtVehicleLength-'+uuid).val();
                t.VDepth=$('#txtVehicleDepth-'+uuid).val();
                t.VWidth=$('#txtVehicleWidth-'+uuid).val();
                t.VCapacity=$('#txtVehicleCapacity-'+uuid).val();

                t.Images=tmpImages;
                Vehicles.push(t);
            });
            $('#tblStockPoint tbody tr td.tdata').each(function(index){
                try {
                    let t=JSON.parse($(this).html());
                    stockPoints.push(t);
                } catch (error) {
                    console.log(error);
                }
            });
            $('#tblSupplyDetails tbody tr td.tdata').each(function(index){
                try {
                    let t=JSON.parse($(this).html());
                    supplyDetails.push(t);
                } catch (error) {
                    console.log(error);
                }
            });
            $('#ServiceLocationAccordin .accordion-item').each(function () {
                let countryID = $(this).attr('data-country-id');
                let stateID = $(this).attr('data-state-id');

                let districtIDs = $(this).find('.lstSLDistricts').val();

                districtIDs.forEach(function (districtID) {
                    let serviceData = {
                        CountryID: countryID,
                        StateID: stateID,
                        DistrictID: districtID,
                    };
                    ServiceLocations.push(serviceData);
                });
            });
            let formData=new FormData();
            formData.append('VendorName',$('#txtVendorName').val());
            formData.append('GSTNo',$('#txtGSTNo').val());
            formData.append('CID',$('#lstCategory').val());
            formData.append('VendorType',$('#lstVendorType').val());
            formData.append('Email',$('#txtEmail').val());
            formData.append('MobileNumber1',$('#txtMobileNumber1').val());
            formData.append('MobileNumber2',$('#txtMobileNumber2').val());
            formData.append('CreditDays',$('#txtCreditDays').val());
            formData.append('CreditLimit',$('#txtCreditLimit').val());
            formData.append('CommissionPercentage',$('#txtCommissionPercentage').val());
            formData.append('PostalCode',$('#lstCity option:selected').attr('data-postal'));
            formData.append('Address',$('#txtAddress').val());
            formData.append('CityID',$('#lstCity').val());
            formData.append('TalukID',$('#lstTaluk').val());
            formData.append('DistrictID',$('#lstDistricts').val());
            formData.append('StateID',$('#lstState').val());
            formData.append('CountryID',$('#lstCountry').val());
            formData.append('ActiveStatus',$('#lstActiveStatus').val());
            formData.append('VehicleData',JSON.stringify(Vehicles));
            formData.append('SupplyDetails',JSON.stringify(supplyDetails));
            formData.append('StockPoints',JSON.stringify(stockPoints));
            formData.append('ServiceLocations',JSON.stringify(ServiceLocations));
            formData.append('Documents',JSON.stringify(Images.documents));
            formData.append('deletedImages',JSON.stringify(deletedImages));

            formData.append('removeLogo', $('#txtVendorLogo').attr('data-remove'));
            if($('#txtVendorLogo').val()!=""){
                formData.append('Logo', $('#txtVendorLogo')[0].files[0]);
            }
            return formData;
        }
        init();

        $(document).on('click','.woo-commerce-style ul.woo-commerce-style a',function(e){
            e.preventDefault();
            $('.woo-commerce-style ul.woo-commerce-style li').removeClass('active');
            $(this).parent().addClass('active');
            showTabs();
        });
        $(document).on('click','#btnAddVehicle',function(){
            AddVehicle();
        });
        $(document).on('click','.addVehicleImages',function(){
            let uuid=$(this).attr('data-uuid');

            $('#txtVehicleImages').attr('data-uuid',uuid);
            $('#txtVehicleImages').trigger('click');

        });
        $(document).on('change','#txtVehicleImages',async function(){
            let uuid=$('#txtVehicleImages').attr('data-uuid');
            var files = this.files;
            for(let file of files){
                TotalImagesCount++;
                let referData={};
                referData.imgID= await generateUUID();
                referData.pType= "vehicle-images";
                referData.uuid= uuid;
                let formData=new FormData();
                formData.append('referData',JSON.stringify(referData));
                formData.append('image',file);
                tmpImageUpload(formData);
            }

            $('#txtVehicleImages').attr('data-uuid',"");
            $('#txtVehicleImages').val("");
        });
        $(document).on('click','.actions a.delete',function(e){
            e.preventDefault();
            let aid=$(this).attr('data-attachment_id');
            let PType=$(this).attr('data-product-type');
            let uuid=$(this).attr('data-uuid');
            $('li[data-attachment_id="'+aid+'"]').remove();
            if(['vehicle-images'].indexOf(PType)>=0){
                $('li[data-attachment_id="'+aid+'"]').remove();
                if(PType=="vehicle-images"){
                    if(Images.vehicle[uuid]!=undefined){
                        if (Images.vehicle[uuid].hasOwnProperty(aid)) {
                            deletedImages.vehicle.push({ImgID:aid,uuid});
                            delete Images.vehicle[uuid][aid];
                        }
                    }
                }
            }
        });
        $(document).on('click','.vehicle-trash',function(){
            let uuid=$(this).attr('data-uuid')
            $('#vehicleAccordion .accordion-item[data-uuid="'+uuid+'"]').remove();
            updateVehicleAccordinTitle();
        });

        $(document).on('keyup','.txtVehicleNumber',function(){updateVehicleAccordinTitle();});

        $(document).on('click','#btnAddDocuments',function(){
            $('#txtDocuments').trigger('click');
        });
        $(document).on('change','#txtDocuments',async function(){
            console.log(this.files[0]);

            var files = this.files;
            for(let file of files){
                TotalImagesCount++;
                let referData={};
                referData.imgID= await generateUUID();
                referData.pType= "vendor-documents";
                referData.uuid= "";
                let formData=new FormData();
                formData.append('referData',JSON.stringify(referData));
                formData.append('image',file);
                tmpImageUpload(formData);
            }
            $('#txtDocuments').attr('data-uuid',"");
            $('#txtDocuments').val("");
        });
        $("#btnCropApply").on('click', function() {
            btnLoading($('#btnCropApply'));
            setTimeout(() => {
                var base64 = $image.cropper('getCroppedCanvas').toDataURL();
                var id = $image.attr('data-id');
                let isUrl=$image.attr('data-is-url');
                let aid=$image.attr('data-attachment_id');
                let pType=$image.attr('data-product-type');
                let uuid=$image.attr('data-uuid');
                    let referData={};
                    referData.imgID= aid;
                    referData.id= id;
                    referData.isUrl= isUrl;
                    referData.pType= pType;
                    referData.uuid= uuid;

                    let formData=new FormData();
                    formData.append('referData',JSON.stringify(referData));
                    formData.append('image',base64);
                    tmpImageUpload(formData);
                $('#ImgCrop').modal('hide');
                setTimeout(() => {
                    btnReset($('#btnCropApply'));
                }, 100);
            }, 100);
        });
        $(document).on('click','#btnGSearchPostalCode',async function(){
            $('#txtPostalCode-err').html('')
            let PostalCode=$('#txtPostalCode').val();
            if(PostalCode!=""){
                $('#btnGSearchPostalCode').attr('disabled','disabled');
                $('#btnGSearchPostalCode').html('<i class="fa fa-spinner fa-pulse"></i>');
                let response=await getCity({PostalCode});
                if(response.length>0){
                    $('#lstCity').select2('destroy');
                    $('#lstCity option').remove();
                    $('#lstCity').append('<option value="">Select a City</option>');
                    for(let Item of response){
                        let selected="";
                        if(Item.CityID==$('#lstCity').attr('data-selected')){selected="selected";}
                        $('#lstCity').append('<option '+selected+' data-postal="'+Item.PostalID+'" data-taluk="'+Item.TalukID+'" data-district="'+Item.DistrictID+'" data-state="'+Item.StateID+'" data-country="'+Item.CountryID+'" data-city-name="'+Item.CityName+'" value="'+Item.CityID+'">'+Item.CityName+' </option>');
                    }
                    $('#lstCity').select2();
                    if($('#lstCity').val()!=""){
                        $('#lstCity').trigger('change');
                    }
                }else{
                    $('#txtPostalCode-err').html('Postal Code does not exists.')
                }
                setTimeout(() => {
                    $('#btnGSearchPostalCode').html('Search <i class="fa fa-search"></i>');
                    $('#btnGSearchPostalCode').removeAttr('disabled');
                }, 100);
            }else{
                $('#txtPostalCode-err').html('Postal Code is required.')
            }
        });
        $(document).on("change",'#lstCity',function(){
            let CountryID=$('#lstCity option:selected').attr('data-country');
            let StateID=$('#lstCity option:selected').attr('data-state');
            let DistrictID=$('#lstCity option:selected').attr('data-district');
            let TalukID=$('#lstCity option:selected').attr('data-taluk');
            $('#lstTaluk').attr('data-selected',TalukID);
            $('#lstDistricts').attr('data-selected',DistrictID);
            $('#lstState').attr('data-selected',StateID);
            $('#lstCountry').attr('data-selected',CountryID);
            $('#lstCountry').val(CountryID).trigger('change');
        });
        $(document).on("change",'#lstDistricts',function(){
            getTaluks({CountryID:$('#lstCountry').val(),StateID:$('#lstState').val(),DistrictID:$('#lstDistricts').val()},'lstTaluk');
        });
        $(document).on("change",'#lstState',function(){
            getDistricts({CountryID:$('#lstCountry').val(),StateID:$('#lstState').val()},'lstDistricts');
        });
        $(document).on("change",'#lstCountry',function(){
            getStates({CountryID:$('#lstCountry').val()},'lstState');
        });
        $(document).on('change','.lstVehicleType',function(){
            let uuid=$(this).attr('data-uuid');
            let VehicleTypeID=$('#lstVehicleType-'+uuid).val();
            getVehicleBrand({VehicleTypeID},'lstVehicleBrand-'+uuid);
        });
        $(document).on('change','.lstVehicleBrand',function(){
            let uuid=$(this).attr('data-uuid');
            let VehicleTypeID=$('#lstVehicleType-'+uuid).val();
            let VehicleBrandID=$('#lstVehicleBrand-'+uuid).val();
            getVehicleModal({VehicleTypeID,VehicleBrandID},'lstVehicleModel-'+uuid);
        });
        $(document).on('click','#btnSPPostalCode',async function(){
            $('#txtSPPostalCode-err').html('')
            let PostalCode=$('#txtSPPostalCode').val()
            if(PostalCode!=""){
                $('#btnSPPostalCode').attr('disabled','disabled');
                $('#btnSPPostalCode').html('<i class="fa fa-spinner fa-pulse"></i>');
                let response=await getCity({PostalCode});
                if(response.length>0){
                    $('#lstSPCity').select2('destroy');
                    $('#lstSPCity option').remove();
                    $('#lstSPCity').append('<option value="">Select a City</option>');
                    for(let Item of response){
                        let selected="";
                        if(Item.CityID==$('#lstSPCity').attr('data-selected')){selected="selected";}
                        $('#lstSPCity').append('<option '+selected+' data-postal="'+Item.PostalID+'" data-taluk="'+Item.TalukID+'" data-district="'+Item.DistrictID+'" data-state="'+Item.StateID+'" data-country="'+Item.CountryID+'" data-city-name="'+Item.CityName+'" value="'+Item.CityID+'">'+Item.CityName+'</option>');
                    }
                    $('#lstSPCity').select2();
                    if($('#lstSPCity').val()!=""){
                        $('#lstSPCity').trigger('change');
                    }
                }else{
                    $('#txtSPPostalCode-err').html('Postal Code does not exists.')
                }
                setTimeout(() => {
                    $('#btnSPPostalCode').html('<i class="fa fa-search"></i>');
                    $('#btnSPPostalCode').removeAttr('disabled');
                }, 100);
            }else{
                $('#txtSPPostalCode-err').html('Postal Code is required.')
            }
        });
        $(document).on('click', '#btnAddStockPoint', function () {
            let EditID=$(this).attr('data-edit-id');
            let Index=$(this).attr('data-index');
            AddStockPoint(EditID,Index);
        });
        $(document).on('click', '.btnEditStockPoint', function () {
            let Row=$(this).closest('tr');
            let EditData=JSON.parse($(this).closest('tr').find("td:eq(6)").html());
            $('#txtSPAddress').val(EditData.Address);
            $('#lstSPCity').attr("data-selected",EditData.CityID);
            $("#txtSPPostalCode").val(EditData.PostalCode);
            $("#txtSPName").val(EditData.Name);
            $("#txtSPName").attr('data-uuid',EditData.UUID);
            $('#btnSPPostalCode').trigger('click')
            //getCity();
            $("#btnAddStockPoint").attr('data-edit-id', Row.attr("data-uuid")).attr('data-index', Row.find("td:first").html()).html("Update");
            $("#btnCancelStockPoint").removeClass('display:none').css('display', 'block');
        });
        $(document).on('click', '#btnCancelStockPoint', function () {
            $('.errors').html("");
            $("#btnAddStockPoint").attr('data-edit-id', "").attr('data-index', "").html("Add");
            $("#btnCancelStockPoint").removeClass('display:block').css('display', 'none');
            clearStockPointDetails();
        });
        $(document).on('click', '.btnDeleteStockPoint', function () {
            $(this).closest("tr").remove();
            $("#tblStockPoint tbody tr").each(function (index) {
                $(this).find("td:eq(0)").text(index + 1);
            });
        });
        $(document).on('click', '.btnAddSupply', async function () {
            $('.errors.SupplyDetails').html('');
            let status = true;
            let ExistStatus = false;
            let PCID = $('#lstPCategory').val();
            let PSCID = $('#lstPSubCategory').val();

            if (PCID == "") {
                $('#lstPCategory-err').html('Product Category is required');status = false;
            }
            if (PSCID.length == 0) {
                $('#lstPSubCategory-err').html('Product Sub Category is required');status = false;
            }

            if (status) {
                let PData = [];
                for (let item of PSCID) {
                    ExistStatus = await isPSCIDInTable(item);
                    if (!ExistStatus) {
                        PData.push({
                            PCID: PCID,
                            PCName: $('#lstPCategory option:selected').text(),
                            PSCID: item,
                            PSCName: $('#lstPSubCategory option[value="' + item + '"]').text(),
                        });
                    }
                }
                if (status && !ExistStatus) {
                    let TableLength = $("#tblSupplyDetails tbody tr").length;
                    if (TableLength == 1){
                        //let table = $('#tblSupplyDetails').DataTable();
                        //table.destroy();
                    }
                    PData.forEach(function (data) {
                        let html = '<tr >';
                        html += '<td>' + data.PCName + '</td>';
                        html += '<td data-id="' + data.PSCID + '">' + data.PSCName + '</td>';
                        html += '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btnDeleteSupply"><i class="fa fa-trash"></i></button></td>';
                        html += '<td class="d-none tdata">' + JSON.stringify({ PCID: data.PCID, PSCID: data.PSCID }) + '</td>';
                        html += '</tr>';
                        $('#tblSupplyDetails tbody').append(html);
                    });
                    //$('#tblSupplyDetails').DataTable();

                    $('#lstPCategory').val('').trigger('change');
                }

            }
        });
        $(document).on('click', '.btnDeleteSupply', function () {
            $(this).closest("tr").remove();
        });
        $(document).on('change','#lstPCategory',function(){
            $('.errors.SupplyDetails').html('');
            getPSubCategory();
        });
        $(document).on('click','.vendor-logo .dropify-clear',function(){
            $(this).parent().find('input[type="file"]').attr('data-remove',1);
        });
        $(document).on('click','#divDocuments .dropify-clear',function(){
            let aid=$(this).parent().find('input.dropify').attr('data-id');
            $('#divDocuments .vendor-documents[data-attachment_id="'+aid+'"]').remove();
            if (Images.documents.hasOwnProperty(aid)) {
                deletedImages.documents.push({ImgID:aid});
                delete Images.documents[aid];
            }
        });
        $(document).on('click','#btnSave',async function(){
            let status=await formValidation();
            if(status){
                swal({
                    title: "Are you sure?",
                    text: "You want @if($isEdit==true)Update @else Save @endif this Vendor!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-outline-success",
                    confirmButtonText: "Yes, @if($isEdit==true)Update @else Save @endif it!",
                    closeOnConfirm: false
                },async function(){
                    swal.close();
                    let formData=await getData();

                    btnLoading($('#btnSave'));
                    let postUrl= @if($isEdit) "{{url('/')}}/admin/master/vendor/manage-vendors/edit/{{$VendorID}}"; @else "{{url('/')}}/admin/master/vendor/manage-vendors/create"; @endif
                    $.ajax({
                        type:"post",
                        url:postUrl,
                        headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                        data:formData,
                        cache: false,
                        processData: false,
                        contentType: false,
                        xhr: function() {
                            var xhr = new window.XMLHttpRequest();
                            xhr.upload.addEventListener("progress", function(evt) {
                                if (evt.lengthComputable) {
                                    var percentComplete = (evt.loaded / evt.total) * 100;
                                    percentComplete=parseFloat(percentComplete).toFixed(2);
                                    $('#divProcessText').html(percentComplete+'% Completed.<br> Please wait for until upload process complete.');
                                    //Do something with upload progress here
                                }
                            }, false);
                            return xhr;
                        },
                        beforeSend: function() {
                            ajaxIndicatorStart("Please wait Upload Process on going.");

                            var percentVal = '0%';
                            setTimeout(() => {
                            $('#divProcessText').html(percentVal+' Completed.<br> Please wait for until upload process complete.');
                            }, 100);
                        },
                        error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                        complete: function(e, x, settings, exception){btnReset($('#btnSave'));ajaxIndicatorStop();$("html, body").animate({ scrollTop: 0 }, "slow");},
                        success:function(response){
                            if(response.status==true){
                                swal({
                                    title: "SUCCESS",
                                    text: response.message,
                                    type: "success",
                                    showCancelButton: false,
                                    confirmButtonClass: "btn-outline-success",
                                    confirmButtonText: "Okay",
                                    closeOnConfirm: false
                                },function(){
                                    @if($isEdit==true)
                                        window.location.replace("{{url('/')}}/admin/master/vendor/manage-vendors  ");
                                    @else
                                        window.location.reload();
                                    @endif
                                });

                            }else{
                                toastr.error(response.message, "Failed", {
                                    positionClass: "toast-top-right",
                                    containerId: "toast-top-right",
                                    showMethod: "slideDown",
                                    hideMethod: "slideUp",
                                    progressBar: !0
                                })
                                if(response['errors']!=undefined){
                                    $('.errors').html('');
                                    $.each( response['errors'], function( KeyName, KeyValue ) {
                                        var key=KeyName;
                                        if(key=="VendorName"){$('#txtVendorName-err').html(KeyValue);}
                                        if(key=="GSTNo"){$('#txtGSTNo-err').html(KeyValue);}
                                        if(key=="CID"){$('#lstCategory-err').html(KeyValue);}
                                        if(key=="VendorType"){$('#lstVendorType-err').html(KeyValue);}
                                        if(key=="Email"){$('#txtEmail-err').html(KeyValue);}
                                        if(key=="MobileNumber1"){$('#txtMobileNumber1-err').html(KeyValue);}
                                        if(key=="MobileNumber2"){$('#txtMobileNumber2-err').html(KeyValue);}
                                        if(key=="Address"){$('#txtAddress-err').html(KeyValue);}
                                        if(key=="PostalCode"){$('#txtPostalCode-err').html(KeyValue);}
                                        if(key=="CityID"){$('#lstCity-err').html(KeyValue);}
                                        if(key=="TalukID"){$('#lstTaluk-err').html(KeyValue);}
                                        if(key=="DistrictID"){$('#lstDistricts-err').html(KeyValue);}
                                        if(key=="StateID"){$('#lstState-err').html(KeyValue);}
                                        if(key=="CountryID"){$('#lstCountry-err').html(KeyValue);}
                                        if(key=="ActiveStatus"){$('#lstActiveStatus-err').html(KeyValue);}
                                    });
                                }
                            }
                        }
                    });
                });
            }
        });

        // Service Locations

        const AddServiceLocations=async(CountryID,StateID,StateName,DistrictIDs)=>{
            let AddressStateID = $("#lstState").val();
            let AddressDistrictID = $("#lstDistricts").val();
            let html='';
                html+='<div class="accordion-item" data-country-id="'+CountryID+'" data-state-id="'+StateID+'">';
                    html+='<h2 class="accordion-header" data-state-id="'+StateID+'" id="'+StateID+'-heading">';
                        html += '<button class="accordion-button" type="button" data-state-id="' + StateID + '" data-bs-toggle="collapse" data-bs-target="#panel-' + StateID + '" aria-expanded="true" aria-controls="panel-' + StateID + '"> ' + StateName + ' <span class="options">' + (AddressStateID != StateID ? '<span class="trash district-trash" data-state-id="' + StateID + '"><i class="fa fa-trash"></i></span>' : '') + ' </span></button>';
                    html+='</h2>';
                    html+='<div id="panel-'+StateID+'" class="accordion-collapse collapse" aria-labelledby="'+StateID+'-heading">';
                        html+='<div class="accordion-body">';
                            html+='<div class="row">';
                                html+='<div class="col-12">';
                                    html+='<label for="lstSLDistricts-'+StateID+'">District</label>';
                                        html+='<div class="form-group">';
                                        html+='<select id="lstSLDistricts-'+StateID+'" data-state-id="'+StateID+'" class="form-control select2 lstSLDistricts" data-selected="' + (AddressStateID == StateID ? AddressDistrictID : '') + '" multiple>';
                                        html+='</select>';
                                    html+='</div>';
                                html+='</div>';
                            html+='</div>';
                        html+='</div>';
                    html+='</div>';
                html+='</div>';
                $('#ServiceLocationAccordin').append(html);

                $('#lstSLDistricts-'+StateID).append('<option value="">Select a District</option>');
                $.ajax({
                    type:"post",
                    url:"{{url('/')}}/get/districts",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:{CountryID : $("#lstSLCountry").val(),StateID : StateID},
                    dataType:"json",
                    async:true,
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);resolve([])},
                    complete: function(e, x, settings, exception){},
                    success:function(response){
                        for(let Item of response){
                            let selected="";
                            if(DistrictIDs){
                                if(DistrictIDs.indexOf(Item.DistrictID)!=-1){
                                    selected="selected";
                                }
                            }else if(Item.DistrictID==$('#lstSLDistricts-'+StateID).attr('data-selected')){
                                selected="selected";
                            }
                            $('#lstSLDistricts-'+StateID).append('<option '+selected+' value="'+Item.DistrictID+'">'+Item.DistrictName+' </option>');
                        }
                    }
                });
                $('#lstSLDistricts-'+StateID).select2();
        }
        $(document).on("change",'#lstSLCountry',async function(){
            let ExistingStates = [];
            $('#ServiceLocationAccordin .accordion-item').each(function(){
                ExistingStates.push($(this).attr('data-state-id'));
            });

            $('#lstSLState').select2('destroy');
            $('#lstSLState option').remove();
            $('#lstSLState').append('<option value="">Select a State</option>');
            $.ajax({
                type:"post",
                url:"{{url('/')}}/get/states",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                data:{CountryID : $(this).val()},
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);resolve([])},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    for(let Item of response){
                        let disabled="";
                        let selected="";
                        if(ExistingStates.indexOf(Item.StateID)!=-1){disabled="disabled";}
                        if(Item.StateID==$('#lstSLState').attr('data-selected')){selected="selected";}
                        $('#lstSLState').append('<option '+selected+' '+disabled+' value="'+Item.StateID+'">'+Item.StateName+' </option>');
                    }
                    if($('#lstSLState').val()!=""){
                        $('#lstSLState').trigger('change');
                    }
                }
            });
            $('#lstSLState').select2();
        });
        $(document).on("change",'#lstSLState',function(){
            let CountryID=$("#lstSLCountry").val();
            let StateID=$(this).val();
            let StateName=$(this).find('option:selected').html();
            let ExistingStates = [];
            $('#ServiceLocationAccordin .accordion-item').each(function(){
                ExistingStates.push($(this).attr('data-state-id'));
            });
            if(StateID && ExistingStates.indexOf(StateID) == -1){
                $('#lstSLState').select2('destroy');
                $('#lstSLState option[value="'+StateID+'"]').attr('disabled','disabled');
                $('#lstSLState').val("").trigger("change");
                $('#lstSLState').select2();
                AddServiceLocations(CountryID,StateID,StateName);
            }
        });
        $(document).on('click','.district-trash',function(){
            let StateID=$(this).attr('data-state-id');
            $('#lstSLState').select2('destroy');
            $('#ServiceLocationAccordin .accordion-item[data-state-id="'+StateID+'"]').remove();
            $('#lstSLState option[value="'+StateID+'"]').removeAttr('disabled');
            $('#lstSLState').select2();
            delete Attributes[StateID];
        });

    });
</script>
@endsection
