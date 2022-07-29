<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>TP Details</title>
</head>
<style>
body {
    height: 842px;
    width: 595px;
    /* to centre page on screen*/
    margin-left: auto;
    margin-right: auto;
}
/*body{
    margin:0px; padding:0px;
    font-family:Cambria, "Hoefler Text", "Liberation Serif", Times, "Times New Roman", serif;
}*/
/*#main{
    margin:0 auto;
    width:980px;
    overflow:hidden;
}*/
h1{
    font-size:20px;
    text-align:center;
}
h2{
    font-size:22px;
    text-align:center;
}
p{margin:0px; padding:0px;  font-weight:normal;}
</style>
<body>
    <div id="main">
      @if($download == 'no')
        <a href="{{$downloadUrl}}"><h5 style="position:absolute; margin-left:42%;">PDF</h5></a>
        @endif
        <h1>Unilever Sri Lanka</h1>
        <hr>
        <h2>{{$title}}</h2>
        <hr>
        
  <table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tbody >
    <tr>
      <td>Date:</td>
      <td>{{$date}}</td>
      <td>TP:</td>
      <td>{{$deliveryMaster->tp_id}} <input type="hidden" name="tp_value" id="tp_value" value="{{$deliveryMaster->tp_id}}"> </td>
      <td rowspan="2" valign="top"><div id="qrcode"></div></td>
    </tr>
    <tr>
      <td>From:</td>
      <td>
       {{$src_location->location_name}} <br>
       {{$src_location->location_address}}<br>
      </td>
      <td>To:</td>
      <td>
       {{$des_location->location_name}}<br>
       {{$des_location->location_address}}<br>
      </td>
      </tr>    
  </tbody>
</table>
<hr>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tbody>
    <tr>
    @foreach($tpAttData as $tpd)
      <td>{{$tpd->attribute_name}}:</td>    
      <td>{{$tpd->value}}</td>
    @endforeach
    </tr>
  </tbody>
</table>
<hr>
<table width="100%" border="1" style="border-collapse:collapse; font-weight:bold; border-color:#d3d3d3" cellspacing="0" cellpadding="10">
  <tbody>
    <tr bgcolor="#c0c0c0">
      <td>SNo</td>
      <td>Material Name</td>
      <td>Material Code</td>
      <td>Batch Number</td>
      <!-- <td>Count</td> -->
      <td>Qty</td>
      <!-- <td>Total</td> -->
    </tr>
    <?php $i=0; $x = 0; $z=0; $sub = count($deliveryCild); ?>
    @foreach($deliveryCild as $to)
    <?php 
          
          $z += $to->qty;
          $i++;
          $ids = explode(',',$to->id);
          $count = count($ids);
    ?>
    <tr>
      <td>{{$i}}</td>
      <td>{{$to->name}}</td>
      <td>{{$to->material_code}}</td>
      <td>{{$to->batch_no}}</td>
      <td>{{$to->qty}}</td>
    </tr>
    <!-- <tr>
      <td>&nbsp;</td>
      <td colspan="5">
      <p>
      @foreach($ids as $id)
      <?php $x++; ?>
      {{$id}}&nbsp|
      @endforeach
      </p>
      </td>
       
      <td>&nbsp;</td>
    </tr> -->
    @endforeach
    
    <tr bgcolor="#d3d3d3" style="text-align:left">
      <td>&nbsp;</td>
      <td style="text-align:left">Sub Count :{{$sub}}</td>
      <td>Sub Total</td>
      <td>&nbsp;</td>
      <!-- <td>{{$x}}</td> -->
      <!-- <td></td> -->
      <td>{{$z}}</td>
    </tr>
    </tbody>
</table>
    </div>
   </body>
</html>
<script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="/js/qrcode.js"></script>
<script type="text/javascript">
  var qrcode = new QRCode(document.getElementById("qrcode"), {
    width : 50,
    height : 50,
    right : 0
  });

  function makeCode () {    
    var elText = <?php echo $deliveryMaster->tp_id; ?>;
    var tptxt = document.getElementById("tp_value");
    console.log(tptxt.value);
    qrcode.makeCode(tptxt.value);
  }

  $( document ).ready(function() {
    console.log( "ready!" );
    makeCode();
  });
</script>

@section('script')
{{HTML::script('js/qrcode.js')}}
@stop