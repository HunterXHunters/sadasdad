<?php

namespace Products;

class ProductServiceCenter extends \Eloquent {

    protected $table = 'product_service_center'; // table name
    protected $primaryKey = 'service_center_id';
    public $timestamps = false;

    // model function to store product data to database
    
    public function saveProductServiceCenter($data) {
        try {
            if(isset($data['service_center']))
            {
                $productData = $data['service_center'];
                foreach($productData as $key => $value){
                    $this->$key = $value;
                }
                $this->save();
                $service_center_id = $this->service_center_id;
                if($service_center_id)
                    return true;
                else
                    return false;
            }
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
}
?>