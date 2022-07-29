<?php

//namespace Products;
namespace App\Models\Products;

class ProductMedia extends \Eloquent {

    protected $table = 'product_media'; // table name
    protected $primaryKey = 'product_media_id';
    public $timestamps = false;

    // model function to store product data to database
    
    public function saveProductMedia($data) {
        try {
            if(isset($data['media']))
            {
                $productData = isset($data['media']['image']) ? $data['media']['image'] : array();                
                $folder_name = isset($data['product']['manufacturer_id']) ? $data['product']['manufacturer_id'] : 'eSeal';
                $video = isset($data['media']['video']) ? $data['media']['video'] : '';
                $imageDefault = isset($data['media']['is_default']) ? $data['media']['is_default'] : '';
                unset($productData['video']);
                $product_id = isset($data['product_id']) ? $data['product_id'] : '';
                if(!empty($product_id) && !empty($productData)){
                    $defaultImageSet = 0;
                    foreach($productData as $key => $value){
                        $fileName = urldecode($value);
                        $image_link = $this->upload($folder_name, $fileName);
                        if($image_link != '')
                        {
                            if($imageDefault == '' && !$defaultImageSet)
                            {
                                $updateArray = array();
                                $updateArray['image'] = $image_link;
                                \DB::table('products')->where('product_id', $product_id)->update($updateArray);
                                $defaultImageSet = 1;
                            }
                            if (urldecode($imageDefault) == $fileName)
                            {
                                \DB::table('product_media')->where('product_id', $product_id)->update(array('sort_order' => 0));
                                \DB::table('product_media')->insert(['product_id' => $product_id, 'media_type' => 'Image', 'url' => $image_link, 'sort_order' => 1]);
                                $updateArray = array();
                                $updateArray['image'] = $image_link;
                                \DB::table('products')->where('product_id', $product_id)->update($updateArray);
                            }else{
                                \DB::table('product_media')->insert(['product_id' => $product_id, 'media_type' => 'Image', 'url' => $image_link, 'sort_order' => 0]);                                
                            }
                        }
                    }
                    if(!empty($video))
                    {
                        $videoLink = $this->uploadVideo($folder_name, $video);
                        if($videoLink != '')
                        {
                            \DB::table('product_media')->insert(['product_id' => $product_id, 'media_type' => 'Video', 'url' => $videoLink, 'sort_order' => 1]);
                        }
                    }
                    $product_media_id = $this->product_media_id;
                    if($product_media_id)
                        return true;
                    else
                        return false;
                }else{
                    if(isset($imageDefault) && $imageDefault != '')
                    {
                        $result = \DB::table('product_media')->where('product_id', $product_id)->where('sort_order', 1)->first(array('product_media_id', 'url'));                        
                        \DB::table('product_media')->where('product_id', $product_id)->update(array('sort_order' => 0));
                        if(!empty($result))
                        {                            
                            if($result->product_media_id != $imageDefault)
                            {
                                $updateMediaArray['sort_order'] = 1;
                                $updateProductArray['image'] = $result->url;
                                \DB::table('product_media')->where('product_id', $product_id)->where('product_media_id', $imageDefault)->update($updateMediaArray);
                                \DB::table('products')->where('product_id', $product_id)->update($updateProductArray);
                            }else{
                                $updateMediaArray['sort_order'] = 1;
                                $updateMediaArray2['sort_order'] = 0;
                                $updateProductArray['image'] = $result->url;
                                \DB::table('product_media')->where('product_id', $product_id)->where('product_media_id', $imageDefault)->update($updateMediaArray);
                                //\DB::table('product_media')->where('product_id', $product_id)->where('product_media_id', $result->product_media_id)->update($updateMediaArray2);
                                \DB::table('products')->where('product_id', $product_id)->update($updateProductArray);
                            }
                        }else{
                            $result1 = \DB::table('product_media')->where('product_id', $product_id)->where('product_media_id', $imageDefault)->first(array('url'));
                            if(!empty($result1))
                            {                                
                                $updateProductArray['image'] = $result1->url;                                
                                \DB::table('products')->where('product_id', $product_id)->update($updateProductArray);
                            }   
                            $updateMediaArray['sort_order'] = 1;
                            \DB::table('product_media')->where('product_id', $product_id)->where('product_media_id', $imageDefault)->update($updateMediaArray);
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
    
    public function upload($folder_name, $file) {
        // getting all of the post data
        // setting up rules
        $rules = array('image' => 'required',); //mimes:jpeg,bmp,png and for max size max:10000
        // doing the validation, passing post data, rules and the messages
        if (!empty($file)) {
            $destinationPath = public_path().'/uploads/products/'; // upload path
            $product_folder = $folder_name.'/product';
            $policy_folder = $folder_name.'/policy';
            $instruction_folder = $folder_name.'/instructions';
            if(!file_exists($destinationPath.$folder_name)){
                $result = \File::makeDirectory($destinationPath.$folder_name, 0775);
                if($result){
                    $result = \File::makeDirectory($destinationPath.$product_folder, 0775);
                    $result = \File::makeDirectory($destinationPath.$policy_folder, 0775);
                    $result = \File::makeDirectory($destinationPath.$instruction_folder, 0775);
                }
            }elseif(!file_exists($destinationPath.$product_folder))
            {
                \File::makeDirectory($destinationPath.$product_folder, 0775);
            }            
            //$extension = $file->getClientOriginalExtension(); // getting image extension            
            $fileDetails = pathinfo($file);
            $extension = isset($fileDetails['extension']) ? $fileDetails['extension'] : 'JPG';            
            $fileName = rand(11111, 99999) . '.' . $extension; // renameing image
            //$file->move($destinationPath.$product_folder, $fileName); // uploading file to given path
            if(file_exists($file))
            {
                copy($file, $destinationPath.$product_folder.'/'.$fileName);
                @unlink($file);
                @unlink(str_replace('/thumbnail', '', $file));
            }
            // sending back with message
            return $product_folder.'/'.$fileName;
        } else {
            // sending back with error message.
            return false;
        }
    }
    
    public function uploadVideo($folderName, $file) {
        // getting all of the post data
        // setting up rules
        $rules = array('video' => 'required',); //mimes:jpeg,bmp,png and for max size max:10000
        // doing the validation, passing post data, rules and the messages
        if (!empty($file)) {
            $destinationPath = public_path().'/uploads/products/'; // upload path
            $productVideoFolder = $folderName.'/product/video';
            if(!file_exists($destinationPath.$productVideoFolder)){
                $result = \File::makeDirectory($destinationPath.$productVideoFolder, 0775);
            }
            //$extension = $file->getClientOriginalExtension(); // getting image extension
            $fileDetails = pathinfo($file);
            $extension = isset($fileDetails['extension']) ? $fileDetails['extension'] : 'wmv';
            $fileName = rand(11111, 99999) . '.' . $extension; // renameing image
            $file = \File::get($file);
            //$file->move($destinationPath.$productVideoFolder, $fileName); // uploading file to given path
            if(file_exists($file))
            {
                copy($file, $destinationPath.$productVideoFolder.'/'.$fileName);
                unlink($file);
            }
            // sending back with message
            return $productVideoFolder.'/'.$fileName;
        } else {
            // sending back with error message.
            return false;
        }
    }
}
?>