@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')

<div class="nav-tabs-custom">
               <ul class="nav nav-tabs pull-right">
<!--                 <li><a href="#shippings" data-toggle="tab">Shipping Details</a></li>
                <li ><a href="#orders" data-toggle="tab">Customer Details</a></li> -->
                <li class="active"><a href="#products" data-toggle="tab">Products</a></li>
                <li class="pull-left header"><i class="fa fa-pencil"></i> Edit Order</li>
              </ul>
      <div class="tab-content">
      {{Form::open(array('url' => 'orders/updatemyOrder/'.$ima_id.'/'.$id, 'id' => 'form'))}}
      {{ Form::hidden('_method', 'POST') }}
         <section id="rootwizard" class="tabbable tile">
                <!-- tile body -->
              <div class="tile-body">
                <div class="tab-content"> 
<!--                   <div class="tab-pane" id="orders">      
                     <div class="row">
                           <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">Customer Name</label>
                              <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control required" placeholder="First Name" name="customer_name"  id="customer_name" value="{{$customer_name}}">
                              </div>
                            </div>
                          
                           <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">First Name</label>
                              <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control required" placeholder="First Name" name="payment_firstname"  id="payment_firstname" value="{{$result[0]->payment_firstname}}">
                              </div>
                            </div>
                          </div>
                          <div class="row">
                           <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">Last Name</label>
                              <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control required" placeholder="Last Name" name="payment_lastname"  id="ship_first_name" value="{{$result[0]->payment_lastname}}">
                              </div>
                            </div>
                        	
                           <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">Telephone</label>
                              <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control required" placeholder="Phone" name="telephone"  id="telephone" value="{{$result[0]->telephone}}">
                              </div>
                            </div>               
                        </div>
                         <div class="row">
                           <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">City</label>
                              <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control required" placeholder="City" name="payment_city"  id="payment_city" value="{{$result[0]->payment_city}}">
                              </div>
                            </div>
                          
                           <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">Email</label>
                              <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control required" placeholder="Email" name="email"  id="email" value="{{$result[0]->email}}">
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Address</label>
                            <div class="input-group">
                              <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
                              <textarea class="form-control required" id="payment_address_1" name="payment_address_1"  rows="3">{{$result[0]->payment_address_1}}</textarea>
                             </div>                        
                          </div>
                          </div>
                           <a class="btn btn-primary"   id="continue">Continue</a>
                         </div>
                  <div class="tab-pane" id="shippings" >
                     <div class="row">
                           <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">First Name</label>
                              <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control required" placeholder="First Name" name="shipping_firstname"  id="shipping_firstname" value="{{$result[0]->shipping_firstname}}">
                              </div>
                            </div>
                          
                           <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">Last Name</label>
                              <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control required" placeholder="Last Name" name="shipping_lastname"  id="shipping_lastname" value="{{$result[0]->shipping_lastname}}">
                              </div>
                            </div>
                          </div>
                           <div class="row">
                           <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">Company</label>
                              <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control required" placeholder="Company" name="company_name"  id="company_name" value="eSeal">
                              </div>
                            </div>
                          
                          <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">Zone</label>
                              <div class="input-group">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control required" placeholder="City" name="zone"  id="zone" value="{{$zone}}">
                              </div>
                            </div>
                        </div>
                         <div class="row">
                           <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">City</label>
                              <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control required" placeholder="City" name="shipping_city"  id="shipping_city" value="{{$result[0]->shipping_city}}">
                              </div>
                            </div>
                          
                           <div class="form-group col-sm-6">
                              <label for="exampleInputEmail">Country</label>
                              <div class="input-group ">
                                <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                                <input type="text" class="form-control required" placeholder="City" name="country"  id="country" value="{{$country}}">
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Address</label>
                            <div class="input-group">
                              <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
                              <textarea class="form-control required" id="shipping_address_1" name="shipping_address_1"  rows="3">{{$result[0]->shipping_address_1}}</textarea>
                              </div>                        
                          </div>
                          </div>
                         <a class="btn btn-primary"   id="continue1">Continue</a>
                  </div> -->
                  <div class="tab-pane active" id="products">
                    <section class="tile">
                      <div class="tile-header"><!-- tile header -->
                        <input type='hidden' name="custid" id="custid" value="{{$result[0]->customer_id}}">
                        <input type='hidden' name="orderid" id="orderid" value="{{$id}}">
                        <div id="available_quantity"><h4><strong>Total Ordered Quantity :</strong> 
                          <div class="pull-right ">
                            <a href="#" class="refresh text-muted"><i class="fa fa-arrow-circle-left"></i></a>                  
                          </div>
                          <input type="text" disabled="true" value="{{$total_prod_quantity[0]->total_quantity}}" id="present_total_codes" name="present_total_codes" /></h4></div>
                      </div><!-- /tile header -->
                      <div id="successid" class="callout" align="center"></div>
                      <div id="failureid" class="callout" align="center"></div>                      
                      <div class="tile-body nopadding"><!-- tile body -->
                          <table class="table table-bordered">
                            <thead>
                              <tr>
                                  <th>Delivery Mode</th>
                                  <th>Delivery To</th>
                                  <th>Enter Quantity</th>
                                  <th>Action  </th>
                              </tr>
                            </thead>
                            <tbody id="final_append">
                              <tr>
                                <td> <select id="deliverymode" name="deliverymode" class='form-control requiredDropdown' style='display:block;' onchange='vendordropdown(this.id)'>
                                <option value="" selected="selected">Please Select..</option>
                                @foreach($array_circle as $value)
                                <option  value="{{$value['id']. '_'. $value['name']}}">{{$value['name']}}</option>
                                @endforeach
                                </select></td>
                                <td>
                                <span id="replacecode"><b>Not Applicable</b></span>
                                  <select id="vendorid" name="vendorid" onchange='getVendorAddress(this.value)' class='form-control requiredDropdown' style='display:block;'>
                                  <option value="" selected="selected">Please Select..</option>
                                  @if(!empty($array_circle1))
                                  @foreach($array_circle1 as $vendor)
                                  <option  value="{{$vendor['id']}}">{{$vendor['name']}}</option>
                                  @endforeach
                                  @endif
                                  </select>
                                </td>
                                <td>
                                  <input type='text' class="form-control" id="quantityplaced" name="quantityplaced" style='display:block;' placeholder="Enter Quantity" value="0" placeholder='IoT Codes' >
                                  <!-- <input type='text' class="form-control" id="quantityplaced" name="quantityplaced" style='display:block;' placeholder="Enter Quantity" value="0" onblur='editchange(this.id)' placeholder='IoT Codes' > -->
                                </td>
                                <td>
                                  <input type='button' class='btn btn-primary' id='' name='' style='display:block;' onclick="submitvendor();" value='submit'>
                                  
                                </td>
                               </tr>
                            </tbody>
                          </table>
                      </div><!-- /tile body -->
                    </section>
                      <div id="showiotgrid"> </div>
                  </div>
                </div>
              </div><!-- /.tab-content -->
         </section>
         {{ Form::close() }}
      </div>
  </div>

  <!--Edit IOT Code-->
  <div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
        <div class="modal-dialog wide">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 class="modal-title" id="basicvalCode">Edit IoT Code</h4>
            </div>
            <div class="modal-body">

            {{ Form::open(array('url' => '/orders/updateeditorderiotcodes/','data-url' => '/orders/updateeditorderiotcodes/','id'=>'editiotform')) }}
            {{ Form::hidden('_method', 'PUT') }}
            <!-- <form name="editiotform" id="editiotform" method="POST" action="#"> -->          
            <div class="row">
              <div class="form-group col-sm-6">
                <label class="control-label" for="input-fields">Delivery Mode</label>
                  <!-- <div class="col-sm-10"> -->
                  <div class="input-group ">
                  <input type="hidden" id="order_product_id" name="order_product_id" value="">
                  <input type="hidden" id="order_id" name="order_id" value="">
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <select id="delivery_mode" name="delivery_mode_id" class='form-control requiredDropdown' style='display:block;' onchange='vendordropdown(this.id)'disabled="true" >                 
                    @foreach($array_circle as $value)
                    <option  value="{{$value['id']}}">{{$value['name']}}</option>
                    @endforeach
                    </select>
                    <!-- <input type="text" readonly="true" id="delivery_mode" name="delivery_mode" style='display:block;'> --> 
                  </div>
                  <!-- </div> -->
              </div>
              <div class="form-group col-sm-6">
              <span id="replacecodeEdit" style="margin-top:40px;"><b>Not Applicable</b></span>
              <span id="vendor_id_edit">
                 <label class="control-label" for="input-fields">Vendor</label>
                  <!-- <div class="col-sm-10"> -->
                  <div class="input-group ">  
                      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                      <select id="vendor_id" name="vendor_id" onchange='getVendorAddress(this.value)' class='form-control requiredDropdown' style='display:block;' disabled="true">
                      <option value="">Please Select..</option>
                      @if(!empty($array_circle1))
                      @foreach($array_circle1 as $vendor)
                      <option  value="{{$vendor['id']}}">{{$vendor['name']}}</option>
                      @endforeach
                      @endif
                      </select>
                     <!--  <input type="text" readonly="true" id="delivery_to" name="delivery_to" style='display:block;'> -->
                  </div>
                  <!-- </div> -->
                  </span>
              </div>
            </div>
            <div class="row">
              <div class="form-group col-sm-6">
                <label class="control-label" for="input-fields">Quantity</label>
                  <!-- <div class="col-sm-10"> -->
                  <div class="input-group ">  
                    <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control" id="quantity" name="quantity" value="" style='display:block;'>                   
                  </div>
                  <!-- </div> -->
              </div>
              
              <!-- <input type="hidden" id="id" name="id" value="" />
              <input type="hidden" id="vendorpopup" name="vendorpopup" value="" />
              <input type="hidden" id="quantitypopup" name="quantitypopup" value="" />
               -->
            </div>
            <input type="button" class="btn btn-primary" value="Update Iot Order" onclick="submitvendorEdit()" />
            <!-- {{ Form::submit('Update Iot Order', array('class' => 'btn btn-primary')) }} -->
            {{ Form::close() }}

              </div>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->    
      <!--Edit IOT Code-->



 @stop

@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
    {{HTML::style('css/easy-responsive-tabs.css')}} 
     
@stop

@section('script')
    {{HTML::script('js/easyResponsiveTabs.js')}}
    {{HTML::script('jqwidgets/jqxcore.js')}}
    {{HTML::script('jqwidgets/jqxbuttons.js')}}
    {{HTML::script('jqwidgets/jqxscrollbar.js')}}
    {{HTML::script('jqwidgets/jqxmenu.js')}}
    {{HTML::script('jqwidgets/jqxgrid.js')}}
    {{HTML::script('jqwidgets/jqxgrid.selection.js')}}
    {{HTML::script('jqwidgets/jqxgrid.columnsresize.js')}}
    {{HTML::script('jqwidgets/jqxdata.js')}}
    {{HTML::script('scripts/demos.js')}}
    {{HTML::script('jqwidgets/jqxlistbox.js')}}
    {{HTML::script('jqwidgets/jqxdropdownlist.js')}}
    {{HTML::script('jqwidgets/jqxgrid.pager.js')}}
    {{HTML::script('jqwidgets/jqxgrid.sort.js')}}
    {{HTML::script('jqwidgets/jqxgrid.filter.js')}}
    {{HTML::script('jqwidgets/jqxgrid.storage.js')}}
    {{HTML::script('jqwidgets/jqxgrid.columnsreorder.js')}}
    {{HTML::script('jqwidgets/jqxpanel.js')}}
    {{HTML::script('jqwidgets/jqxcheckbox.js')}}
<script type="text/javascript">
  $(document).ready(function () 
    {           
      document.getElementById('replacecodeEdit').style.display='none';
      document.getElementById('replacecode').style.display='none';
      document.getElementById('replacecode').style.display='none';
      document.getElementById('successid').style.display='none';
      document.getElementById('failureid').style.display='none';     
      showIotCodes();
     //viewPayments();
    }); 
</script>

<script type="text/javascript">
$('#order_status_id').change(function(){ $('#status_id').val($('#order_status_id').val()); })
</script>

<script type="text/javascript">
  function statusmessage()
  {
    $('#successid').delay(3000).fadeOut(1500);
    $('#failureid').delay(3000).fadeOut(1500);
  }

  function submitvendor(cust_id){    
    var cust_id = $('#custid').val();
    var delivery_mode = $('#deliverymode').val();
    var delivery_id = delivery_mode.substr( 0, delivery_mode.indexOf('_') );
    var delivery_name = delivery_mode.substr( delivery_mode.indexOf('_') + 1 );
    var vendor_id = $('#vendorid').val();
    var vendor_name = $('#vendorid').html();
   // var vendor_name = $('#vendorid').val($('#vendorid option:selected').text());
    var order_id = $('#orderid').val();
    var quantity = parseInt($('#quantityplaced').val());
    if(delivery_name == 'Vendor-Direct')
    {
      if(delivery_mode == '' || vendor_id == '' || quantity == '')
        {
          alert('Please fill all fields');
          return false;
        }
        else{
          $.ajax({
              url: '/orders/addeditorderiotcodes',
              data: {
                  'customer_id' : cust_id,
                  'order_id' : order_id, 
                  'deliveryid' : delivery_id,
                  'deliveryname' : delivery_name,
                  'vendorid' : vendor_id,
                  'vendorname' : vendor_name,
                  'total_codes' : quantity
              },
              type:'POST',
              success: function(result)
              {
                  console.log(result);
                  var result=result.split('-');
                  if(result[0] == 2)
                  {   
                     showIotCodes();
                     $('#present_total_codes').val(result[1]);  
                     $('#quantityplaced').val('');                   

                  }
                  else if(result == 1)
                  {
                    //alert('Already added for this vendor, please update the quantity.');
                    document.getElementById('successid').style.display='none';
                    document.getElementById('failureid').style.display='block';
                    document.getElementById('failureid').innerHTML = 'Already added for this vendor, please update the quantity.';
                    $("#failureid").addClass("callout callout-danger");
                    $("#successid").removeClass("callout-success");
                    $("#failureid").removeClass("callout-info");
                    $("#failureid").removeClass("callout-warning");  
                    statusmessage();                     
                  }
                  else
                  {
                    //alert('IOT Codes are not added.');
                    document.getElementById('successid').style.display='none';
                    document.getElementById('failureid').style.display='block';
                    document.getElementById('failureid').innerHTML = 'IOT Codes are not added.';
                    $("#failureid").addClass("callout callout-danger");
                    $("#successid").removeClass("callout-success");
                    $("#failureid").removeClass("callout-info");
                    $("#failureid").removeClass("callout-warning"); 
                    statusmessage();                   
                  }
              }
          });
        }
      }else{
        var vendor_id = document.getElementById('vendorid').value=0;
        if(delivery_mode == '' || quantity == '')
        {
          //alert('Please fill all fields');
          document.getElementById('successid').style.display='none';
          document.getElementById('failureid').style.display='block';
          document.getElementById('failureid').innerHTML = 'Please fill all fields.';
          $("#failureid").addClass("callout callout-danger");
          $("#successid").removeClass("callout-success");
          $("#failureid").removeClass("callout-info");
          $("#failureid").removeClass("callout-warning"); 
          statusmessage();           
          return false;
        }
        else{
          $.ajax({
              url: '/orders/addeditorderiotcodes',
              data: {
                  'customer_id' : cust_id,
                  'order_id' : order_id, 
                  'deliveryid' : delivery_id,
                  'deliveryname' : delivery_name,
                  'vendorid' : vendor_id,
                  'vendorname' : vendor_name,
                  'total_codes' : quantity
              },
              type:'POST',
              success: function(result)
              {
                  console.log(result);
                  var result=result.split('-');
                  if(result[0] == 2)
                  {   
                      showIotCodes();
                      $('#present_total_codes').val(result[1]); 
                      $('#quantityplaced').val(''); 
                  }else if(result == 1)
                  {
                    //alert('Please select different Vendor.');
                    document.getElementById('successid').style.display='none';
                    document.getElementById('failureid').style.display='block';
                    document.getElementById('failureid').innerHTML = 'Please Select Different Vendor';
                    $("#failureid").addClass("callout callout-danger");
                    $("#successid").removeClass("callout-success");
                    $("#failureid").removeClass("callout-info");
                    $("#failureid").removeClass("callout-warning"); 
                    statusmessage();                   
                  }
                  else
                  {
                    //alert('IOT Codes are not added.');
                    document.getElementById('successid').style.display='none';
                    document.getElementById('failureid').style.display='block';
                    document.getElementById('failureid').innerHTML = 'IOT Codes are not added.';
                    $("#failureid").addClass("callout callout-danger");
                    $("#successid").removeClass("callout-success");
                    $("#failureid").removeClass("callout-info");
                    $("#failureid").removeClass("callout-warning"); 
                    statusmessage();                     
                  }
              }
          });
        }
      }
  }

function getOrderProductId(order_product_id,order_id){
  $('#order_product_id').val(order_product_id);
  $('#order_id').val(order_id);
}
/*$('[data-target="#basicvalCodeModal"]').click(function(){
      document.getElementById('success_id').style.display='none';
      document.getElementById('failure_id').style.display='none';  
});*/
function submitvendorEdit()
{
  var quantity = parseInt($('#quantity').val()); 
  //alert(quantity);
  var id = $('#order_id').val();
  var order_product_id = $('#order_product_id').val();
  if(quantity > 0 ){
            $.ajax({
          url: '/orders/updateeditorderiotcodes/1',
          data: {
              'order_product_id' : order_product_id, 
              'quantity' : quantity,
              'id':id
          },
          type:'POST',
          success: function(result)
          {
             if(result >= 0)
              {
              var total=result;
              //alert(result);
              //alert('Successfully Updated.');
              document.getElementById('successid').style.display='block';
              document.getElementById('failureid').style.display='none';
              document.getElementById('successid').innerHTML = 'Successfully Updated';
              $("#successid").addClass("callout callout-success");
              $("#failureid").removeClass("callout-success");
              $("#successid").removeClass("callout-info");
              $("#successid").removeClass("callout-warning");              
              showIotCodes();
              $('#basicvalCodeModal').modal('hide');
              $('#present_total_codes').val(total);
              statusmessage();
              }
          }
      });      
  }else{
      //alert('Please fill Quantity');
      document.getElementById('successid').style.display='none';
      document.getElementById('failureid').style.display='block';
      document.getElementById('failureid').innerHTML = 'Please fill Quantity';
      $("#failureid").addClass("callout callout-danger");
      $("#successid").removeClass("callout-success");
      $("#failureid").removeClass("callout-info");
      $("#failureid").removeClass("callout-warning");
      $('#basicvalCodeModal').modal('hide'); 
      statusmessage();       
      return false; 
    }
}

function submitvendorEdit1()
  {    
      var cust_id = $('#custid').val();
      var id = $('#id').val();
      var delivery_id = $('#delivery_mode_id').val()?$('#delivery_mode_id').val():'';
      var delivery_name = $('#delivery_mode_id option:selected').html();
      var vendor_id = $('#vendor_id option:selected').val();
      var vendor_idValidate = $('#vendorpopup').val();
      var vendor_name = $('#vendor_id option:selected').html();
      var quantity = parseInt($('#quantity').val());

      var prevquantity = parseInt(document.getElementById('quantitypopup').value);
      var available_quantity = parseInt($('#quantity_present').val());
      available_quantity = available_quantity + prevquantity;
      
      var greater=(quantity <= available_quantity);
      if(!greater)
      { 
          alert('Available quantity is less than Total quantity.');
      }
      else
      {
          var available_codes = available_quantity - quantity;
          if(delivery_name == 'Vendor-Direct')
          {
              if(delivery_name == '' || vendor_id == '' || quantity == '')
              {
                  alert('Please fill all fields');
                  return false;
              }
              else
              {
                  $.ajax({
                  url: '/orders/updateiotcodes/1',
                  data: {
                      'customer_id' : cust_id, 
                      'id' : id, 
                      'deliveryid' : delivery_id,
                      'deliveryname' : delivery_name,
                      'vendorid' : vendor_id,
                      'vendor_idValidate' : vendor_idValidate,
                      'prevquantity' : prevquantity,
                      'vendor_name' : vendor_name,
                      'total_codes' : quantity
                  },
                  type:'POST',
                  success: function(result)
                  {
                     if(result.length>1)
                      {
                        var finalresult = result.substr( 0, result.indexOf('-') );
                        var count = result.substr(result.indexOf('-')+1);
                      }
                      else
                      {
                        var finalresult = result;
                      }
                      if(finalresult == 2)
                      {   
                          document.getElementById('available_quantity').innerHTML='Available Codes : '+count;
                          document.getElementById('quantity_present').value=count;
                          document.getElementById('quantityplaced').value=count;
                         
                      }else if(finalresult == 1)
                      {
                        alert('Please select different Vendor.');
                      }
                      else
                      {
                        alert('IOT Codes are not added.');
                      }
                      showIotCodes();
                      $('#basicvalCodeModal').modal('hide');
                  }
              });
              }
          }
          else
          {
              var vendor_id = document.getElementById('vendorid').value=0;
              if(delivery_name == '' || quantity == '')
              {
                  alert('Please fill all fields');
                  return false;
              }
              else
              {
                  $.ajax({
                  url: '/orders/updateiotcodes/1',
                  data: {
                      'customer_id' : cust_id, 
                      'id' : id, 
                      'deliveryid' : delivery_id,
                      'deliveryname' : delivery_name,
                      'vendorid' : vendor_id,
                      'vendor_idValidate' : vendor_idValidate,
                      'vendor_name' : vendor_name,
                      'total_codes' : quantity
                  },
                  type:'POST',
                  success: function(result)
                  {
                      if(result.length>1)
                      {
                        var finalresult = result.substr( 0, result.indexOf('-') );
                        var count = result.substr(result.indexOf('-')+1);
                      }
                      else
                      {
                        var finalresult = result;
                      }
                      if(finalresult == 2)
                      {   
                          document.getElementById('available_quantity').innerHTML='Available Codes : '+count;
                          document.getElementById('quantity_present').value=count;
                          document.getElementById('quantityplaced').value=count;
                         
                      }else if(finalresult == 1)
                      {
                        alert('Please select different Vendor.');
                      }
                      else
                      {
                        alert('IOT Codes are not added.');
                      }
                      showIotCodes();
                      $('#basicvalCodeModal').modal('hide');
                  }
              });
              }
          }
        }
  }

  function showIotCodes()
    {
        var orderid = document.getElementById('orderid').value;
        var custid = document.getElementById('custid').value;
        var url = "/orders/showeditorderiotcodes/"+orderid+'/'+ custid;
        var source =
        {
            datatype: "json",
            datafields: [
                { name: 'id', type: 'integer' },
                { name: 'customer_id', type: 'integer' },
                { name: 'delivery_mode', type: 'varchar' },
                { name: 'vendor', type: 'varchar' },
                { name: 'quantity', type: 'integer' },
                { name: 'actions', type: 'string' },
            ],
            url: url,
            id: 'order_product_id',
            pager: function (pagenum, pagesize, oldpagenum) {
            }
        };
        var dataAdapter = new $.jqx.dataAdapter(source);
        $("#showiotgrid").jqxGrid(
        {
            width: "100%",
            source: source,
            selectionmode: 'multiplerowsextended',
            sortable: true,
            pageable: false,
            autoheight: true,
            autoloadstate: false,
            autosavestate: false,
            columnsresize: true,
            columnsreorder: true,
            showfilterrow: false,
            filterable: false,
            columns: [
              { text: 'Delivery Mode', filtercondition: 'starts_with', datafield: 'delivery_mode', width: "40%" },
              { text: 'Vendor', datafield: 'vendor', width: "40%"},
              { text: 'Quantity', datafield: 'quantity', width: "10%"},
              { text: 'Actions', datafield: 'actions',width:"10%" }
            ]               
        }); 

        makePopupEditAjaxProceed($('#basicvalCodeModal'),'order_product_id');          
    }
    function makePopupEditAjaxProceed($el, primaryKey) {
    $el.on('shown.bs.modal', function (e) {
        var url = $(e.relatedTarget).data('href'),
                $this = $(this),
                $form = $this.find('form'),
                key = primaryKey || 'id';        
        $.get(url, function (data) {
            $.each(data, function (i, v) {
                if ( i == key ) {
                    $form.attr('action', function () {
                        return $(this).data('url') + v;
                    });
                }
                var el = $form.find('[name="' + i + '"]');
                if ( el.length && el[0].type.toLowerCase() == 'checkbox' ) {
                    el.prop('checked', false);
                    el.filter('[value=' + v + ']').prop('checked', true);
                    return;
                }
                el.val(v);
            });
            var reqid = $('#vendor_id option:selected').val();
            //document.getElementById('vendorpopup').value = reqid;
            //document.getElementById('quantitypopup').value = document.getElementById('quantity').value;
            //$('#basicvalCodeModal').find('option').not('#delivery_mode :selected').prop('disabled', true);
            var delivery_mode_id = $('#delivery_mode').val();
            var delivery_name_edit = $('#delivery_mode option:selected').html();
            console.log(delivery_mode_id);
            if(delivery_name_edit=='Downloadable' || delivery_name_edit=='Print and Deliver')
            {
                document.getElementById('replacecodeEdit').style.display='block';
                document.getElementById('vendor_id_edit').style.display='none';
            }  
            else
            {
                document.getElementById('replacecodeEdit').style.display='none';
                document.getElementById('vendor_id_edit').style.display='block';
            }           
            //popreqid =  reqid;       
            //alert($('#vendorpopup').val()); 
            //alert(reqid);      
        });
        $form.validate();
    });
    
}

  function deleteIot(id,orderid)
    {
          var dec = confirm("Are you sure you want to Delete ?");
          if ( dec == true )
              $.ajax({
                  url: '/orders/deleteeditorderiot/' + id+'/'+orderid,
                  type:'GET',
                  success: function(result)
                  {
                      if(result != 0)
                      {
                        var result=result.split('-');
                        //alert('IoTs Succesfully Deleted !!');
                        //console.log(result[1]);
                        document.getElementById('successid').style.display='block';
                        document.getElementById('failureid').style.display='none';
                        document.getElementById('successid').innerHTML = 'IoTs Succesfully Deleted !!';
                        $("#successid").addClass("callout callout-success");
                        $("#failureid").removeClass("callout-success");
                        $("#successid").removeClass("callout-info");
                        $("#successid").removeClass("callout-warning");                          
                        //document.getElementById('present_total_codes').innerHTML = result[1];
                        showIotCodes();
                        $('#present_total_codes').val(result[1]);
                        statusmessage();
                      }
                      else
                      {
                        //alert('Unable to Delete.');
                        document.getElementById('successid').style.display='none';
                        document.getElementById('failureid').style.display='block';
                        document.getElementById('failureid').innerHTML = 'Unable to Delete.';
                        $("#failureid").addClass("callout callout-danger");
                        $("#successid").removeClass("callout-success");
                        $("#failureid").removeClass("callout-info");
                        $("#failureid").removeClass("callout-warning"); 
                        statusmessage();                         
                      }
                  },
                  error: function(err){
                      console.log('Error: '+err);
                  },
                  complete: function(data){
                      console.log(data);
                  }                
              });
    }

  function vendordropdown(id)
    {
      var test=id.substring(16); 
      $("#vendorid").val($("#vendorid option:first").val());
      var delivery_mode = $('#deliverymode').val();
      var delivery_name = delivery_mode.substr( delivery_mode.indexOf('_') + 1 );
      if(delivery_name=='Downloadable' || delivery_name=='Print and Deliver')
      {
        document.getElementById('replacecode').style.display='block';
        document.getElementById('vendorid').style.display='none';
      }
      else
      {
          document.getElementById('replacecode').style.display='none';
          document.getElementById('vendorid').style.display='block';
      }
      var delivery_mode_id = $('#delivery_mode_id').val();
      var delivery_name_edit = $('#delivery_mode option:selected').html();
      if(delivery_name_edit=='Downloadable' || delivery_name_edit=='Print and Deliver')
      {
          document.getElementById('replacecodeEdit').style.display='block';
          document.getElementById('vendor_id_edit').style.display='none';
      }
      else
      {
          document.getElementById('replacecodeEdit').style.display='none';
          document.getElementById('vendor_id_edit').style.display='block';
      }
    }

</script>

<script type="text/javascript">
   $("#continue").click(function () {
        //alert('nikhil');
        changeTab('#shippings');       
    });
   $("#continue1").click(function () {
        //alert('nikhil');
        changeTab('#products');       
    });
   function changeTab(tabName)
    {
        $('[data-toggle="tab"]').each(function(event){
            $tab = $(this);
            if($tab.attr('href') == tabName)
            {
                $tab.parent().addClass('active');
                $(tabName).addClass('tab-pane active');
            }else{
                $tab.parent().removeClass('active');
                $($tab.attr('href')).removeClass('active');
            }
        });
    }
    function creatnewOrder()
    {
    var new_product_id=document.getElementById('new_product_id').value; 
    var new_ima_id=document.getElementById('new_ima_id').value; 
    var new_cust_id=document.getElementById('new_cust_id').value; 
    var new_order_id=document.getElementById('new_order_id').value; 
    
  $.ajax({
      url: "/orders/createOrder/"+new_product_id+'/'+new_ima_id+'/'+new_cust_id, //This is the page where you will handle your SQL insert
      type: "GET",
      data: "new_order_id=" + new_order_id, //The data your sending to some-page.php
      success: function(response){
        //console.log("AJAX request was successfull");
        document.getElementById('cart').innerHTML= + response;
             
      },
      error:function(){
         // console.log("AJAX request was a failure");
      }   
    });
    }
    // function viewPayments()
    // {
    //     var id=document.getElementById('test_id').value;
    //         //alert(id);
    //         var url = "/orders/viewPayments/"+id;
    //         //alert(url);
    //         var source =
    //         {
    //             datatype: "json",
    //             datafields: [
    //                 { name: 'sno', type: 'integer' },
    //                 { name: 'payment_type', type: 'string' },
    //                 { name: 'reference_no', type: 'integer' },
    //                 { name: 'ifsc_code', type: 'integer' },
    //                 { name: 'amount', type: 'decimal' },
    //                 { name: 'payment_date', type: 'datetime' },
    //                 { name: 'actions', type: 'string' },
    //                // { name: 'delete', type: 'string' }
    //             ],
    //             //id: 'customer_id',
    //             url: url,
    //             pager: function (pagenum, pagesize, oldpagenum) {
    //                 // callback called when a page or page size is changed.
    //             }
    //         };
    //         var dataAdapter = new $.jqx.dataAdapter(source);
    //         $("#paymentsgrid").jqxGrid(
    //         {
    //             width: '100%',
    //             source: source,
    //             selectionmode: 'multiplerowsextended',
    //             sortable: true,
    //             pageable: false,
    //             autoheight: true,
    //             autoloadstate: false,
    //             autosavestate: false,
    //             columnsresize: true,
    //             columnsreorder: true,
    //             showfilterrow: false,
    //             filterable: false,
    //             columns: [
    //               { text: 'Payment Id', filtercondition: 'starts_with', datafield: 'sno', width: 100 },
    //               { text: 'Payment Type', datafield: 'payment_type', width: 150,cellsalign: 'right'},
    //               { text: 'Reference Number', datafield: 'reference_no', width:150},
    //               { text: 'Ifsc Code', datafield: 'ifsc_code', width:100,cellsalign: 'right'},
    //               { text: 'Amount', datafield: 'amount', width:150,cellsalign: 'right'},
    //               { text: 'Payment Date', datafield: 'payment_date', width:200,cellsalign: 'right'},
                  
    //               //{ text: 'Edit', datafield: 'edit' },
    //               { text: 'Actions', datafield: 'actions',width:200 }
    //             ]               
    //         });            
    //         makePopupAjax($('#basicvalCodeModal'));
    //         makePopupEditAjax($('#basicvalCodeModal1'));
    //         $(document).ajaxSuccess(function(e, xhr, settings){
    //           if(settings.url.indexOf('/orders/paymentEdit/') == 0){
    //             var payment_type = $('#payment_type').val(),
    //                 cur = $('[data-payment-type=' + payment_type + ']');
    //             $('#parentVerticalTab1 li').removeClass('resp-tab-active hide');
    //             $('#parentVerticalTab1 .resp-tabs-container div').removeClass('resp-tab-content-active');
    //             $('#parentVerticalTab1 li').not(cur).addClass('hide');
    //             cur.addClass('resp-tab-active resp-tab-content-active');
    //           }
    //         });
            
    // } 
    $('#parentVerticalTab').easyResponsiveTabs({
            type: 'vertical', //Types: default, vertical, accordion
            width: 'auto', //auto or any width like 600px
            fit: true, // 100% fit in a container
            closed: 'accordion', // Start closed if in accordion view
            tabidentify: 'hor_1', // The tab groups identifier
            activate: function(event) { // Callback function if tab is switched
                var $tab = $(this);
                var $info = $('#nested-tabInfo2');
                var $name = $('span', $info);
                $name.text($tab.text());
                $info.show();
            }
        });
    $('#parentVerticalTab1').easyResponsiveTabs({
            type: 'vertical', //Types: default, vertical, accordion
            width: 'auto', //auto or any width like 600px
            fit: true, // 100% fit in a container
            closed: 'accordion', // Start closed if in accordion view
            tabidentify: 'hor_1', // The tab groups identifier
            activate: function(event) { // Callback function if tab is switched
                var $tab = $(this);
                var $info = $('#nested-tabInfo2');
                var $name = $('span', $info);
                $name.text($tab.text());
                $info.show();
            }
         });
  
 
 $(function(){
      $('#form').validate({
        ignore: [],
        messages:{
         trans_reference_no:{
            required: 'Referrence Number is required'
          },
          payee_bank:{
            required: 'Bank Name is required'
          },
           ifsc_code:{
            required: 'IFSC code is required'
          },
           amount:{
            required: 'Amount is required'
          },
          payment_date:{
            required: 'Payment Date is required'
          }
        },submitHandler:function(form){
          form.submit();
        },errorPlacement: function(error, element) {
          element.closest('.form-group').append(error);
          //alert('Check Errors');
        },unhighlight: function (element, errorClass, validClass) {
          if ($(element).hasClass('optional') && $(element).val() == '') {
            $(element).removeClass('error valid');
          }else{
            $(element).removeClass('error').addClass('valid');
          }
        }
      });
    });


function deleteEntityType(id)
{
    var dec = confirm("Are you sure you want to Delete ?");
    if (dec == true)
        window.location.href = '/orders/paymentDelete/'+id;
}

jQuery(document).ready(function($) {

  var ima_id = '<?PHP echo $ima_id;?>';
  var id = '<?PHP echo $id;?>';
  //alert('IMA='+ima_id+'&&'+'ID='+id)
   //alert(temp_cust_id);
  if (window.history && window.history.pushState) {
    
    window.history.pushState('', null, './'+ima_id);

    $(window).on('popstate', function() {
    window.location='/orders/viewOrder/'+id+'/'+ima_id;

    });

  }
});

    </script>    
@stop
