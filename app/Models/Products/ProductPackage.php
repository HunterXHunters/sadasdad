<?php
//namespace Products;
namespace App\Models\Products;
use DB;
use Illuminate\Database\Eloquent\Model;
class ProductPackage extends Model {
    protected $table = 'product_packages'; // table name
    protected $primaryKey = 'id';
    public $timestamps = false;

    // model function to store product data to database

    public function saveProductPackage($data)
    {
        try
        {
            if (isset($data['package']))
            {
                $productData = $data['package'];
                $packageArray = array();
                $packageArray['product_id'] = isset($data['product_id']) ? $data['product_id'] : 0;
                $productId = isset($data['product_id']) ? $data['product_id'] : 0;
                $packageArray['weight'] = isset($productData['weight']) ? $productData['weight'] : '';
                $packageArray['weight_class_id'] = isset($productData['weight_class_id']) ? $productData['weight_class_id'] : '';
                $packageIds = DB::table('product_packages')->where('product_id', $productId)->value(DB::raw('group_concat(id)'));
                $packageIdsArray = array();                    
                if(!empty($packageIds))
                {
                    $packageIdsArray = explode(',' ,$packageIds);
                }                    
                $tempIds = array();
                if (isset($productData['package_details']))
                {
                    $tempArray = array();
                    foreach ($productData['package_details'] as $packages)
                    {
                        $tempArray = (array) json_decode($packages);
                        if(!isset($tempArray['id']))
                        {
                            \DB::table('product_packages')->insert(array_merge($packageArray, $tempArray));
                        }else if(isset($tempArray['id']))
                        {
                            $tempIds[] = $tempArray['id'];
                        }
                    }
                    $deleteIds = array();
                    $tempDiffArray = array_diff($packageIdsArray, $tempIds);
                    if(!empty($tempDiffArray))
                    {
                        $deleteIds = $tempDiffArray;
                    }else{
                        $tempDiffArray = array_diff($tempIds, $packageIdsArray);
                        if(!empty($tempDiffArray))
                        {
                            $deleteIds = $tempDiffArray;
                        }
                    }
                    // foreach($deleteIds as $id)
                    // {
                    //     DB::table('product_packages')->where('id', $id)->delete();
                    // }
                }
            }
            return true;
        } catch (Exception $ex)
        {
            return $ex->getMessage();
        }
    }
}
?>