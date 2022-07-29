<?php
  use Central\Repositories\AmazonApiRepo; 
  
  class ReportApiController extends BaseController
  {
    
    public function __construct(AmazonApiRepo $ApiObj)
    {
      
      $this->ApiRepoObj = $ApiObj;
      
    }
    
    function index()
    {
      try{ 
        $customerId =  (Session::has('customerId') && !empty(Session::get('customerId'))) ? Session::get('customerId') : 0;
        
        
        $data = Input::get();
        // echo "hi";exit;
        if(!empty($data['from_date']) && !empty($data['to_date']))
        {  
          
          $to_date = date('Y-m-d',strtotime($data["to_date"]));
          $from_date = date('Y-m-d',strtotime($data["from_date"]));
          }elseif(empty($data['from_date']) && !empty($data['to_date'])){
          $to_date = date('Y-m-d',strtotime($data["to_date"]));
          $StartDate =date_create($data['to_date']);
          $StartDate = date_sub($StartDate,date_interval_create_from_date_string("7 days"));
          $from_date = date_format($StartDate,"Y/m/d H:i:s"); 
          }elseif(!empty($data['from_date']) && empty($data['to_date'])){
          $Dates = $this->ApiRepoObj->getDates();
          $to_date = $Dates["to_date"];
          $from_date = date('Y-m-d',strtotime($data["from_date"]));
          
          }else{
          $Dates = $this->ApiRepoObj->getDates();
          $to_date = $Dates["to_date"];
          $from_date = $Dates["from_date"];
        }
        
        
        $ebay_orders = DB::select( DB::Raw('Select channel_id,count(order_id) as ebay_num from Channel_orders where channel_id =1')) ;
        //print_r($ebay_orders);exit;
        $amazon_orders = DB::select(DB::Raw('Select channel_id, count(order_id) as amazon_num from Channel_orders where channel_id =3'));
        $flipkart_orders = DB::select(DB::Raw('Select channel_id, count(order_id) as flipkart_num from Channel_orders where channel_id =2'));
        
        $orders = array(); 
        $orders= $this->ApiRepoObj->IndexVal($from_date,$to_date,$customerId);
        
        $fname = 'index';
        $order_status = $this->ApiRepoObj->getAllStatus();
        $channels = $this->ApiRepoObj->getAllChannels();
        $TodayOrderDetails = $this->ApiRepoObj->TodayOrderDetails($customerId);
        
        $urlgrid = "grid";
        return View::make('gdsreports.gdsreports')->with(array('flipkart_orders'=>json_encode($flipkart_orders),'ebay_orders'=>json_encode($ebay_orders),'amazon_orders'=>json_encode($amazon_orders),'orders'=>json_encode($orders), 'channels'=>$channels, 'order_status'=> $order_status,'from_date'=>$from_date,'to_date'=>$to_date,'TodayOrderDetails'=>$TodayOrderDetails,'customerId'=>$customerId,'urlgrid'=>$urlgrid));
      }
      catch(Exception $e){
        $message=$e->getMessage();
      }
    }
    
    public function all()
    {
      //$s =  parse_str($_SERVER['QUERY_STRING']);
      
      
      try{
        $customerId =  (Session::has('customerId') && !empty(Session::get('customerId'))) ? Session::get('customerId') : 0;
        
        
        //print_r($s);
        $StartDate = Input::get('from_date');
        if(!empty($StartDate)){
          $StartDate = (!empty($StartDate)) ? date_create($StartDate) : '';
          $StartDate= (!empty($StartDate)) ? date_format($StartDate,"Y/m/d H:i:s") : '';
        }
        
        $EndDate = Input::get('to_date');
        if(!empty($EndDate)){
          $EndDate = (!empty($EndDate)) ? date_create($EndDate) : '';
          $EndDate= (!empty($EndDate)) ? date_format($EndDate,"Y/m/d H:i:s") : '';
        }
        $order_status =  Input::get('order_status_id');
        
        
        $fname = 'all';
        $channel_id = $this->ApiRepoObj->getAllChannelID($fname);
        
        //$channel_id='0';
        
        
        
        
        if(!empty($StartDate) && !empty($EndDate) )
        
        {   
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
          
        }
        if(!empty($StartDate) && empty($EndDate) )
        
        {
          //print_r($EndDate);
          $Dates = $this->ApiRepoObj->getDates();
          $EndDate = $Dates["to_date"];
          
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
          
          //print_r($EndDate);exit;
        }
        
        
        if(empty($StartDate) && !empty($EndDate) )
        
        {
          //echo "hi";exit;
          $StartDate =date_create($EndDate);
          $StartDate = date_sub($StartDate,date_interval_create_from_date_string("7 days"));
          $StartDate = date_format($StartDate,"Y/m/d H:i:s"); 
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
          
          //echo "Start Date is Not Given. Showing results for the week before.<br>" ;                           
          
          
          
        }
        
        if(empty($StartDate) && empty($EndDate)){
          
          //echo "gu";exit;
          $Dates = $this->ApiRepoObj->getMinMaxOrderDate();
          //echo "<pre>"; print_r($Dates); die;
          //$Dates = $this->ApiRepoObj->getDates();
          $EndDate = $Dates[0]->maximum_date;
          $StartDate = $Dates[0]->minimum_date;
          
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
          
        }
        $ebay_orders = DB::select( DB::Raw('Select channel_id,count(order_id) as ebay_num from Channel_orders where channel_id =1')) ;
        //print_r($ebay_orders);exit;
        $amazon_orders = DB::select(DB::Raw('Select channel_id, count(order_id) as amazon_num from Channel_orders where channel_id =3'));
        $flipkart_orders = DB::select(DB::Raw('Select channel_id, count(order_id) as flipkart_num from Channel_orders where channel_id =2'));
        $order_status = $this->ApiRepoObj->getAllStatus();
        $channels = $this->ApiRepoObj->getAllChannels();
        $TodayOrderDetails = $this->ApiRepoObj->TodayOrderDetails($customerId);
        //print_r($orders);exit;
        //echo $StartDate.''.$EndDate; die;
        $final=array();
        foreach($orders as $orders){
          
          $orders->actions='<span style="padding-left:20px;" ><a href="/reportapis/ViewOrder/'.$orders->channel_order_id.'"><span class="badge bg-green"><i class="fa fa-eye"></i></span></a></span><span style="padding-left:50px;" ></span>';
          $final[] = $orders;
          
        }
        
        return json_encode($final);
        // return [  'orders' => $orders, 
        //           'total_orders' => json_encode($total_orders), 
        //           'channel_orders'=>json_encode($channel_orders),
        //           'order_status'=>$order_status,
        //           'fname'=>$fname,
        //           'gridurl'=>$gridurl,
        //           'StartDate'=>$StartDate,
        //           'EndDate'=>$EndDate,
        //           'channel_id'=>$channel_id ];
        
        // return View::make('gdsreports.report')->with(array('orders'=>$orders,'total_orders'=>json_encode($total_orders),'channel_orders'=>json_encode($channel_orders),'order_status'=>$order_status,'fname'=>$fname,'gridurl'=>$gridurl,'StartDate'=>$StartDate,'EndDate'=>$EndDate,'channel_id'=>$channel_id));
        
        
        }catch(Exception $e){
        $message=$e->getMessage();
      }
    }
    
    
    
    function amazon()
    {
      try{
        $customerId =  (Session::has('customerId') && !empty(Session::get('customerId'))) ? Session::get('customerId') : 0;
        
        
        
        $StartDate = Input::get('from_date');
        $StartDate = (!empty($StartDate)) ? date_create($StartDate) : '';
        $StartDate= (!empty($StartDate)) ? date_format($StartDate,"Y/m/d H:i:s") : '';
        
        $EndDate = Input::get('to_date');
        $EndDate = (!empty($EndDate)) ? date_create($EndDate) : '';
        $EndDate= (!empty($EndDate)) ? date_format($EndDate,"Y/m/d H:i:s") : '';
        
        $order_status = Input::get('order_status_id');
        /* print_r($StartDate);
          print_r($EndDate);
          print_r($order_status);
        exit;*/
        $fname = 'amazon';
        
        $channel_id = $this->ApiRepoObj->getAllChannelID($fname);
        //$channel_id='3';
        
        
        
        
        if(!empty($StartDate) && !empty($EndDate) )
        
        {   
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
          
        }
        if(!empty($StartDate) && empty($EndDate) )
        
        {
          //print_r($EndDate);
          $Dates = $this->ApiRepoObj->getDates();
          $EndDate = $Dates["to_date"];
          
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
          
          //print_r($EndDate);exit;
        }
        
        
        if(empty($StartDate) && !empty($EndDate) )
        
        {
          //echo "hi";exit;
          $StartDate =date_create($EndDate);
          $StartDate = date_sub($StartDate,date_interval_create_from_date_string("7 days"));
          $StartDate = date_format($StartDate,"Y/m/d H:i:s"); 
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
          
          //echo "Start Date is Not Given. Showing results for the week before.<br>" ;                           
          
          
          
        }
        
        if(empty($StartDate) && empty($EndDate)){
          
          //echo "gu";exit;
         $Dates = $this->ApiRepoObj->getMinMaxOrderDate();
          //echo "<pre>"; print_r($Dates); die;
          //$Dates = $this->ApiRepoObj->getDates();
          $EndDate = $Dates[0]->maximum_date;
          $StartDate = $Dates[0]->minimum_date;
          
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
          
        }
        
        
        /* return View::make('gdsreports.report')->with(array('orders'=>$orders,'total_orders'=>json_encode($total_orders),'channel_orders'=>json_encode($channel_orders),'order_status'=>$order_status,'fname'=>$fname,'gridurl'=>$gridurl,'StartDate'=>$StartDate,'EndDate'=>$EndDate,'channel_id'=>$channel_id,));*/
        $final = array();
        foreach($orders as $orders){
          
          $orders->actions='<span style="padding-left:20px;" ><a href="/reportapis/ViewOrder/'.$orders->channel_order_id.'"><span class="badge bg-green"><i class="fa fa-eye"></i></span></a></span><span style="padding-left:50px;" ></span>';
          $final[] = $orders;
          
        }
        
        return json_encode($final);
      }
      catch(Exception $e){
        $message=$e->getMessage();
      }
      
      
    }
    
    function Flipkart()
    {
      try{
        $customerId =  (Session::has('customerId') && !empty(Session::get('customerId'))) ? Session::get('customerId') : 0;
        
        $StartDate = Input::get('from_date');
        
        
        $StartDate = (!empty($StartDate)) ? date_create($StartDate) : '';
        $StartDate= (!empty($StartDate)) ? date_format($StartDate,"Y/m/d H:i:s") : '';
        //echo date_format($StartDate,"Y/m/d H:i:s");
        //print_r($StartDate);exit;
        $EndDate = Input::get('to_date');
        $EndDate = (!empty($EndDate)) ? date_create($EndDate) : '';
        $EndDate= (!empty($EndDate)) ? date_format($EndDate,"Y/m/d H:i:s") : '';
        $order_status = Input::get('order_status_id');
        
        
        //$channel_id = '2';
        
        
        $fname = 'Flipkart';
        $channel_id = $this->ApiRepoObj->getAllChannelID($fname);

        if(!empty($StartDate) && !empty($EndDate) ) 
        {   
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
        }
        if(!empty($StartDate) && empty($EndDate) )
        
        {
          $Dates = $this->ApiRepoObj->getDates();
          $EndDate = $Dates["to_date"];
          
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
        }
        
        
        if(empty($StartDate) && !empty($EndDate) )
        
        {
          $StartDate =date_create($EndDate);
          $StartDate = date_sub($StartDate,date_interval_create_from_date_string("7 days"));
          $StartDate = date_format($StartDate,"Y/m/d H:i:s"); 
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
          
          //echo "Start Date is Not Given. Showing results for the week before.<br>" ;                           
          
          
          
        }
        
        if(empty($StartDate) && empty($EndDate)){
          $Dates = $this->ApiRepoObj->getMinMaxOrderDate();
          //echo "<pre>"; print_r($Dates); die;
          //$Dates = $this->ApiRepoObj->getDates();
          $EndDate = $Dates[0]->maximum_date;
          $StartDate = $Dates[0]->minimum_date;
          
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
          
        }
        /*if(empty($orders)){
          
          echo "No Orders in this Date Range";
        }*/
        /*$piechart = $this->piechart($order_status,$StartDate,$EndDate,$fname);
        return View::make('gdsreports.report')->with(array('orders'=>$orders));*/
        $data = $this->ApiRepoObj->PiechartValues($order_status,$channel_id,$StartDate,$EndDate);
        $channel_orders = $data["channel_orders"];
        $total_orders = $data["total_orders"];
        $gridurl =$this->innergrid($fname);
        //$piechart = $this->piechart($order_status,$StartDate,$EndDate,$fname);
        // echo "guuu";exit;
        $this->innergrid($fname);
        /*return View::make('gdsreports.report')->with(array('orders'=>$orders,'total_orders'=>json_encode($total_orders),'channel_orders'=>json_encode($channel_orders),'order_status'=>$order_status,'fname'=>$fname,'gridurl'=>$gridurl,'StartDate'=>$StartDate,'EndDate'=>$EndDate,'channel_id'=>$channel_id));*/
        $final = array();
        foreach($orders as $orders){
          
          $orders->actions='<span style="padding-left:20px;" ><a href="/reportapis/ViewOrder/'.$orders->channel_order_id.'"><span class="badge bg-green"><i class="fa fa-eye"></i></span></a></span><span style="padding-left:50px;" ></span>';
          $final[] = $orders;
          
        }
        
        return json_encode($final);
      }
      catch(Exception $e){
        $message=$e->getMessage();
      }
    }
    
    function eBay()
    {
      try{
        $customerId =  (Session::has('customerId') && !empty(Session::get('customerId'))) ? Session::get('customerId') : 0;
        
        
        $StartDate = Input::get('from_date');
        
        
        $StartDate = (!empty($StartDate)) ? date_create($StartDate) : '';
        $StartDate= (!empty($StartDate)) ? date_format($StartDate,"Y/m/d H:i:s") : '';
        
        $EndDate = Input::get('to_date');
        $EndDate = (!empty($EndDate)) ? date_create($EndDate) : '';
        $EndDate= (!empty($EndDate)) ? date_format($EndDate,"Y/m/d H:i:s") : '';
        $order_status = Input::get('order_status_id');
        //$channel_id = '1';
        
        $fname = 'eBay';
        $channel_id = $this->ApiRepoObj->getAllChannelID($fname);
        
        if(!empty($StartDate) && !empty($EndDate) ) 
        {    $orders = array(); 
          
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
        }
        if(!empty($StartDate) && empty($EndDate) )
        
        {
          $Dates = $this->ApiRepoObj->getDates();
          $EndDate = $Dates["to_date"];
          
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
        }
        
        
        if(empty($StartDate) && !empty($EndDate) )
        
        {$StartDate =date_create($EndDate);
          $StartDate = date_sub($StartDate,date_interval_create_from_date_string("7 days"));
          $StartDate = date_format($StartDate,"Y/m/d H:i:s"); 
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
          
          //echo "Start Date is Not Given. Showing results for the week before.<br>" ;                           
          
          
          
        }
        
        if(empty($StartDate) && empty($EndDate)){
          
          $Dates = $this->ApiRepoObj->getMinMaxOrderDate();
          //echo "<pre>"; print_r($Dates); die;
          //$Dates = $this->ApiRepoObj->getDates();
          $EndDate = $Dates[0]->maximum_date;
          $StartDate = $Dates[0]->minimum_date;
          
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
          
        }
        
        
        $data = $this->ApiRepoObj->PiechartValues($order_status,$channel_id,$StartDate,$EndDate);
        $channel_orders = $data["channel_orders"];
        $total_orders = $data["total_orders"];
        
        $gridurl =$this->innergrid($fname);
        
        $final = array();
        foreach($orders as $orders){
          
          $orders->actions='<span style="padding-left:20px;" ><a href="/reportapis/ViewOrder/'.$orders->channel_order_id.'"><span class="badge bg-green"><i class="fa fa-eye"></i></span></a></span><span style="padding-left:50px;" ></span>';
          $final[] = $orders;
          
        }
        
        return json_encode($final);
        }catch(Exception $e){
        $message=$e->getMessage();
      }
    }
    
    public   function AllChannels()
    {
      try{
        $customerId =  (Session::has('customerId') && !empty(Session::get('customerId'))) ? Session::get('customerId') : 0;
        
        $StartDate = Input::get('from_date');
        
        
        $StartDate = (!empty($StartDate)) ? date_create($StartDate) : '';
        $StartDate= (!empty($StartDate)) ? date_format($StartDate,"Y/m/d H:i:s") : '';
        
        $EndDate = Input::get('to_date');
        $EndDate = (!empty($EndDate)) ? date_create($EndDate) : '';
        $EndDate= (!empty($EndDate)) ? date_format($EndDate,"Y/m/d H:i:s") : '';
        $order_status = Input::get('order_status_id');
        
        //$channel_id = '0';
        $fname = 'all';
        $channel_id = $this->ApiRepoObj->getAllChannelID($fname);
        
        if(!empty($StartDate) && !empty($EndDate) ) 
        {    $orders = array(); 
          
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
        }
        if(!empty($StartDate) && empty($EndDate) )
        
        {
          $Dates = $this->ApiRepoObj->getDates();
          $EndDate = $Dates["to_date"];
          
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
        }
        
        
        if(empty($StartDate) && !empty($EndDate) )
        
        {$StartDate =date_create($EndDate);
          $StartDate = date_sub($StartDate,date_interval_create_from_date_string("7 days"));
          $StartDate = date_format($StartDate,"Y/m/d H:i:s"); 
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
          
          //echo "Start Date is Not Given. Showing results for the week before.<br>" ;                           
          
          
          
        }
        
        if(empty($StartDate) && empty($EndDate)){
          
          /*$Dates = $this->ApiRepoObj->getDates();
          $EndDate = $Dates["to_date"];
          $StartDate = $Dates["from_date"];*/
          $Dates = $this->ApiRepoObj->getMinMaxOrderDate();
          //echo "<pre>"; print_r($Dates); die;
          //$Dates = $this->ApiRepoObj->getDates();
          $EndDate = $Dates[0]->maximum_date;
          $StartDate = $Dates[0]->minimum_date;
          
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
          
        }
        if(empty($orders)){
          
          //echo "No Orders in this Date Range";
        }
        
        $data = $this->ApiRepoObj->PiechartValues($order_status,$channel_id,$StartDate,$EndDate);
        $channel_orders = $data["channel_orders"];
        $total_orders = $data["total_orders"];
        $this->innergrid($fname);
        
        return View::make('gdsreports.report')->with(array('orders'=>$orders,'total_orders'=>json_encode($total_orders),'channel_orders'=>json_encode($channel_orders),'order_status'=>$order_status,'fname'=>$fname));
        
      }
      catch(Exception $e){
        $message=$e->getMessage();
      }
    }
    public function getStatus($cname)
    {
      try{
        if($cname =="all")
        {
          $cid ='0';
         // $ostatus= $this->ApiRepoObj->getOrderStatus($cid);
        //$ostatus["order_status"] = "all";
        }
        else
        {
          $cid= $this->ApiRepoObj->getpieChannelId($cname);
            //$ostatus= $this->ApiRepoObj->getOrderStatus($cid);
        }
        
        $ostatus= $this->ApiRepoObj->getOrderStatus($cid);
        $ostatus["order_status"] = "all";
        
        return json_encode($ostatus);
        
        
        }catch(Exception $e){
        $message=$e->getMessage();
      }
    }
    
    public function channelOrderDetails($order_id){
      try{
        $odetails= $this->ApiRepoObj->getOrderDetails($order_id);
        //echo "<pre>"; print_r($odetails); die;
        return json_encode($odetails);
        }catch(Exception $e){
        $message=$e->getMessage();
      }
    }
    
    function grid()
    {
      try{
        $customerId =  (Session::has('customerId') && !empty(Session::get('customerId'))) ? Session::get('customerId') : 0;
        
        
        //$data = Input::get();
        // echo "hi";exit;
        if(!empty($data['from_date']) && !empty($data['to_date']))
        {  
          
          $to_date = date('Y-m-d',strtotime($data["to_date"]));
          $from_date = date('Y-m-d',strtotime($data["from_date"]));
          }elseif(empty($data['from_date']) && !empty($data['to_date'])){
          $to_date = date('Y-m-d',strtotime($data["to_date"]));
          $StartDate =date_create($data['to_date']);
          $StartDate = date_sub($StartDate,date_interval_create_from_date_string("7 days"));
          $from_date = date_format($StartDate,"Y/m/d H:i:s"); 
          }elseif(!empty($data['from_date']) && empty($data['to_date'])){
          $Dates = $this->ApiRepoObj->getDates();
          $to_date = $Dates["to_date"];
          $from_date = date('Y-m-d',strtotime($data["from_date"]));
          
          }else{
          $Dates = $this->ApiRepoObj->getDates();
          $to_date = $Dates["to_date"];
          $from_date = $Dates["from_date"];
        }
        
        
        $ebay_orders = DB::select( DB::Raw('Select Co.channel_id,Ci.channel_logo,count(Co.order_id) as ebay_num from Channel_orders Co, Channel Ci where Co.channel_id =1')) ;
        //print_r($ebay_orders);exit;
        $amazon_orders = DB::select(DB::Raw('Select Co.channel_id,Ci.channel_logo,count(Co.order_id) as amazon_num from Channel_orders Co, Channel Ci where Co.channel_id =3'));
        $flipkart_orders = DB::select(DB::Raw('Select Co.channel_id, Ci.channel_logo, count(Co.order_id) as flipkart_num from Channel_orders Co,Channel Ci where Co.channel_id =2'));
        //print_r($flipkart_orders);exit;
        $orders = array(); 
        $orders= $this->ApiRepoObj->IndexVal($from_date,$to_date,$customerId);
        //prin
        $final = array();
        foreach($orders as $orders){
          
          $orders->actions='<span style="padding-left:20px;" ><a href="/reportapis/ViewOrder/'.$orders->channel_order_id.'"><span class="badge bg-green"><i class="fa fa-eye"></i></span></a></span><span style="padding-left:50px;" ></span>';
          $final[] = $orders;
          
        }
        $fname = 'index';
        
        /*$order_status = $this->ApiRepoObj->getAllStatus();
        $channels = $this->ApiRepoObj->getAllChannels();*/
        //print_r($orders);exit;
        return json_encode($final);
        
        /*  return View::make('gdsreports.gdsreports')->with(array('flipkart_orders'=>json_encode($flipkart_orders),'ebay_orders'=>json_encode($ebay_orders),'amazon_orders'=>json_encode($amazon_orders),'orders'=>json_encode($orders), 'channels'=>$channels, 'order_status'=> $order_status,'from_date'=>$from_date,'to_date'=>$to_date));*/
      }
      catch(Exception $e){
        $message=$e->getMessage();
      }
    }
    
    
    
    
    public function eBayGrid(){
      try{
        
        parse_str($_SERVER['QUERY_STRING']);
        
        //dd($channel_id);
        //print_r($)
        
        $customerId =  (Session::has('customerId') && !empty(Session::get('customerId'))) ? Session::get('customerId') : 0;
        
        $StartDate = (!empty($StartDate)) ? date_create($StartDate) : '';
        $StartDate= (!empty($StartDate)) ? date_format($StartDate,"Y/m/d H:i:s") : '';
        
        //$EndDate = Input::get('to_date');
        $EndDate = (!empty($EndDate)) ? date_create($EndDate) : '';
        $EndDate= (!empty($EndDate)) ? date_format($EndDate,"Y/m/d H:i:s") : '';
        //$order_status = Input::get('order_status_id');
        
        //$fname = 'eBay';
        
        if(!empty($StartDate) && !empty($EndDate) ) 
        {    $orders = array(); 
          
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
        }
        if(!empty($StartDate) && empty($EndDate) )
        
        {
          $Dates = $this->ApiRepoObj->getDates();
          $EndDate = $Dates["to_date"];
          
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
        }
        
        
        if(empty($StartDate) && !empty($EndDate) )
        
        {$StartDate =date_create($EndDate);
          $StartDate = date_sub($StartDate,date_interval_create_from_date_string("7 days"));
          $StartDate = date_format($StartDate,"Y-m-d H:i:s"); 
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
          
          //echo "Start Date is Not Given. Showing results for the week before.<br>" ;                           
          
          
          
        }
        
        if(empty($StartDate) && empty($EndDate)){
          
          /*$Dates = $this->ApiRepoObj->getDates();
          $EndDate = $Dates["to_date"];
          $StartDate = $Dates["from_date"];*/
          $Dates = $this->ApiRepoObj->getMinMaxOrderDate();
          //echo "<pre>"; print_r($Dates); die;
          //$Dates = $this->ApiRepoObj->getDates();
          $EndDate = $Dates[0]->maximum_date;
          $StartDate = $Dates[0]->minimum_date;
          
          $orders = $this->ApiRepoObj->AllValues($StartDate,$EndDate,$channel_id,$order_status,$customerId);
          
        }
        $final = array();
        foreach($orders as $orders){
          
          $orders->actions='<span style="padding-left:20px;" ><a href="/reportapis/ViewOrder/'.$orders->channel_order_id.'"><span class="badge bg-green"><i class="fa fa-eye"></i></span></a></span><span style="padding-left:50px;" ></span>';
          $final[] = $orders;
          
        }
        //echo "<pre>"; print_r($orders); die;
        
        return json_encode($final);
        
      }
      catch(Exception $e){
        $message=$e->getMessage();
      }
    }
    
    
    
    public function innergrid($fname)
    {
      try{
        
        if($fname ="eBay")
        {
          
          return 'eBayGrid';
          
        }
        if($fname ="Flipkart")
        {
          
          return 'FlipkartGrid';
          
        }
        if($fname ="amazon")
        {
          
          return 'amazonGrid';
          
        }
        if($fname ="all")
        {
          
          return 'allGrid';
          
        }
        
        
      }
      catch(Exception $e){
        $message=$e->getMessage();
      }
    }
    
    
    public function todayorders()
    {
      
      try{
        $customerId =  (Session::has('customerId') && !empty(Session::get('customerId'))) ? Session::get('customerId') : 0;
        
        
        date_default_timezone_set('Asia/Kolkata');
        $StartDate = date("Y-m-d 00:00:00");
        // print_r($StartDate);
        $EndDate = date("Y-m-d H:i:s");
        $EndDate = date_create($EndDate);
        $EndDate = date_format($EndDate,"Y-m-d H:i:s");
        //  parse_str($_SERVER['QUERY_STRING']);
        
        Input::get('os');
        $os = Input::get('os');
        
        $order_status = $this->ApiRepoObj->getAllStatus();
        $channels = $this->ApiRepoObj->getAllChannels();
        $TodayOrderDetails = $this->ApiRepoObj->TodayOrderDetails($customerId);
        
        
        return View::make('gdsreports.gdsreports')->with(array( 'channels'=>$channels, 'order_status'=> $order_status,'TodayOrderDetails'=>$TodayOrderDetails,'os'=>$os,'customerId'=>$customerId));
        
        
        //  return json_encode($orders);
        
        
      }
      catch(Exception $e){
        $message=$e->getMessage();
      }
    }
    
    function unshippedgrid($os)
    {
      try{
        //parse_str($_SERVER['QUERY_STRING']);
        
        $customerId =  (Session::has('customerId') && !empty(Session::get('customerId'))) ? Session::get('customerId') : 0;
        
        
        date_default_timezone_set('Asia/Kolkata');
        
        $StartDate = date("Y-m-d");
        $StartDate = date_create($StartDate);
        $StartDate = date_format($StartDate,"Y-m-d H:i:s");
        //print_r($stt);exit;
        
        $EndDate = date("Y-m-d H:i:s");
        $EndDate = date_create($EndDate);
        $EndDate = date_format($EndDate,"Y-m-d H:i:s");
        //  parse_str($_SERVER['QUERY_STRING']);
        if($customerId=="0"){
          if($os=="Unshipped"){
           $orders = $this->ApiRepoObj->UnshippedAdminOrders();
          }
          if($os=="Completed"){
            $orders = $this->ApiRepoObj->CompletedAdminOrders($StartDate,$EndDate);
          }
          if($os=="all"){
            $orders = $this->ApiRepoObj->AllAdminOrders($StartDate,$EndDate);
          }
        }
        else{
          
          if($os=="Unshipped"){
            $orders = $this->ApiRepoObj->CustomerUnshippedOrders($customerId);
          }
          if($os=="Completed"){
           $orders = $this->ApiRepoObj->CustomerCompletedOrders($StartDate,$EndDate,$customerId); 
          }
          if($os=="all"){
           $orders = $this->ApiRepoObj->CustomerAllOrders($StartDate,$EndDate,$customerId);  
          }}
          
          $final = array();
          foreach($orders as $orders){
            
            $orders->actions='<span style="padding-left:20px;" ><a href="/reportapis/ViewOrder/'.$orders->channel_order_id.'"><span class="badge bg-green"><i class="fa fa-eye"></i></span></a></span><span style="padding-left:50px;" ></span>';
            $final[] = $orders;
            
          }
          $fname = 'index';
          
          /*$order_status = $this->ApiRepoObj->getAllStatus();
          $channels = $this->ApiRepoObj->getAllChannels();*/
          //print_r($orders);exit;
          return json_encode($final);
          
          /*  return View::make('gdsreports.gdsreports')->with(array('flipkart_orders'=>json_encode($flipkart_orders),'ebay_orders'=>json_encode($ebay_orders),'amazon_orders'=>json_encode($amazon_orders),'orders'=>json_encode($orders), 'channels'=>$channels, 'order_status'=> $order_status,'from_date'=>$from_date,'to_date'=>$to_date));*/
      }
      catch(Exception $e){
        $message=$e->getMessage();
      }
    }
    
    public function ViewOrder($order_id){
      
      try{
        
        
        $order_details  = DB::table('Channel_orders as co')
        ->leftJoin('Channel_order_details as cod','co.channel_order_id','=','cod.order_id')
        ->leftJoin('Channel_order_payment as cop','cop.order_id','=','co.channel_order_id')
        ->leftJoin('Channel_address as cad','cad.channel_id','=','co.channel_id')
        ->leftJoin('Channel as ch','ch.channel_id','=','co.channel_id')
        ->leftJoin('Channel_orders_shipping_address as cosa','cosa.order_id','=','co.channel_order_id')
        ->where('co.channel_order_id',$order_id)
        ->get();
        
        
        $order_product_details = DB::table('Channel_order_details as cod')
        ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
        ->where('order_id',$order_details[0]->order_id)
        ->get();
        
        
        $order_status = DB::table('Channel_order_status')
        ->where(array('status_type'=>"update_order",'channel_id'=>$order_details[0]->channel_id))
        ->select('status_id','status_value')
        ->get();
        //print_r($order_product_details  );
        
        $product_array=array();
        
        foreach($order_product_details as $details){
          //print_r($details->product_id);exit;
          //print_r($details->product_id);exit;
          $product_details=  DB::table('products')
          ->where('product_id',$details->product_id)
          ->get();
          
          $product_array['product_name'] = $product_details[0]->name;
          $product_array['quantity'] = $details->quantity;
          $product_array['price'] = $details->price;
          $product_array['subtotal'] = $details->price*$details->quantity;
          $product_array['tax'] = $details->tax;
          $product_array['total'] = $product_array['subtotal']+$product_array['tax']+$order_details[0]->shipping_cost;
          $final_product_array[] = $product_array;
          
        }
        //print_r($final_product_array);exit;
        
        return View::make('orders.gdsViewOrder',compact('order_details','final_product_array','order_status'));
        
      }
      catch(Exception $e){
        
        $message = $e->getMessage();
      }
    }
    
    public function PrintInvoice($order_id,$print_invoice){
      try{
        
        $order_details  = DB::table('Channel_orders as co')
        ->leftJoin('Channel_order_details as cod','co.channel_order_id','=','cod.order_id')
        //->leftJoin('Channel_order_details as codt','codt.channel_item_id','=','Channel_product_add_update.channel_product_key$
        ->leftJoin('Channel_order_payment as cop','cop.order_id','=','co.channel_order_id')
        ->leftJoin('Channel as ch','ch.channel_id','=','co.channel_id')
        ->leftJoin('Channel_orders_shipping_address as cosa','cosa.order_id','=','co.channel_order_id')
        ->leftJoin('Channel_order_shipping_details as cosd','cosd.order_id','=','co.channel_order_id')
        ->where('co.channel_order_id',$order_id)
        ->get();
        
        $order_product_details = DB::table('Channel_order_details as cod')
        ->leftJoin('Channel_product_add_update as cpau','cpau.channel_product_key','=','cod.channel_item_id')
        ->where('order_id',$order_details[0]->order_id)
        ->get();
        
        $product_array=array();
        
        
        foreach($order_product_details as $details){
          
          $product_details=  DB::table('products')
          ->where('product_id',$details->product_id)
          ->get();
          $product_array['product_name'] = $product_details[0]->name;
          $product_array['quantity'] = $details->quantity;
          $product_array['price'] = $details->price;
          $product_array['subtotal'] = $details->price*$details->quantity;
          $product_array['tax'] = $details->tax;
          $product_array['total'] = $product_array['subtotal']+$product_array['tax'];
          $final_product_array[] = $product_array;
          
        }
        
        return View::make('orders.gdsPrintInvoice',compact('order_details','final_product_array','order_status','print_invoice'));
        
      }
      catch(Exception $e){
        $message=$e->getMessage();
      }
    }
    
  }                 