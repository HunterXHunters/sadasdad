<?php 

use Central\Repositories\RoleRepo;


class NewController extends BaseController{
    
    public $roleAccess;
    
    public function __construct(RoleRepo $roleAccess) {

        $this->roleAccess = $roleAccess;
    }

    public function supplierindex()
    {
        parent::Breadcrumbs(array('Home'=>'/','Index'=>'#')); 

       

    	return View::make('updatereports.supplierindex');  //->with(array('results' => $results));
    }
 public function houseindex()
    {
        parent::Breadcrumbs(array('Home'=>'/','Index'=>'#')); 

       

    	return View::make('updatereports.houseindex');  //->with(array('results' => $results));
    }
     public function vendorindex()
    {
        parent::Breadcrumbs(array('Home'=>'/','Index'=>'#')); 

       

    	return View::make('updatereports.vendorindex');  //->with(array('results' => $results));
    }
    public function plantindex()
    {
        parent::Breadcrumbs(array('Home'=>'/','Index'=>'#')); 

       

    	return View::make('updatereports.plantindex');  //->with(array('results' => $results));
    }
   public function channelindex()
    {
        parent::Breadcrumbs(array('Home'=>'/','Index'=>'#')); 

       

    	return View::make('updatereports.channelindex');  //->with(array('results' => $results));
    }
     public function recharge()
    {
        parent::Breadcrumbs(array('Home'=>'/','Index'=>'#')); 

       

        return View::make('updatereports.recharge');  //->with(array('results' => $results));
    }
    public function batteryexpired()
    {
        parent::Breadcrumbs(array('Home'=>'/','Index'=>'#')); 

       

        return View::make('updatereports.expiredbattery');  //->with(array('results' => $results));
    }
     public function stockaging()
    {
        parent::Breadcrumbs(array('Home'=>'/','Index'=>'#')); 

       

        return View::make('updatereports.stockaging');  //->with(array('results' => $results));
    }
     public function intransit()
    {
        parent::Breadcrumbs(array('Home'=>'/','Index'=>'#')); 

       

        return View::make('updatereports.intransit');  //->with(array('results' => $results));
    }
 public function batteryaging()
    {
        parent::Breadcrumbs(array('Home'=>'/','Index'=>'#')); 

       

        return View::make('updatereports.batteryaging');  //->with(array('results' => $results));
    }

  public function inventory()
    {
        parent::Breadcrumbs(array('Home'=>'/','Index'=>'#')); 

       

        return View::make('updatereports.inventory');  //->with(array('results' => $results));
    }
     public function intransittime()
    {
        parent::Breadcrumbs(array('Home'=>'/','Index'=>'#')); 

       

        return View::make('updatereports.intransittime');  //->with(array('results' => $results));
    }  


}