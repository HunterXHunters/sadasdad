<?php  namespace App\Repositories; 

use DB;

class CommonRepo
{
    public function getLookupData($categoryName)            
    {
        $result = DB::table('lookup_categories')->where('name', $categoryName)->first(array('id'));
        $returnData = array();
        if(!empty($result))
        {
            $categoryId = $result->id;
            $result = DB::table('master_lookup')->where('category_id', $categoryId)->get(array('value', 'name'));
            if(!empty($result))
            {
                $returnData[0] = 'Please select..';
                foreach ($result as $data) {            
                    $returnData[$data->value] = $data->name;
                }
            }            
        }
        return $returnData;
    }
    
    public function getLookupName($lookupId)            
    {
        $result = DB::table('master_lookup')->where('value', $lookupId)->get(array('value', 'name'));
        $returnData = array();
        if(!empty($result))
        {
            $returnData[0] = 'Please select..';
            foreach ($result as $data) {            
                $returnData[$data->value] = $data->name;
            }
        }            
        return $returnData;
    }

    public function getLookupValue($categoryName, $id)            
    {
        $result = DB::table('lookup_categories')->where('name', $categoryName)->first(array('id'));
        $returnData = array();
        if(!empty($result))
        {
            $categoryId = $result->id;
            $result = DB::table('master_lookup')->where('value', $id)->first(array('name'));
            if(!empty($result))
            {
                $returnData = $result->name;
            }            
        }
        return $returnData;
    }
    
}

?>