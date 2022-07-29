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
        
        
  <table width="100%" border="0" cellspacing="0" cellpadding="5" style="border-collapse:collapse; font-weight:bold; border-color:#d3d3d3">
  <tbody >
    <tr>
      <td>Customer Name :</td>
      <td>{{$brand_name}}</td>
     
      <td rowspan="2" valign="top"><img src="img.jpg" width="49" height="49" alt=""/></td>
    </tr>
    <tr>
      <td>From Date:</td>
      <td>
       {{$from_date}}<br>
      </td>
      <td>To Date:</td>
      <td>
       {{$to_date}}<br>
      </td>
      </tr>    
  </tbody>
</table>
<hr>
<hr>
<table width="100%" border="1" style="border-collapse:collapse; font-weight:bold; border-color:#d3d3d3" cellspacing="0" cellpadding="10">
  <tbody>
    <tr bgcolor="#c0c0c0">
      <td>Product Name</td>
      <td>Number Of Orders</td>
    </tr>
    <?php $z=0; ?>
    @foreach($mailArray as $arr)
    <?php 
          
          
          $z += $arr['order_count'];
          $product_name = $arr['product_name'];
          $order_count = $arr['order_count'];
    ?>
    <tr>
      <td>{{$product_name}}</td>
      <td>{{$order_count}}</td>
      
    </tr>
   
    @endforeach
    
    <tr bgcolor="#d3d3d3">
      <td>&nbsp;</td>
      <td style="text-align:left">Total Orders :{{$z}}</td>
      
    </tr>
    </tbody>
</table>
    </div>
   </body>
</html>
