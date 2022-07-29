
  <style>
.btn-space {
    margin-right: 5px;
}

/* start of loading image */

/* Absolute Center Spinner */
.loading {
  position: fixed;
  z-index: 999;
  height: 2em;
  width: 2em;
  overflow: show;
  margin: auto;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
}

/* Transparent Overlay */
.loading:before {
  content: '';
  display: block;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.3);
}

/* :not(:required) hides these rules from IE9 and below */
.loading:not(:required) {
  /* hide "loading..." text */
  font: 0/0 a;
  color: transparent;
  text-shadow: none;
 background-color: transparent;
  border: 0;
}

.loading:not(:required):after {
  content: '';
  display: block;
  font-size: 10px;
  width: 1em;
  height: 1em;
  margin-top: -0.5em;
  -webkit-animation: spinner 1500ms infinite linear;
  -moz-animation: spinner 1500ms infinite linear;
  -ms-animation: spinner 1500ms infinite linear;
  -o-animation: spinner 1500ms infinite linear;
  animation: spinner 1500ms infinite linear;
  border-radius: 0.5em;
  -webkit-box-shadow: rgba(0, 0, 0, 0.75) 1.5em 0 0 0, rgba(0, 0, 0, 0.75) 1.1em 1.1em 0 0, rgba(0, 0, 0, 0.75) 0 1.5em 0 0, rgba(0, 0, 0, 0.75) -1.1em 1.1em 0 0, rgba(0, 0, 0, 0.5) -1.5em 0 0 0, rgba(0, 0, 0, 0.5) -1.1em -1.1em 0 0, rgba(0, 0, 0, 0.75) 0 -1.5em 0 0, rgba(0, 0, 0, 0.75) 1.1em -1.1em 0 0;
  box-shadow: rgba(0, 0, 0, 0.75) 1.5em 0 0 0, rgba(0, 0, 0, 0.75) 1.1em 1.1em 0 0, rgba(0, 0, 0, 0.75) 0 1.5em 0 0, rgba(0, 0, 0, 0.75) -1.1em 1.1em 0 0, rgba(0, 0, 0, 0.75) -1.5em 0 0 0, rgba(0, 0, 0, 0.75) -1.1em -1.1em 0 0, rgba(0, 0, 0, 0.75) 0 -1.5em 0 0, rgba(0, 0, 0, 0.75) 1.1em -1.1em 0 0;
}

/* Animation */

@-webkit-keyframes spinner {
  0% {
    -webkit-transform: rotate(0deg);
    -moz-transform: rotate(0deg);
    -ms-transform: rotate(0deg);
    -o-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    -moz-transform: rotate(360deg);
    -ms-transform: rotate(360deg);
    -o-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
@-moz-keyframes spinner {
  0% {
    -webkit-transform: rotate(0deg);
    -moz-transform: rotate(0deg);
    -ms-transform: rotate(0deg);
    -o-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    -moz-transform: rotate(360deg);
    -ms-transform: rotate(360deg);
    -o-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
@-o-keyframes spinner {
  0% {
    -webkit-transform: rotate(0deg);
    -moz-transform: rotate(0deg);
    -ms-transform: rotate(0deg);
    -o-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    -moz-transform: rotate(360deg);
    -ms-transform: rotate(360deg);
    -o-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
@keyframes spinner {
  0% {
    -webkit-transform: rotate(0deg);
    -moz-transform: rotate(0deg);
    -ms-transform: rotate(0deg);
    -o-transform: rotate(0deg);
    transform: rotate(0deg);
  }
  100% {
    -webkit-transform: rotate(360deg);
    -moz-transform: rotate(360deg);
    -ms-transform: rotate(360deg);
    -o-transform: rotate(360deg);
    transform: rotate(360deg);
  }
}
/*<!--end of image loading -->
*/

</style>
<!-- <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.2/jspdf.min.js"></script>
 -->
<div class="loading"  id="img" style="display: none;">
  <!-- <img src="/img/loading.gif"/ > -->
  </div> 

{{ Form::open(array('url' => '/users/save/'.$user->user_id,'method'=>'POST','id'=>'userFrm', 'name'=>'userFrm','role'=>"form")) }}
  <div class="form-group">
    <level for="email"></level>
    <div id="erroMsg" style="color: #FF0000"></div>
  </div>
  <div class="box-body">
  @if(Session::get('customerId')== 0)
    <div class="form-group col-sm-6">
      <label for="exampleInputCustomerType">Customer Type*</label>
        <select class="form-control" id="customer_type" name="customer_type" onchange="if(this.value==7002) { $('.mfgName1').show() }else{ $('.mfgName1').hide(); }">
            <option value="">Please choose</option>
            @foreach($lookups as $lookup)  
                <option value="{{$lookup->value}}" @if($user->customer_type==$lookup->value) selected="selected" @endif>{{$lookup->name}}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group mfgName1 col-sm-6" style="display:none">
      <label for="exampleInputEmail">Customer Name*</label>
        <select class="form-control" id="customer_id" name="customer_id">
          <option value="">Please choose</option>
          @foreach($customers as $customer)  
            <option value="{{$customer->customer_id}}" @if($user->customer_id==$customer->customer_id) selected="selected" @endif>{{$customer->brand_name}}</option>
           @endforeach
        </select>
    </div>
    @if($user->customer_id > 0)
      <script type="text/javascript">
        $('.mfgName1').show();
      </script>
    @endif
  @else
    <input type="hidden" name="customer_type" value="7002">
    <input type="hidden" name="customer_id" value="{{Session::get('customerId')}}">
  @endif
  <div class="form-group col-sm-6">
    <label for="exampleInputEmail">First Name*</label>
      <input type="text"  id="firstname" name="firstname" placeholder="First Name" class="form-control" value="{{$user->firstname}}" >
  </div>
  <div class="form-group col-sm-6">
    <label for="exampleInputEmail">Last Name*</label>
      <input type="text"  id="lastname" name="lastname" placeholder="Last Name" class="form-control" value="{{$user->lastname}}">
  </div>
  <div class="form-group col-sm-6">
    <label for="exampleInputEmail">Phone Number*</label>
    <input type="text" id="phone_no" name="phone_no" placeholder="Phone Number" class="form-control mobile_no" maxlength="10"  value="{{$user->phone_no}}">
  </div>
  <div class="form-group col-sm-6">
    <label for="exampleInputEmail">Email*</label>
    <input type="text" id="email" name="email" placeholder="Email Address" class="form-control" value="{{$user->email}}">
  </div>      
  <div class="form-group col-sm-6">
      <label for="exampleInputEmail">User Name</label>
      <input type="text"  id="username" name="username" placeholder="User Name" class="form-control" value="{{$user->username}}">
  </div>
  <!-- <div class="form-group col-sm-6">
    <label for="exampleInputEmail">Password*</label>
    <input type="password" id="password" name="password" placeholder="" class="form-control mobile_no" >
  </div>
  <div class="form-group col-sm-6">
      <label for="exampleInputEmail">Confirm Password*</label>
      <input type="password" id="confirm_password" name="confirm_password" placeholder="" class="form-control" >
  </div> -->
  @if(isset($locationsall) && !empty($locationsall))
    <div class="form-group col-sm-6">
        <label for="exampleInputEmail">Locations</label>
        <select name="location_id" id="location_id" class="form-control">
              <option value="">Please Select</option>
              @foreach($locationsall as $locations)
                <option value="{{$locations->location_id}}" @if($user->location_id==$locations->location_id) selected="selected" @endif>{{$locations->location_name}}</option>
              @endforeach
            </select>      
    </div> 
    @endif
    @if(isset($businessunits) && !empty($businessunits))
    <div class="form-group col-sm-6">
        <label for="exampleInputEmail">Business Units</label>
        <select name="business_unit_id" id="business_unit_id" class="form-control">
            <option value="">Please Select</option>
            @foreach($businessunits as $businessunit)
              <option value="{{$businessunit->business_unit_id}}" @if($user->business_unit_id==$businessunit->business_unit_id) selected="selected" @endif>{{$businessunit->name}}</option>
            @endforeach
        </select>      
    </div>  
  @endif
  <div class="form-group col-sm-6">
      <label for="exampleInputEmail">Assign Roles</label>
      <select name="role_id" id="role_id" class="form-control">
        <option value="">Please Select</option>
        @foreach($roles as $role)
          <option value="{{$role->role_id}}" @if(isset($rolesId->role_id) && $role->role_id==$rolesId->role_id) selected="selected" @endif>{{$role->name}} -- {{$role->brand_name}}</option>
        @endforeach
      </select>      
  </div> 
  <div class="form-group col-sm-6">
    <label for="exampleInputEmail"><input type="checkbox" value="1" id="is_active" name="is_active" @if($user->is_active==1) checked="checked" @endif>     Active</label>
  </div>
</div><!-- /.box-body -->
<div class="box-body">
  <div class="form-group">
    <div id="dragandrophandler">Drag & Drop Files Here</div>
    <br><br>
    <div id="status1"></div>
  </div>
</div>  
  <div class="box-footer">
    <button class="btn btn-primary" type="submit">Submit</button>
  </div>
  <input type="hidden" name="profile_picture" id="profile_picture" value="">
{{Form::close()}}
@if(Session::get('customerId')== 0)
<script type="text/javascript">
$(document).ready(function() {
    $('#userFrm')
    .bootstrapValidator({
//        live: 'disabled',
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            customer_type:{
              validators : {
                notEmpty:{
                  message : 'The customer type is required and cannot be empty'
                }
              }
            },
            firstname:{
              validators : {
                notEmpty:{
                  message : 'The frist name is required and cannot be empty'
                },
                regexp: {
                        regexp: /^[a-zA-Z0-9\s]+$/i,
                        message: 'The first name can consist of alphabetical characters and spaces only'
                    }
              }
            },
            lastname:{
              validators : {
                notEmpty:{
                  message : 'The last name is required and cannot be empty'
                },
                regexp: {
                        regexp: /^[a-zA-Z0-9\s]+$/i,
                        message: 'The last name can consist of alphabetical characters and spaces only'
                    }
              }
            },
            phone_no:{
              validators : {
                // notEmpty:{
                //   message : 'The phone number is required and cannot be empty'
                // },
                numeric:{
                  message : 'The phone number should be numeric'
                },
                stringLength: {
                    min: 10,
                    max: 10,
                    message: 'The phone  number must be 10 digits'
                },
              }
            },
            email: {
                validators: {
                  notEmpty: {
                        message: 'The email is required and cannot be empty'
                    },
                    emailAddress: {
                        message: 'The input is not a valid email address'
                    }
                }
            },
            /*username: {
                validators: {
                    notEmpty: {
                        message: 'The username is required and cannot be empty'
                    },
                    stringLength: {
                        min: 6,
                        max: 14,
                        message: 'The username must be more than 6 and less than 14 characters long'
                    },
                }
            },
            password: {
                validators: {
                    
                    stringLength: {
                        min: 5,
                        max: 14,
                        message: 'The password must be more than 4 and less than 14 characters long'
                    },
                    different: {
                        field: 'username',
                        message: 'The password cannot be the same as username'
                    }
                }
            },
            confirm_password: {
                validators: {
                    
                    stringLength: {
                        min: 4,
                        max: 14,
                        message: 'The confirm password must be more than 4 and less than 14 characters long'
                    },
                    identical: {
                        field: 'password',
                        message: 'The password and its confirm are not the same'
                    },
                    different: {
                        field: 'username',
                        message: 'The confirm password cannot be the same as username'
                    }
                }
            }*/
        }
    })
    .on('success.form.bv', function(e) {
      e.preventDefault(); 
        // $("#userDiv").hide();
        // $("#popupLoader").show();
        $("#img").show();
        $.post($(this).attr('action'),$(this).serialize(),function(response){
            //var res_arr = response.split('|');
            var res_arr = response;
                var data = $.parseJSON(res_arr[1])
                if(res_arr[0]=='success')
                {
                    // $("#popupLoader").hide();    
                    // $("#userDiv").show();
                    // $(".close").click();
                    alert("User Updated successfully.");
                    location.href='/users';
                }else{
                    $("#img").hide();
                    $("#popupLoader").hide();    
                    $("#userDiv").show();
                    var Str='';
                    
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
                    if(data.confirm_password!=undefined){
                       Str += data.confirm_password+"<br>"; 
                    }
                    
                    if(data.message!=undefined){
                       Str += data.message+"<br>"; 
                    }
                   $("#erroMsg").html(Str);
                }    
        });
    })
  ;

});
</script>
@else
<script type="text/javascript">
$(document).ready(function() {
    $('#userFrm')
    .bootstrapValidator({
//        live: 'disabled',
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            
            firstname:{
              validators : {
                notEmpty:{
                  message : 'The frist name is required and cannot be empty'
                },
                regexp: {
                        regexp: /^[a-zA-Z0-9\s]+$/i,
                        message: 'The first name can consist of alphabetical characters and spaces only'
                    }
              }
            },
            lastname:{
              validators : {
                notEmpty:{
                  message : 'The last name is required and cannot be empty'
                },
                regexp: {
                        regexp: /^[a-zA-Z0-9\s]+$/i,
                        message: 'The last name can consist of alphabetical characters and spaces only'
                    }
              }
            },
            phone_no:{
              validators : {
                // notEmpty:{
                //   message : 'The phone number is required and cannot be empty'
                // },
                numeric:{
                  message : 'The phone number should be numeric'
                },
                stringLength: {
                    min: 10,
                    max: 10,
                    message: 'The phone  number must be 10 digits'
                },
              }
            },
            email: {
                validators: {
                  notEmpty: {
                        message: 'The email is required and cannot be empty'
                    },
                    emailAddress: {
                        message: 'The input is not a valid email address'
                    }
                }
            },
           /* username: {
                validators: {
                    notEmpty: {
                        message: 'The username is required and cannot be empty'
                    },
                    stringLength: {
                        min: 6,
                        max: 14,
                        message: 'The username must be more than 6 and less than 14 characters long'
                    },
                }
            },
            password: {
                validators: {
                    
                    stringLength: {
                        min: 4,
                        max: 14,
                        message: 'The password must be more than 4 and less than 14 characters long'
                    },
                    different: {
                        field: 'username',
                        message: 'The password cannot be the same as username'
                    }
                }
            },
            confirm_password: {
                validators: {
                    
                    stringLength: {
                        min: 4,
                        max: 14,
                        message: 'The confirm password must be more than 4 and less than 14 characters long'
                    },
                    identical: {
                        field: 'password',
                        message: 'The password and its confirm are not the same'
                    },
                    different: {
                        field: 'username',
                        message: 'The confirm password cannot be the same as username'
                    }
                }
            }*/
        }
    })
    .on('success.form.bv', function(e) {
      e.preventDefault(); 
        // $("#userDiv").hide();
        // $("#popupLoader").show();
        $("#img").show();
        $.post($(this).attr('action'),$(this).serialize(),function(response){
            //var res_arr = response.split('|');
            var res_arr = response;
                var data = $.parseJSON(res_arr[1])
                if(res_arr[0]=='success')
                {
                    // $("#popupLoader").hide();    
                    // $("#userDiv").show();
                    // $(".close").click();
                    alert("User Updated successfully.");
                    location.href='/users';
                }else{
                    $("#img").hide();
                    $("#popupLoader").hide();    
                    $("#userDiv").show();
                    var Str='';
                    
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
                    if(data.confirm_password!=undefined){
                       Str += data.confirm_password+"<br>"; 
                    }
                    
                    if(data.message!=undefined){
                       Str += data.message+"<br>"; 
                    }
                   $("#erroMsg").html(Str);
                }    
        });
    })
  ;

});
</script>
@endif
<script>
function sendFileToServer(formData,status)
{
  var uploadURL ="/users/uploadProfilePic"; //Upload URL
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
  <?PHP /*{{ Form::open(array('url' => '/users/save/'.$user->user_id,'method'=>'POST','id'=>'userFrm')) }}
    <div class="form-group">
        <level for="email"></level>
        <div id="erroMsg" style="color: #FF0000"></div>
    </div>
    @if(Session::get('customerId')== 0)
    <div class="row">
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Customer Type</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
            <select class="chosen-select form-control parsley-validated" id="customer_type" name="customer_type" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox"  onchange="if(this.value==7002) { $('.mfgName1').show() }else{ $('.mfgName1').hide(); }">
                <option value="" >Please choose</option>
                @foreach($lookups as $lookup)  
                    <option value="{{$lookup->value}}" @if($user->customer_type==$lookup->value) selected="selected" @endif>{{$lookup->name}}</option>
                @endforeach
            </select>
          </div>
        </div>

        <div class="form-group col-sm-6 mfgName1" style="display:none">
          <label for="exampleInputEmail">Customer Name</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
            <div id="selectbox">
                <select class="chosen-select form-control parsley-validated" id="customer_id" name="customer_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                  <option value="">Please choose</option>
                  @foreach($customers as $customer)  
                    <option value="{{$customer->customer_id}}" @if($user->customer_id==$customer->customer_id) selected="selected" @endif>{{$customer->brand_name}}</option>
                   @endforeach
                </select>
            </div>
          </div>
        </div>
    </div>
    @else
    <input type="hidden" name="customer_type" value="7002">
    <input type="hidden" name="customer_id" value="{{Session::get('customerId')}}">
    @endif
    <div class="row">
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Salt</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
            <input type="text" id="salt" name="salt" placeholder="salt" value="{{$user->salt}}" class="form-control " required>
          </div>
        </div>

        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">User Name</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
            <input type="text"  id="username" name="username" placeholder="username" value="{{$user->username}}" class="form-control required" required>
          </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">First Name</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
            <input type="text"  id="firstname" name="firstname" placeholder="firstname" value="{{$user->firstname}}" class="form-control" required>
          </div>
        </div>


        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Last Name</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
            <input type="text"  id="lastname" name="lastname" placeholder="lastname" value="{{$user->lastname}}" class="form-control" required>
          </div>
        </div>
    </div>
        <div class="row">
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Phone Number</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
            <input type="text" id="phone_no" name="phone_no" placeholder="phone_no" value="{{$user->phone_no}}" class="form-control mobile_no" required>

        </div>
      </div>
      <div class="form-group col-sm-6">
            <label for="exampleInputEmail">Email</label>
            <div class="input-group">
              <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
              <input type="email" id="email" name="email" placeholder="email" value="{{$user->email}}" class="form-control" required>
            </div>                        
        </div>      


        
        </div>
    <div class="row">
        <div class="form-group col-sm-6">
          <label for="exampleInputEmail">Password</label>
          <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
            <input type="password" id="password" name="password" placeholder="" class="form-control mobile_no" >

        </div>
      </div>
      <div class="form-group col-sm-6">
            <label for="exampleInputEmail">Confirm Password</label>
            <div class="input-group">
              <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
              <input type="password" id="confirm_password" name="confirm_password" placeholder="" class="form-control" >
            </div>                        
        </div>      
    </div>
        <div class="row">
            @if(isset($locationsall) && !empty($locationsall))
            <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Locations</label>
                <div class="input-group">
                  <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                  <select name="location_id" id="location_id" class="form-control">
                      <option value="">Please Select</option>
                      @foreach($locationsall as $locations)
                        <option value="{{$locations->location_id}}" @if($user->location_id==$locations->location_id) selected="selected" @endif>{{$locations->location_name}}</option>
                      @endforeach
                    </select>      
                </div>                        
            </div> 
            @endif
            @if(isset($businessunits) && !empty($businessunits))
            <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Business Units</label>
                <div class="input-group">
                  <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                  <select name="business_unit_id" id="business_unit_id" class="form-control">
                      <option value="">Please Select</option>
                      @foreach($businessunits as $businessunit)
                        <option value="{{$businessunit->business_unit_id}}" @if($user->business_unit_id==$businessunit->business_unit_id) selected="selected" @endif>{{$businessunit->name}}</option>
                      @endforeach
                    </select>      
                </div>                        
            </div>  
            @endif
        </div>         

        <div class="row">
            <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Assign Roles</label>
                <div class="input-group">
                  <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                 <!-- <input type="text" id="is_active" name="is_active" placeholder="is_active" class="form-control" >  -->
                  <select name="role_id" id="role_id" class="form-control">
                      <option value="">Please Select</option>
                      @foreach($roles as $role)
                        <option value="{{$role->role_id}}" @if(isset($rolesId->role_id) && $role->role_id==$rolesId->role_id) selected="selected" @endif>{{$role->name}}</option>
                      @endforeach
                    </select>      
                   <!-- <input name="is_active" type="checkbox" value="1" placeholder="is_active"> -->
                </div>                        
            </div> 
            <div class="form-group col-sm-6">
                <label for="exampleInputEmail">Is Active</label>
                <div class="input-group">
                  <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <div class="checkbox">
                        <input type="checkbox" value="1" id="is_active" name="is_active" @if($user->is_active==1) checked="checked" @endif>
                        <label for="is_active">Active</label>
                    </div>
                </div>                        
            </div>
        </div>
       
        <div class="row">
            <div class="form-group form-footer">
                <div class="col-sm-offset-5 col-sm-3">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="reset" class="btn btn-default">Reset</button>
                </div>
            </div>
        </div>
{{Form::close()}}
<script>
$(document).ready(function(e){
    $("#userFrm").submit(function(e){
        e.preventDefault();
        $.post($(this).attr('action'),$(this).serialize(),function(response){
            var res_arr = response.split('|');
                var data = $.parseJSON(res_arr[1])
                if(res_arr[0]=='success')
                {
                    $(".close").click();
                    location.href='/users';
                }else{
                    var Str='';
                    
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
                    if(data.confirm_password!=undefined){
                       Str += data.confirm_password+"<br>"; 
                    }
                    if(data.message!=undefined){
                       Str += data.message+"<br>"; 
                    }
                   $("#erroMsg").html(Str);
                }    
        });
    });
})    
</script> */ ?>