<?php

//namespace Products;
//use App\Models\Products;
namespace App\Models\Products;


class ProductPallet extends \Eloquent {

    protected $table = 'product_pallet'; // table name
    protected $primaryKey = 'pallet_id';
    public $timestamps = false;

    // model function to store product data to database
    
    public function saveProductPallet($data) {
        try {
            if(isset($data['pallet']))
            {
                $productData = $data['pallet'];
                if(!empty($productData))
                {
                    foreach($productData as $key => $value){
                        $this->$key = $value;
                    }
                    $this->product_id = isset($data['product_id']) ? $data['product_id'] : 0;
                    $this->save();
                    $pallet_id = $this->pallet_id;
                    if($pallet_id)
                        return true;
                    else
                        return false;                    
                }
            }
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
}
?>