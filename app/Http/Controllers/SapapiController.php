<?php

use Central\Repositories\SapApiRepo;

class SapapiController extends BaseController
{

    protected $_url;
    protected $_domain;
    protected $_method;
    protected $_table_name;
    protected $_sap_api_repo;
    protected $_return_type;
    protected $_token;

    public function __construct()
    {
        
        $this->_domain = '14.141.81.243:8000';
        $this->_url = 'http://' . $this->_domain . '/sap/opu/odata/sap/';
        $this->_sap_api_repo = new SapApiRepo();
        $this->_return_type = 'xml';
        $this->_token = '3h8M8A2q8iv7nMq4Rpft5G5TBE4O7PC8';
    }

    public function callSapApi(){
        try{
        $status =0;
        $response = array();
        $mfg_id = Input::get('mfg_id');
        $cred =DB::table('erp_integration')->where('manufacturer_id', $mfg_id)->first(['web_service_url','web_service_username','web_service_password','token']);
        if(empty($cred)){
            return 'There is no erp-configuration for this brand owner';
        }
        else{
           $this->_method = Input::get('method');
           $this->_method_name = Input::get('method_name');
            $url = $cred->web_service_url;
            $url = $url . $this->_method . '/' . $this->_method_name . '/';
            $method = 'GET';
            $data['TOKEN'] = $cred->token;
            $username = $cred->web_service_username;
            $password = $cred->web_service_password;
        }
        
        foreach(Input::get('data') as $key => $value){
            $data[$key] = $value;
        }
        $response = $this->_sap_api_repo->request($username,$password,$url, $method, $data, $this->_return_type);      
        
        $status =1;
        $message = 'Data successfully retrieved';
        }
        catch(Exception $e){
            $message = $e->getMessage();
        }
        return json_encode(['Status'=>$status,'Message'=>$message,'Response' =>$response]);
    }

    public function REVERSE($response){
        try{
            $status =0;
return $response;
            $parseData1 = xml_parser_create();
        xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
        xml_parser_free($parseData1);
        $documentData = array();
        foreach ($documentValues1 as $data) {
            if(isset($data['tag']) && $data['tag'] == 'D:MESSAGE')
            {
                $documentData = $data['value'];
            }
        }
    
        $deXml = simplexml_load_string($documentData);
        $deJson = json_encode($deXml);
        $xml_array = json_decode($deJson,TRUE); 

return $xml_array;
        }
        catch(Exception $e){
            $message = $e->getMessage();            
        }
         return Response::json(['Status'=>$status,'Message'=>$message]);

    }

    public function ET_MAT_DISPLAY($response){
        try{
                       $status =0;
//return $response;
            $parseData1 = xml_parser_create();
        xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
        xml_parser_free($parseData1);
        $documentData = array();
        foreach ($documentValues1 as $data) {
            if(isset($data['tag']) && $data['tag'] == 'D:PRODUCT_SKU')
            {
                $documentData = $data['value'];
            }
        }
    //return $documentData;
        $deXml = simplexml_load_string($documentData);
        $deJson = json_encode($deXml);
        $xml_array = json_decode($deJson,TRUE); 

return $xml_array;

        }
        catch(Exception $e){
            $message = $e->getMessage();            
        }
         return Response::json(['Status'=>$status,'Message'=>$message]);
    }

    public function DELIVER_DETAILS($response){
        try{
            $status =0;

            $parseData1 = xml_parser_create();
        xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
        xml_parser_free($parseData1);
        $documentData = array();
        foreach ($documentValues1 as $data) {
            if(isset($data['tag']) && $data['tag'] == 'D:GET_DELIVER')
            {
                $documentData = $data['value'];
            }
        }
    
        $deXml = simplexml_load_string($documentData);
        $deJson = json_encode($deXml);
        $xml_array = json_decode($deJson,TRUE); 

        if($xml_array['HEADER']['Status'] == 1){

  return $xml_array['DATA'];      
  }
      else{
        throw new Exception('Data not found');
      } 

        }
        catch(Exception $e){
            $message = $e->getMessage();
        }
        return Response::json(['Status'=>$status,'Message'=>$message]);
    }


    public function SALES_ORDER($response){
      try{
        $status =0;
            

        $parseData1 = xml_parser_create();
        xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
        xml_parser_free($parseData1);
        $documentData = array();
        foreach ($documentValues1 as $data) {
            if(isset($data['tag']) && $data['tag'] == 'D:GET_SO')
            {
                $documentData = $data['value'];
            }
        }
    
        $deXml = simplexml_load_string($documentData);
        $deJson = json_encode($deXml);
        $xml_array = json_decode($deJson,TRUE);  


      if($xml_array['HEADER']['Status'] == 1){

  return $xml_array['DATA'];      
  }
      else{
        throw new Exception('Data not found');
      }

      }
      catch(Exception $e){
        $message = $e->getMessage();
      }
return Resposne::json(['Status' =>$status,'Message'=>$message]);


    }

    public function PLANT_DATA($response)
{
       try{ 
        $status =0;
        $parseData1 = xml_parser_create();
        xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
        xml_parser_free($parseData1);
        $documentData = array();
        foreach ($documentValues1 as $data) {
            if(isset($data['tag']) && $data['tag'] == 'D:GET_PLANT_DATA')
            {
                $documentData = $data['value'];
            }
        }

       $deXml = simplexml_load_string($documentData);
        $deJson = json_encode($deXml);
        $xml_array = json_decode($deJson,TRUE);   
        




        $status = $xml_array['HEADER']['STATUS'];
        if($status == 1){

            foreach($xml_array['DATA'] as $data){

                foreach($data as $key => $value){
                    if(is_array($value) && empty($value)){
                        $data[$key] = '';
                    }
                }

           if (!array_key_exists('STORAGE_CODE', $data['ITEM'])) {
            return $data;
            //echo 'hii1';
           }
           
    
             // $erp_code = $data['PLANT'];
                // $plant_name = $data['NAME'];
                // $valuation_area = $data['VALUATION_AREA'];
                // $company_code = $data['COMPANY_CODE'];
                
                // $address_code = $data['ADDRESS'];
                // $city = $data['CITY'];
                // $postal_code = $data['POSTAL_CODE'];
                // $street = $data['STREET'];
                // $street2 = $data['STREET2'];
                // $street3= $data['STREET3'];
                // $country_key =$data['COUNTRY_KEY'];
                // $country_name = $data['COUNTRY_NAME'];
                // $region = $data['REGION'];
                // $state = $data['DESCRIPTION'];

                



            }
           die;

        }  
        else{

        }
    }
    catch(Exception $e){
        $message = $e->getMessage();
    }
       return Response::json(['Status' => $status,'Message' => $message]);  

    }



    public function GET_PORDER_DETAILS($response)
    {
        //return $response;
         $parseData1 = xml_parser_create();
        xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
        xml_parser_free($parseData1);
        $documentData = array();
        foreach ($documentValues1 as $data) {
            if(isset($data['tag']) && $data['tag'] == 'D:PORDER_DATA')
            {
                $documentData = $data['value'];
            }
        }

       $deXml = simplexml_load_string($documentData);
        $deJson = json_encode($deXml);
        $xml_array = json_decode($deJson,TRUE);  
        return $xml_array;    
        $status = $xml_array['HEADER']['STATUS'];

        if($status == 1){

            $order_no = $xml_array['DATA']['PRODUCTION_ORDER_NO'];
            $order_date = $xml_array['DATA']['PRODUCTION_ORDER_DATE'];
            
            $plant_code = $xml_array['DATA']['PLANT_CODE'];
            $plant_name = $xml_array['DATA']['PLANT_NAME'];


            
            $material_code = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['MATERIAL_CODE'];
            $material_description = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['MAT_DESC'];
            $batch_no = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['BATCH_NO'];
            $qty = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['QUANTITY'];
            $exp_date = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['EXP_DATE'];
            $mfg_date = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['MFG_DATE'];                                                  
            $storage_loc_code = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['STORAGE_LOC_CODE'];
            $storage_loc_name = $xml_array['DATA']['FINISHED_MATERIAL']['ITEM']['STORAGE_LOC_NAME'];


            foreach($xml_array['DATA']['BOM']['ITEM2'] as $item){
                return $item;
            }

            return Response::json(['Status' => 1,'Message' => 'Data successfully retrieved']); 

        }
        else{
            return Response::json(['Status'=>0,'Message'=>'Data not retrieved']);
        }

    }
    
    
    public function GRN_OUTPUT($response)
    {
        
        $parseData1 = xml_parser_create();
        xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
        xml_parser_free($parseData1);
        $documentData = array();
        foreach ($documentValues1 as $data) {
            if(isset($data['tag']) && $data['tag'] == 'D:GET_GRN')
            {
                $documentData = $data['value'];
            }
        }
        
        $deXml = simplexml_load_string($documentData);
        $deJson = json_encode($deXml);
        $xml_array = json_decode($deJson,TRUE);      

       $status = $xml_array['HEADER']['Status'];
        if($status == 1){

            $doc_no = $xml_array['DATA']['DOC_NO'];
            $doc_year = $xml_array['DATA']['DOC_YEAR'];
            $vendor_no = $xml_array['DATA']['VENDOR_NO'];
            $plant_code = $xml_array['DATA']['PLANT_CODE'];
            $plant_name = $xml_array['DATA']['PLANT_NAME'];

            foreach($xml_array['DATA']['ITEMS']['ITEM'] as $item){
                return $item;
            }

            return Response::json(['Status' => 1,'Message' => 'Data successfully retrieved']); 

        }
        else{
            return Response::json(['Status'=>0,'Message'=>'Data not retrieved']);
        }

    }


    public function GET_VENDOR_DETAILS($response)
    {
         try{
        $status =0;
        $parseData1 = xml_parser_create();
        xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
        xml_parser_free($parseData1);
        $documentData = array();
        foreach ($documentValues1 as $data) {
            if(isset($data['tag']) && $data['tag'] == 'D:VENDOR_DATA')
            {
                $documentData = $data['value'];
            }
        }

        
        $deXml = simplexml_load_string($documentData);
        $deJson = json_encode($deXml);
        $xml_array = json_decode($deJson,TRUE);  
        //return $xml_array;    
       $status = $xml_array['HEADER']['STATUS'];
        if($status == 1){

           foreach($xml_array['DATA'] as $data){

             foreach($data as $key => $value){
                    if(is_array($value) && empty($value)){
                        $data[$key] = '';
                    }
                }

            $vendor_code = $data['VENDOR_CODE'];
            $purchase_org = $data['PURCHASE_ORG'];
            $title = $data['TITLE'];
            $name1 = $data['NAME1'];
            $name2 = $data['NAME2'];
            $email = $data['EMAIL'];
            $address1 = $data['ADDRESS1'];
            $address2 = $data['ADDRESS2'];
            $city = $data['CITY'];
            $region_key = $data['REGION_KEY'];
            $region_text = $data['REGION_TEXT'];
            $country_key = $data['COUNTRY_KEY'];
            $country = $data['COUNTRY'];
            $post_code = $data['POST_CODE'];
            $phone = $data['PHONE'];

            $isExists = Location::where(['location_name'=>$name1,'manufacturer_id'=>1])->pluck('location_id');
            if(!empty($isExists)){
                $message ='Some vendors already exists';
                goto xyz;
            }
            $location = new Location;
            $location->location_name = $name1;
            $location->location_type_id = 1;
            $location->manufacturer_id = 1;
            $location->firstname = $name2;
            $location->lastname = $name2;
            $location->location_email = $email;
            $location->location_address = $address1;
            $location->location_details = $address2;
            $location->city = $city;
            $location->region = $region_text;
            $location->country = $country;
            $location->pincode = $post_code;
            $location->phone_no = $phone;
            $location->erp_code = $vendor_code;
            $location->save();  
        xyz:
        }
            $status =1;
            if(!isset($message) && empty($message)){
            $message= 'Data successfully retrieved and inserted'; 
        }

        }
        else{
            throw new Exception('Data not retrieved');
        }
        }
        catch(Exception $e){
            $message = $e->getMessage();
        }
        return Response::json(['Status' => $status,'Message' => $message]); 

            }

    public function CUSTOMER($response)
    {
      try{
        $status =0;
        $parseData1 = xml_parser_create();
        xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
        xml_parser_free($parseData1);
        $documentData = array();
        foreach ($documentValues1 as $data) {
            if(isset($data['tag']) && $data['tag'] == 'D:GET_CUSTOMER')
            {
                $documentData = $data['value'];
            }
        }
      
        $deXml = simplexml_load_string($documentData);
        $deJson = json_encode($deXml);
        $xml_array = json_decode($deJson,TRUE);  
       
        $status = $xml_array['HEADER']['Status'];
        if($status == 1){
             foreach($xml_array['DATA'] as $data){

                foreach($data as $key => $value){
                    if(is_array($value) && empty($value)){
                        $data[$key] = '';
                    }
                }


            $customer_id = $data['CUSTOMER_ID'];
            $sales_org = $data['SALES_ORG'];
            $dist_ch = $data['DIST_CH'];
            $division = $data['DIVISION'];
            $title = $data['TITLE'];
            $name1 = $data['NAME1'];
            $name2 = $data['NAME2'];
            $name3 = $data['NAME3'];
            $email = $data['EMAIL'];
            $address1 = $data['ADDRESS1'];
            $address2 = $data['ADDRESS2'];
            $city = $data['CITY'];
            $district = $data['DISTRICT'];
            $region_key = $data['REGION_KEY'];
            $country_key = $data['COUNTRY_KEY'];
            $country = $data['COUNTRY'];
            $po = $data['PO'];
            $phone = $data['PHONE'];

            $isExists = Location::where(['location_name'=>$name1,'manufacturer_id'=>1])->pluck('location_name');
            if($isExists){
                $message ='Some customers already exists';
                goto xyz;
            }


            $location = new Location;
            $location->location_name = $name1;
            $location->location_type_id = 0;
            $location->manufacturer_id = 1;
            $location->firstname = $name2;
            $location->lastname = $name3;
            $location->location_email = '';
            $location->location_address = $address1;
            $location->location_details = $address2;
            $location->city = $city;
            $location->region = $district;
            $location->country = $country;
            $location->pincode = $po;
            $location->phone_no = $phone;
            $location->erp_code = $customer_id;
            $location->save();  
           
           xyz:
           }
            $status =1;
            if(!isset($message) && empty($message)){
            $message= 'Data successfully retrieved and inserted'; 
        } 

        }
        else{
            throw new Exception('Data not retrieved');
        }
}
catch(Exception $e){
    $message = $e->getMessage();
}
return Response::json(['Status' => $status,'Message' => $message]); 

    }

public function test(){
    $url ='http://14.141.81.243:8000/sap/opu/odata/sap/Z0033_ESEAL_UPDATE_MAT_SRV/UPDATE_MAT?$filter=Eseal_input%20eq%20%27%3C%3Fxml+version%3D%221.0%22+encoding%3D%22utf-8%22+%3F%3E+%0D%0A++%3CREQUEST%3E%0D%0A++%3CDATA%3E%0D%0A++%3CINPUT+TOKEN%3D%223h8M8A2q8iv7nMq4Rpft5G5TBE4O7PC8%22++ESEALKEY%3D%22123%22+%2F%3E+%0D%0A++%3CITEMS%3E%0D%0A++%3CITEM+PORDER%3D%2210101000125%22+PLANT%3D%221010%22+STORE_LOC%3D%22RM01%22+MATERIAL%3D%22RM09%22+BATCH%3D%22999999999%22+QTY%3D%221.0%22+ITEM_COUNT%3D%220000%22+%2F%3E+%0D%0A++%3C%2FITEMS%3E%0D%0A++%3CSERIALS%3E%0D%0A++%3CSERIAL+ITEM_COUNT%3D%220000%22+SERIAL_NO%3D%22%22+%2F%3E+%0D%0A++%3C%2FSERIALS%3E%0D%0A++%3C%2FDATA%3E%0D%0A++%3C%2FREQUEST%3E%27';
  $username = 'eseal1';
  $password = 'eseal@123';

$curl = curl_init();
curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
//curl_setopt($curl,CURLOPT_USERAGENT,$agent);
curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_HEADER, 0);
$result = curl_exec($curl);
curl_close($curl);
return $result;  
}
    public function updateDelivery(){

        $this->_method = 'Z0038_ESEAL_UPDATE_DELIVERY_SRV';
        $this->_method_name = 'DELIVERY';
        $url = $this->_url .$this->_method.'/'.$this->_method_name;

        $username = 'eseal1';
        $password ='eseal@123';

        $xml= '<?xml version="1.0" encoding="utf-8" ?><REQUEST><DATA><INPUT

TOKEN="3h8M8A2q8iv7nMq4Rpft5G5TBE4O7PC8" ESEALKEY="12345" />    <SUMMARY>   <DELIVER 

NO="8010000108" METERIAL_CODE="DOMFAN02" BATCH_NO="0000000023" QUANTITY="1.0" 

PLANT="1010" STORE="FG01" COUNT="000010" />    <DELIVER NO="8010000108" 

METERIAL_CODE="DOMFAN02" BATCH_NO="0000000024" QUANTITY="1.0" PLANT="1010" 

STORE="FG01" COUNT="000020" />    </SUMMARY>   <ITEMS>   <ITEM COUNT="000010" 

SERIAL_NO="001F20150700031" />    <ITEM COUNT="000020" SERIAL_NO="002F20150700031" />    

</ITEMS>   </DATA>   </REQUEST>';

  $method = 'GET';

 $response = $this->_sap_api_repo->request($username,$password,$url, $method,null,'xml',1,null,$xml);
return $response;


    }
    
    public function updateMaterials(){
        try{
        
        $this->_method = 'Z0033_ESEAL_UPDATE_MAT_SRV';
        $this->_method_name = 'UPDATE_MAT';
        $url = $this->_url .$this->_method.'/'.$this->_method_name;
        
         $username = 'eseal1';
        $password ='eseal@123';

        $xml= '<?xml version="1.0" encoding="utf-8" ?> 
  <REQUEST>
  <DATA>
  <INPUT TOKEN="3h8M8A2q8iv7nMq4Rpft5G5TBE4O7PC8"  ESEALKEY="123" /> 
  <ITEMS>
  <ITEM PORDER="10101000125" PLANT="1010" STORE_LOC="RM01" MATERIAL="RM09" BATCH="999999999" QTY="1.0" ITEM_COUNT="0000" /> 
  </ITEMS>
  <SERIALS>
  <SERIAL ITEM_COUNT="0000" SERIAL_NO="" /> 
  </SERIALS>
  </DATA>
  </REQUEST>';
        $method = 'GET';
       // $data['TOKEN'] = '3h8M8A2q8iv7nMq4Rpft5G5TBE4O7PC8';
        $response = $this->_sap_api_repo->request($username,$password,$url, $method,null,'xml',1,null,$xml);
    return $response;
$parseData1 = xml_parser_create();
        xml_parse_into_struct($parseData1, $response, $documentValues1, $documentIndex1);
        xml_parser_free($parseData1);
        $documentData = array();
        foreach ($documentValues1 as $data) {
            if(isset($data['tag']) && $data['tag'] == 'd : Eseal_output')
            {
                $documentData = $data['value'];
            }
        }

        return $documentData;
        $deXml = simplexml_load_string($documentData);
        $deJson = json_encode($deXml);
        $xml_array = json_decode($deJson,TRUE);  
        return $xml_array;    

        }
        catch(Exception $e){
            $message =$e->getMessage();
        }
    }

    
    public function createSalesOrders()
    {
        $csrfToken = $this->getTokenDetails();
        //return $csrfToken;
        $this->_method = 'Z0013_ESEAL_CREATE_SALES_ORDER_SRV';
        $this->_method_name = 'CREATE_SO';
        $url = $this->_url .$this->_method.'/'.$this->_method_name;
        $id = 123;
         $username = 'eseal1';
        $password ='eseal@123';

        $xml= '<entry 
    xmlns="http://www.w3.org/2005/Atom" 
    xmlns:m="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata" 
    xmlns:d="http://schemas.microsoft.com/ado/2007/08/dataservices" xml:base="http://14.141.81.243:8000/sap/opu/odata/sap/Z0013_ESEAL_CREATE_SALES_ORDER_SRV/">
    <id>http://14.141.81.243:8000/sap/opu/odata/sap/Z0013_ESEAL_CREATE_SALES_ORDER_SRV/CREATE_SO('.$id.')</id>
    <title type="text">CREATE_SO('.$id.')</title>
    <updated>2015-07-22T10:35:14Z</updated>
    <category term="Z0013_ESEAL_CREATE_SALES_ORDER_SRV.CREATE_SO" scheme="http://schemas.microsoft.com/ado/2007/08/dataservices/scheme" />
    <link href="CREATE_SO('.$id.')" rel="self" title="CREATE_SO" />
    <content type="application/xml">
        <m:properties>
            <d:Sales_Order />
            <d:Message />
            <d:Eseal_input>"
                <![CDATA[   
                <?xml version="1.0" encoding="utf-8" ?><SALES_ORDER><ZESEAL013_SALES_ORDER_TRANSFOR><INPUT TOKEN_NO="3h8M8A2q8iv7nMq4Rpft5G5TBE4O7PC8" ESEALKEY="1234" DOC_TYPE="ZEXP" SALES_ORG="2000" DISTR_CHAN="40" DIVISION="10" /><ITEM><ZESEAL013_ITEM ITM_NUMBER="000010" MATERIAL="FG01" TARGET_QTY="1000.0" TARGET_UOM="EA" TOKEN="" ESEALKEY="" /></ITEM><PARTNERS><ZESEAL013_PARTNERS PARTN_ROLE="SH" PARTN_NUMB="3000000" ITM_NUMBER="000010" TITLE="COMPANY" NAME="TEST_NAME1" NAME_2="TEST_NAME_2" NAME_3="TEST_NAME_3" STREET="TEST_STREET" COUNTRY_KEY="IN" POSTL_CODE="767676" CITY="HYD" DISTRICT="HYD" REGION_KEY="01" TELEPHONE="8888888888" TOKEN="" ESEALKEY="" /><ZESEAL013_PARTNERS PARTN_ROLE="SP" PARTN_NUMB="3000000" ITM_NUMBER="000000" TITLE="" NAME="" NAME_2="" NAME_3="" STREET="" COUNTRY_KEY="" POSTL_CODE="" CITY="" DISTRICT="" REGION_KEY="" TELEPHONE="" TOKEN="" ESEALKEY="" /></PARTNERS></ZESEAL013_SALES_ORDER_TRANSFOR></SALES_ORDER> ]]>"
            </d:Eseal_input>
        </m:properties>
    </content>
</entry>';
        $method = 'GET';
       // $data['TOKEN'] = '3h8M8A2q8iv7nMq4Rpft5G5TBE4O7PC8';
        $response = $this->_sap_api_repo->request($username,$password,$url, $method,null,'xml',2,$csrfToken,$xml);
       print_r ($response); die;
        $jsonResponse = json_decode($response);
	echo "Result -> <pre>";print_R($jsonResponse); 

        }


    public function getTokenDetails()
    {
        $this->_method = 'Z0013_ESEAL_CREATE_SALES_ORDER_SRV';
        $url = $this->_url . $this->_method . '/';
        $method = 'GET';
        $data['Eseal_input'] = '123';
        $response = $this->_sap_api_repo->request($url, $method, $data, 'xml', 1);
        
        if($response != '')
        {
            $headers = array();
            $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));
            foreach (explode("\r\n", $header_text) as $i => $line)
            {
                if ($i === 0)
                    $headers['http_code'] = $line;
                else
                {
                    list ($key, $value) = explode(': ', $line);

                    $headers[$key] = $value;
                }
            }
           // print_r($headers);
            if(isset($headers['x-csrf-token']))
            {
                return $headers['x-csrf-token'];
            }else{
                return;
            }
        }
        return;
    }

}
