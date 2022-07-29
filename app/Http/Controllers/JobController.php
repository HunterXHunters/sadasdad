<?php
use Central\Repositories\RoleRepo;


class JobController extends BaseController {
	protected $roleAccess;


	public function __construct(RoleRepo $roleAccess) {
		$this->roleAccess = $roleAccess;
	}
 
	public function checkUserPermission($api_name){
		try{			
			$status = 0;
			$data = Input::get();
			if($api_name == 'login' || $api_name == 'forgotPassword' || $api_name == 'resetPassword'){
				$result = $this->$api_name($data);
				return $result;
			} 	
			
			$module_id = $data['module_id'];
			$access_token = $data['access_token'];
			if(empty($module_id) || empty($access_token)){
				throw new Exception('Parameters Missing.');	
			}else{
				$result = $this->roleAccess->checkPermission($module_id,$access_token);
				
				if($result == 1){
					$result = $this->$api_name($data);
					return $result;
				}else{
					throw new Exception('User dont have permission.');	
				}
			}
		}
		catch(Exception $e){
			$message = $e->getMessage();
		}
		return Response::json(['Status'=>$status,'Message'=>$message]);
	}

	public function updateInventory(){
		try{
			$status = 0;
			$message = '';
			$moduleId = trim(Input::get('module_id'));
			$userToken = trim(Input::get('access_token'));

			$userId = DB::table('users_token')
				->where('module_id', $moduleId)
				->where('access_token', $userToken)
				->pluck('user_id');
				
			Log::info('user id :'. $userId);	
			if($userId){
				$customerId = DB::table('users')->where('user_id', $userId)->pluck('customer_id');
				if($customerId){
					Log::info('Executing job for manufacturer_id : '. $customerId);
					Artisan::call('update:inventory', array('--manufacturer_id'=> $customerId));
				}else{
					Log::info('Executing job for all : ');
					Artisan::call('update:inventory');
				}
				$status = 1;
				$message = 'Inventory updated succesfully';
			}
		}catch(Exception $e){
		  $message = $e->getMessage();
		}
		//$endTime = $this->getTime();

		return Response::json(Array('Status'=>$status, 'Message'=>$message));
	}

}