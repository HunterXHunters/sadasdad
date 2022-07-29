<?php
namespace App\Http\Controllers;
set_time_limit(0);
ini_set('memory_limit', '-1');

use Session;
use DB;
use View;
use Redirect;
use Log;
use Exception;

use App\Models\Products;
use App\Models\Customers;

use App\Repositories\RoleRepo;
use App\Repositories\CustomerRepo;
use Illuminate\Http\Request;
use App\Repositories\SapApiRepo;
use App\Repositories\ConnectErp;
use App\Repositories\ApiRepo;
use App\Repositories\OrderRepo;
use App\Models\MasterLookup;
use App\Models\Location;
use App\Models\Token;
use App\Models\ApiLog;
use App\Models\User;
use App\Models\ErpObjects;
use App\Models\Conversions;
use App\Models\Trackhistory;
use App\Models\Transaction;
use App\Models\Track;
//use App\Events\test;
use App\Events\scoapi_BindEseals;
use App\Events\scoapi_MapEseals;

/**
 * 
 */
class ImportPOController extends BaseController
{
	protected $_product;
    protected $_manufacturerId;
    private $roleRepo;
    public $_request;
    protected $custRepo;
	protected $roleAccess;
	protected $attributeTable = 'attributes';
	protected $TPAttributeMappingTable = 'tp_attributes';
	protected $attributeMappingTable = 'attribute_mapping';    
	protected $trackHistoryTable = 'track_history';
	protected $trackDetailsTable = 'track_details';
	protected $tpDetailsTable = 'tp_details';    
	protected $tpDataTable = 'tp_data';        
	protected $tpPDFTable = 'tp_pdf';            
	protected $locationsTable = 'locations';            
	protected $prodSummaryTable = 'production_summary';            
	protected $transactionMasterTable = 'transaction_master';  
	protected $bindHistoryTable = 'bind_history';  
	protected $valuation ='valuation_type';          
	private $_childCodes = array();
	private $_apiRepo;
	public $erp;
	public $eSeal_erp;
    public function __construct(OrderRepo $OrderObj, CustomerRepo $custRepoObj,Request $request) {
    	$this->OrderObj = $OrderObj;

        $this->custRepoObj = $custRepoObj;
        $this->_request=$request;
        $product = new Products\Products();
        $productattr = new Products\ProductAttributes();

        $this->_onboarding = new Customers\Onboarding();
        //$this->_onboarding = $onboarding;
        $this->custRepo = new CustomerRepo;
        $this->roleRepo = new RoleRepo;
        $this->_esealCustomer = new Customers\EsealCustomers();        
        $this->_manufacturerId = $this->custRepo->getManufacturerId();
        
        if(Session::has('userId')==''||Session::has('userId')==0){
            return Redirect::to('/login');
        }

    }

    public function index()
    {
//        echo Session::has('userId'); exit;
    if(Session::has('userId')==''||Session::has('userId')==0){
        return Redirect::to('/login');
    }
    }

	public function importPOList(Request $request){
		$customerId=$this->_manufacturerId;
		$importPOData = DB::table('ImportPO')
                        ->leftJoin('import_grn', 'ImportPO.po_number', '=', 'import_grn.po_number')
                        ->select('ImportPO.po_number', 'ImportPO.idb_number', 'ImportPO.material', 'ImportPO.product_id', 'ImportPO.vender', 'import_grn.status', 'import_grn.batch', 'import_grn.grn_number', 'import_grn.wms_status')
                        ->get();
		return View::make('importpo/importPoList')
					->with(array(
					'importPOData'	=> $importPOData,
					'customer_id'	=> $this->roleRepo->encodeData($customerId)));
	}

    public function importPOList_new(Request $request){
        if(Session::has('userId')==''||Session::has('userId')==0){
            return Redirect::to('/login');
        }
        $customerId=$this->_manufacturerId;
        $importPOData = DB::table('ImportPO')
                        ->leftJoin('import_grn', 'ImportPO.po_number', '=', 'import_grn.po_number')
                        ->select('ImportPO.po_number', 'ImportPO.idb_number', 'ImportPO.material', 'ImportPO.product_id', 'ImportPO.vender', 'import_grn.status', 'import_grn.batch', 'import_grn.grn_number', 'import_grn.wms_status')
                        ->get();
        return View::make('importpo/importPOlist2')
                    ->with(array(
                    'importPOData'  => $importPOData,
                    'customer_id'   => $this->roleRepo->encodeData($customerId)));
    }

    public function importPODataList(Request $request){
        $importPOData = DB::table('ImportPO')->get();
        return $data['masterData'] = $importPOData;
    }

    public function getTreeImportPoList($id){
    	try{
    		$id = $this->roleRepo->decodeData($id);
    		if($id == '')
    		{
    			$id = 1;
    		}
    		$finalCustArrs = array();
    		$finalCustArr = array();
            $cust = array();
            $importPOData = DB::table('import_grn')->get();
            foreach ($importPOData as $key => $value) {
            	$cust['actions'] = '';
            	$cust['po_number'] = $value->po_number;
            	$cust['idb_number'] = $value->ibd_number;
                $cust['status'] = $value->status;
                $cust['batch'] = $value->batch;
                $cust['grn_number'] = $value->grn_number;
                $cust['wms_status'] = $value->wms_status;
                $cust['actions'] = '<span style="padding-left:10px;" ><a onclick = "deleteGrn('.$value->grn_number.')"><span class="badge bg-red">Grn Cancel</span></a></span>';
                $finalCustArrl = array();
            	$custl = array();
            	$importgrnData = DB::table('ImportPO')
            					->where('po_number', $value->po_number)
            					->get();
                // return $importgrnData;
            	foreach ($importgrnData as $ckey => $cvalue) {
            		$custl['po_number_grn'] = $cvalue->po_number;
            		$custl['material'] = $cvalue->material;
                    $custl['material_des'] = $cvalue->material_des;
            		$finalCustArrl[] = $custl;
            	}
            	$cust['children'] = $finalCustArrl;
                $finalCustArr[] = $cust;
                
            }
            return $finalCustArr;
    	}catch(Ecxeption $e){
            Log::info("message:-------");
            Log::info($e->getMessage());
        }
    }
    public function getTreeImportPoList_new(){
        try{
           
            $finalCustArrs = array();
            $finalCustArr = array();
            $cust = array();
            $importPOData = DB::table('import_grn as ig')
                            ->Join('ImportPO as ip','ip.id','=','ig.import_po_id')
                            ->leftJoin('grn_to_creation_queue as toq', 'toq.gr_document_no', '=', 'ig.cancelled_doc_no')
                            ->select(
                                DB::RAW("ip.id,ip.po_number,ip.idb_number,ig.batch,ig.grn_number,ig.cancelled_doc_no,ip.vender,ip.product_id,ip.order_uom,toq.to_number,toq.status as tostatus,toq.gr_document_no,
                                    (case when ig.status=1 then CONCAT('<span style=\"padding-left:10px;\"><a onclick = \"deleteGrn(',ig.grn_number,')\"><span class=\"badge bg-red\">Grn Cancel</span></a></span>')
                                    else '' end )as actions"),
                                DB::raw('case when ig.status=1 then "GRN Created" when ig.status=3 then "GRN Cancelled" end as status'))
                            ->orderby('ig.id', 'desc')
                            ->get()
                            ->toArray();
            // return $importPOData = array_unique($importPOData, SORT_REGULAR);
            $parentData = [];
                foreach ($importPOData as $key => $val) {
                    $val->idGrn = $val->po_number.''.$val->grn_number;
                    $actionData = '<span style="padding-left:10px;"><a onclick = "GrnToConfirm('.$val->cancelled_doc_no.')"><span class="badge bg-blue">TO Confirm</span></a></span>';
                    $val->actions = ($val->to_number !== null) ? ($val->tostatus == 1) ? '' : $actionData : $val->actions;
                    $val->status = ($val->to_number !== null) ? ($val->tostatus == 1) ? 'TO Confirmed - GRN Cancellation' : 'TO Create - GRN Cancellation' : $val->status;
                    $parentData[] = $val;
                }
             // print_r($importPOData);exit;ip.wms_status,
            $po_id = json_decode(json_encode($parentData), true);
                /*CONCAT('<a 
         data-href=\"javascript:void(0)\" onclick = \"deleteGrn(',grn_number,')\"></a>')as actions*/

         /*CONCAT('<span style=\"padding-left:10px;\"><a onclick = \"deleteGrn(',grn_number,')\"><span class=\"badge bg-red\">Grn Cancel</span></a></span>')as actions*/
                
                $importgrnData = DB::table('ImportPO as ip')
                                ->leftJoin('import_grn as ig','ig.import_po_id','=','ip.id')
                                ->leftJoin('products as  p','p.product_id','=','ip.product_id')
                                // ->whereIn('ig.grn_number',array_column($po_id, 'grn_number'))
                                ->select('ip.id', 'ip.po_number','ig.grn_number', 'ig.action_quantity', 'ig.action_uom','ig.batch','p.material_code as material','ip.open_quantity','ip.base_uom','p.description as material_des')
                                ->get()->toArray();
                $childsData = [];
                foreach ($importgrnData as $key => $value) {
                    $value->idGrn = $value->po_number.''.$value->grn_number;
                    $value->grn_number = ($value->grn_number == null) ? 0 : $value->grn_number;
                    $childsData[] = $value;
                }
                $finalCustArr['master'] = $po_id;
                $finalCustArr['childs'] = json_decode(json_encode($childsData), true);
              // echo"<pre>";print_r($finalCustArr);exit;
            return $finalCustArr;
        }catch(Ecxeption $e){
            Log::info("message:-------");
            Log::info($e->getMessage());
        }
    }

    public function deleteGrn($grnNumber = ''){
    	if (!$grnNumber){
            return 'Enter GRN Number';
        }
        $this->erp=new ConnectErp($this->_manufacturerId);
        $dateYear = date('Y');
        $params = 'grn_number='.$grnNumber.'&doc_year='.$dateYear.''; 
        $body = array(
            'Yh3mmEsealGrnCancellation' => array(
                'IHeader'   => array(
                    'GrnNumber'     => $grnNumber,
                    'DocumentYear'  => $dateYear)));

        $method = 'grnCancellation';
        $result=$this->erp->request($method,'',$body,'POST');
        $result=json_decode($result);
        if (empty($result)){
            return 'Please try after sometime';
        }
        if($result->Yh3mmEsealGrnCancellationResponse->EStatus->Status == 0){
            $msg = explode('##', $result->Yh3mmEsealGrnCancellationResponse->EStatus->Message);
            return $msg;
            // return $result->Yh3mmEsealGrnCancellationResponse->EStatus->Message;

        }

        if($result->Yh3mmEsealGrnCancellationResponse->EStatus->Status == 1){
            $cancelledDocNo = $result->Yh3mmEsealGrnCancellationResponse->EOutput->item[0]->CancelledDocNo;
            $grnDetail = DB::table('import_grn')->where('grn_number', $grnNumber)->update(['cancelled_doc_no'   => $cancelledDocNo, 'status'    => 3]);
            return 1;
            return $result;
        }
    }

    public function grnCanToConfirmation($grnCanDocNo){
        if (!$grnCanDocNo){
            return json_encode(array('Status' => 0, 'Message' => 'Server : Please Enter GRN Number', 'Data' => array()));
        }
        $grnToCreationData = DB::table('grn_to_creation_queue')
                            ->where('gr_document_no', $grnCanDocNo)
                            ->get()->toArray();
        if(empty($grnToCreationData)){
            return 'Server : Confirmation is not possible as TO for GRN  is not yet Created';
            return json_encode(array('Status' => 0, 'Message' => 'Server : Confirmation is not possible as TO for GRN  is not yet Created', 'Data' => array()));
        }

        $requestData = array();
        $itemData = array();
        $itemsData = [];
        foreach ($grnToCreationData as $key => $value) {
            $itemData = array(
                        'Tapos'     =>  $value->to_line_item,
                        'Nista'     =>  '',
                        'Matnr'     =>  $value->material_code,
                        'Charg'     =>  $value->batch,
                        'Bestq'     =>  '',
                        'Vltyp'     =>  $value->source_storage_type,
                        'Vlber'     =>  $value->source_storage_section,
                        'Vlpla'     =>  $value->source_bin,
                        'Nltyp'     =>  $value->destination_storage_type,
                        'Nlber'     =>  $value->destination_storage_section,
                        'Nlpla'     =>  $value->destination_bin);
            $itemsData[] = $itemData;
        }
        $headerData = array(
                'Mblnr'     =>      $grnCanDocNo,
                'Tanum'     =>      $grnToCreationData[0]->to_number,
                'Lgnum'     =>      $grnToCreationData[0]->warehouse_no);
        $body = array(
            'Yh3mmEsealCangrnConfrmTo'      =>      array(
                'IHeader'       =>      $headerData,
                'TTodata'       =>      array('item'        => $itemsData)));
        $method = 'toConfirmationForGRNCancellation';
        $methodType = 'POST';
        $this->erp=new ConnectErp($this->_manufacturerId);
        $result=$this->erp->request($method,'',$body,$methodType);
        // return $result;
        $result=json_decode($result);

        if(empty($result)){
            return 'Server : Unable to connect with SAP';
            return json_encode(array('Status' => 0, 'Message' => 'Server : Please try after sometime', 'Data' => array()));
        }

        if($result->Yh3mmEsealCangrnConfrmToResponse->EStatus == 0){
            $message = $result->Yh3mmEsealCangrnConfrmToResponse->EMessage;
            $data = $result->Yh3mmEsealCangrnConfrmToResponse->TTodata;
            return $message;
            return json_encode(array('Status' => 0, 'Message' => 'Server : '.$message, 'Data' => $data));
        }

        if($result->Yh3mmEsealCangrnConfrmToResponse->EStatus == 1){
            $message = $result->Yh3mmEsealCangrnConfrmToResponse->EMessage;
            $data = $result->Yh3mmEsealCangrnConfrmToResponse->TTodata;
            
            DB::table('grn_to_creation_queue')->where('gr_document_no', $grnCanDocNo)->update(['status'   => 1]);
            return $message;
            return '1';
            return json_encode(array('Status' => 1, 'Message' => 'Server : '.$message, 'Data' => $data));
        }
    }
}

?>