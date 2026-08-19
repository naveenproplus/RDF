@extends('layouts.app')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@icon/dashicons@0.9.0-alpha.4/dashicons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.css">
<style>
    .body{
        background:#f0f0f1;
    }
    input,select,textarea{
        border-radius:0px !important;
    }
    .card{
        border-radius:0px !important;
        position: relative;
        border: 1px solid #c3c4c7;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
        background: #fff;
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
    .product_images_container{
        padding: 0 0 0 9px;
        position: relative;
    }
    .product_images_container.main_product{
        min-height:250px;
    }
    .product_images_container ul.product_images{
        display:none;
    }
    .product_images_container ul.product_images:has(li){
        display:block;
    }
    .product_images_container .no-gallery-images{
        display:flex;
        width:100%;
        height:250px;
        min-height:250px;
    }
    .product_images_container ul.product_images:has(li)+ .no-gallery-images{
        display:none;
    }
    .product_images_container ul{
        margin: 0;
        padding: 0;
    }
    .product_images_container ul:before{
        content: " ";
        display: table;
    }
    .product_images_container ul li.image{
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
    .product_images_container ul li.image img{
        width: 100%;
        height: auto;
        display: block;
    }
    .product_images_container ul .actions {
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
    .product_images_container ul li:hover .actions {
        display:flex;
    }
    .product_images_container ul li:hover .actions li{
        margin:0px 2px;
    }
    .product_images_container ul li:hover .actions li a{
        background: #999;
        /* width: 40px; */
        /* height: 40px; */
        font-size: 11px;
        padding: 1px 3px 3px;
        border-radius: 3px;
        color: #fff;
    }
    .product_images_container ul li:hover .actions li a:hover{
        background:#a00;
    }
    .form-group label {
        margin-bottom:2px;
    }
    .input-group .select2{
        width:75% !important;
        max-width:75% !important;
    }
    .dropify-wrapper{
        height:auto !important;
        min-height: 200px;
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
					<li class="breadcrumb-item"><a href="{{url('/')}}/admin/master/product/products">{{$PageTitle}}</a></li>
                    <li class="breadcrumb-item">@if($isEdit==true)Update @else Create @endif</li>
				</ol>
			</div>
		</div>
	</div>
</div>

<div class="container-fluid d-none d-sm-block pl-20 pr-20">
	<div class="row d-flex justify-content-center">
		<div class="col-12 col-sm-9 col-md-9 col-lg-9 mb-10">
            <div class="row">
                <div class="col-12 p-0">
                    <div class="form-group">
                        <label for="txtProductName">Product Name  <span class="required"> * </span></label>
                        <input type="text" class="form-control form-control-lg" id="txtProductName" placeholder="Product Name" value="<?php if($isEdit){ echo $data->ProductName;} ?>" autocomplete="off">
                        <div class="errors err-sm" id="txtProductName-err"></div>
                    </div>
                </div>
                @if(count($languages) > 0)
                    <div class="col-sm-12 p-0 text-center mt-20">
                        <label class="align-middle fw-bold mb-0">Product Name Translations</label>
                        @foreach($languages as $index=>$language)
                            <div class="form-group text-left mt-10">
                                <label class="txtProductNameIn_{{ $language->code }}">Product Name
                                    In {{ $language->name_in_english }}<span class="required"> * </span></label>
                                <input type="text"
                                       class="form-control PNameLanguageFieldsCheck {{$Theme['input-size']}}"
                                       id="txtProductNameIn_{{ $language->code }}"
                                       data-language-code="{{ $language->code }}"
                                       data-language="{{ $language->name_in_english }}"
                                       value="{{ $isEdit ? ($data->ProductNameInTranslation->{$language->code} ?? '') : '' }}"
                                       autocomplete="off">
                                <div class="errors" id="txtProductNameIn_{{ $language->code }}-err"></div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="row d-none d-md-flex mt-20">
                <div class="col-12 p-0">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-18 col-sm-8 col-md-6 col-lg-5 col-xl-4 d-flex align-items-center">
                                    <div class=" fw-700 text-nowrap pr-10">Product Data</div>
                                    <select class="form-control" id="lstProductType">
                                        <option value="Simple"  @if($isEdit) @if($data->ProductType=="Simple") selected @endif @else selected @endif>Simple Product</option>
                                        <option value="Variable"  @if($isEdit) @if($data->ProductType=="Variable") selected @endif @endif>Variable Product</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <?php
                            $isVariationTab="";
                            if($isEdit){
                                if($data->ProductType=="Variable"){
                                    $isVariationTab="";
                                }
                            }
                        ?>
                        <div class="card-body p-0 woo-commerce-style">
                            <ul class="woo-commerce-style ">
                                <li class="general_options active"><a href="#general-tab"><span>General</span></a></li>
                                <li class="short_description_options d-none"><a href="#short-description-tab"><span>Short Description</span></a></li>
                                <li class="description_options d-none"><a href="#description-tab"><span>Description</span></a></li>
                                <li class="attribute_options" ><a href="#attributes-tab"><span>Attributes</span></a></li>
                                <li class="variation_options"  style="{{$isVariationTab}}"><a href="#variations-tab"><span>Variations</span></a></li>
                            </ul>
                            <div class="tab-contents" id="general-tab">

                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div>Product Code<span class="required"> * </span></div></div>
                                    <div class="col-6 col-lg-8">
                                        <input type="text" id="txtProductCode" class="form-control" placeholder="Product Code" value="<?php if($isEdit){ echo $data->ProductCode;} ?>">
                                        <div class="errors err-sm" id="txtProductCode-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div>SKU<span class="required"> * </span></div></div>
                                    <div class="col-6 col-lg-8">
                                        <input type="text" id="txtSKU" class="form-control" placeholder="SKU" value="{{ $isEdit ? ($data->SKU ?? '') : '' }}">
                                        <div class="errors err-sm" id="txtSKU-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >HSN/SAC</div></div>
                                    <div class="col-6 col-lg-8">
                                        <input type="text" id="txtHSNSAC" class="form-control" placeholder="HSN / SAC" value="<?php if($isEdit){ echo $data->HSNSAC;} ?>">
                                        <div class="errors err-sm" id="txtHSNSAC-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
{{--                                <div class="col-sm-12 mt-20">--}}
{{--                                    <div class="form-group">--}}
{{--                                        <label for="lstPCategoryType"> Product Category Type <span class="required"> * </span>--}}
{{--                                            <span class="addOption" id="btnReloadPCategoryType" title="Reload Product Category Type"><i--}}
{{--                                                    class="fa fa-refresh"></i></span> @if($OtherCruds['PCategoryType']['add']==1)--}}
{{--                                                <span class="addOption" id="btnAddPCategoryType" title="add new product Category Type"><i--}}
{{--                                                        class="fa fa-plus"></i></span>--}}
{{--                                            @endif</label>--}}
{{--                                        <select class="form-control  {{$Theme['input-size']}} select2" id="lstPCategoryType"--}}
{{--                                                data-selected="{{ $isEdit ? ($EditData[0]->PCTID ?? '') : '' }}">--}}
{{--                                            <option value="">Select a Product Category Type</option>--}}
{{--                                        </select>--}}
{{--                                        <div class="errors" id="lstPCategoryType-err"></div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div>Category Type<span class="required"> * </span></div></div>
                                    <div class="col-6 col-lg-8">
                                        <select class="form-control {{$Theme['input-size']}} select2" id="lstCategoryType" data-selected="<?php if($isEdit){ echo $data->CTID;} ?>">
                                            <option value="">Select a Category Type</option>
                                        </select>
                                        <div class="errors err-sm" id="lstCategoryType-err"></div>
                                        <button class="btn btn-outline-dark {{$Theme['button-size']}}" style="display:none" id="btnReloadPCategoryType" title="Reload Category Type" ><i class="fa fa-plus"></i></button>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center">
                                        @if($OtherCruds['CategoryType']['add']==1)  <button class="btn btn-outline-dark {{$Theme['button-size']}}" id="btnAddPCategoryType" title="add new category type" ><i class="fa fa-plus"></i></button> @endif
                                    </div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Category <span class="required"> * </span></div></div>
                                    <div class="col-6 col-lg-8">
                                        <select class="form-control {{$Theme['input-size']}} select2" id="lstCategory" data-selected="<?php if($isEdit){ echo $data->CID;} ?>" data-category-type-id="lstCategoryType">
                                            <option value="">Select a Category</option>
                                        </select>
                                        <div class="errors err-sm" id="lstCategory-err"></div>
                                        <button class="btn btn-outline-dark {{$Theme['button-size']}}" style="display:none" id="btnReloadPCategory" title="Reload Category" ><i class="fa fa-plus"></i></button>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center">
                                        @if($OtherCruds['Category']['add']==1)  <button class="btn btn-outline-dark {{$Theme['button-size']}}" id="btnAddPCategory" title="add new  category" ><i class="fa fa-plus"></i></button> @endif
                                    </div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Sub Category <span class="required"> * </span></div></div>
                                    <div class="col-6 col-lg-8">
                                        <select class="form-control {{$Theme['input-size']}} select2" id="lstSubCategory" data-category-id="lstCategory" data-category-type-id="lstCategoryType" data-selected="<?php if($isEdit){ echo $data->SCID;} ?>">
                                            <option value="">Select a Sub Category</option>
                                        </select>
                                        <div class="errors err-sm" id="lstSubCategory-err"></div>
                                        <button class="btn btn-outline-dark {{$Theme['button-size']}}" style="display:none" id="btnReloadPSubCategory" title="Reload Sub Category" ><i class="fa fa-plus"></i></button>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center">
                                        @if($OtherCruds['SubCategory']['add']==1)  <button class="btn btn-outline-dark {{$Theme['button-size']}}" id="btnAddPSubCategory" title="add new  sub category" ><i class="fa fa-plus"></i></button> @endif
                                    </div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >UOM <span class="required"> * </span></div></div>
                                    <div class="col-6 col-lg-8">
                                        <select class="form-control {{$Theme['input-size']}} select2" id="lstUOM"  data-selected="<?php if($isEdit){ echo $data->UID;} ?>">
                                            <option value="">Select a UOM</option>
                                        </select>
                                        <div class="errors err-sm" id="lstUOM-err"></div>
                                        <button class="btn btn-outline-dark {{$Theme['button-size']}}" style="display:none" id="btnReloadUOM" title="reload UoM" ><i class="fa fa-plus"></i></button>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center">
                                        @if($OtherCruds['uom']['add']==1)  <button class="btn btn-outline-dark {{$Theme['button-size']}}" id="btnAddUOM" title="add new UoM" ><i class="fa fa-plus"></i></button> @endif
                                    </div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Tax  <span class="required"> * </span></div></div>
                                    <div class="col-6 col-lg-8">
                                        <div class="input-group">
                                            <select class="form-control {{$Theme['input-size']}} select2" id="lstTax" data-selected="<?php if($isEdit){ echo $data->TaxID;} ?>">
                                                <option value="">Select a Tax</option>
                                            </select>
                                            <select class="form-control" id="lstTaxType" disabled>
                                                <option value="Include" @if($isEdit) @if($data->TaxType=="Include") selected @endif @endif>Include</option>
                                                <option value="Exclude" {{-- @if($isEdit) @if($data->TaxType=="Exclude") selected @endif @endif --}} >Exclude</option>
                                            </select>
                                        </div>
                                        <div class="errors err-sm" id="lstTax-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center">
                                        <button class="btn btn-outline-dark {{$Theme['button-size']}}" style="display:none" id="btnReloadTax" title="reload tax" ><i class="fa fa-plus"></i></button>
                                        @if($OtherCruds['Tax']['add']==1)  <button class="btn btn-outline-dark {{$Theme['button-size']}}" id="btnAddTax" title="add new tax" ><i class="fa fa-plus"></i></button> @endif
                                    </div>
                                </div>
                                <div class="row mt-20 d-none">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Stages <span class="required"> * </span></div></div>
                                    <div class="col-6 col-lg-8">
                                        <select class="form-control {{$Theme['input-size']}} select2" size=1 id="lstStages" data-selected="@if($isEdit){{json_encode(unserialize($data->Stages))}}@endif" multiple>
                                            <option value="">Select a Stage</option>
                                        </select>
                                        <div class="errors err-sm" id="lstStages-err"></div>
                                    </div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Related Products </div></div>
                                    <div class="col-6 col-lg-8">
                                        <select class="form-control {{$Theme['input-size']}} select2" size=1 id="lstRProducts" multiple>
                                            @if ($isEdit && count($data->RelatedProducts))
                                                @foreach ($data->RelatedProducts as $item)
                                                    <option value="{{$item->ProductID}}" selected>{{$item->ProductName}}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="errors err-sm" id="lstRProducts-err"></div>
                                    </div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Regular Price <span class="required"> * </span></div></div>
                                    <div class="col-6 col-lg-8">
                                        <input type="number" id="txtRegularPrice" class="form-control" min=0 step="{{Helper::NumberSteps($Settings["PRICE-DECIMALS"])}}" placeholder="Regular Price" value="<?php if($isEdit){ echo Helper::NumberFormat($data->PRate,$Settings['PRICE-DECIMALS']);} ?>">
                                        <div class="errors err-sm" id="txtRegularPrice-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Sales Price  <span class="required"> * </span></div></div>
                                    <div class="col-6 col-lg-8">
                                        <input type="number" id="txtSalesPrice" class="form-control" min=0 step="{{Helper::NumberSteps($Settings["PRICE-DECIMALS"])}}" placeholder="Sales Price" value="<?php if($isEdit){ echo Helper::NumberFormat($data->SRate,$Settings['PRICE-DECIMALS']);} ?>">
                                        <div class="errors err-sm" id="txtSalesPrice-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center"></div>
                                </div>
                                <div class="row mt-20 d-none">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Decimal </div></div>
                                    <div class="col-6 col-lg-8">
                                        <select class="form-control" id="lstDecimal">
                                            <option value="auto"  @if($isEdit) @if($data->Decimals=="auto") selected @endif @endif >Default</option>
                                            @for($i=0;$i<=4;$i++)
                                                <option value="{{$i}}"   @if($isEdit) @if($data->Decimals==$i) selected @endif @endif>{{$i}}</option>
                                            @endfor
                                        </select>
                                        <div class="errors err-sm" id="lstDecimal-err"></div>
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center">
                                    </div>
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
                                <div class="row mt-20 d-none">
                                    <div class="col-4 col-lg-2 d-flex align-items-center"><div >Product Video URL</div></div>
                                    <div class="col-6 col-lg-8">
                                        <input type="text" id="txtVideoURL" class="form-control" placeholder="Video URL" value="<?php if($isEdit){ echo $data->VideoURL;} ?>">
                                    </div>
                                    <div class="col-1 col-lg-2 d-flex align-items-center">
                                        <button class="btn btn-outline-danger {{$Theme['button-size']}}" id="btnPlayVideo" title="Play Video" ><i class="fa fa-play"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-contents" id="short-description-tab">
                                <div class="row">
                                    <div class="col-12">
                                        <div id="ShortDescriptionEditor"><?php if($isEdit){ echo $data->ShortDescription;} ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-contents" id="description-tab">
                                <div class="row">
                                    <div class="col-12">
                                        <div id="DescriptionEditor"><?php if($isEdit){ echo $data->Description;} ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-contents product-attributes-tabs p-0" id="attributes-tab" data-status="0">
                                <div class="row justify-content-center mt-20 product-attributes">
                                    <div class="col-12 col-sm-8 col-md-7 col-lg-4">
                                        <select  class="form-control select2" id="lstAttributes">
                                            <option value="">Select a Attributes</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-10 mb-20 product-attributes">
                                    <div class="col-12">
                                        <div class="accordion" id="AttributesAccordin">
                                        </div>
                                    </div>
                                </div>
                                <div class="no-sub-category-found">
                                    <div class="row">
                                        <div class="col-12 text-center"><i class="fa fa-info-circle fs-70" aria-hidden="true"></i></div>
                                        <div class="col-12 mt-10"><div>Select Category and Sub Category in the General tab to add product attributes.</div> </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-contents variation-product-tabs p-0" id="variations-tab" data-status="0">
                                <div class="row justify-content-center mt-20 ">
                                    <div class="col-12 col-sm-8 col-md-7 col-lg-4">
                                        <button id="btnGenerateVariations" data-status="0" class="btn btn-sm btn-outline-primary"></button>
                                    </div>
                                </div>
                                <div class="row mt-10 mb-20">
                                    <div class="col-12">
                                        <div class="accordion accordion-flush" id="variationAccordion">
                                        </div>
                                        <div class="no-attributes-found">
                                            <div class="row">
                                                <div class="col-12 text-center"><i class="fa fa-info-circle fs-70" aria-hidden="true"></i></div>
                                                <div class="col-12 mt-10"><div>Add some attributes in the Attributes tab to generate variations.</div><div>Make sure to check the Used for variations box.</div> </div>
                                            </div>
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
                    <a href="{{url('/')}}/admin/master/product/products" class="btn btn-outline-dark mr-10">Cancel</a>
                    <button type="button" class="btn btn-outline-success mr-10" id="btnSave">Save</button>
                </div>
            </div>
		</div>
		<div class="col-12 col-sm-3 col-md-3 col-lg-3 pt-22">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header"> <h3>Product Image</h3> <span class="options"><span class="trash ProductCoverImageTrash"><i class="fa fa-trash"></i></span></span></div>
                        <div class="card-body product-cover-image ">
                            <div class="row">
                                <div class="col-12">
                                    <input type="file" class="dropify imageScrop" data-product-type="main" data-aspect-ratio="{{$Settings['image-crop-ratio']['w']/$Settings['image-crop-ratio']['h']}}" data-remove="0"  data-is-cover-image="1" id="txtProductImage" data-default-file="<?php if($isEdit){ echo url('/')."/".$data->ProductImage;} ?>"  data-allowed-file-extensions="<?php echo implode(" ",$FileTypes['category']['Images']) ?>" data-max-file-size="{{$Settings['product-cover-image-upload-limit']}}M" >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3>Product Gallery</h3>
                            <span class="options">
                                <span class="add" id="btnAddProductGallery"><i class="fa fa-plus-circle"></i></span>
                            </span>
                            <input type="file" name="" id="txtProductGallery" class="d-none" multiple accept="<?php if(count($FileTypes['category']['Images'])>0) {echo ".".implode(",.",$FileTypes['category']['Images']);} ?>">
                        </div>
                        <div class="card-body">
                            <div class="product_images_container main_product">
                                <ul class="product_images" id="productGalleries">
                                </ul>
                                <div class="no-gallery-images  justify-content-center align-items-center">
                                    <div>Product gallery images not found.</div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row d-none">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header"> <h3>Product Brochure</h3> <span class="options"><span class="trash ProductBrochureTrash"><i class="fa fa-trash"></i></span></span></div>
                        <div class="card-body product-brochure ">
                            <div class="row">
                                <div class="col-12">
                                    <input type="file" id="txtProductBrochure" class="dropify" data-remove="0" accept="<?php if(count($FileTypes['category']['Documents'])>0) {echo ".".implode(",.",$FileTypes['category']['Documents']);} ?>" data-default-file="<?php if($isEdit){ echo url('/')."/".$data->ProductBrochure;} ?>" data-max-file-size="{{$Settings['product-cover-image-upload-limit']}}M">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		</div>
	</div>
    <div class="modal fade" id="videoModal" tabindex="-1" role="dialog" aria-labelledby="videoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="videoModalLabel">Video Player</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="videoPlayer"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="file" data-uuid="" id="txtVariationGallery" class="d-none" multiple accept="<?php if(count($FileTypes['category']['Images'])>0) {echo ".".implode(",.",$FileTypes['category']['Images']);} ?>">
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
            var files = this.files;
            if (files && files.length) {
                $('#ImgCrop').modal('show');
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
        var variationMap={};
        let pImages={
            productImage:{isDeleted:0,data:{}},
            productBrochure:{isDeleted:0,data:{}},
            gallery:{},
            variation:[]
        };
        let uploadXHR={};
        let Attributes={};
        let variationData={}
        let TotalImagesCount=0;
        let deletedImages={
            product:[],
            variation:[]
        };
        var ProductDetails=[];
        const init=async()=>{
            CKEDITOR.replace( 'ShortDescriptionEditor' );
            CKEDITOR.replace( 'DescriptionEditor' );
            showTabs();
            getCategoryType();
            getTax();
            getUOM();
            // getStages();
            @if($isEdit)
                getProductDetails();
            @endif
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
        const loadEditData=async(data)=>{
            if(data.length>0){
                data=data[0];

                for(let item of data.gallery){
                    item.gImage=item.gImage.replace("{{url('/')}}/","");
                    let tdata={
                        uploadPath:item.gImage,
                        fileName:item.fileName,
                        ext:item.ext,

                        referData:{isTemp:0,imgID:item.ImgID,pType:"main-gallery"}
                    }
                    pImages.gallery[item.ImgID]=tdata;
                    await AddProductGalleryImages(item.ImgID,{uploadPath:item.gImage})
                }

                $('#lstAttributes').select2('destroy');
                const loadAttributes=async()=>{
                    return await new Promise( async(resolve,reject)=>{
                        let objKeys=Object.keys(data.Attributes);
                        let q=0;let isFinish=false;

                        while(isFinish==false){
                            try {
                                let AID=objKeys[q];
                                let AValues=data.Attributes[AID];
                                let AName=$('#lstAttributes option[value="'+AID+'"]').attr('data-name');

                                $('#lstAttributes option[value="'+AID+'"]').attr('disabled','disabled');

                                let selectedAValues=[];

                                $.each(AValues.data,(index,Values)=>{
                                    selectedAValues.push(Values.ValueID);
                                });
                                await AddAttributes(AID,AValues.AName,selectedAValues,data.CID,data.SCID,AValues.isVariation);
                                $('#chkUseVariation'+AID).prop('checked',AValues.isVariation);

                                $.each(AValues.data,(index,Values)=>{
                                    Attributes[AID].data.push({ValueID:Values.ValueID,Value:Values.Value});

                                });
                            } catch (e) {
                                console.log(e);
                            }
                            q++;
                            if(q>=objKeys.length){
                                isFinish=true;
                                resolve("completed");
                            }
                        }
                    })
                }
                await loadAttributes();
                $('#lstAttributes').select2();

                for(let variation of data.variation){
                    variationMap[variation.UUID]=variation.Attributes.ValueIDs;
                }
                let variationList=await getVariationList();
                if(Object.keys(variationList).length>0){
                    await generateVariations(variationList)
                }
                for(let variation of data.variation){
                    let uuid=variation.UUID;
                    if (variation.DFlag === 1) {
                        $('#variationAccordion .accordion-item[data-uuid="'+uuid+'"] .variation-trash').trigger('click');
                    }else{
                        let tmp={
                                uploadPath:variation.VImage,
                                fileName:variation.VImageFileName,
                                ext:variation.VImageExt,
                                referData:{isTemp:0}
                            }
                        if(pImages.variation[uuid] === undefined){
                            pImages.variation[uuid]={cover:{url:"",isDeleted:0,data:tmp},gallery:{}};
                        }
                        $('#txtVTtitle-'+uuid).val(variation.Title);
                        $('#txtVSKU-'+uuid).val(variation.SKU);
                        $('#txtVRegularPrice-'+uuid).val(NumberFormat(variation.PRate,'price'));
                        $('#txtVSalesPrice-'+uuid).val(NumberFormat(variation.SRate,'price'));

                        $('#divVImage-'+uuid).html('<input type="file" class="dropify variation-product-img imageScrop txtVImage" data-default-file="{{url("/")}}/'+variation.VImage+'" data-product-type="variation" data-remove="0" data-is-cover-image="1" data-uuid="'+uuid+'" id="txtVImage-'+uuid+'" data-aspect-ratio="{{$Settings['image-crop-ratio']['w']/$Settings['image-crop-ratio']['h']}}" data-remove="0" data-is-cover-image="1"   data-allowed-file-extensions="<?php echo implode(" ",$FileTypes['category']['Images']) ?>" >');
                        $('#txtVImage-'+uuid).dropify();

                        for(let item of variation.gallery){
                            debugger
                            item.gImage=item.gImage.replace("{{url('/')}}/","");
                            let tdata={
                                uploadPath:item.gImage,
                                fileName:item.fileName,
                                ext:item.ext,

                                referData:{isTemp:0,imgID:item.ImgID,pType:"variation-gallery"}
                            }

                            pImages.variation[uuid].gallery[item.ImgID]=tdata;
                            await AddVariationGalleryImages(uuid,item.ImgID,{uploadPath:item.gImage})
                        }
                    }
                }
                // Attributes=data.Attributes
            }
        }
        const getProductDetails=async()=>{
            ProductDetails=await new Promise((resolve,reject)=>{
                let ProductID="<?php if($isEdit){ echo $ProductID;} ?>"
                $.ajax({
                    type:"post",
                    url:"{{url('/')}}/admin/master/product/products/get/product-details/"+ProductID,
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    dataType:"json",
                    async:true,
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);resolve([]);},
                    complete: function(e, x, settings, exception){},
                    success:async function(response){
                        loadEditData(response);
                        resolve(response);
                    }
                });
            });
        }

        $('#lstRProducts').select2({
            placeholder: 'Search Products',
            multiple: true,
            minimumInputLength: 3,
            ajax: {
                url: "{{ url('/') }}/admin/master/product/products/get/product-search",
                type: "post",
                headers: { 'X-CSRF-Token': $('meta[name=_token]').attr('content') },
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        SearchText: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                text: item.ProductName,
                                id: item.ProductID
                            };
                        })
                    };
                },
                cache: true
            }
        });

        const getAttributes=async()=>{
            let PCID=$('#lstCategory').val();
            let PSCID=$('#lstSubCategory').val();
            $('#lstAttributes').select2('destroy');
            $('#lstAttributes option').remove();
            $('#lstAttributes').append('<option value="">Select a Attributes</option>');
            $.ajax({
                type:"post",
                url:"{{url('/')}}/admin/master/product/products/get/attributes",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                data:{PCID,PSCID},
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    let AIDs=Object.keys(Attributes);
                    for(let Item of response){
                        let disabled="";
                        if(AIDs.indexOf(Item.AttrID)>=0){disabled="disabled";}
                        $('#lstAttributes').append('<option '+disabled+' data-name="'+Item.AttrName+'" value="'+Item.AttrID+'">'+Item.AttrName+' </option>');
                    }
                }
            });
            $('#lstAttributes').select2();

        }
        const getAttributeValues=async(AID,PCID=null,PSCID=null)=>{
            let result=await new Promise((resolve,reject)=>{
                PCID=PCID==null?$('#lstCategory').val():PCID;
                PSCID=PSCID==null?$('#lstSubCategory').val():PSCID;
                $.ajax({
                    type:"post",
                    url:"{{url('/')}}/admin/master/product/products/get/attribute-details",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    dataType:"json",
                    data:{PCID,PSCID,AID},
                    async:true,
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);resolve([])},
                    complete: function(e, x, settings, exception){},
                    success:function(response){
                        resolve(response)
                    }
                });
            });
            return result!=undefined?result:[];
        };
        const AddAttributes=async(AID,AName,dataSelected=[],PCID=null,PSCID=null,isVariation=false)=>{
            let AValues=await getAttributeValues(AID,PCID,PSCID);
            let style=$('#lstProductType').val()=="Simple"?"display:none;":"";

            let html='';
                html+='<div class="accordion-item" data-attribute-id="'+AID+'">';
                    html+='<h2 class="accordion-header" data-attribute-id="'+AID+'" id="'+AID+'-heading">';
                        html+='<button class="accordion-button" type="button" data-attribute-id="'+AID+'" data-bs-toggle="collapse" data-bs-target="#panel-'+AID+'" aria-expanded="true" aria-controls="panel-'+AID+'"> '+AName+' <span class="options"> <span class="trash attr-trash" data-attribute-id="'+AID+'"><i class="fa fa-trash"></i></span></span></button>';
                    html+='</h2>';
                    html+='<div id="panel-'+AID+'" class="accordion-collapse collapse show" aria-labelledby="'+AID+'-heading">';
                        html+='<div class="accordion-body">';
                            html+='<div class="row">';
                                html+='<div class="col-12">';
                                    html+='<div class="form-group">';
                                        html+='<select id="lstAValue-'+AID+'" data-attribute-id="'+AID+'" class="form-control select2 lstAValue" multiple>';
                                        for(let AVal of AValues){
                                            let selected=dataSelected.indexOf(AVal.ValueID)>=0?"Selected":"";
                                            let disabled=dataSelected.indexOf(AVal.ValueID)>=0?"disabled":"";
                                            html+='<option '+selected+' '+disabled+' data-value-text="'+AVal.Values+'" value="'+AVal.ValueID+'">'+AVal.Values+' </option>'
                                        }
                                        html+='</select>';
                                    html+='</div>';
                                html+='</div>';
                            html+='</div>';
                            html+='<div class="row mt-15 mb-10 ">';
                                html+='<div class="col-12 text-center d-flex justify-content-center align-items-center">';
                                    html+='<button class="btn btn-sm btn-outline-primary mr-10  btnAttrSelectAll" data-attribute-id="'+AID+'">Select All</button>';
                                    html+='<button class="btn btn-sm btn-outline-primary mr-10 btnAttrDeselectAll" data-attribute-id="'+AID+'">Deselect All</button>';
                                    html+='<div style="margin-top:-10px;'+style+'" class="checkbox checkbox-solid-dark mr-10"><input data-attribute-id="'+AID+'" id="chkUseVariation'+AID+'" type="checkbox" class="checkbox_animated chkUseVariation"><label class="mb-0" for="chkUseVariation'+AID+'">Used For Variations</label></div>';
                                html+='</div>';
                            html+='</div>';
                        html+='</div>';
                    html+='</div>';
                html+='</div>';
                $('#AttributesAccordin').append(html);
                $('#lstAValue-'+AID).select2();

                if(Attributes[AID]==undefined){
                    Attributes[AID]={isVariation,data:[]};
                }
        }
        const AddProductGalleryImages=async(imgID,tdata)=>{
            let html='';
            if($('ul#productGalleries li[data-attachment_id="'+imgID+'"]').length>0){
                    html+='<img src="{{url("/")}}/'+tdata.uploadPath+'">';
                    html+='<div class="actions">';
                        html+='<ul class="actions">';
                            html+='<li><a href="{{url("/")}}/'+tdata.uploadPath+'" data-attachment_id="'+imgID+'" data-lightbox="Product Gallery" class="view" title="view image"><i class="fa fa-eye" aria-hidden="true"></i></a></li>';
                            html+='<li><a href="{{url("/")}}/'+tdata.uploadPath+'" data-attachment_id="'+imgID+'" data-product-type="main-gallery" data-aspect-ratio="{{$Settings["image-crop-ratio"]["w"]/$Settings["image-crop-ratio"]["h"]}}" class="crop" title="crop image"><i class="fa fa-crop" aria-hidden="true"></i></a></li>';
                            html+='<li><a href="#" class="delete" data-product-type="main-gallery" data-attachment_id="'+imgID+'" title="Delete image"><i class="fa fa-trash" aria-hidden="true"></i></a></li>';
                        html+='</ul>';
                    html+='</div>';
                $('ul#productGalleries li[data-attachment_id="'+imgID+'"]').html(html);
            }else{
                html+='<li class="image" data-attachment_id="'+imgID+'" style="cursor: default;">';
                    html+='<img src="{{url("/")}}/'+tdata.uploadPath+'">';
                    html+='<div class="actions">';
                        html+='<ul class="actions">';
                            html+='<li><a href="{{url("/")}}/'+tdata.uploadPath+'" data-attachment_id="'+imgID+'" data-lightbox="Product Gallery" class="view" title="view image"><i class="fa fa-eye" aria-hidden="true"></i></a></li>';
                            html+='<li><a href="{{url("/")}}/'+tdata.uploadPath+'" data-attachment_id="'+imgID+'"  data-product-type="main-gallery" data-aspect-ratio="{{$Settings["image-crop-ratio"]["w"]/$Settings["image-crop-ratio"]["h"]}}" class="crop" title="crop image"><i class="fa fa-crop" aria-hidden="true"></i></a></li>';
                            html+='<li><a href="#" class="delete" data-product-type="main-gallery" data-attachment_id="'+imgID+'" title="Delete image"><i class="fa fa-trash" aria-hidden="true"></i></a></li>';
                        html+='</ul>';
                    html+='</div>';
                html+='</li>';
                $('ul#productGalleries').append(html);
            }
        }
        const AddVariationGalleryImages=async(uuid,imgID,tdata)=>{
            let html='';
            if($('ul#VGalleries-'+uuid+' li[data-attachment_id="'+imgID+'"]').length>0){
                    html+='<img src="{{url("/")}}/'+tdata.uploadPath+'">';
                    html+='<div class="actions">';
                        html+='<ul class="actions">';
                            html+='<li><a href="{{url("/")}}/'+tdata.uploadPath+'" data-uuid="'+uuid+'" data-attachment_id="'+imgID+'" data-lightbox="Product Gallery" class="view" title="view image"><i class="fa fa-eye" aria-hidden="true"></i></a></li>';
                            html+='<li><a href="{{url("/")}}/'+tdata.uploadPath+'" data-uuid="'+uuid+'" data-attachment_id="'+imgID+'" data-product-type="variation-gallery" data-aspect-ratio="{{$Settings["image-crop-ratio"]["w"]/$Settings["image-crop-ratio"]["h"]}}" class="crop" title="crop image"><i class="fa fa-crop" aria-hidden="true"></i></a></li>';
                            html+='<li><a href="#" class="delete" data-uuid="'+uuid+'" data-product-type="variation-gallery" data-attachment_id="'+imgID+'" title="Delete image"><i class="fa fa-trash" aria-hidden="true"></i></a></li>';
                        html+='</ul>';
                    html+='</div>';
                $('ul#VGalleries-'+uuid+' li[data-attachment_id="'+imgID+'"]').html(html);
            }else{
                html+='<li class="image" data-attachment_id="'+imgID+'" style="cursor: default;">';
                    html+='<img src="{{url("/")}}/'+tdata.uploadPath+'">';
                    html+='<div class="actions">';
                        html+='<ul class="actions">';
                            html+='<li><a href="{{url("/")}}/'+tdata.uploadPath+'" data-uuid="'+uuid+'" data-attachment_id="'+imgID+'" data-lightbox="Product Gallery" class="view" title="view image"><i class="fa fa-eye" aria-hidden="true"></i></a></li>';
                            html+='<li><a href="{{url("/")}}/'+tdata.uploadPath+'" data-uuid="'+uuid+'" data-attachment_id="'+imgID+'"  data-product-type="variation-gallery" data-aspect-ratio="{{$Settings["image-crop-ratio"]["w"]/$Settings["image-crop-ratio"]["h"]}}" class="crop" title="crop image"><i class="fa fa-crop" aria-hidden="true"></i></a></li>';
                            html+='<li><a href="#" class="delete" data-product-type="variation-gallery" data-uuid="'+uuid+'" data-attachment_id="'+imgID+'" title="Delete image"><i class="fa fa-trash" aria-hidden="true"></i></a></li>';
                        html+='</ul>';
                    html+='</div>';
                html+='</li>';
                $('ul#VGalleries-'+uuid).append(html);
            }
        }
        const tmpImageUpload=async(formData,id)=>{

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
                xhr: function () {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function (evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = (evt.loaded / evt.total) * 100;
                            if (id) {
                                var loaderWrapper = $('#' + id).closest('.dropify-wrapper');
                                if (Number(percentComplete) < 100) {
                                   loaderWrapper.find('.dropify-loader').css('display','block').text(percentComplete.toFixed(2) + '%');
                                } else {
                                    loaderWrapper.find('.dropify-loader').css('display','none').text("");
                                }
                            }
                        }
                    }, false);
                    return xhr;
                },
                error: function(e, x, settings, exception) {ajaxErrors(e, x, settings, exception);},
                success:function(response){
                    response.referData=JSON.parse(response.referData);
                    response.referData.isTemp=1;
                    let referData=response.referData;

                    if(referData.pType=="main"){
                        if(referData.isBochure == 1){
                            $('#' + response.referData.id).attr('data-remove',0);
                            pImages.productBrochure.data=response;
                            pImages.productBrochure.isDeleted=0;
                        }else if($('#' + response.referData.id).length>0){
                            try {
                                if(pImages.productImage.data.referData.isTemp==0){
                                    TotalImagesCount++;
                                }
                            } catch (error) {
                                TotalImagesCount++;
                            }
                            $('#' + response.referData.id).attr('data-remove',0);
                            $('#' + response.referData.id).parent().find('img').attr('src', "{{url('/')}}/"+response.uploadPath)
                            pImages.productImage.data=response;
                            pImages.productImage.isDeleted=0;
                        }
                    }else if(referData.pType=="main-gallery"){
                        pImages.gallery[referData.imgID]=response;
                        AddProductGalleryImages(referData.imgID,response);
                    }else if(referData.pType=="variation"){
                        if(pImages.variation[referData.uuid]==undefined){
                            pImages.variation[referData.uuid]={cover:{url:"",isDeleted:0,data:{}},gallery:{}};
                        }
                        try {
                            if(pImages.variation[referData.uuid].cover.data.referData.isTemp==0){
                                TotalImagesCount++;
                            }
                        } catch (error) {
                            TotalImagesCount++;
                        }
                            $('#' + response.referData.id).attr('data-remove',0);
                        $('#' + response.referData.id).parent().find('img').attr('src', "{{url('/')}}/"+response.uploadPath)
                        pImages.variation[referData.uuid].cover.data=response;
                    }else if(referData.pType=="variation-gallery"){
                        if(pImages.variation[referData.uuid]==undefined){
                            pImages.variation[referData.uuid]={cover:{url:"",isDeleted:0,data:{}},gallery:{}};
                        }

                        pImages.variation[referData.uuid].gallery[referData.imgID]=response;
                        AddVariationGalleryImages(referData.uuid,referData.imgID,response);

                    }
                }
            });
        }
        const getSelectedAttributeValues=(AID)=>{

            if(Attributes[AID]==undefined){
                Attributes[AID]={isVariation:false,data:[]};
            }
            let data=[];
            $('#lstAValue-'+AID+' option:selected').each(function(index){
                let t={};
                t.ValueID=$(this).attr('value');
                t.Value=$(this).attr('data-value-text');
                data.push(t);
            });

            Attributes[AID].data=data;
        }
        const loadDeteletedVariationData=async(uuid)=>{
            if(ProductDetails.length>0){
                for(let tmpvariation of ProductDetails[0].variation){
                    if(uuid==tmpvariation.UUID){
                        let tmp={
                            uploadPath:tmpvariation.VImage,
                            fileName:tmpvariation.VImageFileName,
                            ext:tmpvariation.VImageExt,
                            referData:{isTemp:0}
                        }
                        if(pImages.variation[uuid]==undefined){
                            pImages.variation[uuid]={cover:{url:"",isDeleted:0,data:tmp},gallery:{}};
                        }
                        $('#txtVTtitle-'+uuid).val(tmpvariation.Title);
                        $('#txtVRegularPrice-'+uuid).val(NumberFormat(tmpvariation.PRate,'price'));
                        $('#txtVSalesPrice-'+uuid).val(NumberFormat(tmpvariation.SRate,'price'));
                        $('#divVImage-'+uuid).html('<input type="file" class="dropify variation-product-img imageScrop txtVImage" data-default-file="{{url("/")}}/'+tmpvariation.VImage+'" data-product-type="variation" data-remove="0" data-is-cover-image="1" data-uuid="'+uuid+'" id="txtVImage-'+uuid+'" data-aspect-ratio="{{$Settings['image-crop-ratio']['w']/$Settings['image-crop-ratio']['h']}}" data-remove="0" data-is-cover-image="1"   data-allowed-file-extensions="<?php echo implode(" ",$FileTypes['category']['Images']) ?>" >');
                        $('#txtVImage-'+uuid).dropify();

                        for(let item of tmpvariation.gallery){
                            item.gImage=item.gImage.replace("{{url('/')}}/","");
                            let tdata={
                                uploadPath:item.gImage,
                                fileName:item.fileName,
                                ext:item.ext,
                                referData:{isTemp:0,imgID:item.ImgID,pType:"variation-gallery"}
                            }
                            pImages.variation[uuid].gallery[item.ImgID]=tdata;
                            await AddVariationGalleryImages(uuid,item.ImgID,{uploadPath:item.gImage})
                        }
                    }
                }
            }
        }
        const generateVariations=async(VariationList)=>{
            let PurchasePrice=$('#txtRegularPrice').val();PurchasePrice=isNaN(parseFloat(PurchasePrice))==false?parseFloat(PurchasePrice):0;
            let SalesPrice=$('#txtSalesPrice').val();SalesPrice=isNaN(parseFloat(SalesPrice))==false?parseFloat(SalesPrice):0;
            let tmpData=[];
            variationData=VariationList;
            $.each(VariationList, function(uuid, data) {
                tmpData.push(uuid);
                let html='';
                let title=$('#txtProductName').val()!=""?$('#txtProductName').val()+" - ":"";
                title+=data.Values.join(" - ");

                if($('#variationAccordion .accordion-item[data-uuid="'+uuid+'"]').length<=0){
                    html+='<div class="accordion-item" data-uuid="'+uuid+'">';
                        html+='<h5 class="accordion-header" id="'+uuid+'-heading">';
                            html+='<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#'+uuid+'" aria-expanded="false" aria-controls="'+uuid+'"> <span class="variation-name">'+data.Values.join(" - ")+'</span><span class="options"> <span class="variation-trash trash" data-uuid="'+uuid+'"><i class="fa fa-trash"></i></span></span> </button>';
                        html+='</h5>';
                        html+='<div id="'+uuid+'" class="accordion-collapse collapse" aria-labelledby="'+uuid+'-heading" data-bs-parent="#variationAccordion">';
                            html+='<div class="accordion-body">';
                                html+='<div class="row">';
                                    html+='<div class="col-12  col-md-6 d-none">';
                                        html+='<div class="row">';
                                            html+='<div class="col-12 text-center">';
                                                html+='<div class="form-group ">';
                                                    html+='<label for="">Variation Image</label>';
                                                    html+='<div id="divVImage-'+uuid+'"><input type="file" class="dropify variation-product-img imageScrop txtVImage" data-product-type="variation" data-remove="0" data-is-cover-image="1" data-uuid="'+uuid+'" id="txtVImage-'+uuid+'" data-aspect-ratio="{{$Settings['image-crop-ratio']['w']/$Settings['image-crop-ratio']['h']}}" data-remove="0" data-is-cover-image="1"   data-allowed-file-extensions="<?php echo implode(" ",$FileTypes['category']['Images']) ?>" ></div>';
                                                html+='</div>';
                                            html+='</div>';
                                        html+='</div>';
                                    html+='</div>';
                                    html+='<div class="col-12 col-md-12">';
                                        html+='<div class="row">';
                                            html+='<div class="col-12 d-none">';
                                                html+='<div class="form-group">';
                                                    html+='<label for="">Variation Title <span class="required"> * </span></label>';
                                                    html+='<input type="text" class="form-control txtVTtitle" data-uuid="'+uuid+'" id="txtVTtitle-'+uuid+'" value="'+title+'">';
                                                    html+='<div class="errors err-sm" id="txtVTtitle-'+uuid+'-err"></div>';
                                                html+='</div>';
                                            html+='</div>';
                                            html+='<div class="col-12">';
                                                html+='<div class="form-group">';
                                                    html+='<label for="">SKU <span class="required"> * </span></label>';
                                                    html+='<input type="text" class="form-control txtVSKU" data-uuid="'+uuid+'" id="txtVSKU-'+uuid+'" value="">';
                                                    html+='<div class="errors err-sm" id="txtVSKU-'+uuid+'-err"></div>';
                                                html+='</div>';
                                            html+='</div>';
                                            html+='<div class="col-12 mt-15">';
                                                html+='<div class="form-group">';
                                                    html+='<label for="">Regular Price <span class="required"> * </span></label>';
                                                    html+='<input type="number" min=0 step="{{Helper::NumberSteps($Settings["PRICE-DECIMALS"])}}" class="form-control txtVRegularPrice" data-uuid="'+uuid+'" id="txtVRegularPrice-'+uuid+'" value="'+PurchasePrice+'">';
                                                    html+='<div class="errors err-sm" id="txtVRegularPrice-'+uuid+'-err"></div>';
                                                html+='</div>';
                                            html+='</div>';
                                            html+='<div class="col-12 mt-15">';
                                                html+='<div class="form-group">';
                                                    html+='<label for="">Sales Price <span class="required"> * </span></label>';
                                                    html+='<input type="number" min=0 step="{{Helper::NumberSteps($Settings["PRICE-DECIMALS"])}}" class="form-control txtVSalesPrice" data-uuid="'+uuid+'"  id="txtVSalesPrice-'+uuid+'" value="'+SalesPrice+'">';
                                                    html+='<div class="errors err-sm" id="txtVSalesPrice-'+uuid+'-err"></div>';
                                                html+='</div>';
                                            html+='</div>';
                                        html+='</div>';
                                    html+='</div>';
                                html+='</div>';
                                html+='<div class="row mt-10 d-none">';
                                    html+='<div class="col-12 text-center fw-600"> Gallery Images</div>';
                                    html+='<div class="col-12 mt-10">';
                                        html+='<div class="product_images_container">';
                                            html+='<ul class="product_images"  data-uuid="'+uuid+'" id="VGalleries-'+uuid+'">';
                                            html+='</ul>';
                                        html+='</div>';
                                    html+='</div>';
                                    html+='<div class="col-12 mt-10 text-center"><button class="btn btn-sm btn-outline-primary addVGalleryImages"  data-uuid="'+uuid+'">Add Gallery Images</button></div>';
                                html+='</div>';
                            html+='</div>';
                        html+='</div>';
                    html+='</div>';
                    $('#variationAccordion').append(html);
                    loadDeteletedVariationData(uuid)
                }else{
                    $('#variationAccordion .accordion-item[data-uuid="'+uuid+'"] .accordion-header button span.variation-name').html(data.Values.join(" - "));

                }
                setTimeout(() => {
                    $('#txtVImage-'+uuid).dropify();
                }, 10);
            });

            $('#variationAccordion .accordion-item').each(function(index){
                let uuid1=$(this).attr('data-uuid');
                if(tmpData.indexOf(uuid1)<0){
                    $('#variationAccordion .accordion-item[data-uuid="'+uuid1+'"]').remove();
                }
            });
        }
        const getVariationList=()=>{
            let variationList=[];

            const generateCombinations=(options, currentCombination) =>{
                if (currentCombination.length === options.length) {
                    variationList.push(currentCombination.join('|'));
                    //output.append('<p>' + currentCombination.join('-') + '</p>');
                } else {
                    $.each(options[currentCombination.length], function(index, value) {

                        generateCombinations(options, currentCombination.concat(value));
                    });
                }
            }
            const generateCombinationsList=(options)=> {
                generateCombinations(options, []);
            }
            const generateTmpAttributes=()=>{

                let tmpAttributes=[];
                $.each(Attributes, function(AID, data) {
                    if(data.isVariation){
                        let t=[];
                        $.each(data.data, function(ValueID, data1) {
                            let tval=data1.ValueID+"___"+data1.Value;
                            t.push(tval)
                        });
                        tmpAttributes.push(t);
                    }
                });
                generateCombinationsList(tmpAttributes);
            }
            const formatVariationList=async()=>{
                let tmpVariationList={};
                for(let item of variationList) {
                    if(item!=""){
                        let tmp=item.split("|");
                        let tValue=[];
                        let ValueIDs=[];
                        let Values=[];
                        for( let t of tmp){
                            if(t!=""){
                                let t1=t.split("___");
                                tValue.push({
                                    ValueID:t1[0],
                                    Value:t1[1],
                                });
                                ValueIDs.push(t1[0]);
                                Values.push(t1[1]);
                            }
                        }

                        const getVariationUUID = async () => {
                            return await new Promise(async (resolve, reject) => {
                                const checkUUID = async () => {
                                    return await new Promise((resolve1, reject1) => {
                                        $.each(variationMap, (uuid1, tdata) => {
                                            if (Array.isArray(tdata)) { // Check if tdata is an array
                                                if (tdata.length === ValueIDs.length && ValueIDs.every(value => tdata.includes(value))) {
                                                    resolve1(uuid1);
                                                }
                                            }
                                        });
                                        resolve1(null);
                                    });
                                }
                                let tmpID = await checkUUID();
                                if (tmpID == null) {
                                    if (ProductDetails.length > 0) {
                                        for (let variation of ProductDetails[0].variation) {
                                            let tmp = [];
                                            tmp.push(variation.Attributes.ValueIDs);
                                            if (Array.isArray(tmp[0]) && tmp[0].length === ValueIDs.length && ValueIDs.every(value => tmp[0].includes(value))) {
                                                variationMap[variation.UUID] = variation.Attributes.ValueIDs;
                                            }
                                        }
                                    }
                                    tmpID = await checkUUID();
                                }
                                tmpID = tmpID != null ? tmpID : generateVariationID();
                                resolve(tmpID);
                            });
                        }

                        let uuid= await getVariationUUID();
                        variationMap[uuid]=ValueIDs;
                        let tdata={
                            UUID:uuid,
                            ValueIDs,
                            Values,
                            VID:"",
                            data:tValue,
                        }
                        tmpVariationList[uuid]=tdata;
                    }
                }
                return tmpVariationList;
            }
            generateTmpAttributes();
            return formatVariationList();
        }

        const getCategoryType=async()=>{
            $('#lstCategoryType').select2('destroy');
            $('#lstCategoryType option').remove();
            $('#lstCategoryType').append('<option value="" selected>Select a Category Type</option>');
            $.ajax({
                type:"post",
                url:"{{url('/')}}/admin/master/product/products/get/category-type",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                dataType:"json",
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    for(let Item of response){
                        let selected="";
                        if(Item.PCTID === $('#lstCategoryType').attr('data-selected')){selected="selected";}
                        $('#lstCategoryType').append('<option '+selected+' value="'+Item.PCTID+'">'+Item.PCTName+' </option>');
                    }
                    if($('#lstCategoryType').val()!=""){
                        $('#lstCategoryType').trigger('change');
                    }
                }
            });
            $('#lstCategoryType').select2();
        };
        const getCategory=async()=>{
            let CTID=$('#lstCategoryType').val();
            $('#lstCategory').select2('destroy');
            $('#lstCategory option').remove();
            $('#lstCategory').append('<option value="" selected>Select a Category</option>');
            $.ajax({
                type:"post",
                url:"{{url('/')}}/admin/master/product/products/get/category",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                data: {CTID},
                dataType:"json",
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    for(let Item of response){
                        let selected="";
                        if(Item.PCID==$('#lstCategory').attr('data-selected')){selected="selected";}
                        $('#lstCategory').append('<option '+selected+' value="'+Item.PCID+'">'+Item.PCName+' </option>');
                    }
                    if($('#lstCategory').val()!=""){
                        $('#lstCategory').trigger('change');
                    }
                }
            });
            $('#lstCategory').select2();
        };
        const getSubCategory=async()=>{
            let CID=$('#lstCategory').val();
            $('#lstSubCategory').select2('destroy');
            $('#lstSubCategory option').remove();
            $('#lstSubCategory').append('<option value="" selected>Select a sub category</option>');
            $.ajax({
                type:"post",
                url:"{{url('/')}}/admin/master/product/products/get/sub-category",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                data:{CID},
                dataType:"json",
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    for(let Item of response){
                        let selected="";
                        if(Item.PSCID==$('#lstSubCategory').attr('data-selected')){selected="selected";}
                        $('#lstSubCategory').append('<option '+selected+' value="'+Item.PSCID+'">'+Item.PSCName+' </option>');
                    }
                    if($('#lstSubCategory').val()!=""){
                        $('#lstSubCategory').trigger('change');
                    }
                }
            });
            $('#lstSubCategory').select2();
        };
        const getTax=async()=>{ console.log(1);
            $('#lstTax').select2('destroy');
            $('#lstTax option').remove();
            $('#lstTax').append('<option value="" selected>Select a Tax</option>');
            $.ajax({
                type:"post",
                url:"{{url('/')}}/admin/master/product/products/get/tax",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    for(let Item of response){
                        let selected="";
                        if(Item.TaxID==$('#lstTax').attr('data-selected')){selected="selected";}
                        $('#lstTax').append('<option '+selected+' data-value="'+Item.TaxPercentage+'" value="'+Item.TaxID+'">'+Item.TaxName+' ( '+NumberFormat(Item.TaxPercentage,"percentage")+'% ) </option>');
                    }
                    if($('#lstTax').val()!=""){
                        $('#lstTax').trigger('click');
                    }
                }
            });
            $('#lstTax').select2();
        };
        {{--const getStages=async()=>{--}}
        {{--    $('#lstStages').select2('destroy');--}}
        {{--    $('#lstStages option').remove();--}}
        {{--    $.ajax({--}}
        {{--        type:"post",--}}
        {{--        url:"{{url('/')}}/admin/master/general/stages/get/stage",--}}
        {{--        headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },--}}
        {{--        dataType:"json",--}}
        {{--        async:true,--}}
        {{--        error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},--}}
        {{--        complete: function(e, x, settings, exception){},--}}
        {{--        success:function(response){--}}
        {{--            let selectedValues = $("#lstStages").attr("data-selected");--}}
        {{--            for(let Item of response){--}}
        {{--                let selected = selectedValues.includes(Item.StageID) ? "selected" : "";--}}
        {{--                $('#lstStages').append('<option '+selected+' value="'+Item.StageID+'">'+Item.StageName+'</option>');--}}
        {{--            }--}}
        {{--            if($('#lstStages').val()!=""){--}}
        {{--                $('#lstStages').trigger('click');--}}
        {{--            }--}}
        {{--        }--}}
        {{--    });--}}
        {{--    $('#lstStages').select2();--}}
        {{--};--}}
        const getUOM=async()=>{
            $('#lstUOM').select2('destroy');
            $('#lstUOM option').remove();
            $('#lstUOM').append('<option value="" selected>Select a UOM</option>');
            $.ajax({
                type:"post",
                url:"{{url('/')}}/admin/master/product/products/get/uom",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    for(let Item of response){
                        let selected="";
                        if(Item.UID===$('#lstUOM').attr('data-selected')){selected="selected";}
                        $('#lstUOM').append('<option '+selected+'  value="'+Item.UID+'">'+Item.UName+' ( '+Item.UCode+') </option>');
                    }
                    if($('#lstUOM').val()!=""){
                        $('#lstUOM').trigger('click');
                    }
                }
            });
            $('#lstUOM').select2();
        };
        const getData=async()=>{
            let vData={};
            $.each(variationData,(uuid,data)=>{
                if($('.accordion-item[data-uuid="'+uuid+'"]').length>0){
                    let images={cover:{url:"",isDeleted:0,data:{}},gallery:{}};
                    if(pImages.variation[uuid] !== undefined){
                        images=pImages.variation[uuid];
                    }
                    data.images=images;
                    data.title=$('#txtVTtitle-'+uuid).val();
                    data.SKU=$('#txtVSKU-'+uuid).val();
                    data.PurchasePrice=$('#txtVRegularPrice-'+uuid).val();
                    data.SalesPrice=$('#txtVSalesPrice-'+uuid).val();
                    vData[uuid]=data;
                }
            });
            let formData={};
            let ProductNameInTranslation = {};

            $('.PNameLanguageFieldsCheck').each(function() {
                let input = $(this);
                let language_code = input.data('language-code');
                ProductNameInTranslation[language_code] = input.val();
            });
            formData.ProductID="<?php if($isEdit){ echo $ProductID;} ?>";
            formData.ProductName=$('#txtProductName').val();
            formData.ProductNameInTranslation=JSON.stringify(ProductNameInTranslation);
            formData.ProductType=$('#lstProductType').val();
            formData.ProductCode=$('#txtProductCode').val();
            // formData.Stages=$('#lstStages').val();
            formData.RelatedProducts=$('#lstRProducts').val();
            formData.VideoURL=$('#txtVideoURL').val();
            formData.SKU=$('#txtSKU').val();
            formData.HSNSAC=$('#txtHSNSAC').val();
            formData.CategoryType=$('#lstCategoryType').val();
            formData.Category=$('#lstCategory').val();
            formData.SubCategory=$('#lstSubCategory').val();
            formData.TaxType=$('#lstTaxType').val();
            formData.TaxID=$('#lstTax').val();
            formData.UID=$('#lstUOM').val();
            // formData.Decimals=$('#lstDecimal').val();
            // formData.TaxPercentage=$('#lstTax option:selected').attr('data-value');
            formData.RegularPrice=$('#txtRegularPrice').val();
            formData.SalesPrice=$('#txtSalesPrice').val();
            formData.ActiveStatus=$('#lstActiveStatus').val();
            formData.Description=CKEDITOR.instances['DescriptionEditor'].getData();
            formData.ShortDescription=CKEDITOR.instances['ShortDescriptionEditor'].getData();
            formData.ProductImage=JSON.stringify(pImages.productImage);
            formData.ProductBrochure=JSON.stringify(pImages.productBrochure);
            formData.gallery=JSON.stringify(pImages.gallery);
            formData.variationData=JSON.stringify(vData);
            formData.Attributes=JSON.stringify(Attributes);

            return formData;
        }
        const formValidation=async(data)=>{
            //
            $('.errors').html('');
            let vData=JSON.parse(data.variationData)
            let status=true;
            let isGeneral=false;
            if(data.ProductName==""){
                $('#txtProductName-err').html('Product Name is required');status=false;
            }else if(data.ProductName.length<2){
                $('#txtProductName-err').html('The Product Name must be greater than 2 characters');status=false;
            }else if(data.ProductName.length>150){
                $('#txtProductName-err').html('The Product Name may not be greater than 150 characters');status=false;
            }

            if(data.SKU === ""){
                $('#txtSKU-err').html('SKU is required');status=false;
            }

            $('.PNameLanguageFieldsCheck').each(function() {
                let input = $(this);
                let value = input.val();
                let languageCode = input.data('language-code');
                let language = input.data('language');

                if (value === "") {
                    $('#txtProductNameIn_' + languageCode + '-err').html('Product Name in ' + language + ' is required.');
                    status = false;
                } else {
                    $('#txtProductNameIn_' + languageCode + '-err').html('');
                }
            });

            if(data.ProductType==""){
                $('#lstProductType-err').html('Product Type is required');status=false;isGeneral=true;
            }
            if(data.CategoryType==""){
                $('#lstCategoryType-err').html('Category Type is required');status=false;isGeneral=true;
            }
            if(data.Category==""){
                $('#lstCategory-err').html('Category is required');status=false;isGeneral=true;
            }
            if(data.SubCategory==""){
                $('#lstSubCategory-err').html('Sub Category is required');status=false;isGeneral=true;
            }
            if(data.Brand==""){
                $('#lstBrand-err').html('Brand is required');status=false;isGeneral=true;
            }
            if(data.TaxType==""){
                $('#lstTaxType-err').html('Tax Type is required');status=false;isGeneral=true;
            }
            if(data.TaxID==""){
                $('#lstTax-err').html('Tax is required');status=false;isGeneral=true;
            }
            if(data.UID==""){
                $('#lstUOM-err').html('Unit of measurement is required');status=false;isGeneral=true;
            }
            // if(data.Decimals==""){
            //     $('#lstDecimal-err').html('Decimal is required');status=false;isGeneral=true;
            // }
            // if(data.Stages.length == 0){
            //     $('#lstStages-err').html('Stages are required');status=false;isGeneral=true;
            // }
            if(data.RelatedProducts.length > 10){
                $('#lstRProducts-err').html('Related Products must be less than or equal to 10');status=false;isGeneral=true;
            }
            if(data.RegularPrice==""){
                $('#txtRegularPrice-err').html('Regular Price is required');status=false;isGeneral=true;
            }else if($.isNumeric(data.RegularPrice)==false){
                $('#txtRegularPrice-err').html('Regular Price is must be numeric value');status=false;isGeneral=true;
            }else if(parseFloat(data.RegularPrice)<0){
                $('#txtRegularPrice-err').html('Regular Price is must be equal or  greater than 0.');status=false;isGeneral=true;
            }
            if(data.SalesPrice==""){
                $('#txtSalesPrice-err').html('Sales Price is required');status=false;isGeneral=true;
            }else if($.isNumeric(data.SalesPrice)==false){
                $('#txtSalesPrice-err').html('Sales Price is must be numeric value');status=false;isGeneral=true;
            }else if(parseFloat(data.SalesPrice)<0){
                $('#txtSalesPrice-err').html('Sales Price is must be equal or  greater than 0.');status=false;isGeneral=true;
            }

            let errorUUID=null;
            if(data.ProductType=="Variable"){
                $.each(vData,(uuid,attributes)=>{
                    let status1=true;
                    if(attributes.title==""){
                        $('#txtVTtitle-'+uuid+'-err').html('Variation Title is required.');status1=false;
                    }else if(attributes.title.length<2){
                        $('#txtVTtitle-'+uuid+'-err').html('The Variation Title must be greater than 2 characters');status1=false;
                    }else if(attributes.title.length>150){
                        $('#txtVTtitle-'+uuid+'-err').html('The Variation Title may not be greater than 150 characters');status1=false;
                    }
                    if(attributes.SKU==""){
                        $('#txtVSKU-'+uuid+'-err').html('SKU is required');status1=false;
                    }
                    if(attributes.PurchasePrice==""){
                        $('#txtVRegularPrice-'+uuid+'-err').html('Regular Price is required');status1=false;
                    }else if($.isNumeric(attributes.PurchasePrice)==false){
                        $('#txtVRegularPrice-'+uuid+'-err').html('Regular Price is must be numeric value');status1=false;
                    }else if(parseFloat(attributes.PurchasePrice)<0){
                        $('#txtVRegularPrice-'+uuid+'-err').html('Regular Price is must be equal or  greater than 0.');status1=false;
                    }
                    if(attributes.SalesPrice==""){
                        $('#txtVSalesPrice-'+uuid+'-err').html('Sales Price is required');status1=false;
                    }else if($.isNumeric(attributes.SalesPrice)==false){
                        $('#txtVSalesPrice-'+uuid+'-err').html('Sales Price is must be numeric value');status1=false;
                    }else if(parseFloat(attributes.SalesPrice)<0){
                        $('#txtVSalesPrice-'+uuid+'-err').html('Sales Price is must be equal or  greater than 0.');status1=false;
                    }
                    status=status1==false?status1:status;
                    errorUUID=(status1==false && errorUUID==null)?uuid:errorUUID;
                });
            }
            if(isGeneral==true && status==false){
                if($('.woo-commerce-style ul.woo-commerce-style li.general_options').hasClass('active')==false){
                    $('.woo-commerce-style ul.woo-commerce-style li').removeClass('active');
                    $('.woo-commerce-style ul.woo-commerce-style li.general_options').addClass('active');
                    showTabs();
                }
            }else if(isGeneral==false && errorUUID!=null && status==false && data.ProductType=="Variable"){

                if($('.woo-commerce-style ul.woo-commerce-style li.variation_options').hasClass('active')==false){
                    $('.woo-commerce-style ul.woo-commerce-style li').removeClass('active');
                    $('.woo-commerce-style ul.woo-commerce-style li.variation_options').addClass('active');
                    showTabs();
                }
                if( $('#'+errorUUID+'-heading button').hasClass('collapsed')){

                    $('#'+errorUUID+'-heading button').trigger('click');
                }

            }
            if(status == false){$("html, body").animate({ scrollTop: 0 }, "slow");}
            return status;
        }
        const save=async(formData)=>{
            //ajaxIndicatorStart("Please wait Product creating...");
            let postUrl= @if($isEdit) "{{url('/')}}/admin/master/product/products/edit/{{$ProductID}}"; @else "{{url('/')}}/admin/master/product/products/create"; @endif
            var vData=JSON.parse(formData.variationData)
            var fdata=formData;
                delete formData.variationData;
                fdata.saveType="main"
            let ajaxSubmit=[];
            const variationCount=Object.keys(vData).length;
            let variationCompleted=0;
            let VariationSaveStatus=true;
            let ProductID="";
            const confirmation=async()=>{
                if(VariationSaveStatus){
                    let eventSource=null;
                    $.ajax({
                        type:"post",
                        url:postUrl,
                        headers: { 'X-CSRF-Token' : "{{ csrf_token() }}" },
                        data:{TotalImagesCount:TotalImagesCount,ProductID:ProductID,ProductType:"Variable",saveType:"confirm",deletedImages:JSON.stringify(deletedImages)},
                        dataType:"json",
                        async:true,
                        error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                        beforeSend:async()=>{
                            setTimeout(() => {
                                eventSource = new EventSource("{{url('/')}}/admin/master/product/products/get/process-status");
                                eventSource.onmessage = function (event) {
                                    let data = JSON.parse(event.data);
                                    setTimeout(() => {
                                        $('#divProcessText').html( data+'% completed.<br> Please wait product saving process on going.');
                                    }, 10);
                                };
                                eventSource.onerror = function (error) {
                                    eventSource.close();
                                };
                            }, 0);
                        },
                        complete:async(e, x, settings, exception)=>{ajaxIndicatorStop(); if(eventSource!=null){eventSource.close();}},
                        success:function(response){
                            if(response.status){
                                swal({
                                    title: "SUCCESS",
                                    text: response.message,
                                    type: "success",
                                    showCancelButton: false,
                                    confirmButtonClass: "btn-outline-success",
                                    confirmButtonText: "Okay",
                                    closeOnConfirm: false
                                },function(){
                                    @if($isEdit)
                                        window.location.replace("{{url('/')}}/admin/master/product/products");
                                    @else
                                        window.location.reload();
                                    @endif
                                });
                            }else{
                                toastr.error(response.message, "Failed", {positionClass: "toast-top-right",containerId: "toast-top-right",showMethod: "slideDown",hideMethod: "slideUp",progressBar: !0})
                            }
                        }
                    });
                }else{
                    toastr.error("Product save failed.", "Failed", {positionClass: "toast-top-right",containerId: "toast-top-right",showMethod: "slideDown",hideMethod: "slideUp",progressBar: !0})
                    ajaxIndicatorStop();
                }
            }
            const uploadComplete=async(e, x, settings, exception)=>{
                variationCompleted++;
                if(variationCount<=variationCompleted){
                    $('#divProcessText').html( '0% completed.<br> Please wait product saving process on going');
                    confirmation();
                }else{
                    let percentage=(100*variationCompleted)/variationCount;
                    $('#divProcessText').html(parseInt(percentage).toString() + '% completed.<br> Please wait product variation saving process on going');
                }
            }
            const saveVariation=async()=>{
                $.each(vData,(uuid,data)=>{
                    data.ProductType="Variable";
                    data.saveType="Variable";
                    data.ProductID=ProductID;
                    data.ValueIDs=JSON.stringify(data.ValueIDs)
                    data.Values=JSON.stringify(data.Values)
                    data.data=JSON.stringify(data.data)
                    data.images=JSON.stringify(data.images)
                    $.ajax({
                        type:"post",
                        url:postUrl,
                        headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                        data:data,
                        async:true,
                        error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);VariationSaveStatus=false;},
                        complete:uploadComplete,
                        success:function(response){
                            if(response.status==false){
                                VariationSaveStatus=false;
                            }
                        }
                    });
                });
            }
            const saveProduct=async()=>{
                $.ajax({
                    type:"post",
                    url:postUrl,
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:fdata,
                    beforeSend: function() {
                        ajaxIndicatorStart("Please wait Product is saving...");
                    },
                    error:function(e, x, settings, exception){ajaxIndicatorStop();ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnSave'));$("html, body").animate({ scrollTop: 0 }, "slow");},
                    success:function(response){
                        if(response.status==true){
                            ProductID=response.ProductID
                            setTimeout(() => {
                            $('#divProcessText').html("Please wait variation save process on initiating.");
                                setTimeout(() => {
                                    if(variationCount>0){
                                        saveVariation();
                                    }else{
                                        confirmation();
                                    }

                                }, 100);
                            }, 100);

                        }else{
                            ajaxIndicatorStop();
                            toastr.error(response.message, "Failed", {positionClass: "toast-top-right",containerId: "toast-top-right",showMethod: "slideDown",hideMethod: "slideUp",progressBar: !0})
                            if(response['errors']!=undefined){
                                $('.errors').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="ProductName"){$('#txtProductName-err').html(KeyValue);}
                                    if(key=="ProductType"){$('#lstProductType-err').html(KeyValue);}
                                    if(key=="ProductCode"){$('#txtProductCode-err').html(KeyValue);}
                                    if(key=="SKU"){$('#txtSKU-err').html(KeyValue);}
                                    if(key=="HSNSAC"){$('#txtHSNSAC-err').html(KeyValue);}
                                    if(key=="CategoryType"){$('#txtCategoryType-err').html(KeyValue);}
                                    if(key=="Category"){$('#txtCategory-err').html(KeyValue);}
                                    if(key=="SubCategory"){$('#lstSubCategory-err').html(KeyValue);}
                                    if(key=="TaxType"){$('#lstTaxType-err').html(KeyValue);}
                                    if(key=="TaxID"){$('#lstTax-err').html(KeyValue);}
                                    if(key=="UID"){$('#lstUOM-err').html(KeyValue);}
                                    // if(key=="Decimals"){$('#lstDecimal-err').html(KeyValue);}
                                    if(key=="RegularPrice"){$('#txtRegularPrice-err').html(KeyValue);}
                                    if(key=="SalesPrice"){$('#txtSalesPrice-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                });
            }
            saveProduct();
        }
        init();

        $(document).on('click','.woo-commerce-style ul.woo-commerce-style a',function(e){
            e.preventDefault();
            $('.woo-commerce-style ul.woo-commerce-style li').removeClass('active');
            $(this).parent().addClass('active');
            showTabs();
        });
        $(document).on('click','#btnAddProductGallery',function(){
            $('#txtProductGallery').trigger('click');
        });
        $(document).on('change','#txtProductGallery',async function(){
            var files = this.files;
            TotalImagesCount+=files.length;
            let maxSize = {{$Settings['product-gallery-image-upload-limit']}} * 1024 * 1024;

            for(let file of files){
                if(file.size <= maxSize){
                    let referData={};
                    referData.imgID= await generateUUID();
                    referData.pType= "main-gallery";
                    let formData=new FormData();
                    formData.append('referData',JSON.stringify(referData));
                    formData.append('image',file);
                    tmpImageUpload(formData,"");
                }else{
                    toastr.error("Images with file size above {{$Settings['product-gallery-image-upload-limit']}} MB are not uploaded", "Failed", {positionClass: "toast-top-right",containerId: "toast-top-right",showMethod: "slideDown",hideMethod: "slideUp",progressBar: !0})
                }
            }
            $('#txtProductGallery').val("");
        });
        $(document).on('click','.actions a.delete',function(e){
            e.preventDefault();
            let aid=$(this).attr('data-attachment_id');
            let PType=$(this).attr('data-product-type');
            let uuid=$(this).attr('data-uuid');
            $('li[data-attachment_id="'+aid+'"]').remove();
            if(['main-gallery','variation-gallery'].indexOf(PType)>=0){
                $('li[data-attachment_id="'+aid+'"]').remove();
                if(PType=="main-gallery"){
                    if (pImages.gallery.hasOwnProperty(aid)) {
                        deletedImages.product.push(aid);
                        delete pImages.gallery[aid];
                    }
                }else if(PType=="variation-gallery"){
                    if(pImages.variation[uuid]!=undefined){
                        if (pImages.variation[uuid].gallery.hasOwnProperty(aid)) {
                            deletedImages.variation.push({ImgID:aid,uuid});
                            delete pImages.variation[uuid].gallery[aid];
                        }
                    }
                }
            }
        });
        $(document).on('change','#lstAttributes',async function(){
            let AID=$('#lstAttributes').val();
            let AName=$('#lstAttributes option:selected').attr('data-name');
            if(AID!=""){
                $('#lstAttributes').select2('destroy');
                $('#lstAttributes option[value="'+AID+'"]').attr('disabled','disabled');
                $('#lstAttributes').val("").trigger("change")
                $('#lstAttributes').select2();
                AddAttributes(AID,AName)
            }
        });
        $(document).on('click','.btnAttrSelectAll',function(){
            let AID=$(this).attr('data-attribute-id');
            $('#lstAValue-'+AID).select2('destroy');
            $('#lstAValue-'+AID+' option').attr('selected','selected');
            $('#lstAValue-'+AID).select2();
            getSelectedAttributeValues(AID);
        });
        $(document).on('click','.btnAttrDeselectAll',function(){
            let AID=$(this).attr('data-attribute-id');
            $('#lstAValue-'+AID).select2('destroy');
            $('#lstAValue-'+AID+' option').removeAttr('selected');
            $('#lstAValue-'+AID).select2();
            getSelectedAttributeValues(AID);
        });
        $(document).on('click','.attr-trash',function(){
            let AID=$(this).attr('data-attribute-id')
            $('#lstAttributes').select2('destroy');
            $('#AttributesAccordin .accordion-item[data-attribute-id="'+AID+'"]').remove();
            $('#lstAttributes option[value="'+AID+'"]').removeAttr('disabled');
            $('#lstAttributes').select2();
            delete Attributes[AID];
        });
        $(document).on('change','.lstAValue',function(){
            let AID=$(this).attr('data-attribute-id');
            getSelectedAttributeValues(AID);
        });
        $(document).on('click','.chkUseVariation',function(){
            let AID=$(this).attr('data-attribute-id');

            if(Attributes[AID]==undefined){
                Attributes[AID]={isVariation:false,data:[]};
            }
            Attributes[AID].isVariation=$(this).prop('checked')
        });
        $(document).on('click','#btnGenerateVariations',async function(){
            let variationList=await getVariationList();
            if(Object.keys(variationList).length>0){
                generateVariations(variationList)
            }

            $('#btnGenerateVariations').attr('data-status',1)
        });
        $(document).on('click','.addVGalleryImages',function(){
            let uuid=$(this).attr('data-uuid');

            $('#txtVariationGallery').attr('data-uuid',uuid);
            $('#txtVariationGallery').trigger('click');

        });
        $(document).on('change','#txtVariationGallery',async function(){
            let uuid=$('#txtVariationGallery').attr('data-uuid');
            var files = this.files;
            TotalImagesCount+=files.length;
            for(let file of files){
                let referData={};
                referData.imgID= await generateUUID();
                referData.pType= "variation-gallery";
                referData.uuid= uuid;
                let formData=new FormData();
                formData.append('referData',JSON.stringify(referData));
                formData.append('image',file);
                tmpImageUpload(formData,"");
            }

            $('#txtVariationGallery').attr('data-uuid',"");
            $('#txtVariationGallery').val("");
        });
        $(document).on('click','.variation-trash',function(){
            let uuid=$(this).attr('data-uuid')
            $('#variationAccordion .accordion-item[data-uuid="'+uuid+'"]').remove();
            delete variationMap[uuid];
        });
        $(document).on('change','#lstProductType',function(){
            let pType=$('#lstProductType').val();
            if(pType=="Variable"){
                $('ul.woo-commerce-style li.variation_options').show();
                $('.chkUseVariation').parent().show();
            }else{
                $('ul.woo-commerce-style li.variation_options').hide();
                $('ul.woo-commerce-style li.variation_options').removeClass('active');
                $('.woo-commerce-style #variations-tab').hide();
                $('.chkUseVariation').parent().hide();
            }
        });
        $(document).on('change','#lstCategoryType',function(){
            $('#lstMPCategoryType-err').html('');
            if($('#lstCategoryType').val() === ""){
                $('#lstCategoryType-err').html('Category Type is required');
            }
            getCategory();
        });
        $(document).on('change','#lstCategory',function(){
            $('#lstCategory-err').html('');
            if($('#lstCategory').val()==""){
                $('#lstCategory-err').html('Category is required');
            }
            getSubCategory();
        });
        $(document).on('keyup','.txtVTtitle',function(){
            let uuid=$(this).attr('data-uuid');
            let ProductName=$(this).val();
            $('#txtVTtitle-err').html('');
            if(ProductName==""){
                $('#txtVTtitle-'+uuid+'-err').html('Variation Title is required.');
            }else if(ProductName.length<2){
                $('#txtVTtitle-'+uuid+'-err').html('The Variation Title must be greater than 2 characters');
            }else if(ProductName.length>150){
                $('#txtVTtitle-'+uuid+'-err').html('The Variation Title may not be greater than 150 characters');
            }
        });
        $(document).on('keyup','.txtVRegularPrice',function(){
            let uuid=$(this).attr('data-uuid');
            let price=$(this).val();
            $('#txtVRegularPrice-'+uuid+'-err').html('');
            if(price==""){
                $('#txtVRegularPrice-'+uuid+'-err').html('Regular Price is required');
            }else if($.isNumeric(price)==false){
                $('#txtVRegularPrice-'+uuid+'-err').html('Regular Price is must be numeric value');
            }else if(parseFloat(price)<0){
                $('#txtVRegularPrice-'+uuid+'-err').html('Regular Price is must be equal or  greater than 0.');
            }
        });
        $(document).on('keyup','.txtVSalesPrice',function(){
            let uuid=$(this).attr('data-uuid');
            let price=$(this).val();
            $('#txtVSalesPrice-'+uuid+'-err').html('');
            if(price==""){
                $('#txtVSalesPrice-'+uuid+'-err').html('Sales Price is required');
            }else if($.isNumeric(price)==false){
                $('#txtVSalesPrice-'+uuid+'-err').html('Sales Price is must be numeric value');
            }else if(parseFloat(price)<0){
                $('#txtVSalesPrice-'+uuid+'-err').html('Sales Price is must be equal or  greater than 0.');
            }
        });
        $(document).on('keyup','#txtRegularPrice',function(){
            let price=$(this).val();
            $('#txtRegularPrice-err').html('');
            if(price==""){
                $('#txtRegularPrice-err').html('Regular Price is required');
            }else if($.isNumeric(price)==false){
                $('#txtRegularPrice-err').html('Regular Price is must be numeric value');
            }else if(parseFloat(price)<0){
                $('#txtRegularPrice-err').html('Regular Price is must be equal or  greater than 0.');
            }
        });
        $(document).on('keyup','#txtSalesPrice',function(){
            let price=$(this).val();
            $('#txtSalesPrice-err').html('');
            if(price==""){
                $('#txtSalesPrice-err').html('Sales Price is required');
            }else if($.isNumeric(price)==false){
                $('#txtSalesPrice-err').html('Sales Price is must be numeric value');
            }else if(parseFloat(price)<0){
                $('#txtSalesPrice-err').html('Sales Price is must be equal or  greater than 0.');
            }
        });
        $(document).on('change','#lstCategory',function(){

        });
        $(document).on('change','#lstSubCategory',function(){
            $('#lstSubCategory-err').html('');
            if($('#lstSubCategory').val()==""){
                $('#lstSubCategory-err').html('Sub Category is required');
            }
            getAttributes();
        });
        $(document).on('change','#lstBrand',function(){
            $('#lstBrand-err').html('');
            if($('#lstBrand').val()==""){
                $('#lstBrand-err').html('Brand is required');
            }
        });
        $(document).on('change','#lstTax',function(){
            $('#lstTax-err').html('');
            if($('#lstTax').val()==""){
                $('#lstTax-err').html('Tax is required');
            }
        });
        $(document).on('change','#lstUOM',function(){
            $('#lstUOM-err').html('');
            if($('#lstUOM').val()==""){
                $('#lstUOM-err').html('Unit of measurement is required');
            }
        });
        $(document).on('keyup','#txtProductName',function(){
            let ProductName=$(this).val();
            $('#txtProductName-err').html('');
            if(ProductName==""){
                $('#txtProductName-err').html('Product  name is required.');status=false;
            }else if(ProductName.length<2){
                $('#txtProductName-err').html('The Product Name must be greater than 2 characters');status=false;
            }else if(ProductName.length>150){
                $('#txtProductName-err').html('The Product Name may not be greater than 150 characters');status=false;
            }
        });
        $(document).on('click','#btnSave',async function(){
            let formData=await getData();
            let status = false;
            let status1 = true;
            $(".dropify-loader").each(function () {
                if ($(this).css('display') === 'block') {
                    toastr.error("Please wait. Files are uploading", "Failed", {positionClass: "toast-top-right",containerId: "toast-top-right",showMethod: "slideDown",hideMethod: "slideUp",progressBar: !0})
                    status1 = false;
                    // if(status1 == false){$("html, body").animate({ scrollTop: 0 }, "slow");}
                    return false;
                }
            });
            if(status1){
                status=await formValidation(formData);
            }
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
        $(document).on('click','.product-cover-image .dropify-clear',function(){
            $('.product-cover-image input.dropify').attr('data-remove',1);
            pImages.productImage={isDeleted:1,data:{}}
        });
        $(document).on('click','.product-brochure .dropify-clear',function(){
            $('.product-brochure input.dropify').attr('data-remove',1);
            pImages.productBrochure={isDeleted:1,data:{}}
        });
        $(document).on('click','.ProductCoverImageTrash',function(){
            $('.product-cover-image .dropify-clear').trigger('click');
        });
        $(document).on('click','.ProductBrochureTrash',function(){
            $('.product-brochure .dropify-clear').trigger('click');
        });
        $(document).on('click','.variation-product-tabs .dropify-clear',function(){
            let uuid=$(this).parent().find('input.dropify').attr('data-uuid')
            $(this).parent().find('input.dropify').attr('data-remove',1);
            if(pImages.variation[uuid]!=undefined){
                pImages.variation[uuid]={cover:{url:"",isDeleted:1,data:{}},gallery:{}};
            }
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
                    tmpImageUpload(formData,"txtProductImage");
                $('#ImgCrop').modal('hide');
                setTimeout(() => {
                    btnReset($('#btnCropApply'));
                }, 100);
            }, 100);
        });

        $('#btnPlayVideo').on('click', function() {
            var Url = $('#txtVideoURL').val();
            var embedUrl = Url;

            if(isYouTubeUrl(Url)){
                var videoId = extractYouTubeVideoId(Url);
                embedUrl = 'https://www.youtube.com/embed/' + videoId;
            }

            var videoPlayerHtml = '<iframe width="100%" height="400" src="' + embedUrl + '" frameborder="0" allowfullscreen></iframe>';

            bootbox.dialog({
                title: 'Video Player',
                message: videoPlayerHtml,
                className: 'video-modal',
                closeButton: true,
            }).find('.modal-dialog').css('--bs-modal-width', '900px');
        });

        function extractYouTubeVideoId(url) {
            var videoIdMatch = url.match(/[?&]v=([^&]+)/);
            return videoIdMatch ? videoIdMatch[1] : null;
        }
        function isYouTubeUrl(url) {
            return /^(https?:\/\/)?(www\.)?(youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i.test(url);
        }

        $(document).on('change', '#txtProductBrochure', async function () {
            var file = this.files[0];
            if (file) {
                let referData={};
                referData.isBochure=1;
                referData.id="txtProductBrochure";
                referData.pType="main";
                let formData=new FormData();
                formData.append('referData',JSON.stringify(referData));
                formData.append('image',file);
                tmpImageUpload(formData, "txtProductBrochure");
            }
        });

    });
</script>
@endsection
