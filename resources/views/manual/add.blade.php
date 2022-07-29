@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')
@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}    
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

    #loading{

    display:none;
    position:fixed;
    top:300px;
    left:700px;
    z-index: 1;
}
</style>

@stop

  @section('script')
      
      {{HTML::script('jqwidgets/jqxcore.js')}}
      {{HTML::script('js/common-validator.js')}}
      {{HTML::script('js/jquery.validate.min.js')}}
      {{HTML::script('js/helper.js')}}
      {{HTML::script('jqwidgets/jqxbuttons.js')}}
      {{HTML::script('jqwidgets/jqxscrollbar.js')}}
      {{HTML::script('jqwidgets/jqxlistbox.js')}}
      {{HTML::script('jqwidgets/jqxdropdownlist.js')}}
      {{HTML::script('jqwidgets/jqxdropdownbutton.js')}}
      {{HTML::script('jqwidgets/jqxcolorpicker.js')}}
      {{HTML::script('jqwidgets/jqxwindow.js')}}
      {{HTML::script('jqwidgets/jqxeditor.js')}}
      {{HTML::script('jqwidgets/jqxtooltip.js')}}
      {{HTML::script('jqwidgets/jqxcheckbox.js')}}
      {{HTML::script('scripts/demos.js')}}
      <script type="text/javascript">
        $(document).ready(function () {
            $('#content').jqxEditor({
                height: "400px"
            });
        });
    </script>

@stop
<div class="box">
    <div class="box-header">
      <h3 class="box-title"><i class="fa fa-th"></i><strong>Add </strong> New </h3>
    </div>
    <div class="box-body">
      	{{Form::open(array('url'=>'manual/save/','method'=>'post','id'=>'manual_form'))}}
  			<div class="row">
            	<div class="form-group col-sm-6">
                	<label>Parent Screen Name</label>
                  	<div id="selectbox">
                    	<select class="form-control select2" id="parent_screen_id" name="parent_screen_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                      	<option value="">Please choose</option>
                          @foreach($manuals as $manual)  
                            <option value="{{$manual->manual_id}}" @if(isset($row['manual_id']) && $row['parent_screen_id']==$manual->manual_id) selected="selected" @endif>{{$manual->screen_name}}</option>
                          @endforeach
                    	</select>
                  	</div>
              	</div>
              
                <div class="form-group col-sm-6">
                    <label>Screen Name *</label>
                    
                        <input type="text" class="form-control select2" placeholder="Screen Name" name="screen_name" id="screen_name" @if(isset($row['screen_name'])) value="{{$row['screen_name']}}" @endif >
                </div>
            </div>
            <div class="row">    
              	<div class="form-group col-sm-12">
                	<label>Content</label>
                
                  	<textarea class="form-control select2" id="content" name="content" rows="3">@if(isset($row['content'])) {{$row['content']}} @endif</textarea>
                
              	</div>
            </div>
            <div class="row">  	
            	<div class="form-group col-sm-6">
                    <label>Previous Screen Name</label>
                  	<div id="selectbox">
                    	<select class="form-control select2" id="previous_screen_id" name="previous_screen_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                      	<option value="">Please choose</option>
                          @foreach($manuals as $manual)  
                            <option value="{{$manual->manual_id}}" @if(isset($row['manual_id']) && $row['previous_screen_id']==$manual->manual_id) selected="selected" @endif>{{$manual->screen_name}}</option>
                          @endforeach
                    	</select>
                  	</div>
              	</div>
              	<div class="form-group col-sm-6">
                    <label>Next Screen Name</label>
                  	<div id="selectbox">
                    	<select class="form-control select2" id="next_screen_id" name="next_screen_id" parsley-trigger="change" parsley-required="true" parsley-error-container="#selectbox">
                      	<option value="">Please choose</option>
                          @foreach($manuals as $manual)  
                            <option value="{{$manual->manual_id}}" @if(isset($row['manual_id']) && $row['next_screen_id']==$manual->manual_id) selected="selected" @endif>{{$manual->screen_name}}</option>
                          @endforeach
                    	</select>
                  	</div>
              	</div>
            </div>
            <div class="row">
            	<div class="form-group col-sm-6">
            		<div id="dragandrophandler">Drag & Drop Files Here</div>
				      <br><br>
				    <div id="status1"></div>
				    <input type="hidden" name="fileupload" id="fileupload" value="">
            	</div>
            	<div class="form-group col-sm-6">
            		<div class="col-sm-10 ">
	                	<button type="button" class="btn btn-default pull-right" onclick=" location.href='/manual'"> <i class="fa fa-times-circle"></i> Cancel</button>
	                </div>
	        	
	                <div class="col-sm-2 ">
	                	<button type="submit" class="btn btn-primary pull-right"><i class="fa fa-hdd-o"></i> Save</button>
	                </div>
            	</div>
            </div>
            
      		<input type="hidden" name="manual_id" value="{{$manual_id}}">
      	{{Form::close()}}
    </div>
    <div class="box-footer">
    </div>  
  </div>

  <script type="text/javascript">
/*	  $(document).ready(function () {
	  	$('#manual_form').bootstrapValidator({
	  		message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
            	screen_name: {
                    validators: {
                        notEmpty: {
                            message: 'The screen name is required and can\'t be empty'
                        }                        
                    }
                },
                content: {
                    validators: {
                        notEmpty: {
                            message: 'The content is required and can\'t be empty'
                        }                        
                    }
                },
                previous_screen_id: {
                    validators: {
                        notEmpty: {
                            message: 'The prvious page scree name is required and can\'t be empty'
                        }                        
                    }
                },
                next_screen_id: {
                    validators: {
                        notEmpty: {
                            message: 'The next page scree name is required and can\'t be empty'
                        }                        
                    }
                }
            }
	  	}).on('success.form.bv', function(event) {
            
            $.post($(this).attr('action'),$(this).serialize(),function(response) {        
         		alert(response);
            });
            //return false;
        })
	});*/
      /*=====================file upload drage and drop =======================*/  
function sendFileToServer(formData,status)
{
  var uploadURL ="/manual/uploadFile"; //Upload URL
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
                $("#fileupload").val(data);
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