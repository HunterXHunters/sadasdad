<?php 

use Central\Repositories\RoleRepo;
use Central\Repositories\CustomerRepo;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class WalletController extends BaseController{
    
    public $roleAccess;
    public $custRepoObj;
    public $customerId;
    public function __construct(RoleRepo $roleAccess, CustomerRepo $custRepoObj) {
        $this->roleAccess = $roleAccess;
        $this->custRepoObj = $custRepoObj;
    	$this->customerId = (!empty(session::get('customerId'))) ? session::get('customerId') : 3;
    }

    public function statement()
    {
        parent::Breadcrumbs(array('Home'=>'/')); 
        
        $balance_counter = DB::table('wallet_balance')->where(array('manufacturer_id'=>$this->customerId))->get(['used_count','drawn_count','print_count','download_count']);

        $toalIdQty = DB::table('wallet_po as wp')  
                     ->join('wallet_request as wr','wp.po_id','=','wr.po_id')
                     ->join('wallet_approval as wa','wp.po_id','=','wa.po_id')
                     ->where(array('wp.manufacturer_id'=>$this->customerId,'wp.po_for'=>'IDs','wa.is_approved'=>1))->sum('wr.quantity');

        $availableIds = ($toalIdQty - ($balance_counter[0]->used_count+$balance_counter[0]->drawn_count));

        /*$po_results = DB::table('wallet_po')->where(array('manufacturer_id'=>$this->customerId))
                      ->select(DB::Raw('po_number,quantity,DATE_FORMAT(po_date,"%M %d, %Y") as date,po_file_path,po_for'))->orderby('po_id','desc')->get();*/

        $po_results =   DB::table('wallet_approval as wa')
                        ->select(DB::Raw('wa.invoice_no,wp.po_number,wr.quantity,DATE_FORMAT(wa.invoice_date,"%M %d, %Y") as date,wa.invoice_file_path,wp.po_for'))
                        ->join('wallet_po as wp','wa.po_id','=','wp.po_id')
                        ->join('wallet_request as wr','wa.request_id','=','wr.request_id')
                        ->where(array('wp.manufacturer_id'=>$this->customerId,'wp.po_for'=>'IDs','wa.is_approved'=>1))->orderby('wa.approval_id','desc')->get();

        $currentMonthUsedId  = DB::table('wallet_usages')->whereRaw("MONTH(sdate)='".date('m')."' and YEAR(sdate)='".date('Y')."' and type='IDs' and manufacturer_id='".$this->customerId."'")->sum('qty');

         $monthlyUsedCount = DB::table('wallet_usages')->whereRaw('manufacturer_id ='.$this->customerId.' and type = "IDs"  and YEAR(sdate)= "'.(date('Y')).'"')->groupBy('mth')->select(DB::raw('MONTH(sdate) as mth,sum(qty) as qty'))->get();

        $months = array_values(array_unique(array_column(json_decode(json_encode($monthlyUsedCount),true), 'mth')));
        
        $quantity = array_column(json_decode(json_encode($monthlyUsedCount),true), 'qty');

        foreach ($months as $key => $month) {
            $dateObj   = DateTime::createFromFormat('!m', $month);
            $months[$key] = $dateObj->format('M');
        }

        $usagesHistory = DB::table('wallet_usages')->groupBy('level')->where(array('manufacturer_id'=>$this->customerId,'type'=>'IDs'))->select(DB::raw('sum(qty) as qty'))->get();

        foreach ($usagesHistory as $key => $uh) {
            $usagesHistory[$key]->qty = $this->number_format($uh->qty);
        }    
        return View::make('wallet.statement1')->with(array('availableIds'=>$this->number_format($availableIds),'currentMonthUsedId'=>$this->number_format($currentMonthUsedId),'totalUsedCount'=>$this->number_format($balance_counter[0]->used_count),"drawn_count"=>$this->number_format($balance_counter[0]->drawn_count),"print_count"=>$this->number_format($balance_counter[0]->print_count),"download_count"=>$this->number_format($balance_counter[0]->download_count),'po_results'=>$po_results,'months'=>json_encode($months),'quantity'=>json_encode($quantity),'usagesHistory'=>$usagesHistory));
    }

    public function autoRefreshStatement()
    {
        $balance_counter = DB::table('wallet_balance')->where(array('manufacturer_id'=>$this->customerId))->get(['used_count','drawn_count','print_count','download_count']);

        $toalIdQty = DB::table('wallet_po as wp')  
                     ->join('wallet_request as wr','wp.po_id','=','wr.po_id')
                     ->join('wallet_approval as wa','wp.po_id','=','wa.po_id')
                     ->where(array('wp.manufacturer_id'=>$this->customerId,'wp.po_for'=>'IDs','wa.is_approved'=>1))->sum('wr.quantity');

        $availableIds = ($toalIdQty - ($balance_counter[0]->used_count+$balance_counter[0]->drawn_count));

        $usagesHistorys = DB::table('wallet_usages')->groupBy('level')->where(array('manufacturer_id'=>$this->customerId,'type'=>'IDs'))->select(DB::raw('sum(qty) as qty,level'))->get();
        $product_count = 0;
        $corton_count = 0;
        $tp_count = 0;
        foreach ($usagesHistorys as $key => $usagesHistory) {
            if($usagesHistory->level==0)
                $product_count = $this->number_format($usagesHistory->qty);
            elseif($usagesHistory->level==1)
                $corton_count = $this->number_format($usagesHistory->qty);
            elseif($usagesHistory->level==9)
                $tp_count = $this->number_format($usagesHistory->qty);
        }        


        echo json_encode(array("availableIds"=>$this->number_format($availableIds),"drawn_count"=>$this->number_format($balance_counter[0]->drawn_count),"used_count"=>$this->number_format($balance_counter[0]->used_count),"print_count"=>$this->number_format($balance_counter[0]->print_count),"download_count"=>$this->number_format($balance_counter[0]->used_count),"product_count"=>$product_count,"corton_count"=>$corton_count,"tp_count"=>$tp_count)); 
    }
    public function getIds()
    {
        $fromDate = Input::get('from_date');
        $toDate = Input::get('to_date');

        if(empty($fromDate)){
            $fromDate =  DB::table('wallet_usages')->orderby('usages_id','asc')->take(1)->whereRaw('sdate is not null and sdate!=""')->pluck('sdate');
            $fromDate = date('Y-m-d',strtotime($fromDate));
        }else
            $fromDate = date('Y-m-d',strtotime($fromDate));
        

        if(empty($toDate))
            $toDate = date('Y-m-d');
        else
            $toDate = date('Y-m-d',strtotime($toDate));

       // echo $fromDate."=> ".$toDate." ";  
        $fromDateObj = new DateTime($fromDate);    
        $toDateObj = new DateTime($toDate);   
        $interval = $fromDateObj->diff($toDateObj);
        $days = $interval->format('%a');
        $select = '';
        if($days <= 30){
            $select = 'DAY(sdate) as mth, sum(qty) as qty';
        }elseif($days > 30 && $days <= 365){
            $select = 'MONTH(sdate) as mth, sum(qty) as qty';
        }elseif ($days > 365){
            $select = 'YEAR(sdate) as mth, sum(qty) as qty';
        } 
        
        $where = "type='IDs' and manufacturer_id=".$this->customerId;
        
        if(!empty($fromDate))
        {   
            $where .= !empty($where) ? ' and ' : '';
            $where .= "DATE(sdate) >='".date('Y-m-d',strtotime($fromDate))."'";
        }

        if(!empty($toDate))
        {   
            $where .= !empty($where) ? ' and ' : '';
            $where .= "DATE(sdate) <='".date('Y-m-d',strtotime($toDate))."'";
        }

        $monthlyUsedCount = DB::table('wallet_usages')->whereRaw($where)->groupBy('mth')->select(DB::raw( $select))->orderby('usages_id','asc')->get();

        $months = array_values(array_unique(array_column(json_decode(json_encode($monthlyUsedCount),true), 'mth')));
        if($days > 30 && $days <= 365){
            foreach ($months as $key => $month) {
                $dateObj   = DateTime::createFromFormat('!m', $month);
                $months[$key] = $dateObj->format('M');
            }    
        }
        

        $quantity = array_column(json_decode(json_encode($monthlyUsedCount),true), 'qty');

        $total = 0;
        foreach ($quantity as $value) {
            $total = $total+$value;
        }

        //$result= DB::table('wallet_usages')->whereRaw($where)->sum('qty');

        echo json_encode(array("months"=>$months,"quantity"=>$quantity,"total"=>$this->number_format($total))); die;
    }

    public function number_format($number)
    {
        setlocale(LC_MONETARY,"en_IN");
        $temp = money_format("%i",$number);
        return str_replace("INR ", "", str_replace(".00", "", $temp));
    }

    public function getHistory()
    {
        $initFrom = Input::get('init');

        if($initFrom=='Drawn') {
            $results = DB::table('wallet_balance')->where(array('manufacturer_id'=>$this->customerId))->get(['print_count','download_count']);
            if(!empty($results)){
                echo json_encode(array('printed'=>$results[0]->print_count,'download'=>$results[0]->download_count)); die;
            }
        }elseif($initFrom=='Activated'){
            $results = DB::table('wallet_usages')->whereRaw("type='IDs' and manufacturer_id='".$this->customerId."'")->groupBy('level')->select(DB::raw('sum(qty) as qty,level'))->get();
            $response = array();
            if(!empty($results)){
                
                foreach($results as $result){
                    if($result->level==0)
                        $response['product']=$result->qty;
                    if($result->level==9)
                        $response['tp']=$result->qty;
                    if($result->level==1)
                        $response['carton']=$result->qty;
                }
                echo json_encode($response); die;
            }
        }
    }
    public function downloadStatement()
    {
         
        $balance_counter = DB::table('wallet_balance')->where(array('manufacturer_id'=>$this->customerId))->get(['used_count','drawn_count','print_count','download_count']);

        $toalIdQty = DB::table('wallet_po as wp')  
                     ->join('wallet_request as wr','wp.po_id','=','wr.po_id')
                     ->join('wallet_approval as wa','wp.po_id','=','wa.po_id')
                     ->where(array('wp.manufacturer_id'=>$this->customerId,'wp.po_for'=>'IDs','wa.is_approved'=>1))->sum('wr.quantity');

        $availableIds = ($toalIdQty - ($balance_counter[0]->used_count+$balance_counter[0]->drawn_count));

        /*$po_results = DB::table('wallet_po')->where(array('manufacturer_id'=>$this->customerId))
                      ->select(DB::Raw('po_number,quantity,DATE_FORMAT(po_date,"%M %d, %Y") as date,po_file_path,po_for'))->orderby('po_id','desc')->get();*/

        $po_results =   DB::table('wallet_approval as wa')
                        ->select(DB::Raw('wa.invoice_no,wp.po_number,wr.quantity,DATE_FORMAT(wa.invoice_date,"%M %d, %Y") as date,wa.invoice_file_path,wp.po_for'))
                        ->join('wallet_po as wp','wa.po_id','=','wp.po_id')
                        ->join('wallet_request as wr','wa.request_id','=','wr.request_id')
                        ->where(array('wp.manufacturer_id'=>$this->customerId,'wp.po_for'=>'IDs','wa.is_approved'=>1))->orderby('wa.approval_id','desc')->get();

        $currentMonthUsedId  = DB::table('wallet_usages')->whereRaw("MONTH(sdate)='".date('m')."' and YEAR(sdate)='".date('Y')."' and type='IDs' and manufacturer_id='".$this->customerId."'")->sum('qty');

         $monthlyUsedCount = DB::table('wallet_usages')->whereRaw('manufacturer_id ='.$this->customerId.' and type = "IDs"  and YEAR(sdate)= "'.(date('Y')).'"')->groupBy('mth')->select(DB::raw('MONTH(sdate) as mth,sum(qty) as qty'))->get();

        $months = array_values(array_unique(array_column(json_decode(json_encode($monthlyUsedCount),true), 'mth')));
        
        $quantity = array_column(json_decode(json_encode($monthlyUsedCount),true), 'qty');

        foreach ($months as $key => $month) {
            $dateObj   = DateTime::createFromFormat('!m', $month);
            $months[$key] = $dateObj->format('M');
        }

        $usagesHistory = DB::table('wallet_usages')->groupBy('level')->where(array('manufacturer_id'=>$this->customerId,'type'=>'IDs'))->select(DB::raw('sum(qty) as qty'))->get();

        foreach ($usagesHistory as $key => $uh) {
            $usagesHistory[$key]->qty = $this->number_format($uh->qty);
        }    
        $html = View::make('wallet.wdownload')->with(array('availableIds'=>$this->number_format($availableIds),'currentMonthUsedId'=>$this->number_format($currentMonthUsedId),'totalUsedCount'=>$this->number_format($balance_counter[0]->used_count),"drawn_count"=>$this->number_format($balance_counter[0]->drawn_count),"print_count"=>$this->number_format($balance_counter[0]->print_count),"download_count"=>$this->number_format($balance_counter[0]->download_count),'po_results'=>$po_results,'months'=>json_encode($months),'quantity'=>json_encode($quantity),'usagesHistory'=>$usagesHistory))->render();

        echo $html; die;
        

        $pdf = PDF::loadHTML($html);
        
        return $pdf->download('wallet.pdf');
    }	

}    
