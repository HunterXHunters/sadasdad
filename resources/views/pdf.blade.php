<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
</head>
<style>
body{
    margin:0px; padding:0px;
    font-family:Cambria, "Hoefler Text", "Liberation Serif", Times, "Times New Roman", serif;
}
#main{
    margin:0 auto;
    width:980px;
    overflow:hidden;
}
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
        <h1>{{$manufacturer}}</h1>
        <hr>
        <h2>{{$status}}</h2>
        <hr>
        
  <table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tbody >
    <tr>
      <td>Date :</td>
      <td>{{$datetime}}</td>
      <td>TP:</td>
      <td>{{$tp}}</td>
      <td rowspan="2" valign="top"><img src="img.jpg" width="49" height="49" alt=""/></td>
    </tr>
    <tr>
      <td>From:</td>
      <td>
       {{$src}}<br>
       {{$src_name}}<br>
      </td>
      <td>To:</td>
      <td>
       {{$dest}}<br>
       {{$dest_name}}<br>
      </td>
      </tr>    
  </tbody>
</table>
<hr>
<hr>
<table width="100%" border="1" style="border-collapse:collapse; font-weight:bold; border-color:#d3d3d3" cellspacing="0" cellpadding="10">
  <tbody>
    <tr bgcolor="#c0c0c0">
      <td>SNo</td>
      <td>Product Name</td>
      <td>Material Code</td>
      <td>MRP</td>
      <td>Count</td>
      <td>Qty</td>
      <td>Total</td>
    </tr>
    <?php $i=0; $x = 0; $z=0; $sub = count($tot); ?>
    @foreach($tot as $to)
    <?php 
          
          $z += $to[0]->qty;
          $i++;
          $ids = explode(',',$to[0]->id);
          $count = count($ids);
    ?>
    <tr>
      <td>{{$i}}</td>
      <td>{{$to[0]->name}}</td>
      <td>{{$to[0]->material_code}}</td>
      <td>{{$to[0]->mrp}}</td>
      <td>{{$count}}</td>
      <td>{{$to[0]->pack}}</td>
      <td>{{$to[0]->qty}}</td>
    </tr>
    <tr>
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
    </tr>
    @endforeach
    
    <tr bgcolor="#d3d3d3" style="text-align:left">
      <td>&nbsp;</td>
      <td style="text-align:left">Sub Count :{{$sub}}</td>
      <td>Sub Total</td>
      <td>&nbsp;</td>
      <td>{{$x}}</td>
      <td></td>
      <td>{{$z}}</td>
    </tr>
    </tbody>
</table>
    </div>
   </body>
</html>
