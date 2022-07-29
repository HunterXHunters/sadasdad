@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')

@section('content')
<style>
#dragandrophandler
{
border: 2px dashed #92AAB0;
width: 350px;
height: 50px;
color: #92AAB0;
text-align: center;
vertical-align: middle;
padding: 10px 0px 10px 10px;
font-size:200%;
display: table-cell;
}
.progressBar {
	width: 100px;
	height: 22px;
	border: 1px solid #ddd;
	border-radius: 5px;	
	overflow: hidden;
	display:inline-block;
	margin:0px 10px 5px 5px;
	vertical-align:top;
}

.progressBar div {
	height: 100%;
	color: #fff;
	text-align: right;
	line-height: 22px; /* same as #progressBar height if we want text middle aligned */
	width: 0;
	background-color: #0ba1b5; border-radius: 3px; 
}
.statusbar
{
	border-top:1px solid #A9CCD1;
	min-height:25px;
	width:450px;
	padding:10px 10px 0px 10px;
	vertical-align:top;
}
.statusbar:nth-child(odd){
    background:#EBEFF0;
}
.filename
{
display:inline-block;
vertical-align:top;
width:150px;
}
.filesize
{
display:inline-block;
vertical-align:top;
color:#30693D;
width:80px;
margin-left:10px;
margin-right:5px;
}
.abort{
	background-color:#A8352F;
	-moz-border-radius:4px;
	-webkit-border-radius:4px;
	border-radius:4px;display:inline-block;
	color:#fff;
	font-family:arial;font-size:13px;font-weight:normal;
	padding:4px 15px;
	cursor:pointer;
	vertical-align:top
	}
</style>

<div class="row margleft">
<div class="col-md-12">
<section id="rootwizard" class="tabbable tile">
<div class="nav-tabs-custom">
  <ul class="nav nav-tabs pull-right">
    <li><a href="#tab3" data-toggle="tab" aria-expanded="false" >Users</a></li>
    <li><a href="#tab2" data-toggle="tab" aria-expanded="false">Permission</a></li>
    <li class="active"><a href="#tab1" data-toggle="tab" aria-expanded="true">Role</a></li>
    <li class="pull-left header"><i class="fa fa-th"></i> Edit Role</li>
  </ul>
  {{Form::open(array('url'=>'rbac/saveRole/'.$row->role_id,'method'=>'put'))}}
    <div class="tab-content">
    @if(Session::has('errorMsgArr'))
              <?PHP 
                $errorMsgArr = Session::get('errorMsgArr');
                
                //$row = Session::get('row');            
              ?>
              <div style="color: #FF0000;">
                  <?PHP echo str_replace(',', "<br>", $errorMsgArr);?>
              </div>
    @endif
    
      <div class="tab-pane active" id="tab1">
        <div class="box-body">
          <div class="row">
            @if(Session::get('customerId') == 0)
              <div class="form-group col-sm-6">
                <label>Customer Type *</label>
                <select class="form-control" id="customer_type" name="customer_type"  onchange="if(this.value==7002) { $('.mfgName').show() }else{ $('.mfgName').hide(); getCustomerUser(this.value); }">
                  <option value="">Please choose</option>
                  @foreach($lookups as $lookup)  
                  <option value="{{$lookup->value}}" @if($lookup->value==$row->role_type) selected="selected" @endif>{{$lookup->name}}</option>
                   @endforeach
                </select>
              </div>
              <div class="form-group mfgName col-sm-6" @if($row->manufacturer_id==0) style="display: none" @endif>
                <label>Manufacturer Name</label>
                <select class="form-control" id="manufacture_id" name="manufacture_id" onchange="getCustomerUser(this.value)">
                  <option value="">Please choose</option>
                  @foreach($customers as $customer)  
                    <option value="{{$customer->customer_id}}" @if($customer->customer_id==$row->manufacturer_id) selected="selected" @endif>{{$customer->brand_name}}</option>
                   @endforeach
                </select>
              </div>
            @else
              <input type="hidden" name="customer_type" value="7002">
              <input type="hidden" name="manufacture_id" value="{{Session::get('customerId')}}">
            @endif
            <div class="form-group col-sm-6">
              <label>Role Name *</label>
              <input type="text" class="form-control" placeholder="Role Name" name="role_name" id="role_name" value="{{$row->name}}" >
            </div>
            <div class="form-group col-sm-6">
              <label>Description</label>
              <textarea class="form-control" id="description" name="description" rows="3">{{$row->description}}</textarea>
            </div>
            <div class="form-group col-sm-6">
              <label><input type="checkbox" value="1" id="opt01" name="is_active" class="minimal"  @if($row->is_active==1) checked="checked" @endif>Active</label>
            </div>
            @if($row->is_active==1)
              <script>$("#opt01").attr('checked',true);</script>
            @endif
          
          </div>
        </div>  
      </div>
      <div class="tab-pane" id="tab2">
        <div class="box-body">
          <div class="row">
            @foreach($modules as $module) 
              <?PHP $fea_id = isset($module->child[1]->feature_id) ? $module->child[1]->feature_id : 0;  ?>
              <div class="form-group col-sm-12">
                <label><input type="checkbox" value="{{$module->value}}" name="{{$module->name}}" id="{{$module->name}}_{{$module->value}}" class="minimal" onchange="checkAll('{{$module->value}}',this.checked);">{{$module->name}}</label>
              </div>
              @if(isset($module->child))
                @foreach($module->child as $moduleChild)
                  <div class="col-sm-12" style="padding-left:35px;">
                    <label><input type="checkbox" value="{{$moduleChild->feature_id}}" name="feature_name[]" id="feature_name{{$moduleChild->feature_id}}" class="minimal {{$module->value}}" @if(in_array($moduleChild->feature_id,$row->feature_id))checked="checked" @endif onchange="checkAll('{{$moduleChild->feature_id}}',this.checked);" > {{$moduleChild->name}}</label>
                  </div>
                  @if(isset($moduleChild->child))
                    @foreach($moduleChild->child as $moduleChild1)
                      <div class="col-sm-12" style="padding-left: 50px;">
                        <label>  <input type="checkbox" value="{{$moduleChild1->feature_id}}" name="feature_name[]" id="feature_name{{$moduleChild1->feature_id}}" class="parsley-validated {{$module->value}} {{$moduleChild->feature_id}}" @if(in_array($moduleChild1->feature_id,$row->feature_id))checked="checked" @endif onchange="checkAll('{{$moduleChild1->feature_id}}',this.checked);checkedParent('{{$moduleChild->feature_id}}',this.checked)">
                        {{$moduleChild1->name}}</label>
                      </div>
                      @if(isset($moduleChild1->child))
                        @foreach($moduleChild1->child as $moduleChild2)
                          <div class="col-sm-12" style="padding-left: 80px;">
                            <label><input type="checkbox" value="{{$moduleChild2->feature_id}}" name="feature_name[]" id="feature_name{{$moduleChild2->feature_id}}" class="minimal {{$module->value}} {{$moduleChild->feature_id}} {{$moduleChild1->feature_id}}"  @if(in_array($moduleChild2->feature_id,$row->feature_id))checked="checked" @endif onchange="checkedParent1('{{$moduleChild1->feature_id}}','{{$moduleChild->feature_id}}',this.checked)">{{$moduleChild2->name}}</label>
                          </div>
                        @endforeach
                      @endif
                      @endforeach 
                  @endif
                @endforeach
              @endif
            @endforeach
          </div>
        </div>  
      </div>
<!--       <div class="tab-pane" id="tab3">
        <div class="box-body">
          <div class="row col-sm-10">
            <div class="form-group col-sm-5">
              <label class=" control-label">Select Users 
                @if($addPermission==true) 
                <a href="#" data-toggle="modal" data-target="#wizardCodeModal" data-placement="right" title="Add New User!"><i class="fa fa-user-plus"></i></a>
                @endif
              </label>
              <select class="form-control " id="Selectuser" name="users" multiple="true" >
                @foreach($users as $user)
                  @if(!in_array($user->user_id,$row->user_id))
                    <option value="{{$user->user_id}}" data-imagesrc="{{URL::asset('uploads/profile_picture/'.$user->profile_picture)}}" data-description="" >{{$user->username}}</option>
                  @endif
                @endforeach   
              </select>
            </div>
            <div class="form-group col-sm-1" style="padding-top:25px;" style="float:left;">
              <div class="form-group col-sm-12">
                <input type="button" value=">>" class="btn btn-primary adduser">
              </div>
              <div class="form-group col-sm-12">  
                <input type="button" value="<<" class="btn btn-primary removeUser">
              </div>  
            </div>
            <div class="form-group col-sm-5">
              <label>Selected Users </label>
              <select class="form-control " id="user_id" name="user_id[]" multiple="true" >
                @foreach($users as $user)
                  @if(in_array($user->user_id,$row->user_id))
                    <option value="{{$user->user_id}}" selected="selected">{{$user->username}}</option>
                  @endif 
                @endforeach 
              </select>
            </div>
          </div>
        </div>  
      </div> -->
       <div class="tab-pane" id="tab3">
        <div class="box-body">
          <div class="form-group col-sm-12">
              <label>Assigned Users </label>
         <table class="table" border="1">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>User Name</th>
        </tr>
    </thead>
          @foreach($userinfo as $userinfo)
           @if(in_array($userinfo->user_id,$row->user_id))
    
    <tbody>
        <tr> <td>{{$userinfo->firstname}}&nbsp{{$userinfo->lastname}}</td>
             <td>{{$userinfo->email}}</td>
             <td>{{$userinfo->username}}</td>
     </tr>   
   
    </tbody>
           @endif 
                @endforeach 
                </table> 
              </div>
            </div>
        </div>

    
      <div class="navbar-fixed-bottom" role="navigation">
        <div id="content" class="col-md-12">
          <button type="submit" class="btn btn-primary"><i class="fa fa-hdd-o"></i> Save</button>
          <button type="button" class="btn btn-default" onclick=" location.href='/rbac'"> <i class="fa fa-times-circle"></i> Cancel</button>
        </div>           
      </div>
    </div>  
  {{Form::close()}}  
</div>



</div>
  


<div class="modal fade" id="wizardCodeModal" tabindex="-1" role="dialog" aria-labelledby="wizardCode" aria-hidden="true" style="display: none;">
<div class="modal-dialog wide">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
<h4 class="modal-title" id="wizardCode">Add New User</h4>
</div>
<div class="modal-body">

<!--<h1>Hello..!!!</h1>-->
<!--Added for popup-->
<div class="tile-body" id="popupLoader" align="center" style="display: none">
<img src="/img/ajax-loader.gif" >
</div>   
<div class="tile-body" id="popupContent">
{{Form::open(array('name'=>'userForm','id'=>'userForm','method'=>'put'))}} 
<div class="form-group">
<level for="email"></level>
<div id="erroMsg" style="color: #FF0000"></div>
</div>
<?PHP /* @if(Session::get('customerId') == 0)
<div class="row">
<div class="form-group col-sm-6">
<label for="exampleInputEmail">Customer Type</label>
<div class="input-group ">
<span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
<div id="selectbox">
<select class="chosen-select form-control parsley-validated" id="customer_type1" name="customer_type1" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox"  onchange="if(this.value==7002) { $('.mfgName1').show() }else{ $('.mfgName1').hide(); }">
<option value="">Please choose</option>
@foreach($lookups as $lookup)  
<option value="{{$lookup->value}}" >{{$lookup->name}}</option>
@endforeach
</select>
</div>
</div>
</div>
<div class="form-group col-sm-6 mfgName1" style="display:none">
<label for="exampleInputEmail">Manufacturer Name</label>
<div class="input-group ">
<span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
<div id="selectbox">
<select class="chosen-select form-control parsley-validated" id="customer_id" name="customer_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
<option value="">Please choose</option>
@foreach($customers as $customer)  
<option value="{{$customer->customer_id}}" >{{$customer->brand_name}}</option>
@endforeach
</select>
</div>
</div>
</div>
</div>
@else
<input type="hidden" name="customer_type1" value="7002" />
<input type="hidden" name="customer_id" value="{{Session::get('customerId')}}" />
@endif */ ?>
<input type="hidden" name="customer_type1" value="{{$row->role_type}}" />
<input type="hidden" name="customer_id" value="{{$row->manufacturer_id}}" />
<div class="row">
<div class="form-group col-sm-6">
<label for="exampleInputEmail">First Name</label>
<div class="input-group ">
<span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
<input type="text"  id="first_name" name="firstname" placeholder="First Name" class="form-control" required>
</div>
</div>
<div class="form-group col-sm-6">
<label for="exampleInputEmail">Last Name</label>
<div class="input-group ">
<span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
<input type="text" id="last_name" name="lastname" placeholder="Last Name" class="form-control" required>                       
</div>
</div>
</div>

<div class="row">
<div class="form-group col-sm-6">
<label for="exampleInputEmail">Phone Number</label>
<div class="input-group ">
<span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
<input required type="tel"  maxlength="10" id="phone_no" name="phone_no" placeholder="Phone Number" class="form-control mobile_no">
</div>
</div>

<div class="form-group col-sm-6">
<label for="exampleInputEmail">Email</label>
<div class="input-group ">
<span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
<input type="email" id="email" name="email" placeholder=Email" class="form-control" required>
</div>
</div>
</div>
<div class="row">
<div class="form-group col-sm-6">
<label for="exampleInputEmail">Password</label>
<div class="input-group ">
<span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
<input type="password" id="password" name="password" placeholder="" class="form-control mobile_no" required>

</div>
</div>
<div class="form-group col-sm-6">
<label for="exampleInputEmail">Confirm Password</label>
<div class="input-group">
<span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
<input type="password" id="confirm_password" name="confirm_password" placeholder="" class="form-control" required>
</div>                        
</div>      
</div>
<div class="row">

<div class="form-group col-sm-6">
<label for="exampleInputEmail">Locations</label>
<div class="input-group">
<span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
<select name="location_id" id="location_id" class="form-control">
<option value="">Please Select</option>
@foreach($locationsall as $locations)
<option value="{{$locations->location_id}}">{{$locations->location_name}}</option>
@endforeach
</select>      
</div>                        
</div> 

<div class="form-group col-sm-6">
<label for="exampleInputEmail">Business Units</label>
<div class="input-group">
<span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
<select name="business_unit_id" id="business_unit_id" class="form-control">
<option value="">Please Select</option>
@foreach($businessunits as $businessunit)
<option value="{{$businessunit->business_unit_id}}">{{$businessunit->name}}</option>
@endforeach
</select>      
</div>                        
</div>  

</div>
<div class="row">
<div class="form-group col-sm-6">
<label for="exampleInputEmail">User Name</label>
<div class="input-group ">
<span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
<input type="text" id="username" name="username" placeholder="User Name" class="form-control" required>
</div>
</div>
<div class="form-group col-sm-6">
<div id="dragandrophandler">Drag & Drop Files Here</div>
<br><br>
<div id="status1"></div>
</div>
<?PHP /*<div class="form-group col-sm-6">
<label for="exampleInputEmail"></label>
<div class="input-group ">
<div id="myproperlabel">
<div class="checkbox">
<input type="checkbox" value="1" id="optUser1" name="is_active" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated" checked="checked">
<label for="optUser1">Active</label>
</div>
</div>
</div>
</div> */ ?>

</div>

<br/><br/>
<button id="userUpdate" type="submit" class="btn btn-primary">Save</button>
<input type="hidden" name="is_active" id="is_active" value="1">
<input type="hidden" name="profile_picture" id="profile_picture" value="">
{{Form::close()}}
</div>

<!--Added for popup till here-->
</div>
</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal --> 
</section>
</div>   
</div>

<script type="text/javascript">
$(document).ready(function(){
$("#customer_type1").val($("customer_type").val());
$('.adduser').on('click', function() {
return !$('#Selectuser option:selected').remove().appendTo('#user_id');

});
$('.removeUser').on('click', function() {
return !$('#user_id option:selected').remove().appendTo('#Selectuser');

});
$("#userForm").submit(function(event){
$("#popupContent").hide();
$("#popupLoader").show();
event.preventDefault();
$.post('/rbac/saveUser',$("#userForm").serialize(),function(response){
var res_arr = response.split('|');
var data = $.parseJSON(res_arr[1]);

if(res_arr[0]=='success')
{
$("#popupLoader").hide();    
$("#popupContent").show();
$('#user_id').append('<option value="' + data.user_id + '">' + data.username + '</option>');
$('#user_id option').prop('selected', true);  

$(".close").click();
}else{
var Str='';
$("#popupLoader").hide();    
$("#popupContent").show();

if(data.customer_type!=undefined){
Str += data.customer_type+"<br>"; 
}
if(data.firstname!=undefined){
Str += data.firstname+"<br>"; 
}
if(data.lastname!=undefined){
Str += data.lastname+"<br>"; 
}
if(data.email!=undefined){
Str += data.email+"<br>"; 
}
if(data.username!=undefined){
Str += data.username+"<br>"; 
}
if(data.password!=undefined){
Str += data.password+"<br>"; 
}
if(data.confirm_password!=undefined){
Str += data.confirm_password+"<br>"; 
}
if(data.phone_no!=undefined){
Str += data.phone_no+"<br>"; 
}
if(data.message!=undefined){
Str += data.message+"<br>"; 
}
$("#erroMsg").html(Str);
}
});
});
}); 
function CheckedFeature(val, fromid, toid) 
{ 
if(val==false){
for(i=fromid;i<toid;i++)
{
$( "#feature_name"+i ).prop( "checked", false );
}
}else {
for(i=fromid;i<toid;i++)
{
$( "#feature_name"+i ).prop( "checked", true );
}
}

}
function checkAll(clsId, state)
{ 
$("."+clsId).prop("checked", state);

}

function checkedParent(id,state){
if(state==true){
$("#feature_name"+id).prop('checked',true);
}    
}

function checkedParent1(id,subid,state){
if(state==true){
$("#feature_name"+id).prop('checked',true);
$("#feature_name"+subid).prop('checked',true);
}    
}

function getCustomerUser(id)
{
if(id==7001){
$("#customer_type1").val(id);
id=0;

} 

$("#customer_id").val(id);

$.get('/rbac/getUserDetail/'+id,function(data){

var dataArr = $.parseJSON(data);
var Sel = $('#Selectuser');
Sel.empty()
for (var i=0; i<dataArr['users'].length; i++) {
Sel.append('<option value="' + dataArr['users'][i].user_id + '">' + dataArr['users'][i].username + '</option>');
}
var Location = $("#location_id");
for (var i=0; i<dataArr['locations'].length; i++) {
Location.append('<option value="' + dataArr['locations'][i].location_id + '">' + dataArr['locations'][i].location_name+ '</option>');
}
var business_unit_id = $("#business_unit_id");
for (var i=0; i<dataArr['businessunits'].length; i++) {
business_unit_id.append('<option value="' + dataArr['businessunits'][i].business_unit_id + '">' + dataArr['businessunits'][i].name+ '</option>');
}

/* var Str = '<table width="100%" style="border-bottom: 1px solid">';
Str +=  '<tr><th style="border-bottom: 1px solid"><input type="checkbox" value="1" name="multiSelect" id="multiSelect"> </th>';         
Str += '<th style="border-bottom: 1px solid">User Name</th>';
Str += '<th style="border-bottom: 1px solid">First Name</th>';
Str += '<th style="border-bottom: 1px solid">Last Name</th>';
Str += '<th style="border-bottom: 1px solid">Email</th>';
Str += '<th style="border-bottom: 1px solid">Status</th></tr>';

for(i=0;i< dataArr.length;i++){
Str += '<tr><td><input type="checkbox" value="'+dataArr[i].user_id+'" name="user_id[]" id="user_id_'+dataArr[i].user_id+'"> </td>';
Str +='<td>'+dataArr[i].username+'</td>';
Str +='<td>'+dataArr[i].firstname+'</td>';
Str +='<td>'+dataArr[i].lastname+'</td>';
Str +='<td>'+dataArr[i].email+'</td>';
Str +='<td>'+dataArr[i].is_active+'</td>';
Str +='</tr>'
}
Str += '</table>'; 

$("#userTab").html(Str); */
});

}
</script>   
<script>
function sendFileToServer(formData,status)
{
var uploadURL ="/rbac/uploadProfilePic"; //Upload URL
var extraData ={}; //Extra Data.
var jqXHR=$.ajax({
xhr: function() {
var xhrobj = $.ajaxSettings.xhr();
if (xhrobj.upload) {
xhrobj.upload.addEventListener('progress', function(event) {
var percent = 0;
var position = event.loaded || event.position;
var total = event.total;
if (event.lengthComputable) {
percent = Math.ceil(position / total * 100);
}
//Set progress
status.setProgress(percent);
}, false);
}
return xhrobj;
},
url: uploadURL,
type: "POST",
contentType:false,
processData: false,
cache: false,
data: formData,
success: function(data){
status.setProgress(100);

//$("#status1").append("Data from Server:"+data+"<br>");    
$("#profile_picture").val(data);
}
});	

status.setAbort(jqXHR);
}

var rowCount=0;
function createStatusbar(obj)
{
rowCount++;
var row="odd";
if(rowCount %2 ==0) row ="even";
this.statusbar = $("<div class='statusbar "+row+"'></div>");
this.filename = $("<div class='filename'></div>").appendTo(this.statusbar);
this.size = $("<div class='filesize'></div>").appendTo(this.statusbar);
this.progressBar = $("<div class='progressBar'><div></div></div>").appendTo(this.statusbar);
this.abort = $("<div class='abort'>Abort</div>").appendTo(this.statusbar);
obj.after(this.statusbar);

this.setFileNameSize = function(name,size)
{
var sizeStr="";
var sizeKB = size/1024;
if(parseInt(sizeKB) > 1024)
{
var sizeMB = sizeKB/1024;
sizeStr = sizeMB.toFixed(2)+" MB";
}
else
{
sizeStr = sizeKB.toFixed(2)+" KB";
}

this.filename.html(name);
this.size.html(sizeStr);
}
this.setProgress = function(progress)
{		
var progressBarWidth =progress*this.progressBar.width()/ 100;  
this.progressBar.find('div').animate({ width: progressBarWidth }, 10).html(progress + "%&nbsp;");
if(parseInt(progress) >= 100)
{
this.abort.hide();
}
}
this.setAbort = function(jqxhr)
{
var sb = this.statusbar;
this.abort.click(function()
{
jqxhr.abort();
sb.hide();
});
}
}
function handleFileUpload(files,obj)
{
for (var i = 0; i < files.length; i++) 
{
var fd = new FormData();
fd.append('file', files[i]);

var status = new createStatusbar(obj); //Using this we can set progress.
status.setFileNameSize(files[i].name,files[i].size);
sendFileToServer(fd,status);

}
}
$(document).ready(function()
{
var obj = $("#dragandrophandler");
obj.on('dragenter', function (e) 
{
e.stopPropagation();
e.preventDefault();
$(this).css('border', '2px solid #0B85A1');
});
obj.on('dragover', function (e) 
{
e.stopPropagation();
e.preventDefault();
});
obj.on('drop', function (e) 
{

$(this).css('border', '2px dotted #0B85A1');
e.preventDefault();
var files = e.originalEvent.dataTransfer.files;

//We need to send dropped files to Server
handleFileUpload(files,obj);
});
$(document).on('dragenter', function (e) 
{
e.stopPropagation();
e.preventDefault();
});
$(document).on('dragover', function (e) 
{
e.stopPropagation();
e.preventDefault();
obj.css('border', '2px dotted #0B85A1');
});
$(document).on('drop', function (e) 
{
e.stopPropagation();
e.preventDefault();
});

});
</script>
@stop