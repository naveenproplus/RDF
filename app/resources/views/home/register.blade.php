@extends('home.home-layout')
@section('content')

<div class="container-fluid">
	<div class="row d-flex justify-content-center">
		<div class="col-sm-9">
			<div class="card">
				<div class="card-header text-center"><h4 class="m-0">@if($isEdit) Profile @else Registration Form @endif</h4></div>
				<div class="card-body " >
                    <div class="row customerRegister">
                        <div class="col-sm-12">
                            <div class="row mb-3 d-flex justify-content-center">
                                {{-- <div class="col-sm-4">
                                    <input type="file" class="dropify imageScrop" data-aspect-ratio="1" data-remove="0" data-is-cover-image="1" id="txtCustomerImage" data-default-file="@if($UserData->ProfileImage){{$UserData->ProfileImage}}@endif">
                                    <div class="errors" id="txtCustomerImage-err"></div>
                                </div> --}}
                                <div class="col-sm-4 d-flex justify-content-center">
                                    <div style="position: relative; display: inline-block;">
                                        <img src="@if($isEdit){{$EditData->CustomerImage}} @elseif($UserData->ProfileImage){{$UserData->ProfileImage}}@endif" alt="" class="rounded-circle border border-secondary" height="200px" width="200px">
                                        <input type="file" class="imageScrop d-none" data-aspect-ratio="1" data-remove="0" data-is-cover-image="1" id="txtCustomerImage">
                                        <button id="btnEditImage" class="btn btn-sm btn-warning rounded-circle">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="txtCustomerName">Customer Name <span class="required">*</span></label>
                                        <input type="text" id="txtCustomerName" class="form-control " placeholder="Customer Name" value="@if($isEdit){{$EditData->CustomerName}} @elseif($UserData->Name){{$UserData->Name}}@endif">
                                        <span class="errors Customer err-sm" id="txtCustomerName-err"></span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="txtEmail">Email <span class="required">*</span></label>
                                        <input type="text" disabled id="txtEmail" class="form-control " placeholder="Email"  value="@if($isEdit){{$EditData->Email}} @elseif($UserData->EMail){{$UserData->EMail}}@endif">
                                        <span class="errors Customer err-sm" id="txtEmail-err"></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="txtMobileNo1">Mobile Number <span class="required">*</span></label>
                                        <input type="text" id="txtMobileNo1" class="form-control " placeholder="Mobile Number"  value="@if($isEdit){{$EditData->MobileNo1}}@endif">
                                        <span class="errors Customer err-sm" id="txtMobileNo1-err"></span>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="txtMobileNo2">Alternate Mobile Number </label>
                                        <input type="text" id="txtMobileNo2" class="form-control " placeholder="Alternate Mobile Number"  value="@if($isEdit){{$EditData->MobileNo2}}@endif">
                                        <span class="errors Customer err-sm" id="txtMobileNo2-err"></span>
                                    </div>
                                </div>
                                {{-- <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="lstCusType">Customer Type <span class="required">*</span></label>
                                        <select class="form-control" id="lstCusType" data-selected="">
                                            <option value="">Select a Customer Type</option>
                                        </select>
                                        <span class="errors Customer err-sm" id="lstCusType-err"></span>
                                    </div>
                                </div> --}}
                                <div class="col-sm-12 mt-20">
                                    <label for="txtAddress">Billing Address <span class="required">*</span></label>
                                    <textarea  id="txtAddress" class="form-control">@if($isEdit){{$EditData->Address}}@endif</textarea>
                                    <span class="errors BA err-sm" id="txtAddress-err"></span>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="txtPostalCode">Postal Code <span class="required">*</span></label>
                                        <div class="input-group">
                                            <input type="text" id="txtPostalCode" class="form-control" placeholder="Postal Code" value="@if($isEdit){{$EditData->PostalCode}}@endif">
                                            <button type="button" class="btn btn-sm btn-outline-dark" id="btnGSearchPostalCode">Search <i class="fa fa-search"></i></button>
                                        </div>
                                        <div class="errors BA err-sm" id="txtPostalCode-err"></div>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="lstCity">City <span class="required">*</span></label>
                                        <select class="form-control" id="lstCity" data-selected="@if($isEdit){{$EditData->CityID}}@endif">
                                            <option value="">Select a City</option>
                                        </select>
                                        <div class="errors BA err-sm" id="lstCity-err"></div>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="lstTaluk">Taluk <span class="required">*</span></label>
                                        <select class="form-control" id="lstTaluk" data-selected="@if($isEdit){{$EditData->TalukID}}@endif">
                                            <option value="">Select a Taluk</option>
                                        </select>
                                        <div class="errors BA err-sm" id="lstTaluk-err"></div>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="lstDistrict">District <span class="required">*</span></label>
                                        <select class="form-control" id="lstDistricts" data-selected="@if($isEdit){{$EditData->DistrictID}}@endif">
                                            <option value="">Select a District</option>
                                        </select>
                                        <div class="errors BA err-sm" id="lstDistricts-err"></div>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="lstState">State <span class="required">*</span></label>
                                        <select class="form-control" id="lstState"  data-selected="@if($isEdit){{$EditData->StateID}}@endif">
                                            <option value="">Select a State</option>
                                        </select>
                                        <div class="errors BA err-sm" id="lstState-err"></div>
                                    </div>
                                </div>
                                <div class="col-sm-6 mt-20">
                                    <div class="form-group">
                                        <label for="lstCountry">Country <span class="required">*</span></label>
                                        <select class="form-control" id="lstCountry" data-selected="@if($isEdit){{$EditData->CountryID}}@endif">
                                            <option value="">Select a Country</option>
                                        </select>
                                        <div class="errors BA err-sm" id="lstCountry-err"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-sm-12 text-center">
                                    <button class="btn btn-outline-info btnAddAddress" data-title="Shipping Address">Add Shipping Address</button>
                                    <div class="errors err-sm" id="btnAddAddress-err"></div>
                                </div>
                            </div>
                            <div class="row mt-2 justify-content-center">
                                <div class="col-sm-8 d-flex justify-content-center">
                                    <table class="table" id="tblShippingAddress">
                                        <tbody>
                                            @if($isEdit)
                                                @foreach ($EditData->SAddress as $key => $item)
                                                    <tr id="{{ $key + 1 }}" data-aid="{{ $item->AID }}">
                                                        <td class="text-right checkbox1 align-middle">
                                                            <div class="radio radio-primary">
                                                                <input id="chkSA{{ $key + 1 }}" data-aid="{{ $item->AID }}" type="radio" name="SAddress" value="{{ $key + 1 }}" {{ $item->isDefault == 1 ? 'checked' : '' }}>
                                                                <label for="chkSA{{ $key + 1 }}"></label>
                                                            </div>
                                                        </td> 
                                                        <td class="pointer">
                                                            <b>{{ $item->Address }}</b>,<br>
                                                            {{ $item->CityName }}, {{ $item->TalukName }},<br>
                                                            {{ $item->DistrictName }}, {{ $item->StateName }},<br>
                                                            {{ $item->CountryName }} - {{ $item->PostalCode }}.
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-outline-success m-5 btnEditSAddress"><i class="fas fa-pencil-alt"></i></button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger m-5 btnDeleteSAddress"><i class="fas fa-trash-alt"></i></button>
                                                        </td>
                                                        <td class="d-none">{{ json_encode($item) }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-sm-8 errors err-sm" id="tblShippingAddress-err"></div>
                            </div>
                        </div>
                    </div>
				</div>
                <div class="card-footer">
                    <div class="text-right">
                        <button class="btn btn-success" id="btnSave" type="button" >@if($isEdit) Update @else Register @endif</button>
                    </div>
                </div>
			</div>
		</div>
	</div>
    <div id="confirm-modal" class="newsletter-popup mfp-hide bg-img p-6 h-auto" style="background: #f1f1f1 no-repeat center/cover">
        <h2>Are you sure you want to @if($isEdit) Update @else Register @endif ?</h2>
        <div class="modal-buttons">
            <button id="btnMConfirm" class="btn btn-primary">@if($isEdit) Update @else Register @endif</button>
            <button id="btnMCancel" class="btn btn-secondary">Cancel</button>
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
            const btnReset=async($this)=> {
                $('.waves-ripple').remove();
                $this.html($this.data('original-text'));
                $this.removeAttr('disabled');
            }
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
            $(document).on('click','.dropify-clear',function(){
                $(this).parent().find('input[type="file"]').attr('data-remove',1);
            });
            $('.dropify').dropify();
        });
</script>
<!-- Image Crop Script End -->
<script>
    $(document).ready(function(){
        
        const ajaxIndicatorStart =async(text="") =>{
            var basepath=$('#txtRootUrl').val();
            if ($('body').find('#resultLoading').attr('id') != 'resultLoading') {
                if(text==""){text="Processing";}
                $('body').append('<div id="resultLoading" style="display:none"><div style="font-weight: 700;"><img src="' + basepath + '/assets/images/ajax-loader.gif"><div id="divProcessText">'+text+'</div></div><div class="bg"></div></div>');
            }
            $('#resultLoading').css({
                'width': '100%',
                'height': '100%',
                'position': 'fixed',
                'z-index': '10000000',
                'top': '0',
                'left': '0',
                'right': '0',
                'bottom': '0',
                'margin': 'auto'
            });
            $('#resultLoading .bg').css({
                'background': '#000000',
                'opacity': '0.7',
                'width': '100%',
                'height': '100%',
                'position': 'absolute',
                'top': '0'
            });
            $('#resultLoading>div:first').css({
                'width': '50%',
                'height': '75px',
                'text-align': 'center',
                'position': 'fixed',
                'top': '0',
                'left': '0',
                'right': '0',
                'bottom': '0',
                'margin': 'auto',
                'font-size': '16px',
                'z-index': '10',
                'color': '#ffffff'
            });
            $('#resultLoading .bg').height('100%');
            $('#resultLoading').fadeIn(300);
            $('body').css('cursor', 'wait');
        }
        const ajaxIndicatorStop=async()=> {
            $('#resultLoading .bg').height('100%');
            $('#resultLoading').fadeOut(300);
            $('body').css('cursor', 'default');
        }
        const UploadImages = async () => {
            let RootUrl=$('#txtRootUrl').val();
            let uploadImages=await new Promise((resolve,reject)=>{
                ajaxIndicatorStart("% Completed. Please wait for until upload process complete.");
                setTimeout(() => {
                    let count = $("input.imageScrop").length;
                    let completed = 0;
                    let rowIndex=0;
                    let images={profileImage:{uploadPath:"",fileName:""},coverImage:{uploadPath:"",fileName:""},gallery:[]};
                    const uploadComplete=async(e, x, settings, exception)=>{
                        completed++;
                        let percentage=(100*completed)/count;
                        $('#divProcessText').html(percentage + '% Completed. Please wait for until upload process complete.');
                        checkUploadCompleted();
                    }
                    const checkUploadCompleted=async()=>{
                        if(count<=completed){
                            ajaxIndicatorStop();
                            resolve(images);
                        }
                    }
                    const upload=async(formData)=>{
                        console.log(formData);
                        $.ajax({
                            type: "post",
                            url: RootUrl+"tmp/upload-image",
                            headers: {'X-CSRF-Token': $('meta[name=_token]').attr('content')},
                            data: formData,
                            dataType:"json",
                            error: function(e, x, settings, exception) {ajaxErrors(e, x, settings, exception);},
                            complete: uploadComplete,
                            success:function(response){
                                if(response.referData.isProfileImage==1){
                                    images.profileImage={uploadPath:response.uploadPath,fileName:response.fileName};
                                }else if(response.referData.isCoverImage==1){
                                    images.coverImage={uploadPath:response.uploadPath,fileName:response.fileName};
                                }else{
                                    images.gallery.push({uploadPath:response.uploadPath,fileName:response.fileName,slno:response.referData.slno});
                                }
                            }
                        });
                    }
                    $("input.imageScrop").each(function (index){
                        let id = $(this).attr('id');
                        if ($('#' + id).val() != "" ) {
                            let isProfileImage=$('#'+id).attr('data-is-profile-image');
                            let isCoverImage=$('#'+id).attr('data-is-cover-image');
                            isProfileImage=isNaN(parseInt(isProfileImage))==false?isProfileImage:0;
                            isCoverImage=isNaN(parseInt(isCoverImage))==false?isCoverImage:0;
                            rowIndex++;
                            let formData = {};
                                formData.image = $('#'+id).attr('src');
                                formData.referData = {index:rowIndex,id:id,slno:$('#'+id).attr('data-slno'),isProfileImage:isProfileImage,isCoverImage:isCoverImage};
                                upload(formData);
                        }else{
                            completed++;
                            let percentage=(100*completed)/count;
                            $('#divProcessText').html(percentage + '% Completed. Please wait for until upload process complete.');
                            checkUploadCompleted();
                        }
                    });
                }, 200);
                
                
            });
            return uploadImages;
        }
		function validateForm() {
            $('.errors').html('');
			let status=true;
            let CustomerName=$('#txtCustomerName').val();
            let MobileNo1=$('#txtMobileNo1').val();
            let MobileNo2=$('#txtMobileNo2').val();
            let Email=$('#txtEmail').val();
            let Address=$('#txtAddress').val();
            let PostalCode=$('#lstCity option:selected').attr('data-postal');
            let CityID=$('#lstCity').val();
            let TalukID=$('#lstTaluk').val();
            let DistrictID=$('#lstDistricts').val();
            let StateID=$('#lstState').val();
            let CountryID=$('#lstCountry').val();
            if(!CustomerName){
                $('#txtCustomerName-err').html('Customer Name is required');status=false;
            }else if(CustomerName.length<2){
                $('#txtCustomerName-err').html('The Customer Name is must be greater than 2 characters.');status=false;
            }else if(CustomerName.length>100){
                $('#txtCustomerName-err').html('The Customer Name is not greater than 100 characters.');status=false;
            }
            let mobilePattern = /^\d{10}$/;
            if(!MobileNo1){
                $('#txtMobileNo1-err').html('Mobile Number is required.');status=false;
            }else if (!mobilePattern.test(MobileNo1)){
                $("#txtMobileNo1-err").html("Mobile Number must be 10 digit");
            }
            if (MobileNo2.length > 0 && !mobilePattern.test(MobileNo2)){
                $("#txtMobileNo2-err").html("Alternate Mobile Number must be 10 digit");status=false;
            }

            if(!PostalCode){
                $('#txtPostalCode-err').html('Postal Code is required.');status=false;isAddress=true;
            }
            if(CityID==""){
                $('#lstCity-err').html('City is required.');status=false;isAddress=true;
            }
            if(TalukID==""){
                $('#lstTaluk-err').html('Taluk is required.');status=false;isAddress=true;
            }
            if(DistrictID==""){
                $('#lstDistricts-err').html('District is required.');status=false;isAddress=true;
            }
            if(StateID==""){
                $('#lstState-err').html('State is required.');status=false;isAddress=true;
            }
            if(CountryID==""){
                $('#lstCountry-err').html('Country is required.');status=false;isAddress=true;
            }
            if(Address==""){
                $('#txtAddress-err').html('Address is required.');status=false;
            }else if(Address.length<10){
                $('#txtAddress-err').html('Address must be greater than 10 characters');status=false;isAddress=true;
            }
            let TotRows=$('#tblShippingAddress tbody tr').length;
            let isSelectedDefaultShipping=$('input[type="radio"][name="SAddress"]:checked').length
            if(TotRows<=0){
                $('#btnAddAddress-err').html('Shipping address is required');status=false;
            }else if(isSelectedDefaultShipping<=0){
                $('#tblShippingAddress-err').html('Select a default shipping address');status=false;
            }
            if(status==false){$("html, body").animate({ scrollTop: 0 }, "slow");}

			// return true;
			return status;
		}
        $(document).on('click','#btnGSearchPostalCode',async function(){
            $('#txtPostalCode-err').html('')
            let PostalCode=$('#txtPostalCode').val();
            if(PostalCode!=""){
                $('#btnGSearchPostalCode').attr('disabled','disabled');
                $('#btnGSearchPostalCode').html('<i class="fa fa-spinner fa-pulse"></i>');
                let response=await getCity({PostalCode});
                if(response.length>0){
                    $('#lstCity option').remove();
                    $('#lstCity').append('<option value="">Select a City</option>');
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

            if (!$('.chkServiceBy:checked').length) {
                $('.chkServiceBy[data-value="PostalCode"]').prop('checked', true).trigger('change');
                setTimeout(function() {
                },2000)
            }
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
        $(document).on('click', '#btnSaveAddress', function () {
            SaveAddress();
        });
        $(document).on('click', '.btnEditSAddress', function () {
            let Row=$(this).closest('tr');
            let EditData=JSON.parse($(this).closest('tr').find("td:eq(3)").html());
            EditData.EditID=Row.attr('id');
            EditData.AID=Row.attr('data-aid');
            getAddressModal(EditData);
        });
        $(document).on('click', '.btnDeleteSAddress', function () {
            $(this).closest('tr').remove();
        });
        $(document).on('click', '#btnEditImage', function () {
            $('#txtCustomerImage').trigger('click');
        });
        $('#btnSave').on('click',async function () {
            let status = await validateForm();
            if (status) {
                $.magnificPopup.open({
                    items: {
                        src: '#confirm-modal'
                    },
                    type: 'inline',
                    mainClass: 'mfp',
                    removalDelay: 350
                });
            }
        });

        $('#btnMConfirm').on('click',async function () {
            let formData = await getData();
            let postUrl = @if($isEdit) "{{url('/')}}/update" @else "{{url('/')}}/save" @endif;
            $.ajax({
                type: "post",
                url: postUrl,
                headers: { 'X-CSRF-Token': $('meta[name=_token]').attr('content') },
                data: formData,
                cache: false,
                processData: false,
                contentType: false,
                error: function (e, x, settings, exception) { ajax_errors(e, x, settings, exception); },
                complete: function (e, x, settings, exception) { btnReset($('#btSave')); ajaxIndicatorStop(); $("html, body").animate({ scrollTop: 0 }, "slow"); },
                success: function (response) {
                    if (response.status == true) {
                        window.location.replace("{{url('/')}}/customer-home");
                    } else {
                        $("html, body").animate({ scrollTop: 0 }, "slow");
                        if (response['errors'] != undefined) {
                            $('.errors').html('');
                            $.each(response['errors'], function (KeyName, KeyValue) {
                                var key = KeyName;
                                if (key == "Email") { $('#txtEmail-err').html(KeyValue); }
                                if (key == "MobileNo1") { $('#txtMobileNo1-err').html(KeyValue); }
                                if (key == "CustomerImage") { $('#txtCustomerImage-err').html(KeyValue); }
                            });
                        }
                    }
                }
            });
        });

        $('#btnMCancel').on('click', function () {
            $.magnificPopup.close();
        });
        const SaveAddress = async () => {
            let { status, formData, Address } = await ValidateGetAddress();
            // console.log(formData);

            if (status) {
                let index = formData.EditID ? formData.EditID : $('#tblShippingAddress tbody tr').length + 1;

                let html = `<tr id="${index}" data-aid="${formData.AID}">
                                <td class="text-right checkbox1 align-middle">
                                    <div class="radio radio-primary">
                                        <input id="chkSA${index}" data-aid="${formData.AID}" type="radio" name="SAddress" value="${index}">
                                        <label for="chkSA${index}"></label>
                                    </div>
                                </td> 
                                <td class="pointer">
                                    <b>${formData.Address}</b>,<br>
                                    ${formData.CityName}, ${formData.TalukName},<br> 
                                    ${formData.DistrictName}, ${formData.StateName},<br> 
                                    ${formData.CountryName} - ${formData.PostalCode}.
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-success m-5 btnEditSAddress"><i class="fas fa-pencil-alt"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-danger m-5 btnDeleteSAddress"><i class="fas fa-trash-alt"></i></button>
                                </td>
                                <td class="d-none">${JSON.stringify(formData)}</td>
                            </tr>`;

                if (formData.EditID) {
                    $("#tblShippingAddress tbody tr").each(function () {
                        let SNo = $(this).attr('id');
                        if (SNo == formData.EditID) {
                            $(this).replaceWith(html);
                            return false;
                        }
                    });
                } else {
                    $('#tblShippingAddress tbody').append(html);
                }

                bootbox.hideAll();
            }
        };
        const ValidateGetAddress = async () => {
            $(".errors.Address").html("");
            let status = true;
            let formData={};
            formData.EditID=$("#btnSaveAddress").attr('data-edit-id');
            formData.AID=$("#btnSaveAddress").attr('data-aid');
            formData.Address=$('#txtADAddress').val();
            formData.CountryID=$('#lstADCountry').val();
            formData.CountryName=$('#lstADCountry option:selected').attr('data-country-name');
            formData.StateID=$('#lstADState').val();
            formData.StateName=$('#lstADState option:selected').text();
            formData.DistrictID=$('#lstADDistrict').val();
            formData.DistrictName=$('#lstADDistrict option:selected').text();
            formData.TalukID=$('#lstADTaluk').val();
            formData.TalukName=$('#lstADTaluk option:selected').text();
            formData.CityID=$('#lstADCity').val();
            formData.CityName=$('#lstADCity option:selected').text();
            formData.PostalCode=$('#txtADPostalCode').val();
            formData.PostalCodeID=$('#lstADCity option:selected').attr('data-postal-id');
            // console.log(formData);
            let Address ="";
            if(formData.Address==""){
                $('#txtADAddress-err').html('Address is required');status=false;
            }else if(formData.Address.length<5){
                $('#txtADAddress-err').html('The Address must be greater than 5 characters.');status=false;
            }else{
                Address+=",<br>"+formData.Address;
            }
            if(formData.CityID==""){
                $('#lstADCity-err').html('City is required');status=false;
            }else{
                Address+=",<br>"+formData.CityName;
            }
            if(formData.TalukID==""){
                $('#lstADTaluk-err').html('Taluk is required');status=false;
            }else{
                Address+=",<br>"+formData.TalukName;
            }
            if(formData.DistrictID==""){
                $('#lstADDistrict-err').html('District is required');status=false;
            }else{
                Address+=",<br>"+formData.DistrictName;
            }
            if(formData.StateID==""){
                $('#lstADState-err').html('State is required');status=false;
            }else{
                Address+=",<br>"+formData.StateName;
            }
            if(formData.CountryID==""){
                $('#lstADCountry-err').html('Country is required');status=false;
            }else{
                Address+=","+formData.CountryName;
            }
            if(formData.PostalCode==""){
                $('#txtADPostalCode-err').html('Postal Code is required');status=false;
            }else{
                Address+=" - "+formData.PostalCode;
            }
            // status = true;
            return { status, formData, Address };
        };
        const getData=async ()=>{
            let status = await validateForm();
            if(status){
                let tmp=await UploadImages();
                let formData=new FormData();
                formData.append('CustomerName',$('#txtCustomerName').val());
                formData.append('MobileNo1',$('#txtMobileNo1').val());
                formData.append('MobileNo2',$('#txtMobileNo2').val());
                formData.append('Email',$('#txtEmail').val());
                formData.append('Address',$('#txtAddress').val());
                formData.append('PostalCodeID',$('#lstCity option:selected').attr('data-postal'));
                formData.append('CityID',$('#lstCity').val());
                formData.append('TalukID',$('#lstTaluk').val());
                formData.append('DistrictID',$('#lstDistricts').val());
                formData.append('StateID',$('#lstState').val());
                formData.append('CountryID',$('#lstCountry').val());
                if(tmp.coverImage.uploadPath!=""){
                    formData.append('CustomerImage', JSON.stringify(tmp.coverImage));
                }
                let SAddress = [];
                $("#tblShippingAddress tbody tr").each(function() {
                    let Address = JSON.parse($(this).find("td:eq(3)").html());
                    let isSelectedDefaultShipping = $(this).find('input[type="radio"][name="SAddress"]:checked').length;
                    Address.isDefault = isSelectedDefaultShipping ? 1 : 0;
                    SAddress.push(Address);
                });
                formData.append('SAddress',JSON.stringify(SAddress));
                return formData;
            }
        }
        $(document).on('keyup change', 'input, select', function () {
            $('.errors').html('');
        });

        const getAddressModal=(data={})=>{
            $.ajax({
                type:"post",
                url:"{{url('/')}}/address-form",
                data:{"data":JSON.stringify(data)},
                headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                dataType:"html",
                async:true,
                error:function(e, x, settings, exception){},
                success:async(response)=>{
                    if(response!=""){
                        bootbox.dialog({
                            title:"Shipping Address",
                            closeButton: true,
                            message: response,
                            className:"AddressModal",
                            buttons: {}
                        });
                    }
                }
            });
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
        }
        const getDistricts=async(data,id)=>{
            let Data = [];
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
            return Data;
        }
        const getStates=async(data,id)=>{
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
        }
        const getCountry=async(data,id)=>{
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
        }
        getCountry({},'lstCountry');
        @if($isEdit)
        $('#btnGSearchPostalCode').trigger('click');
        @endif

    });
</script>
@endsection
