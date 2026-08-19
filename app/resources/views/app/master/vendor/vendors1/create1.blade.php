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
    .select2-selection--multiple{
        height: 100px !important;
        min-height: 100px !important;
        max-height: 100px !important;
        overflow-y:auto;
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
					<li class="breadcrumb-item">Product Master</li>
					<li class="breadcrumb-item"><a href="{{url('/')}}/master/product/products">{{$PageTitle}}</a></li>
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
                        <input type="text" class="form-control form-control-lg" id="txtVendorName" placeholder="Vendor Name" value="<?php if($isEdit){ echo $data->txtVendorName;} ?>">
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
                            <ul class="woo-commerce-style ">
                                <li class="general_options "><a href="#general-tab"><span>General</span></a></li>
                                <li class="address_options active"><a href="#address-tab"><span>Address</span></a></li>
                                <li class="transport_details_options "><a href="#transport-tab"><span>Transport Details</span></a></li>
                                <li class="supply_details_options  " ><a href="#supply-details-tab"><span>Supply Details</span></a></li>
                                <li class="stock_points_options " ><a href="#stock-points-tab"><span>Stock Points</span></a></li>
                                <li class="documents_options " ><a href="#vendor-documents-tab"><span>Documents</span></a></li>
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
                                        <select class="form-control {{$Theme['input-size']}} select2" id="lstVendorType"  data-selected="<?php if($isEdit){ echo $data->VendorType;} ?>">
                                            <option value="">Select a Vendor Type</option>
                                        </select>
                                        <div class="errors err-sm" id="lstVendorType-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >E-Mail</div></div>
                                    <div class="col-6 col-lg-8">
                                        <input type="email" id="txtEmail" class="form-control" placeholder="Email" value="<?php if($isEdit){ echo $data->EMail;} ?>">
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
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Postal Code</div></div>
                                    <div class="col-6 col-lg-8">
                                        <div class="input-group">
                                            <input type="text" id="txtPostalCode" class="form-control" placeholder="Postal Code" value="">
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
                                <div class="row justify-content-center">
                                    <div class="col-sm-5 col-md-4 col-lg-4">
                                        <div class="form-group">
                                            <label for="">Product Category</label>
                                            <select class="form-control select2" id=""></select>
                                        </div>
                                    </div>
                                    <div class="col-sm-5 col-md-4 col-lg-4">
                                        <div class="form-group">
                                            <label for="">Product Sub Category</label>
                                            <select class="form-control select2" id=""></select>
                                        </div>
                                    </div>
                                    <div class="col-sm-2 col-md-1 col-lg-1 text-center pt-25"><button type="button" id="btnAddSupply" class="btn btn-outline-primary">Add</button></div>
                                </div>
                                <div class="row mt-10">
                                    <div class="col-12">
                                        <table class="table" id="tblSupplyDetails">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">Product Category</th>
                                                    <th class="text-center">Product Sub Category</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>
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
                                    <div class="col-md-4">
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
                                    <div class="col-md-2 pt-27 text-center">
                                        <button id="btnCancelStockPoint" class="btn btn-sm btn-outline-dark" style="display:none" >Cancel</button>
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
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-right">
                    <a href="{{url('/')}}/master/product/products" class="btn btn-outline-dark mr-10">Cancel</a>
                    <button type="button" class="btn btn-outline-success mr-10" id="btnSave">Save</button>
                </div>
            </div>
		</div>
		<div class="col-12 col-sm-3 col-md-3 col-lg-3 pt-22">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header"> <h3>Vendor Logo</h3> <span class="options"><span class="trash ProductCoverImageTrash"><i class="fa fa-trash"></i></span></span></div>
                        <div class="card-body product-cover-image ">
                            <div class="row">
                                <div class="col-12">
                                    <input type="file" class="dropify imageScrop" data-product-type="main" data-aspect-ratio="{{$Settings['image-crop-ratio']['w']/$Settings['image-crop-ratio']['h']}}" data-remove="0" data-is-cover-image="1" id="txtProductImage" data-default-file="<?php if($isEdit){ echo url('/')."/".$data->ProductImage;} ?>"   data-allowed-file-extensions="<?php echo implode(" ",$FileTypes['category']['Images']) ?>" >
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
            documents:[]
        };
        let vehiclesCount=1;
        let stockPointCount=1;
        let vehiclesData={};
        var ProductDetails=[];
        let tblStockPoint=null;
        let deletedImages={
            product:[],
            vehicle:[],

        };
        let TotalImagesCount=0;
        const init=async()=>{
            showTabs();
            //InitStockPointTable();
            getVendorCategory();
            getVendorType();
            getCountry({},'lstCountry');
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
        const AddVehicle=async()=>{
            let uuid=generateUUID();
            let VehicleTypes=await getVehicleType(); console.log(VehicleTypes)
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
                        html+='<input type="file" class="dropify" id="txtVD-'+imgID+'" data-product-type="vendor-documents"  data-default-file="'+data.uploadPath+'" accept="<?php echo ".".implode(",.",$FileTypes['category']['Documents']) ?>" data-allowed-file-extensions="<?php echo implode(" ",$FileTypes['category']['Documents']) ?>" >';
                    html+='</div>';
                $('#divDocuments .vendor-documents[data-attachment_id="'+imgID+'"]').append(html);
            }else{
                html+='<div class="col-md-2  m-5 vendor-documents" data-attachment_id="'+imgID+'">';
                    html+='<div class="form-group">';
                        html+='<input type="file" class="dropify" id="txtVD-'+imgID+'" data-product-type="vendor-documents"  data-default-file="'+data.uploadPath+'" accept="<?php echo ".".implode(",.",$FileTypes['category']['Documents']) ?>" data-allowed-file-extensions="<?php echo implode(" ",$FileTypes['category']['Documents']) ?>" >';
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
                let uuid1=$(this).attr('data-uuid'); console.log(uuid1)
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
                url:"{{url('/')}}/master/vendor/vendors/get/vendor-category",
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
                url:"{{url('/')}}/master/vendor/vendors/get/vendor-type",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    for(let Item of response){
                        let selected="";
                        if(Item.TypeID==$('#lstVendorType').attr('data-selected')){selected="selected";}
                        $('#lstVendorType').append('<option '+selected+' value="'+Item.TypeID+'">'+Item.Type+' </option>');
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
                }
            });
            $('#'+id).select2();
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
            let FormData = {
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
                html += `<td class="d-none">${JSON.stringify(FormData)}</td>`;
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
            $("#txtSPPostalCode, #txtSPName, #txtSPAddress").val("").trigger('change');
            getCity();
        }
        const formValidation=async()=>{

        }
        init();

        $(document).on('click','.woo-commerce-style ul.woo-commerce-style a',function(e){
            e.preventDefault();
            $('.woo-commerce-style ul.woo-commerce-style li').removeClass('active');
            $(this).parent().addClass('active');
            showTabs();
        });
        $(document).on('click','#btnSave',async function(){
            let formData=await getData();
            let status=await formValidation(formData); status=true;
            if(status){
                swal({
                    title: "Are you sure?",
                    text: "You want @if($isEdit==true)Update @else Save @endif this Product!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-outline-success",
                    confirmButtonText: "Yes, @if($isEdit==true)Update @else Save @endif it!",
                    closeOnConfirm: false
                },async function(){
                    swal.close();
                    save(formData);
                });
            }
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
            let PostalCode=$('#txtPostalCode').val()
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
            getTaluks({CountryID:$('#lstCountry').val(),StateID:$('#lstState').val(),DistrictID:$('#lstStates').val()},'lstTaluk');
        });
        $(document).on("change",'#lstState',function(){
            getDistricts({CountryID:$('#lstCountry').val(),StateID:$('#lstState').val()},'lstDistricts');
        });
        $(document).on("change",'#lstCountry',function(){
            getStates({CountryID:$('#lstCountry').val()},'lstState');
        });
        $(document).on('change','.lstVehicleType',function(){
            let uuid=$(this).attr('data-uuid'); console.log(uuid)
            let VehicleTypeID=$('#lstVehicleType-'+uuid).val();
            getVehicleBrand({VehicleTypeID},'lstVehicleBrand-'+uuid);
        });
        $(document).on('change','.lstVehicleBrand',function(){
            let uuid=$(this).attr('data-uuid');
            let VehicleTypeID=$('#lstVehicleType-'+uuid).val();
            let VehicleBrandID=$('#lstVehicleModel-'+uuid).val();
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
            $('#lstSPCity').attr("data-selected",EditData.SPCity);
            $("#txtSPPostalCode").val(EditData.PostalCode);
            $("#txtSPName").val(EditData.SPName);
            getCity();
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
    });
</script>
@endsection
