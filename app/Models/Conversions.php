<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use DB;
class Conversions extends Model {
	protected $table = 'conversions';
	protected $primaryKey = 'id';
	/*public function __construct($product_id,$base_quatity,$base_uom='EA',$alt_uom='PAL') 
	{
		$uom=$this->getUom($product_id,$base_quatity,$base_uom,$alt_uom);
	}*/
	public function getUom($product_id,$base_quatity,$base_uom='EA',$alt_uom='PAL',$ceil=1){
	$bea = DB::table('conversions')->where('product_id',$product_id)->where('alt_uom',$base_uom)->value('base_quantity');
	$aea = DB::table('conversions')->where('product_id',$product_id)->where('alt_uom',$alt_uom)->value('base_quantity');
	//echo $base_quatity;echo"-------------->><<<-------";
	try {
		$total_ea=($base_quatity*$bea)/$aea;
	} catch(Exception $e){
		$total_ea=($base_quatity*$bea);
	}
	//echo $total_ea;exit;
	if($ceil)
	return ceil($total_ea);
	else 
	return $total_ea;


	/*
	echo "total_ea".$total_ea.'<br>'; 
	echo "base_quatity".$base_quatity.'<br>'; 
	echo "bea".$bea.'<br>'; 
	exit;
	*/
		//echo $total_ea/$aea;
	}

	public function getUom_new($product_id,$base_quatity,$base_uom='EA',$alt_uom='PAL'){
	$bea = DB::table('conversions')->where('product_id',$product_id)->where('alt_uom',$base_uom)->value('base_quantity');
	$aea = DB::table('conversions')->where('product_id',$product_id)->where('alt_uom',$alt_uom)->value('base_quantity');
	//echo $base_quatity;echo"-------------->><<<-------";
	try {
		$total_ea=($base_quatity*$bea)/$aea;
	} catch(Exception $e){
		$total_ea=($base_quatity*$bea);
	}
	return round($total_ea,2);
	}


	public function getUomAll($qty,$UOM,$p_id,$alt_uom='PAL'){

		$baseUOMqty= DB::table('conversions as  c ')->where ('alt_uom',$UOM)->orWhere('id',$UOM)->where('product_id',$p_id)->value('base_quantity');
		/*$bea = DB::table('conversions')->where('product_id',$product_id)->where('alt_uom',$baseUOM)->value('base_quantity');*/
		
	$aea = DB::table('conversions')->where('product_id',$p_id)->where('alt_uom',$alt_uom)->value('base_quantity');
	try {
		$total_ea=($qty*$baseUOMqty)/$aea;
		// echo "p_id".$total_ea;exit;
	} catch(Exception $e){
		$total_ea=$qty*$baseUOMqty;
	}
	// echo $total_ea;exit;
	return round($total_ea,2);

	}
	public function getbaseUomName($UOM){
		$baseUOM= DB::table('conversions as  c ')->where ('id',$UOM)->pluck('alt_uom');
		return $baseUOM;
	}
	public function getPALtoZ01($pallets_packed,$UOM,$p_id,$alt_uom='PAL'){
			$Z01_eaches= DB::table('conversions as  c ')->where ('alt_uom',$UOM)->where('product_id',$p_id)->value('base_quantity');
			$PAL_eaches=DB::table('conversions as  c ')->where ('alt_uom','=','PAL')->where('product_id',$p_id)->value('base_quantity');
			$PAL_to_Z01 = ($pallets_packed*$PAL_eaches)/$Z01_eaches;
			
			return round($PAL_to_Z01,2);
	}
	public function getZ01toEA($p_id,$packed_qty_EA,$alt_uom){
					$total_EA_for_uom=DB::table('conversions as  c ')->where ('alt_uom',$alt_uom)->where('product_id',$p_id)->value('base_quantity');
		//echo $packed_qty_EA;exit;
					 $Z01_to_eaches=$packed_qty_EA*$total_EA_for_uom;
		
					return round($Z01_to_eaches,2);

	}
	public function getZ01($qty,$alt_uom,$p_id){
			
					$total_EA_for_uom=DB::table('conversions as  c ')->where ('alt_uom',$alt_uom)->where('product_id',$p_id)->value('base_quantity');
					 $total_z01=$qty/$total_EA_for_uom;
			
					return $total_z01;

	}

}

/*
			$conversions=new Conversions();
			print_r($conversions->getUom(1,200,'CS','PAL'));
			exit;

							$inputData=array('module_id' => $this->_request->input('module_id'),'access_token' => $this->_request->input('access_token'), 'plant_id' => $plantId,'type' => $objectType,'object_id' => $objectId,'action' => $action);
				$req = Request::create('scoapi/notifyEseal', 'POST',$inputData);
				$originalInput=$this->_request->all();
				$this->_request->replace($req->all());
				$res = app()->handle($req);
				$res2 = $res->getContent();
				return 

			*/