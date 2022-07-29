 @extends('layouts.default')

@extends('layouts.sideview')

@section('content')

<aside class="right-side">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Packages
            <small>Edit</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href=""><i class="fa fa-dashboard"></i> Home</a></li>
            <!-- <li class="active">Dashboard</li> -->
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
    <!--box-start-->
    <div class="box">
    
      <div class="box-body table-responsive">
<div class="container" style="width:auto !important;">

<h1 style="color:Brown">Edit {{ $packages->package_name }}</h1>

{{ HTML::ul($errors->all()) }}

{{ Form::open(array('url' => 'package/packageupdate/'.$packages->id)) }}
{{ Form::hidden('_method', 'PUT') }}

<fieldset>
<legend>package details</legend>
  <table width="100%" border="0" cellspacing="0" cellpadding="6">
    <tbody>
            
        <tr>
                <td>Package Name :</td>
                <td>:</td>
                <td><input type='text'  name='package_name' id='package_name' value='{{$packages->package_name}}' class='input1'></td>
                <td width="10"></td>
                <!-- <td>Validation Message</td> -->
              </tr>
        
        <tr>
                <td>Product Name :</td>
                <td>:</td>
                <td><select name="pname" id="pname" class="select1">
                      @foreach($products as $key => $value)
                @if($key==$packages->pname)
            <option value="{{ $key}}" selected="selected">{{ $value}}</option>
              @else
            <option value="{{ $key}}">{{ $value}}</option>
              @endif
                    @endforeach
                 </select></td>
                <td width="10"></td>
                <!-- <td>Validation Message</td> -->
              </tr>
        
        <tr>
                <td>Weight :</td>
                <td>:</td>
                <td><input type='text'  name='weight' id='weight' value='{{$packages->weight}}' class='input1'></td>
                <td width="10"></td>
                <!-- <td>Validation Message</td> -->
              </tr>
         
         <tr>
                <td>Weight UOM :</td>
                <td>:</td>
                <td><select name="weight_uom_id" id="weight_uom_id" class="select1">
             @foreach($weight_uom as $key => $value)
             @if($key==$packages->weight_uom_id)
            <option value="{{ $key}}" selected="selected">{{ $value}}</option>
             @else
            <option value="{{ $key}}">{{ $value}}</option>
             @endif
             @endforeach
                 </select></td>
                <td width="10"></td>
                <!-- <td>Validation Message</td> -->
              </tr>
        
        <tr>
                <td>Select Package Type :</td>
                <td>:</td>
                <td><select name="package_type_id" id="package_type_id"  class="select1">                   
                 @if($packages->package_type_id==101)
                  <option value="101" selected="selected">Primary</option>
                  @else
                 <option value="101">Primary</option>
                  @endif  
                  @if($packages->package_type_id==2)
                  <option value="102" selected="selected">Secondary</option>
                  @else
                 <option value="102">Secondary</option>
                  @endif 
                  @if($packages->package_type_id==3)
                  <option value="103" selected="selected">Teritiary</option>
                  @else
                 <option value="103">Teritiary</option>
                  @endif 
                  </select></td>
                <td width="10"></td>
                <!-- <td>Validation Message</td> -->
              </tr>
        
                   
         
         </tbody>
     </table>
   </fieldset>
<fieldset>
<legend>package dimensions</legend>
     <table width="100%" border="0" cellspacing="0" cellpadding="6">
    <tbody>
          
<tr>
                <td>Length</td>
                <td>:</td>
                <td><input type='text'  name='package_length' id='package_length' value='{{$packages->package_length}}' class='input1'></td>
                <td width="10"></td>
                <!-- <td>Validation Message</td> -->
              </tr>
          
<tr>
                <td>Width</td>
                <td>:</td>
                <td><input type='text'  name='package_width' id='package_width' value='{{$packages->package_width}}' class='input1'></td>
                <td width="10"></td>
                <!-- <td>Validation Message</td> -->
              </tr>       
         
         <tr>
                <td>Height</td>
                <td>:</td>
                <td><input type='text'  name='package_height' id='package_height' value='{{$packages->package_height}}' class='input1'></td>
                <td width="10"></td>
                <!-- <td>Validation Message</td> -->
              </tr> 
         
          <tr>
                <td>UOM :</td>
                <td>:</td>
                <td><select name="package_dimension_id" id="package_dimension_id" class="select1">
                
              @foreach($dimension_uom as $key => $value)
                @if($key==$packages->package_dimension_id)
            <option value="{{ $key}}" selected="selected">{{ $value}}</option>
             @else
            <option value="{{ $key}}">{{ $value}}</option>
             @endif
            @endforeach
          </select></td>
                <td width="10"></td>
                <!-- <td>Validation Message</td> -->
              </tr> 
         
                   
         </tbody>
     </table>
   </fieldset>
     <div> {{ Form::submit('UPDATE', array('class' => 'btn btn-primary')) }}
{{ Form::close() }}
</div>

 </div>
    </div>
    <!-- /.box-end -->
    
  </section><!-- /.content -->
</aside><!-- /.right-side -->
@stop

@section('style')
    {{HTML::style('css/style.css')}}
@stop

@section('script')
  <script type="text/javascript">
    $(function(){
      $('form').validate({
        rules:{
          package_name:{
            required:true
          },pname:{
            requiredDropdown:true
          },weight:{
            required:true,
            decimal:true
          },weight_uom_id:{
            requiredDropdown:true
          },package_type_id:{
            requiredDropdown:true
          },package_length:{
            required:true,
            decimal:true
          },package_width:{
            required:true,
            decimal:true
          },package_height:{
            required:true,
            decimal:true
          },package_dimension_id:{
            requiredDropdown:true
          }
        },messages:{
          package_name:{
            required: 'Package Name is required'
          },pname:{
            requiredDropdown: 'Product  is required'
          },weight:{
            required: ' weight is required'
            
          },weight_uom_id:{
            requiredDropdown: 'weight dimension is required'
          },package_type_id:{
            requiredDropdown: 'package type is required'
          },package_length:{
            required: 'length is required'
            
          },package_width:{
            required: 'width is required'
            
          },package_height:{
             required: 'height is required'
          },package_dimension_id:{
            requiredDropdown: 'dimension is required'
          }
        },submitHandler:function(form){
          form.submit();
        },errorPlacement: function(error, element) {
          element.closest('td').append(error);
        },unhighlight: function (element, errorClass, validClass) {
          if ($(element).hasClass('optional') && $(element).val() == '') {
            $(element).removeClass('error valid');
          }else{
            $(element).removeClass('error').addClass('valid');
          }
        }
      });
    });
  </script>
@stop



      
