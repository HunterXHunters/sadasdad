<?php
set_time_limit(0);
ini_set('memory_limit', '-1');

use Central\Repositories\RoleRepo;
use Central\Repositories\OrderRepo;
use Central\Repositories\CustomerRepo;
use Central\Repositories\SapApiRepo;
use Central\Repositories\ApiRepo;


class MulSoftController extends ScoapiController 
{
	public function __construct(RoleRepo $roleAccess,CustomerRepo $custRepo,SapApiRepo $sapRepo, ApiRepo $apiRepo) 
	{
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$_SERVER['REMOTE_ADDR']=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		$this->roleAccess = $roleAccess;
		$this->custRepo = $custRepo;
		$this->sapRepo = $sapRepo;
		$this->_apiRepo = $apiRepo;		
	}
	

}