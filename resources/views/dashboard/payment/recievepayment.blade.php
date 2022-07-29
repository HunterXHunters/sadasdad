@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')

<style type="text/css">
    #load:hover {
        cursor:pointer;
    }

</style>
<div class="container">
    <div class="row ">
    <div class="form-horizontal box">
    <form method="post" action="savepaymentdetails" id="">
        <div class="form-group">
            <div class="col-md-5">
                <div class="input-group">
                <span class="icon input-group-addon"><i class="fa fa-search"></i></span>
                <input type="search" class="form-control" name="invoice_number" id="invoice_number" placeholder="Find by Invoice Number" >
                <span class="input-group-addon"><i style="background-color:green" id="load">load</i></span>
                </div>
            </div>
            
        </div>


        <div class="form-group">

            <div class="col-md-5">
                <label class="control-label col-md-4">Customer:</label>
                <div class="col-md-8">
                    <select class="form-control" name ="customer" id="customer">
                        <!-- <option>select Customer</option> -->
                    </select>
                </div>
            </div>
            <div class="col-md-5">
                <label class="control-label col-md-4">payment Date:</label>
                <div class="col-md-8 input-group">

                    <input type="text" class="form-control" name="payment_date" id="payment_date" autocomplete="off">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                </div>
            </div>

            
            <!-- <div class="col-md-4">

                
            </div> -->

        </div>

        <div class="form-group">

            <div class="col-md-5">
                <label class="control-label col-md-4">Payment Method:</label>
                <div class="col-md-8">
                    <select  class="form-control" name="payment_method">
                        <option>select payment method</option>
                        <option>Debit Card</option>
                        <option>Credit Card</option>
                        <option>Cheque</option>
                    </select>
                </div>
            </div>
            <div class="col-md-5">
                <label class="control-label col-md-4">Reference No:</label>
                <div class="col-md-8">
                    <input type ="text" class="form-control" name="reference_no">
                </div>
            </div>
        </div>
            <!-- <div class="col-md-3">
                <label class="control-label col-md-4">Deposit To:</label>
                <div class="col-md-8">
                    <select class=" form-control">
                        <option>undeposited funds</option>
                    </select>
                </div>
            </div> -->
            <div class="form-group">

            <div class="col-md-5">
                <label class="control-label col-md-4">Email Address:</label>
                <div class="col-md-8">
                    <input type="text" class="form-control" name="email" id="email">
                    <input type="checkbox" class="" name="send_later" id="send"><span>Send Later</span>
                </div>
            </div>
            <div class="col-md-5">
                <label class="control-label col-md-4">Amount Recieved:</label>
                <div class="col-md-8">
                    <input type ="text" class=" form-control" name="amount_recieved">
                </div>
            </div>
            </div>
        


        <div class="pull-right">
            <button id="save" type="submit">Save</button>
            <button>Cancel</button>
        </div>
        </form>

    </div>

    <div>
        <table class = "table" id="payment_history">
            <caption><b><center>Outstanding Transactions</center></b> </caption>
            <thead>
            <tr>
                <td>Description</td>
                <td>Due Date</td>
                <td>Invoiced Amount</td>
                <td>Balanced</td>
                <!-- <td>Payment</td> -->
            </tr>
            </thead>
            <tbody>
            

            </tbody>
        </table>

        
    </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-1.12.4.js" type="text/javascript"></script>
<script src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="https://cdn.datatables.net/buttons/1.2.4/js/dataTables.buttons.min.js" type="text/javascript"></script>
<script src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.print.min.js" type="text/javascript"></script>


<script>


$(document).ready(function(){
$('#payment_history').DataTable( {
        dom: 'Bfrtip',
        buttons: [
            'print'
        ]
    } );

$('#payment_date').datepicker();

});



$('#load').on('click',function(){
    //alert($(this).val());
    $.ajax({
        url:'getinvoicedetails',
        data:{'invoice_number':$('#invoice_number').val()},
        method:'get',
        success:function(response){
            //if(response.length >0){


                $('#payment_history').dataTable().fnDestroy();
                $('#customer').empty();
                var opt = new Option(response['invoice_details'].brand_name,response['invoice_details'].customer_id);
                $('#customer').append(opt);
                $('#email').val(response['invoice_details'].email);
                $('#payment_history tbody').empty();
                var a= "";
                $.each(response['payment_history'],function(i,val){
                    a += "<tr><td>Invoice#"+val.invoice_no+"</td><td>"+val.due_date+"</td><td>"+val.amount_recieved+"</td><td>"+(val.amount-val.amount_recieved)+"</td></tr>"
                });
                $('#payment_history tbody').html(a);
                $('#payment_history').DataTable( {
                    dom: 'Bfrtip',
                    buttons: [
                        'print'
                    ]
                } );
           // }
        }

    });
});


// $('#save').on('click',function(){

// });
</script>
@stop



