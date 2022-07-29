<?php
set_time_limit(0);
ini_set('memory_limit', '-1');
ini_set('soap.wsdl_cache_enabled', '0'); 
ini_set('soap.wsdl_cache_ttl', '0'); 

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylorotwell@gmail.com>
 */

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/

require __DIR__.'/../bootstrap/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let's turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight these users.
|
*/

$app = require_once __DIR__.'/../bootstrap/start.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can simply call the run method,
| which will execute the request and send the response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have whipped up for them.
|
*/

//$app->run();

$mfgId = 58;


function getMaxDateForStoredData(){
	$maxDate = DB::table('escortData')->max('utilizedDate');

	if($maxDate)
		return $maxDate;
	else
		return false;
}

function getSoapObject($maxDate){
	$wsdlUrl = 'http://tracker.escorts.co.in/Services/ESEALService.svc?wsdl';
	$soapParams = array ('soap_version' => SOAP_1_1,	'trace' => 1, 'exceptions' => 1, "connection_timeout" => 180);

	try{
		$soapObj = new SoapClient($wsdlUrl, $soapParams);
		$response = $soapObj->GetData(array('date'=>$maxDate));	
		//$response = $soapObj->GetData(array('date'=>'24 Jun 2015'));	
	}catch(SoapFault $fault){
		echo 'Exception occured '. $fault->getMessage()."\n";
		return false;
	}
	return $response;
}

function checkNCreateProduct($product, $productName){

	if(!empty($product) && !empty($productName)){
		$cnt = DB::table('products')->where('name', $product)->count();
		if($cnt){
			return true;
		}else{
			DB::table('products')->insert(Array(
				'name'=>$product, 'title'=>$product, 'description'=>$productName, 'product_type_id'=>8003, 'category_id'=>119,
				'business_unit_id'=>23, 'attribute_set_id'=>1, 'is_gds_enabled'=>0, 'manufacturer_id'=> $GLOBALS['mfgId'], 'model_name'=>$product,
				'is_active'=>1
				));
			return true;
		}
	}
	return false;
}


	/*
	*	Function checks if passed packType is retail, wholesale or unitised.
	*   And updates/insert packaging level quantity based on packType for passed product
	*/
function checkForProductPackagingLevel($product, $packcode, $packType){
	$pid = DB::table('products')->where('name', $product)->pluck('product_id');
	echo $pid."\n";
	if($pid){
		$packType = strtolower($packType);
		echo 'RETAIL======>'.strtolower($packType)."\n";
		
		if(strtolower($packType) == 'retail'){
			$cnt = checkProductLevelPackagingQty($pid, 16001);
			if(!$cnt){
				insertProductLevelPackagingQty($pid, 16001, $packcode);
			}else{
				updateProductLevelPackagingQty($pid, 16001, $packcode);
			}
		}
		
		echo 'W/S======>'.strtolower($packType)."\n";
		
		if(strtolower($packType) == 'w/s'){
			$cnt = checkProductLevelPackagingQty($pid, 16002);
			if(!$cnt){
				insertProductLevelPackagingQty($pid, 16002, $packcode);
			}else{
				updateProductLevelPackagingQty($pid, 16002, $packcode);
			}
		}
		
		echo 'UNIT======>'.strtolower($packType)."\n";
		
		if(strtolower($packType) == 'unitized'){
			$cnt = checkProductLevelPackagingQty($pid, 16003);
			if(!$cnt){
				insertProductLevelPackagingQty($pid, 16003, $packcode);
			}else{
				updateProductLevelPackagingQty($pid, 16003, $packcode);
			}
		}
		return true;
	}else{
		return false;
	}
}


////UPDATE Product packaging quantity for passed level
function updateProductLevelPackagingQty($pid, $level, $packcode){
	DB::table('product_packages')
		->where('product_id', $pid)
		->where('level', $level)
		->update(Array('quantity'=>$packcode));
}

////INSERT Product packaging quantity for passed level
function insertProductLevelPackagingQty($pid, $level, $packcode){
	DB::table('product_packages')->insert(Array(
			'product_id'=> $pid, 'level'=>$level, 'quantity'=>$packcode
		));
}

////CHECK if packaging quantiry for given product and level already exists or not
function checkProductLevelPackagingQty($pid, $level){
	return DB::table('product_packages')
			->where('product_id', $pid)
			->where('level', $level)
			->count();		
}



	/**
	*
	*	Bind the data 
	*/
function bindData($code, $pid){
	try{
		$status = 0;
		$esealTable = 'eseal_'.$GLOBALS['mfgId'];
		$esealBankTable = 'eseal_bank_'.$GLOBALS['mfgId'];

		$cnt = DB::table($esealBankTable)
			->where('issue_status', 1)
			->where('id', $code)
			->count();

		if(!$cnt){
			throw new Exception('Code '.$code.' not matching with code bank');
		}
		try{
			$cCnt = DB::table($esealTable)
				->where('primary_id', $code)
				->count();
			if($cCnt){
				DB::table($esealTable)
					->where('primary_id', $code)
					->update(Array('pid' => $pid));
			}else{
				DB::table($esealTable)
					->insert(
						Array(
							'primary_id' => $code, 
							'pid' => $pid
							)
						);
			}	
		}catch(PDOException $e){
			throw $e;
		}

		try{
			DB::table($esealBankTable)->where('id', $code)->update(Array(
					'used_status' => 1
				));
		}catch(PDOException $e){
			throw $e;	
		}
		$status = 1;
	}catch(Exception $e){
		echo $e->getMessage()."\n";
	}
	return $status;
}


try{
	$maxDate = getMaxDateForStoredData();
	if(!$maxDate){
		$maxDate = '2015-06-25 00:00:00';
	}
	echo 'Max Date '.$maxDate."\n";
	$response = getSoapObject($maxDate);

	if(!$response){
		echo 'Error occured'."\n";
	}else{
		$response = $response->GetDataResult;
		$response = json_decode($response);
		if(!empty($response)){
			$fname = date('YmdHis').'.txt';
			$fh = fopen($fname,'w');
			echo 'Writind data into file'."\n";
			foreach($response as $val){
				$str = $val->esealCodes."#".$val->itemCode."#".$val->itemDescription."#".$val->packCode."#".$val->packingType."#".$val->utilizedDate."\n";
				fwrite($fh, $str);
			}
			fclose($fh);
			echo 'data writing to file '.$fname.' is done'."\n";

			$response = file($fname);
			$modifiedDate = date('Y-m-d H:i:s');
			$productArray = Array();
			echo 'count of file rows '.count($response)."\n";		

			$insertCount = 0;
			$retailInsertCount = 0;
			foreach($response as $value){
				$lineItems = explode('#', $value);
				$codes = $lineItems[0];
				$product = $lineItems[1];
				$productName = $lineItems[2];
				$packcode = $lineItems[3];
				$packType = $lineItems[4];
				$utilizedDate = $lineItems[5];

				if(!checkNCreateProduct($product, $productName)){
					throw new Exception('Exception occured during product creation');
				}

				if(!checkForProductPackagingLevel($product, $packcode, $packType)){
					throw new Exception('Exception occured during product packaging quantity creation');	
				}

				$existCodes = DB::table('escortData')
									->where('code', '=', $codes)
									->count();

				echo 'Codes Exists'. $existCodes."\n";

				if(in_array($product, $productArray)){
					$pid = array_keys($productArray, $product);
					$pid = $pid[0];
				}else{
					$pid = DB::table('products')
						->where('name', $product)
						->pluck('product_id');
					$productArray[$pid] = $product;
				}

				try{
					if(!$existCodes){

						DB::table('escortData')->insert(Array(
							'code' => $codes, 'pid' => $pid, 'packCode' => $packcode, 
							'packingType' => $packType, 'utilizedDate'=>$utilizedDate, 'modifiedDate'=>$modifiedDate
							));
						$insertCount++;
						if( strtolower($packType) == 'retail' ){
							$retailInsertCount++;
							if(!bindData($codes, $pid)){
								throw new Exception('Failed during binding of '.$codes);
							}
						}					
					}

				}catch(PDOException $e){
					echo $e->getMessage();				
					throw $e;
				}
			}
			echo 'Total inserted record are : '.$insertCount."\n";
			echo 'Total inserted retial record are : '.$retailInsertCount."\n";

		}



	}


	//var_dump($response);
}catch(Exception $e){
	print_r($e,true);	
}

