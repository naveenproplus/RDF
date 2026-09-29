@extends('layouts.app')
@section('content')
<style>
    .addOption{
        display:none !important;
    }
</style>
<div class="container-fluid">
	<div class="page-header">
		<div class="row">
			<div class="col-sm-12">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="f-16 fa fa-home"></i></a></li>
					<li class="breadcrumb-item">Users & Permissions</li>
					<li class="breadcrumb-item"><a href="{{ url('/') }}/admin/users-and-permissions/users">{{$PageTitle}}</a></li>
					<li class="breadcrumb-item">@if($isEdit) Update @else Create @endif</li>
				</ol>
			</div>
		</div>
	</div>
</div>
<div class="container-fluid mt-40">
	<div class="row d-flex justify-content-center">
		<div class="col-sm-8">
			<div class="row">
				<div class="col-sm-12">
					<div class="card">
						<div class="card-header text-center">
							<div class="form-row align-items-center">
								<div class="col-sm-4">	</div>
								<div class="col-sm-4 my-2">
									<h5>{{$PageTitle}}</h5>
								</div>
								<div class="col-sm-4 my-2 text-right text-md-right"></div>
							</div>
						</div>
						<div class="card-body">
                            <div class="row  d-flex justify-content-center">
                                <div class="col-sm-4">
                                    <input type="file" id="txtProfileImage" class="dropify imageScrop" data-aspect-ratio="{{$Settings['profile-image-crop-ratio']['w']/$Settings['profile-image-crop-ratio']['h']}}" data-remove="0" data-is-profile-image="1" data-max-file-size="{{$Settings['upload-limit']}}" data-default-file="<?php if($isEdit==true){if($EditData[0]->ProfileImage !=""){ echo url('/')."/".$EditData[0]->ProfileImage;}}?>"  data-allowed-file-extensions="jpeg jpg png gif bmp webp" />
                                    <span class="errors" id="txtProfileImage-err"></span>
                                </div>
                            </div>
                            <div class="row mt-10">
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="txtFirstName">First Name <span class="required">*</span></label>
                                        <input type="text" id="txtFirstName" class="form-control  {{$Theme['input-size']}}" placeholder="First Name" value="<?php if($isEdit==true){ echo $EditData[0]->FirstName;} ?>" autofocus>
                                        <span class="errors err-sm" id="txtFirstName-err"></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="txtLastName">Last Name </label>
                                        <input type="text" id="txtLastName" class="form-control  {{$Theme['input-size']}} " placeholder="Last Name" value="<?php if($isEdit==true){ echo $EditData[0]->LastName;} ?>">
                                        <span class="errors  err-sm" id="txtLastName-err"></span>
                                    </div>
                                </div>
                                <div class="col-sm-12 mt-20">
                                    <div class="form-group">
                                        <label for="txtAddress">Address </label>
                                        <textarea class="form-control  {{$Theme['input-size']}}" placeholder="Address" id="txtAddress" name="Address" rows="3" ><?php if($isEdit==true){ echo $EditData[0]->Address;} ?></textarea>
                                        <span class="errors err-sm" id="txtAddress-err"></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="lstPostalCode">Postal Code <span class="required">*</span> </label>
                                        <div class="input-group">
                                            <input type="text" id="txtPostalCode" class="form-control" placeholder="Postal Code" value="<?php if($isEdit){ echo $EditData[0]->PostalCode;} ?>">
                                            <button type="button" class="btn btn-outline-dark" id="btnSearchPostalCode">Search <i class="fa fa-search"></i></button>
                                        </div>
                                        <span class="errors err-sm" id="lstPostalCode-err"></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="lstCity">City <span class="required">*</span> </label>
                                        <select class="form-control  {{$Theme['input-size']}} select2" id="lstCity" data-selected="<?php if($isEdit){ echo $EditData[0]->CityID;} ?>">
                                            <option value="">Select a City</option>
                                        </select>
                                        <span class="errors err-sm" id="lstCity-err"></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="lstTaluk">Taluk <span class="required">*</span> </label>
                                        <select class="form-control  {{$Theme['input-size']}} select2" id="lstTaluk" data-selected="<?php if($isEdit){ echo $EditData[0]->TalukID;} ?>">
                                            <option value="">Select a City</option>
                                        </select>
                                        <span class="errors err-sm" id="lstTaluk-err"></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="lstDistricts">District <span class="required">*</span> </label>
                                        <select class="form-control  {{$Theme['input-size']}} select2" id="lstDistricts"  data-selected="<?php if($isEdit){ echo $EditData[0]->DistrictID;} ?>">
                                            <option value="">Select a City</option>
                                        </select>
                                        <span class="errors err-sm" id="lstDistricts-err"></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="lstState">State <span class="required">*</span> </label>
                                        <select class="form-control  {{$Theme['input-size']}} select2" id="lstState"   data-selected="<?php if($isEdit){echo $EditData[0]->StateID;} ?>">
                                            <option value="">Select a State</option>
                                        </select>
                                        <span class="errors err-sm" id="lstState-err"></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="lstCountry">Country <span class="required">*</span> </label>
                                        <select class="form-control  {{$Theme['input-size']}} select2" id="lstCountry" data-selected="<?php if($isEdit){echo $EditData[0]->CountryID;} ?>">
                                            <option value="">Select a Country</option>
                                        </select>
                                        <span class="errors err-sm" id="lstCountry-err"></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="lstGender">Gender <span class="required">*</span> </label>
                                        <select class="form-control  {{$Theme['input-size']}} select2" id="lstGender" data-selected="<?php if($isEdit){ echo $EditData[0]->GenderID;} ?>">
                                            <option value="">Select a Gender</option>
                                        </select>
                                        <span class="errors err-sm" id="lstGender-err"></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="lstUserRole">Role <span class="required">*</span> </label>
                                        <select class="form-control  {{$Theme['input-size']}} select2" id="lstUserRole" data-selected="<?php if($isEdit){ echo $EditData[0]->RoleID;} ?>">
                                            <option value="">Select a Role</option>
                                        </select>
                                        <span class="errors err-sm" id="lstUserRole-err"></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="txtEmail">Email <span class="fs-10 fw-500" style="color:#ab9898">(User Name)</span> <span class="required">*</span></label>
                                        <input type="email"  id="txtEmail" class="form-control  {{$Theme['input-size']}}" placeholder="E-Mail" value="<?php if($isEdit==true){ echo $EditData[0]->EMail;} ?>">
                                        <span class="errors err-sm" id="txtEmail-err"></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="txtMobileNumber">Mobile Number <span class="required">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text" id="CallCode">+91</span>
                                            <input type="number" @if($isEdit) disabled @endif id="txtMobileNumber" class="form-control  {{$Theme['input-size']}}" data-length="0" placeholder="Mobile Number enter without country code"  value="<?php if($isEdit==true){ echo $EditData[0]->MobileNumber;} ?>">
                                        </div>
                                        <span class="errors err-sm" id="txtMobileNumber-err"></span>
                                    </div>
                                </div>
                                @if($isEdit==false)
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="txtPassword">Password <span class="required">*</span></label>
                                        <div class="input-group">
                                            <input type="password" id="txtPassword" class="form-control  {{$Theme['input-size']}}" placeholder="Password" value="">
                                            <button class="btn btn-outline-light" data-is-show=0 id="btnShowPassword" type="button"><i class="fa fa-eye"></i> </button>
                                        </div>

                                        <span class="errors err-sm" id="txtPassword-err"></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="txtConfirmPassword">Confirm Password <span class="required">*</span></label>
                                        <div class="input-group">
                                            <input type="password" id="txtConfirmPassword" class="form-control  {{$Theme['input-size']}}" placeholder="Confirm Password" value="">
                                            <button class="btn btn-outline-light" data-is-show=0 id="btnShowConfirmPassword" type="button"><i class="fa fa-eye"></i> </button>
                                        </div>
                                        <span class="errors err-sm" id="txtConfirmPassword-err"></span>
                                    </div>
                                </div>
                                @endif
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="">Login Status</label>
                                        <select class="form-control  {{$Theme['input-size']}} select2" id="lstLoginStatus" data-minimum-results-for-search="Infinity">
                                            <option value="1" @if($isEdit==true) @if($EditData[0]->isLogin=="1") selected @endif @endif >Enabled</option>
                                            <option value="0" @if($isEdit==true) @if($EditData[0]->isLogin=="0") selected @endif @endif>Disabled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="">User Status</label>
                                        <select class="form-control  {{$Theme['input-size']}} select2" id="lstActiveStatus" data-minimum-results-for-search="Infinity">
                                            <option value="Active" @if($isEdit==true) @if($EditData[0]->ActiveStatus=="Active") selected @endif @endif >Active</option>
                                            <option value="Inactive" @if($isEdit==true) @if($EditData[0]->ActiveStatus=="Inactive") selected @endif @endif>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
						</div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-sm-12 text-right">
                                    @if($crud['view']==1)
                                        <a href="{{ url('/') }}/admin/users-and-permissions/users" class="btn {{$Theme['button-size']}} btn-outline-dark mr-10">Cancel</a>
                                    @endif
                                    @if($crud['edit']==1 || $crud['add']==1)
                                        <button class="btn {{$Theme['button-size']}} btn-outline-success btn-air-success" id="btnSave">@if($isEdit) Update @else Create @endif </button>
                                    @endif
                                </div>
                            </div>
                        </div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
@section('scripts')
<!-- Image Crop Script Start -->
<script>
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
        var $image = $('#ImageCrop').cropper(options);
        $('#ImgCrop').modal({backdrop: 'static',keyboard: false});
        $('#ImgCrop').modal('hide');
        $(document).on('change', '.imageScrop', function() {
            let id = $(this).attr('id');
            $('#'+id).attr('data-remove',0);
            if($('#'+id).attr('data-aspect-ratio')!=undefined){
                options.aspectRatio=$('#'+id).attr('data-aspect-ratio')
            }
            $image.attr('data-id', id);
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
                    uploadedImageURL = URL.createObjectURL(file); console.log(options)
                    $image.cropper('destroy').attr('src', uploadedImageURL).cropper(options);
                } else {
                    window.alert('Please choose an image file.');
                }
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
            btnLoading($('#btnCropApply'));
            setTimeout(() => {
                var base64 = $image.cropper('getCroppedCanvas').toDataURL();
                var id = $image.attr('data-id');
                $('#' + id).attr('src', base64);
                $('#' + id).parent().find('img').attr('src', base64)
                $('#ImgCrop').modal('hide');
                setTimeout(() => {
                    btnReset($('#btnCropApply'));
                }, 100);
            }, 100);
        });
        $(document).on('click','#btnCropModelClose',function(){
            var id = $image.attr('data-id');
            $('#' + id).val("");
            $('#' + id).attr('src', "");
            $('#' + id).parent().find('img').attr('src', "");
            $('#' + id).parent().find('.dropify-clear').trigger('click');
            $('#ImgCrop').modal('hide');
        });
    });
</script>
<!-- Image Crop Script End -->
<script src="{{url('/')}}/assets/js/user-roles.js"></script>
<script>
    $(document).ready(function(){
        let validate={MobileNumber:{status:false},email:{status:false}};
        const appInit=async()=>{
			getCountry();
			getGender();
			getUserRoles();
            @if($isEdit)
                $('#txtEmail').trigger('change');
                $('#txtMobileNumber').trigger('change');
                $('#btnSearchPostalCode').trigger('click');
            @endif
		}
        const getUserRoles=async()=>{
            $.ajax({
                type:"post",
                url:"{{url('/')}}/admin/users-and-permissions/users/get/user-roles",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    $('#lstUserRole').select2('destroy');
                    $('#lstUserRole option').remove();
                    $('#lstUserRole').append('<option value="" selected>Select a Role</option>');
                    for(let Item of response){
                        let selected="";
                        if(Item.RoleID==$('#lstUserRole').attr('data-selected')){selected="selected";}
                        $('#lstUserRole').append('<option '+selected+' value="'+Item.RoleID+'">'+Item.RoleName+' </option>');
                    }
                    $('#lstUserRole').select2();
                }
            });
        }
        const getGender=async()=>{
            $.ajax({
                type:"post",
                url:"{{url('/')}}/get/gender",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    $('#lstGender').select2('destroy');
                    $('#lstGender option').remove();
                    $('#lstGender').append('<option value="" selected>Select a Gender</option>');
                    for(let Item of response){
                        let selected="";
                        if(Item.GID==$('#lstGender').attr('data-selected')){selected="selected";}
                        $('#lstGender').append('<option '+selected+' value="'+Item.GID+'">'+Item.Gender+' </option>');
                    }
                    $('#lstGender').select2();
                }
            });
        }
        const getCountry=async()=>{
            $.ajax({
                type:"post",
                url:"{{url('/')}}/get/country",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    $('#lstCountry').select2('destroy');
                    $('#lstCountry option').remove();
                    $('#lstCountry').append('<option value="" selected>Select a Country</option>');
                    for(let Item of response){
                        let selected="";
                        if(Item.CountryID==$('#lstCountry').attr('data-selected')){selected="selected";}
                        $('#lstCountry').append('<option '+selected+' data-phone-code="'+Item.PhoneCode+'" data-phone-length="'+Item.PhoneLength+'" value="'+Item.CountryID+'">'+Item.CountryName+' ( '+Item.sortname+' ) '+' </option>');
                    }
                    $('#lstCountry').select2();
                    if($('#lstCountry').val()!=""){
                        $('#lstCountry').trigger('change');
                    }
                }
            });
        }
        const getStates=async()=>{
            $.ajax({
                type:"post",
                url:"{{url('/')}}/get/states",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                dataType:"json",
                data:{CountryID:$('#lstCountry').val()},
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    $('#lstState').select2('destroy');
                    $('#lstState option').remove();
                    $('#lstState').append('<option value="" selected>Select a State</option>');
                    for(let Item of response){
                        let selected="";
                        if(Item.StateID==$('#lstState').attr('data-selected')){selected="selected";}
                        $('#lstState').append('<option '+selected+'  value="'+Item.StateID+'">'+Item.StateName+' </option>');
                    }
                    $('#lstState').select2();
                    if($('#lstState').val()!=""){
                        $('#lstState').trigger('change');
                    }
                }
            });
        }
        const getTaluks=async()=>{
            $('#lstTaluk').select2('destroy');
            $('#lstTaluk option').remove();
            $('#lstTaluk').append('<option value="">Select a Taluk</option>');
            $.ajax({
                type:"post",
                url:"{{url('/')}}/get/taluks",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                data:{CountryID:$('#lstCountry').val(),StateID:$('#lstState').val(),DistrictID:$('#lstDistricts').val()},
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);resolve([])},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    for(let Item of response){
                        let selected="";
                        if(Item.TalukID==$('#lstTaluk').attr('data-selected')){selected="selected";}
                        $('#lstTaluk').append('<option '+selected+' value="'+Item.TalukID+'">'+Item.TalukName+' </option>');
                    }
                    if($('#lstTaluk').val()!=""){
                        $('#lstTaluk').trigger('change');
                    }
                }
            });
            $('#lstTaluk').select2();
        }
        const getDistricts=async()=>{
            $('#lstDistricts').select2('destroy');
            $('#lstDistricts option').remove();
            $('#lstDistricts').append('<option value="">Select a District</option>');
            $.ajax({
                type:"post",
                url:"{{url('/')}}/get/districts",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                data:{CountryID:$('#lstCountry').val(),StateID:$('#lstState').val()},
                dataType:"json",
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);resolve([])},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    for(let Item of response){
                        let selected="";
                        if(Item.DistrictID==$('#lstDistricts').attr('data-selected')){selected="selected";}
                        $('#lstDistricts').append('<option '+selected+'  value="'+Item.DistrictID+'">'+Item.DistrictName+' </option>');
                    }
                    if($('#lstDistricts').val()!=""){
                        $('#lstDistricts').trigger('change');
                    }
                }
            });
            $('#lstDistricts').select2();
        }
        const getCity=async()=>{
            $('#lstCity').select2('destroy');
            $('#lstCity option').remove();
            $('#lstCity').append('<option value="">Select a City</option>');
            $.ajax({
                type:"post",
                url:"{{url('/')}}/get/city",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                dataType:"json",
                data:{PostalCode:$('#txtPostalCode').val()},
                async:true,
                beforeSend:()=>{
                    $('#btnSearchPostalCode').attr('disabled','disabled');
                    $('#btnSearchPostalCode').html('<i class="fa fa-spinner fa-pulse"></i>');
                },
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                complete: function(e, x, settings, exception){
                    setTimeout(() => {
                        $('#btnSearchPostalCode').html('Search <i class="fa fa-search"></i>');
                        $('#btnSearchPostalCode').removeAttr('disabled');
                    }, 100);
                },
                success:function(response){
                    if(response.length>0){
                        for(let Item of response){
                            let selected="";
                            if(Item.CityID==$('#lstCity').attr('data-selected')){selected="selected";}
                            $('#lstCity').append('<option '+selected+' data-postal="'+Item.PostalID+'" data-taluk="'+Item.TalukID+'" data-district="'+Item.DistrictID+'" data-state="'+Item.StateID+'" data-country="'+Item.CountryID+'" data-city-name="'+Item.CityName+'" value="'+Item.CityID+'">'+Item.CityName+' </option>');
                        }
                        if($('#lstCity').val()!=""){
                            $('#lstCity').trigger('change');
                        }
                    }else{
                        $('#txtPostalCode-err').html('Postal Code does not exists.')
                    }
                }
            });
            $('#lstCity').select2();
        }
        const getPostalCode=async()=>{
            $.ajax({
                type:"post",
                url:"{{url('/')}}/get/postal-code",
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                dataType:"json",
                data:{CountryID:$('#lstCountry').val(),StateID:$('#lstState').val()},
                async:true,
                error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                complete: function(e, x, settings, exception){},
                success:function(response){
                    $('#lstPostalCode').select2('destroy');
                    $('#lstPostalCode option').remove();
                    $('#lstPostalCode').append('<option value="" selected>Select a Postal Code or Enter</option>');
                    for(let Item of response){
                        let selected="";
                        if(Item.PID==$('#lstPostalCode').attr('data-selected')){selected="selected";}
                        $('#lstPostalCode').append('<option '+selected+'  value="'+Item.PID+'">'+Item.PostalCode+' </option>');
                    }
                    $('#lstPostalCode').select2({tags: true});
                }
            });
        }
        const formValidation=()=>{
            $('.errors').html('')
            let FirstName=$('#txtFirstName').val();
			let LastName=$('#txtLastName').val();
            let Gender=$('#lstGender').val();
            let Password=$('#txtPassword').val();
            let ConfirmPassword=$('#txtConfirmPassword').val();
            let UserRole=$('#lstUserRole').val();
			let EMail=$('#txtEmail').val();

            let Address=$('#txtAddress').val();
            let Country=$('#lstCountry').val();
            let State=$('#lstState').val();
            let District=$('#lstDistricts').val();
            let Taluk=$('#lstTaluk').val();
            let City=$('#lstCity').val();
            let PostalCode=$('#lstCity option:selected').attr('data-postal');
            let MobileNumber=$('#txtMobileNumber').val();
            let PhoneLength=$('#lstCountry option:selected').attr('data-phone-length');
            let status=true;
            $('#txtMobileNumber-err').removeClass('success');
            $('#txtEmail-err').removeClass('success');
            if(FirstName==""){
                $('#txtFirstName-err').html('First Name is required');status=false;
            }else if(FirstName.length<3){
                $('#txtFirstName-err').html('The First Name is must be greater than 3 characters.');status=false;
            }else if(FirstName.length>50){
                $('#txtFirstName-err').html('The First Name is not greater than 50 characters.');status=false;
            }
            if(EMail==""){
                $('#txtEmail-err').html('E-Mail is required');status=false;
            }else if(EMail.isValidEMail()==false){
                $('#txtEmail-err').html('E-Mail is not valid');status=false;
            }else if(validate.email.status==false){
                $('#txtEmail-err').html(validate.email.message);status=false;
            }else if(validate.email.status==true){
                $('#txtEmail-err').html(validate.email.message);
                $('#txtEmail-err').addClass('success');
            }
            if(LastName==""){
				if(LastName.length>50){
					$('#txtLastName-err').html('The Last Name is not greater than 50 characters.');status=false;
				}
			}
            if(Gender==""){
                $('#lstGender-err').html('Gender is required');status=false;
            }
            if(UserRole==""){
                $('#lstUserRole-err').html('User Role is required');status=false;
            }
            if(Country==""){
                $('#lstCountry-err').html('Country is required');status=false;
            }
            if(State==""){
                $('#lstState-err').html('State is required');status=false;
            }
            if(City==""){
                $('#lstCity-err').html('City is required');status=false;
            }
            if(District==""){
                $('#lstDistricts-err').html('District is required');status=false;
            }
            if(Taluk==""){
                $('#lstTaluk-err').html('Taluk is required');status=false;
            }
            if(PostalCode==""){
                $('#txtPostalCode-err').html('Postal Code is required');status=false;
            }
            if(MobileNumber==""){
                $('#txtMobileNumber-err').html('Mobile Number is required');status=false;
            }else if($.isNumeric(MobileNumber)==false){
                $('#txtMobileNumber-err').html('Mobile Number is must be numeric value');status=false;
            }else if((parseInt(PhoneLength)>0)&&(parseInt(PhoneLength)!=MobileNumber.length)){
                $('#txtMobileNumber-err').html('Mobile Number is not valid');status=false;
            }else if(validate.MobileNumber.status==false){
                $('#txtMobileNumber-err').html(validate.MobileNumber.message);status=false;
            }else if(validate.MobileNumber.status==true){
                $('#txtMobileNumber-err').html(validate.MobileNumber.message);
                $('#txtMobileNumber-err').addClass('success');
            }
            @if($isEdit==false)
				if(Password==""){
					$('#txtPassword-err').html('Password is required');status=false;
				}else if(Password.length<3){
					$('#txtPassword-err').html('Password must be at least 4 characters');status=false;
				}
				if(ConfirmPassword==""){
					$('#txtConfirmPassword-err').html('Confirm Password is required');status=false;
				}else if(ConfirmPassword.length<4){
					$('#txtConfirmPassword-err').html('Confirm Password must be at least 4 characters');status=false;
				}else if(Password!==ConfirmPassword){
					$('#txtConfirmPassword-err').html('Confirm Password does not match with password');status=false;
				}
            @endif
            return status;
        }
		const getData=async()=>{
            let tmp=await UploadImages();
			let formData=new FormData();
			formData.append('FirstName',$('#txtFirstName').val());
			formData.append('LastName',$('#txtLastName').val());
			formData.append('Gender',$('#lstGender').val());
			formData.append('UserRole',$('#lstUserRole').val());
			formData.append('ActiveStatus',$('#lstActiveStatus').val());
			formData.append('loginStatus',$('#lstLoginStatus').val());
			formData.append('Address',$('#txtAddress').val());
			formData.append('District',$('#lstDistricts').val());
			formData.append('Taluk',$('#lstTaluk').val());
			formData.append('City',$('#lstCity').val());
			formData.append('State',$('#lstState').val());
			formData.append('Country',$('#lstCountry').val());
			formData.append('PostalCodeID',$('#lstCity option:selected').attr('data-postal'));
			formData.append('PostalCode',$('#txtPostalCode').val());
			formData.append('EMail',$('#txtEmail').val());
			formData.append('MobileNumber',$('#txtMobileNumber').val());
            @if($isEdit==false)
                formData.append('Password',$('#txtPassword').val());
                formData.append('ConfirmPassword',$('#txtConfirmPassword').val());
            @endif

            formData.append('removeProfileImage', $('#txtProfileImage').attr('data-remove'));

            if(tmp.profileImage.uploadPath!=""){
                formData.append('ProfileImage', JSON.stringify(tmp.profileImage));
            }
			return formData;
		}
        const getValidationStatus=async (Type,formData)=>{
            let response=await new Promise((resolve,reject)=>{
                $.ajax({
                    type:"post",
                    url:"{{url('/')}}/admin/users-and-permissions/users/validate/"+Type,
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    dataType:"json",
                    data:formData,
                    async:true,
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);reject(e)},
                    complete: function(e, x, settings, exception){},
                    success:function(results){
                        resolve(results)
                    }
                });
            });
            if(response==undefined){
                response={status:false,message:""}
            }
            return response;
        }
        $(document).on('click','#btnSearchPostalCode',async function(){
            $('#txtPostalCode-err').html('')
            let PostalCode=$('#txtPostalCode').val();
            if(PostalCode!=""){
                getCity();
                let response=await getCity({PostalCode});
                setTimeout(() => {
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
        $(document).on('change','#lstCountry',function(){
            getStates();
			let CallCode=$('#lstCountry option:selected').attr('data-phone-code');
			if(CallCode!=""){
				$('#CallCode').html(" ( +"+CallCode+" )");
			}
        });
        $(document).on('change','#lstState',function(){
            getDistricts();
        });
        $(document).on('change','#lstDistricts',function(){
            getTaluks();
        });
        $(document).on('click','#btnSave',function(){
            let status=formValidation();
            if(status){
                swal({
                    title: "Are you sure?",
                    text: "You want @if($isEdit==true)Update @else Save @endif this User!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-outline-success",
                    confirmButtonText: "Yes, @if($isEdit==true)Update @else Save @endif it!",
                    closeOnConfirm: false
                },async function(){
                    swal.close();
                    btnLoading($('#btnSave'));
                    const formData=await getData();
                    @if($isEdit) let posturl="{{url('/')}}/admin/users-and-permissions/users/edit/{{$UserID}}"; @else let posturl="{{url('/')}}/admin/users-and-permissions/users/create"; @endif
                    $.ajax({
                        type:"post",
                        url:posturl,
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
                        complete: function(e, x, settings, exception){btnReset($('#btnSave'));ajaxIndicatorStop();},
                        success:function(response){
                            document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
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
                                        window.location.replace("{{url('/')}}/admin/users-and-permissions/users/");
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
                                    $('#txtMobileNumber-err').removeClass('success');
                                    $('#txtEmail-err').removeClass('success');
                                    $.each( response['errors'], function( KeyName, KeyValue ) {
                                        var key=KeyName;
                                        if(key=="FirstName"){$('#txtFirstName-err').html(KeyValue);}
                                        if(key=="LastName"){$('#txtLastName-err').html(KeyValue);}
                                        if(key=="Gender"){$('#lstGender-err').html(KeyValue);}
                                        if(key=="UserRole"){$('#lstUserRole-err').html(KeyValue);}
                                        if(key=="Address"){$('#txtAddress-err').html(KeyValue);}
                                        if(key=="City"){$('#lstCity-err').html(KeyValue);}
                                        if(key=="State"){$('#lstState-err').html(KeyValue);}
                                        if(key=="Country"){$('#lstCountry-err').html(KeyValue);}
                                        if(key=="PostalCode"){$('#lstPostalCode-err').html(KeyValue);}
                                        if(key=="EMail"){$('#txtEmail-err').html(KeyValue);}
                                        if(key=="MobileNumber"){$('#txtMobileNumber-err').html(KeyValue);}
                                        if(key=="ProfileImage"){$('#txtProfileImage-err').html(KeyValue);}
                                        if(key=="ActiveStatus"){$('#lstActiveStatus-err').html(KeyValue);}
                                    });
                                }
                            }
                        }
                    });
                });
            }
        });
        $(document).on('change','#txtMobileNumber',async function(){
            let MobileNumber=$('#txtMobileNumber').val();
            let PhoneLength=$('#lstCountry option:selected').attr('data-phone-length')
            $('#txtMobileNumber-err').html('');
            $('#txtMobileNumber-err').removeClass('success');
            if(MobileNumber==""){
                $('#txtMobileNumber-err').html('Mobile Number is required.');
            }else if($.isNumeric(MobileNumber)==false){
                $('#txtMobileNumber-err').html('The Mobile Number must be numeric value.');
            }else if((MobileNumber.length!=PhoneLength)&&(PhoneLength>0)){
                $('#txtMobileNumber-err').html('The Mobile Number must be '+PhoneLength+' digits.');
            }else {
                validate.MobileNumber=await getValidationStatus("mobile-number",{MobileNumber:MobileNumber,"UserID":"<?php if($isEdit){ echo $UserID; } ?>"})
                if(validate.MobileNumber.status){
                    $('#txtMobileNumber-err').html(validate.MobileNumber.message);
                    $('#txtMobileNumber-err').addClass("success");
                }else{
                    $('#txtMobileNumber-err').html(validate.MobileNumber.message);
                }

            }
        });
        $(document).on('keyup','#txtMobileNumber',async function(){
            let MobileNumber=$('#txtMobileNumber').val();
            let PhoneLength=$('#lstCountry option:selected').attr('data-phone-length')
            $('#txtMobileNumber-err').html('');
            $('#txtMobileNumber-err').removeClass('success');

            if(MobileNumber==""){
                $('#txtMobileNumber-err').html('Mobile Number is required.');
            }else if($.isNumeric(MobileNumber)==false){
                $('#txtMobileNumber-err').html('The Mobile Number must be numeric value.');
            }else if((MobileNumber.length!=PhoneLength)&&(PhoneLength>0)){
                $('#txtMobileNumber-err').html('The Mobile Number must be '+PhoneLength+' digits.');
            }else {
                validate.MobileNumber=await getValidationStatus("mobile-number",{MobileNumber:MobileNumber,"UserID":"<?php if($isEdit){ echo $UserID; } ?>"})
                if(validate.MobileNumber.status){
                    $('#txtMobileNumber-err').html(validate.MobileNumber.message);
                    $('#txtMobileNumber-err').addClass("success");
                }else{
                    $('#txtMobileNumber-err').html(validate.MobileNumber.message);
                }

            }
        });
        $(document).on('change','#txtEmail',async function(){
            let email=$('#txtEmail').val();
            $('#txtEmail-err').html('');
            $('#txtEmail-err').removeClass('success');
            if(email!=""){
                if(email.isValidEMail()==false){
                    $('#txtEmail-err').html('E-Mail is not valid');status=false;
                }else {
                    validate.email=await getValidationStatus("email",{email:email,"UserID":"<?php if($isEdit){ echo $UserID; } ?>"})
                    if(validate.email.status){
                        $('#txtEmail-err').html(validate.email.message);
                        $('#txtEmail-err').addClass("success");
                    }else{
                        $('#txtEmail-err').html(validate.email.message);
                    }

                }
            }
        });
        $(document).on('keyup','#txtEmail',async function(){
            let email=$('#txtEmail').val();
            $('#txtEmail-err').html('');
            $('#txtEmail-err').removeClass('success');
            if(email!=""){
                if(email.isValidEMail()==false){
                    $('#txtEmail-err').html('E-Mail is not valid');status=false;
                }else {
                    validate.email=await getValidationStatus("email",{email:email,"UserID":"<?php if($isEdit){ echo $UserID; } ?>"})
                    if(validate.email.status){
                        $('#txtEmail-err').html(validate.email.message);
                        $('#txtEmail-err').addClass("success");
                    }else{
                        $('#txtEmail-err').html(validate.email.message);
                    }

                }
            }
        });
        $(document).on('keyup','#txtFirstName',async function(){
            let FirstName=$('#txtFirstName').val();
            $('#txtFirstName-err').html('');
            if(FirstName==""){
                $('#txtFirstName-err').html('First Name is required');
            }else if(FirstName.length<3){
                $('#txtFirstName-err').html('The First Name is must be greater than 3 characters.');
            }else if(FirstName.length>50){
                $('#txtFirstName-err').html('The First Name is not greater than 50 characters.');
            }
        });
        $(document).on('keyup','#txtLastName',async function(){
            let LastName=$('#txtLastName').val();
            $('#txtLastName-err').html('');
            if(LastName==""){
				if(LastName.length>50){
					$('#txtLastName-err').html('The Last Name is not greater than 50 characters.');status=false;
				}
			}
        });
        $(document).on('change','#lstGender',async function(){
            let Gender=$('#lstGender').val();
            $('#txtLastName-err').html('');
            if(Gender==""){
                $('#lstGender-err').html('Gender is required');status=false;
            }
        });
        $(document).on('change','#lstUserRole',async function(){
            let UserRole=$('#lstUserRole').val();
            $('#lstUserRole-err').html('');
            if(UserRole==""){
                $('#lstUserRole-err').html('User Role is required');status=false;
            }
        });
        $(document).on('change','#lstCountry',async function(){
            let Country=$('#lstCountry').val();
            $('#lstCountry-err').html('');
            if(Country==""){
                $('#lstCountry-err').html('Country is required');status=false;
            }
        });
        $(document).on('change','#lstState',async function(){
            let State=$('#lstState').val();
            $('#lstState-err').html('');
            if(State==""){
                $('#lstState-err').html('State is required');status=false;
            }
        });
        $(document).on('change','#lstCity',async function(){
            let City=$('#lstCity').val();
            $('#lstCity-err').html('');
            if(City==""){
                $('#lstCity-err').html('City is required');status=false;
            }
        });
        $(document).on('change','#lstPostalCode',async function(){
            let PostalCode=$('#lstPostalCode').val();
            $('#lstPostalCode-err').html('');
            if(PostalCode==""){
                $('#lstPostalCode-err').html('Postal Code is required');status=false;
            }
        });
        $(document).on('keyup','#txtPassword',async function(){
            let Password=$('#txtPassword').val();
            let ConfirmPassword=$('#txtConfirmPassword').val();
            $('#txtPassword-err').html('');
			if(Password==""){
				$('#txtPassword-err').html('Password is required');status=false;
			}else if(Password.length<3){
				$('#txtPassword-err').html('Password must be at least 4 characters');status=false;
			}else if(Password!==ConfirmPassword){
				$('#txtConfirmPassword-err').html('Confirm Password does not match with password');status=false;
			}
        });
        $(document).on('keyup','#txtConfirmPassword',async function(){
            let ConfirmPassword=$('#txtConfirmPassword').val();
            let Password=$('#txtPassword').val();
            $('#txtConfirmPassword-err').html('');
			if(ConfirmPassword==""){
				$('#txtConfirmPassword-err').html('Confirm Password is required');status=false;
			}else if(ConfirmPassword.length<4){
				$('#txtConfirmPassword-err').html('Confirm Password must be at least 4 characters');status=false;
			}else if(Password!==ConfirmPassword){
				$('#txtConfirmPassword-err').html('Confirm Password does not match with password');status=false;
			}
        });

        $(document).on('click','#btnShowPassword',function(){
            if($(this).attr('data-is-show')=="0"){
                $(this).attr('data-is-show',1)
                $(this).html('<i class="fa fa-eye-slash"></i>');
                $('#txtPassword').attr('type','text');
            }else{
                $(this).attr('data-is-show',0)
                $(this).html('<i class="fa fa-eye"></i>')
                $('#txtPassword').attr('type','password');
            }
        });
        $(document).on('click','#btnShowConfirmPassword',function(){
            if($(this).attr('data-is-show')=="0"){
                $(this).attr('data-is-show',1)
                $(this).html('<i class="fa fa-eye-slash"></i>');
                $('#txtConfirmPassword').attr('type','text');
            }else{
                $(this).attr('data-is-show',0)
                $(this).html('<i class="fa fa-eye"></i>')
                $('#txtConfirmPassword').attr('type','password');
            }
        });
        $(document).on('click','.dropify-clear',function(){
            $(this).parent().find('input[type="file"]').attr('data-remove',1);
        });
        appInit();
    });
</script>
@endsection
