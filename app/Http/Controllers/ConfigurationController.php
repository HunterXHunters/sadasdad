<?php

/* 
 * This Controller is used to manage all Some kind of master data 
 * like lookup, language, currencies, Taxs, Countries, Zones and Master Price 
 */
use Central\Repositories\RoleRepo;

class ConfigurationController extends BaseController{
    
    private $roleRepo;

    public function __construct() {

      $this->roleRepo = new RoleRepo;
        
    }
    public function lookupsCategory()

    {
      $allowAddLookupCtg = $this->roleRepo->checkPermissionByFeatureCode('MLC002');

       $allowedButtons['add_lookupctg'] = $allowAddLookupCtg;
         return View::make('lookups.category')->with(array('allowed_buttons' => $allowedButtons));
    }
    public function addLookCat()
    {
      return View::make('lookups.addlookupcategory');exit;
    }
    public function addCategory()
    {
        return View::make('lookups.category');
    }
    
    public function saveCategory()
    {
        DB::table('lookup_categories')->insert([
         'name' => Input::get('name'),
         'description'=>Input::get('description'),
         'is_active'=>Input::get('is_active'),
         'created_by' => Input::get('created_by'),
         'created_date'=>Input::get('created_date'),
         'modified_by'=>Input::get('modified_by'),
         'modified_on'=>Input::get('modified_on')
       ]);
 return Redirect::to('lookupscategory')->withFlashMessage('Lookup category created Successfully.');
    }
    
    public function showCategory()
    {

      $allowEditLookupCtg = $this->roleRepo->checkPermissionByFeatureCode('MLC003');
      $allowDeleteLookupCtg = $this->roleRepo->checkPermissionByFeatureCode('MLC004');

        $lookupctg = array();
        $finallookupctg = array();
        $lookup = DB::table('lookup_categories')->orderBy('id','desc')->get();
        $lookupctg_details=json_decode(json_encode($lookup),true);

       foreach($lookupctg_details as $value)
       {  
        if($value['is_active']==1)
        {
            $status = 'Active';
         }
        else
        {
         $status = 'Inactive';
        }    
         //return $customer_details;
         $lookupctg['id'] = $value['id'];
         $lookupctg['name'] = $value['name'];
         $lookupctg['description'] = $value['description'];
         $lookupctg['is_active'] = $status;
         $lookupctg['actions'] ='';
         if($allowEditLookupCtg)
                    {
                      $lookupctg['actions'] = $lookupctg['actions'] . '<span style="padding-left:20px;" ><a href="javascript:void(0);" onclick="editLookupCat(' . "'" . $this->roleRepo->encodeData($value['id']). "'" .')" data-target="#basicvalCodeModal"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span>';
                    }
              if($allowDeleteLookupCtg)
                    {
          
                      $lookupctg['actions'] = $lookupctg['actions'] .'<span style="padding-left:5px;" ><a onclick="deleteEntityType(' . "'" . $this->roleRepo->encodeData($value['id']). "'" .')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                    }
        
         $finallookupctg[] = $lookupctg;
        }
         return json_encode($finallookupctg);
    }
 /**
  * Show the form for editing the specified resource.
  *
  * @param  int  $id
  * @return Response
  */
  
    public function editCategory($id)
     {
           $id = $this->roleRepo->decodeData($id);
           $lookupcat = DB::Table('lookup_categories')->find($id);
           
           //print_r($lookupcat);exit;
           //return Response::json($lookupcat);

      return View::make('lookups.editlookupcategory')->with('loc',$lookupcat);exit;
    }

    public function updateCategory($id)
    {


     /* * Update the specified resource in storage.
      *
      * @param  int  $id
      * @return Response
      */    
            //create a rule validation
            DB::table('lookup_categories')
                ->where('id', $id)
                ->update(array(
                  'name' => Input::get('name'),
                  'description' => Input::get('description'),
                  'is_active'=>Input::get('is_active'),
                  'created_by' => Input::get('created_by'),
                  'created_date' => Input::get('created_date'),
                  'modified_by' => Input::get('modified_by'),
                  'modified_on' => Input::get('modified_on')));

    
 return Redirect::to('lookupscategory')->withFlashMessage('Lookup category updated Successfully.');

    }

     public function deleteCategory($id)
 {
 
        $id = $this->roleRepo->decodeData($id);
        $password = Input::get();
        $userId = Session::get('userId');
        $verifiedUser = $this->roleRepo->verifyUser($password['password'], $userId);
        if($verifiedUser >= 1)
        {
        DB::Table('lookup_categories')->where('id', '=', $id)->delete();
        return 1;
        }else{
            return "You have entered incorrect password !!";
        }
  }

     /**
      * Remove the specified resource from storage.
      *
      * @param  int  $id
      * @return Response
      */
     
     public function validatename()
     {

      $data = Input::get('name');
      $id = Input::get('id');

            if($id)
            {
        
                $ctgname = DB::Table('lookup_categories') 
                              ->where('id' ,'!=',$id)
                              ->where('name',$data)
                              ->pluck('name');
            }
            else {
           $ctgname = DB::Table('lookup_categories')
                      //->select('name')
                      ->where('name',$data)
                      ->pluck('name');
                    }
           if(empty($ctgname))
           {
            //return 'success';
            return json_encode([ "valid" => true ]);
           }                     
          else 
          {
            //return 'fail';
            return json_encode([ "valid" => false ]);
          }          

     }
    public function lookups()
    {
         $allowAddLookup = $this->roleRepo->checkPermissionByFeatureCode('MSL002');

         $lc = DB::Table('lookup_categories')
              ->select('id','name','description')
        ->get();

        $ml = DB::Table('master_lookup')
        ->select('id','category_id','name','description','value')
        ->get();
        $allowedButtons['add_lookup'] = $allowAddLookup;

        return View::make('lookups.index')->with('lc',$lc)
                                          ->with('ml',$ml)
                                          ->with(array('allowed_buttons' => $allowedButtons));
    }
   
    public function create()
 {
  return View::make('lookups.create');
 }
 /**
  * Store a newly created resource in storage.
  *
  * @return Response
  */

 public function storeLookup()
 {
  DB::table('master_lookup')->insert([
      'category_id'=> Input::get('name'),
      'name' => Input::get('mname'),
      'description'=>Input::get('mdescription'),
      'value'=>Input::get('mvalue')
      
    ]);

return Redirect::to('lookups')->withFlashMessage('Lookup created Successfully.');
}


 public function store()
 {

  
 DB::table('master_lookup')->insert([
      'category_id'=> Input::get('name'),
      'name' => Input::get('mname'),
      'description'=>Input::get('mdescription'),
      'value'=>Input::get('mvalue')
      //'created_by' => Input::get('created_by'),
      //'created_date'=>Input::get('created_date'),
      //'modified_by'=>Input::get('modified_by'),
      //'modified_on'=>Input::get('modified_on')
    ]);

    /*return Response::json([
       'status' => true
      ]);*/

return Redirect::to('lookups');
}
 /**
  * Display the specified resource.
  *
  * @param  int  $id
  * @return Response
  */
 public function show($id)
 {
  //
 }
 /**
  * Show the form for editing the specified resource.
  *
  * @param  int  $id
  * @return Response
  */
public function edit($mid)
 {
  return 'abc';
  /*$lookupcat = DB::Table('master_lookup')->find($mid);
  return Response::json($lookupcat);
  return 'abc';*/
   $lookupcat = DB::table('master_lookup')
                ->Join('lookup_categories', 'lookup_categories.id', '=', 'master_lookup.category_id')
                ->select('lookup_categories.id as lookup_id','lookup_categories.description as lookup_desc','master_lookup.category_id','master_lookup.name','master_lookup.value','master_lookup.description',
                  'master_lookup.id')
                ->where('master_lookup.id',$mid)
                ->first();

                 //$lookupcat = DB::Table('master_lookup');
  //return $lookupcat;
  //return View::make('lookup_categories.index')->with('lookupcat',$lookupcat);               
  return Response::json($lookupcat);
}

public function editLookup($mid)
 {

   $mid = $this->roleRepo->decodeData($mid);   
   $lookupcat = DB::table('master_lookup')
                ->Join('lookup_categories', 'lookup_categories.id', '=', 'master_lookup.category_id')
                ->select('lookup_categories.id as lookup_id','lookup_categories.name as lname', 'lookup_categories.description as lookup_desc','master_lookup.category_id','master_lookup.name','master_lookup.value as mvalue','master_lookup.description as mdescription',
                  'master_lookup.id')
                ->where('master_lookup.id',$mid)
                ->first();             
  return View::make('lookups.edit')->with('lc',$lookupcat);exit;
}

 public function updateLookup($mid)
{

DB::table('master_lookup')
            ->where('id', $mid)
            ->update(array(
              'name' => Input::get('name'),
              'description' => Input::get('mdescription'),
              'value' => Input::get('mvalue'),
              'name' => Input::get('mname'),
              /*'is_active'=>Input::get('is_active'),
              'created_by' => Input::get('created_by'),
              'created_date' => Input::get('created_date'),
              'modified_by' => Input::get('modified_by'),
              'modified_on' => Input::get('modified_on')*/
              'value' => Input::get('value')));

return Redirect::to('lookups')->withFlashMessage('Lookup updated Successfully.');
}

 public function update($mid)
{

DB::table('master_lookup')
            ->where('id', $mid)
            ->update(array(
              'description' => Input::get('mdescription'),
              
              'value' => Input::get('value'),
              'mname' => Input::get('mname'),
              'name' => Input::get('name'),
              /*'is_active'=>Input::get('is_active'),
              'created_by' => Input::get('created_by'),
              'created_date' => Input::get('created_date'),
              'modified_by' => Input::get('modified_by'),
              'modified_on' => Input::get('modified_on')*/
              'value' => Input::get('value')));

return Redirect::to('lookupsk');
}
 /**
  * Remove the specified resource from storage.
  *
  * @param  int  $id
  * @return Response
  */
public function delete($id)
 {
 //delete book
//return 'abc';
        DB::Table('master_lookup')->where('id', '=', $id)->delete();
    return Redirect::to('lookup_categories');
  }

  public function deleteLookup($id)
 {
 $id = $this->roleRepo->decodeData($id);
        $password = Input::get();
        $userId = Session::get('userId');
        $verifiedUser = $this->roleRepo->verifyUser($password['password'], $userId);
        if($verifiedUser >= 1)
        {
      
        DB::Table('master_lookup')->where('id', $id)->delete();
      return 1;
        }else{
            return "You have entered incorrect password !!";
        }
  }

public function getTreeData()
{
    
    $lukcat = DB::table('lookup_categories')
            ->select('lookup_categories.id','lookup_categories.name','lookup_categories.description')
            ->get();
    
    $finalLcArrs = array();
    $lcs = array();
    $allowAddLookup = $this->roleRepo->checkPermissionByFeatureCode('MSL002');
    $allowEditLookup = $this->roleRepo->checkPermissionByFeatureCode('MSL003');
    $allowDeleteLookup = $this->roleRepo->checkPermissionByFeatureCode('MSL004');
        $customers_details = json_decode(json_encode($lukcat), true);
        foreach($customers_details as $valus)
    {
      $mlu = DB::table('master_lookup')
                ->Join('lookup_categories', 'lookup_categories.id', '=', 'master_lookup.category_id')
                ->select('lookup_categories.id','lookup_categories.name','lookup_categories.description','master_lookup.category_id','master_lookup.name as mname','master_lookup.value as mvalue','master_lookup.description as mdesc','master_lookup.id as mid')
                ->where('master_lookup.category_id',$valus['id'])
                ->get();
        
    $finalMlArr = array();
    $ml = array();
        $master_details = json_decode(json_encode($mlu), true);
        foreach($master_details as $valu)
        {         
          $ml['mname'] = $valu['mname'];
          $ml['mdescription'] = $valu['mdesc'];
          $ml['mvalue'] = $valu['mvalue'];
          $ml['actions'] =  '';
              if($allowEditLookup)
                    {
                      $ml['actions'] = $ml['actions'] . '<span style="padding-left:30px;" ><a href="javascript:void(0);" onclick="editLookup(' . "'" . $this->roleRepo->encodeData($valu['mid']). "'" .')"  data-target="#basicvalCodeModal1" ><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span>';
                    }
              if($allowDeleteLookup)
                    {
          
                      $ml['actions'] = $ml['actions'] .'<span style="padding-left:15px;"><a onclick = "deleteEntityType(' . "'" . $this->roleRepo->encodeData($valu['mid']). "'" .')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                    }
          
          
      $finalMlArr[] = $ml;
        }
      
          $lcs['name'] = $valus['name'];
          $lcs['description'] = $valus['description'];
          $lcs['actions'] = '';
           if($allowAddLookup)
                    {
                      $lcs['actions'] = $lcs['actions'] .'<span style="padding-left:15px;"><a data-toggle="modal" onclick="getlookupCategoryName(this);" data-target="#basicvalCodeModal"><span class="badge bg-green"><i class="fa fa-plus"></i></span></a></span>';

                    }

          $lcs['children']=$finalMlArr;
          
      $finalLcArrs[] = $lcs;
  }
       return json_encode($finalLcArrs);
}
    
    public function languages()
    { 
        $languages = Languages::all();
        print_r($languages); die;
    }
    
    public function addLanguage()
    {
      $countries = Countries::all();
      
    }
    
    public function editLanguage($lang_id)
    {
        $countries = Countries::all();    
        $language = Languages::find($lang_id);
        print_r($language); die;
    }
    
    public function emailTemplate()
    {  

        $allowAddEmailtemp = $this->roleRepo->checkPermissionByFeatureCode('ET002');

       $allowedButtons['add_emailtemp'] = $allowAddEmailtemp;
         
        $emails=EmailTemplate::all();
        return View::make('emailtemp.list')->with(array('allowed_buttons' => $allowedButtons));
    }
    
    public function showTemplate()
    {

      $allowEditEmailtemp = $this->roleRepo->checkPermissionByFeatureCode('ET003');
      $allowDeleteEmailtemp = $this->roleRepo->checkPermissionByFeatureCode('ET004');

        $emailTemp = array();
        $finalemailTemp = array();

        /*$emails = json_decode(json_encode(DB::select(DB::raw("SELECT master_lookup.name template,master_lookup.value code,email_templates.* 
        	                 FROM master_lookup,lookup_categories,email_templates 
        	                 where 1=1 
        	                 and lookup_categories.id=master_lookup.category_id 
        	                 and lookup_categories.name='Email Template Code' 
        	                 and email_templates.Code=master_lookup.value "))), true);*/
        $emails = json_decode(json_encode(EmailTemplate::all()), true);

        foreach($emails as $value)
        {       	
        	$emailTemp['code'] = $value['Code'];
        	$emailTemp['name'] = $value['Name'];
        	$emailTemp['from'] = $value['From'];
        	$emailTemp['replyto'] = $value['ReplyTo'];
        	$emailTemp['subject'] = $value['Subject'];
        	$emailTemp['htmlbody'] = $value['HtmlBody'];
        	$emailTemp['textbody'] = $value['TextBody'];
        	$emailTemp['signature'] = $value['Signature'];
        	$emailTemp['version'] = $value['Version'];
        	$emailTemp['actions'] = '';

          if($allowEditEmailtemp)
                    {
                      $emailTemp['actions'] = $emailTemp['actions'] . '<span style="padding-left:10px;" ><a href="email/edit/'.$this->roleRepo->encodeData($value['Id']).'" ><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span>';
                    }
              if($allowDeleteEmailtemp)
                    {
          
                     $emailTemp['actions'] = $emailTemp['actions'] .'<span style="padding-left:10px;" ><a onclick="deleteEntityType(' . "'" . $this->roleRepo->encodeData($value['Id']). "'" .')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                    }
          
                                
         	$finalemailTemp[] = $emailTemp;
        }
    
       return json_encode($finalemailTemp);
    }
    
    public function addEmailTemplate()
    {
       /* $code=DB::select(DB::raw("SELECT master_lookup.name template,master_lookup.value code 
                                FROM master_lookup,lookup_categories 
                                where lookup_categories.id=master_lookup.category_id 
                                and lookup_categories.name='Email Template Code'"));*/
        $code=EmailTemplate::all();
		
	return View::make('emailtemp.add')->with('code',$code);
    }
    
    public function saveEmailTemplate()
    {
        DB::Table('email_templates')->insert([
	       // 'Id'=>Input::get('id'),
                'Code'=>Input::get('code'),
                'Name'=>Input::get('name'),
                'From'=>Input::get('from'),	
                'ReplyTo'=>Input::get('replyto'),
                'Subject'=>Input::get('subject'),
                'HtmlBody'=>Input::get('htmlbody'),
                'TextBody'=>Input::get('textbody'),
                'Signature'=>Input::get('signature'),
                'Version'=>Input::get('version')
		]);

	        //return Response::json(['status'=>true, 'message'=>'added successfully']);
	       return Redirect::to('email')->withFlashMessage('Email Template Created Successfully');           

    }

    public function editEmailTemplate($id)
    {

       $id = $this->roleRepo->decodeData($id);
        /*$codes=DB::select(DB::raw("SELECT master_lookup.name template,master_lookup.value code 
                FROM master_lookup,lookup_categories 
                where 1=1 
                and lookup_categories.id=master_lookup.category_id 
                and lookup_categories.name='Email Template Code'"));*/

        /*$email = DB::select(DB::raw("SELECT master_lookup.name template,master_lookup.value code,email_templates.* 
                    FROM master_lookup,lookup_categories,email_templates 
                    where 1=1 
                    and lookup_categories.id=master_lookup.category_id 
                    and lookup_categories.name='Email Template Code' 
                    and email_templates.Code=master_lookup.value
                    and email_templates.Id=$id"));*/
        $email = EmailTemplate::where('Id',$id)->get();

        /*return View::make('emailtemp/edit')->with('email', $email)->with('codes',$codes);*/
        return View::make('emailtemp/edit')->with('email', $email);
    }
    
    public function updateEmailTemplate($id)
    {
        DB::Table('email_templates')
                    ->where('Id',$id)
                    ->update(array(
                    //'Id'=>Input::get('Id'),
                    /*'Code'=>Input::get('template'),*/
                    'Code'=>Input::get('Code'),
                    'Name'=>Input::get('Name'),
                    'From'=>Input::get('From'),	
                    'ReplyTo'=>Input::get('ReplyTo'),
                    'Subject'=>Input::get('Subject'),
                    'HtmlBody'=>Input::get('HtmlBody'),
                    'TextBody'=>Input::get('TextBody'),
                    'Signature'=>Input::get('Signature'),
                    'Version'=>Input::get('Version')));
                    

                    return Redirect::to('email')->withFlashMessage('Email Template updated Successfully');;

             /*return Response::json([
                            'status' => true,
                            'message'=>'Sucessfully updated.'
                    ]);*/
    }
    
    public function destroyEmailTemplate($Id)
    {
           //delete feature
        //EmailTemplate::find($Id)->delete();
       $Id = $this->roleRepo->decodeData($Id);
       $password = Input::get();
        $userId = Session::get('userId');
        $verifiedUser = $this->roleRepo->verifyUser($password['password'], $userId);
        if($verifiedUser >= 1)
        {
              DB::Table('email_templates')
                    ->where('Id',$Id)
                    ->delete();
        return 1;
        }else{
            return "You have entered incorrect password !!";
        }
    }

    public function priceMaster()
    {
       $allowPriceMaster = $this->roleRepo->checkPermissionByFeatureCode('ESP002');
       $allowedButtons['add_price'] = $allowPriceMaster;

            
        return View::make('pricemaster.list')->with(array('allowed_buttons' => $allowedButtons));

    }
    public function showPriceMaster()
    {

        $custs=DB::table('master_lookup')
            ->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
            ->select('master_lookup.value as custtypeid','master_lookup.name as cust_type')
            ->where('lookup_categories.name','Customer Types')
            ->get();
        $custarr = array();
        $finalcustarr = array();
        $allowEditPriceMaster = $this->roleRepo->checkPermissionByFeatureCode('ESP003');
    $allowDeletePriceMaster = $this->roleRepo->checkPermissionByFeatureCode('ESP004');

        foreach($custs as  $cust)
                {
                  $prods=DB::table('eseal_price_master as a')
              ->join('master_lookup as b','a.customer_type_lookup_id','=','b.value')
              ->join('master_lookup as c','a.product_lookup_id','=','c.value')
              ->select(DB::raw('distinct(c.name) as product'),'c.value as productid','b.name as customer','b.value as custtypeid')
              ->where('a.customer_type_lookup_id',$cust->custtypeid)
              ->get();
                  $prodarr = array();
            $finalprodarr = array();


                    foreach($prods as  $prod)
                    {
                      $compts=DB::table('eseal_price_master as a')
                  ->join('master_lookup as b','a.component_type_lookup_id','=','b.value')
                  ->join('master_lookup as c','a.product_lookup_id','=','c.value')
                  ->select(DB::raw('distinct(a.product_lookup_id) as productid'),'c.name as product','b.name as componenttype','b.value as compid','a.customer_type_lookup_id as custtypeid')
                  ->where(array('a.customer_type_lookup_id'=>$cust->custtypeid,'a.product_lookup_id'=>$prod->productid))
                  ->get();
                      $comptarr = array();
                $finalcomptarr = array();

          
                    foreach($compts as  $compt)
                        {
                          $feats=DB::table('eseal_price_master as a')
                      ->join('master_lookup as b','a.component_type_lookup_id','=','b.value')
                      ->join('master_lookup as c','a.product_lookup_id','=','c.value')
                      ->join('master_lookup as d','a.customer_type_lookup_id','=','d.value')
                      ->select('a.id','a.price','a.name as component','c.name as product','c.value as productid','b.name as componenttype',
                        'b.value as compid','d.name as custtype','d.value as custid')
                      ->where(array('a.customer_type_lookup_id'=>$cust->custtypeid,'a.product_lookup_id'=>$prod->productid,'a.component_type_lookup_id'=>$compt->compid))
                      ->get();
                    $featarr = array();
                      $finalfeatarr = array();
                                  
              
                    foreach($feats as $feat)
                    {
                      $featarr['id']=$feat->id;
                      $featarr['component']=$feat->component;
                      $featarr['price']=$feat->price;
                      $featarr['actions'] = '';
                       if($allowEditPriceMaster)
                        {
                          $featarr['actions'] =$featarr['actions'] .'<span style="padding-left:10px;" ><a href="pricemaster/edit/'.$this->roleRepo->encodeData($feat->id).'"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></a></span>';
                        }
                        if($allowDeletePriceMaster)
                        {
                            $featarr['actions'] =$featarr['actions'] .'<span style="padding-left:5px;" ><a onclick="deleteEntityType(' . "'" . $this->roleRepo->encodeData($feat->id). "'" .')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                        }
                      $finalfeatarr[]=$featarr;
                    }
                    $comptarr['id']=$compt->compid;
                    $comptarr['component']=$compt->componenttype;
                              $comptarr['children']=$finalfeatarr;
                    $finalcomptarr[]=$comptarr;
                          }
                $prodarr['id']=$prod->productid;
                $prodarr['component']=$prod->product;
                $prodarr['children']=$finalcomptarr;
                $finalprodarr[]=$prodarr;
                    }
            $custarr['id']=$cust->custtypeid;
          $custarr['component']=$cust->cust_type;
          $custarr['children']=$finalprodarr;
          $finalcustarr[]=$custarr;
        }
      return json_encode($finalcustarr);                          
    }                       
    public function addPriceMaster()
    {

      $custtypeprod=DB::table('customer_type_products as a')
            ->join('master_lookup as b','a.customer_type_lookup_id','=','b.value')
            ->join('master_lookup as c','a.product_lookup_id','=','c.value')
            ->select('a.customer_type_lookup_id','b.name as custtype','a.product_lookup_id','c.name as prodtype')
            ->get();

      $custtype=DB::table('master_lookup')
            ->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
            ->select('master_lookup.value as cust_type_id','master_lookup.name as cust_type')
            ->where('lookup_categories.name','Customer Types')
            ->get();
      $comptype=DB::table('master_lookup')
            ->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
            ->select('master_lookup.value as comp_type_id','master_lookup.name as comp_type')
            ->where('lookup_categories.name','Component Types')
            ->get();
      $prod=DB::table('master_lookup')
            ->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
            ->select('master_lookup.value as prod_type_id','master_lookup.name as product')
            ->where('lookup_categories.name','Eseal Products')
            ->get();
      $modules=DB::table('master_lookup')
            ->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
            ->select('master_lookup.value as module_id','master_lookup.name')
            ->where('lookup_categories.name','Modules')
            ->get();
      $taxclass=DB::table('master_lookup')
            ->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
            ->select('master_lookup.value as taxclass_id','master_lookup.name as taxclass')
            ->where('lookup_categories.name','Tax Classes')
            ->get();
      $curr=DB::table('currency')         
            ->select('currency_id','code')
            ->get();
      return View::make('pricemaster.add')->with('custtype',$custtype)->with('comptype',$comptype)
                ->with('prod',$prod)->with('modules',$modules)->with('curr',$curr)
                ->with('taxclass',$taxclass)->with('custtypeprod',$custtypeprod);
    }
    public function editPriceMaster($id)
     {
           $id = $this->roleRepo->decodeData($id);
            $compt = EsealPriceMaster::where('id',$id)->first();
            $modu = DB::table('module_users')
                ->select('module_id','users','id')
            ->where('product_plan_id',$id)
            ->get();
        $custtypeprod=DB::table('customer_type_products as a')
            ->join('master_lookup as b','a.customer_type_lookup_id','=','b.value')
            ->join('master_lookup as c','a.product_lookup_id','=','c.value')
            ->select('a.customer_type_lookup_id','b.name as custtype','a.product_lookup_id','c.name as prodtype')
            ->get();
        $custtype=DB::table('master_lookup')
            ->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
            ->select('master_lookup.value as cust_type_id','master_lookup.name as cust_type')
            ->where('lookup_categories.name','Customer Types')
            ->get();
        $comptype=DB::table('master_lookup')
            ->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
            ->select('master_lookup.value as comp_type_id','master_lookup.name as comp_type')
            ->where('lookup_categories.name','Component Types')
            ->get();
        $prod=DB::table('master_lookup')
            ->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
            ->select('master_lookup.value as prod_type_id','master_lookup.name as product')
            ->where('lookup_categories.name','Eseal Products')
            ->get();
        $modules=DB::table('master_lookup')
            ->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
            ->select('master_lookup.value as module_id','master_lookup.name')
            ->where('lookup_categories.name','Modules')
            ->get();
        $taxclass=DB::table('master_lookup')
            ->join('lookup_categories','lookup_categories.id','=','master_lookup.category_id')
            ->select('master_lookup.value as taxclass_id','master_lookup.name as taxclass')
            ->where('lookup_categories.name','Tax Classes')
            ->get();
        $curr=DB::table('currency')         
            ->select('currency_id','code')
            ->get();
            return View::make('pricemaster/edit')->with('compt', $compt)->with('custtype',$custtype)
                    ->with('comptype',$comptype)->with('prod',$prod)->with('modules', $modules)
                    ->with('curr',$curr)->with('taxclass',$taxclass)->with('modu',$modu)->with('custtypeprod',$custtypeprod);
    }
    public function storePriceMaster()

    {
            /*$image = Input::file('image_url'); 
            if(isset($image)) 
            {      
              $destinationPath = 'public/uploads/esealproducts/';
              $filename = $image->getClientOriginalName();
              $img=Input::file('image_url')->move($destinationPath, $filename);
              }*/
          $id= DB::Table('eseal_price_master')->insertGetId([
        'customer_type_lookup_id'=>Input::get('custtypeid'),
        'component_type_lookup_id'=>Input::get('comptypeid'),
        'product_lookup_id'=>Input::get('prodid'),
        'name'=>Input::get('pname'),
        'description'=>Input::get('description'), 
        'is_active'=>Input::get('is_active'),
        'price'=>Input::get('price'),
        'subscription_mode'=>Input::get('subscription_mode'),
        'min_subscription'=>Input::get('min_subscription'),
        'valid_from'=>Input::get('dtp_input1'),
        'valid_upto'=>Input::get('dtp_input2'),
        //'image_url'=>Input::file('image_url')->move($destinationPath, $filename),
        'currency_id'=>Input::get('currency_id'),
        'tax_class_id'=>Input::get('tax_class_id')
      ]);

       $checkbox=Input::get('modules');
           $users=Input::get('users');

           if(isset($checkbox) && isset($users))
           {
           foreach($checkbox as $key=>$chk)
             {
             DB::Table('module_users')->insert([
              'product_plan_id'=>$id,
              'module_id'=>$chk,
              'users'=>$users[$key]
              ]);
             }
            }

            return Redirect::to('pricemaster')->withFlashMessage('Price Plan Created Successfully');
                
    }
    public function updatePriceMaster($id)
    {
      /*$image = Input::file('image_url');        
              $destinationPath = 'public/uploads/esealproducts/';
              $filename = $image->getClientOriginalName();*/
      DB::Table('eseal_price_master')
        ->where('id',$id)
        ->update(array('customer_type_lookup_id'=>Input::get('custtypeid'),
        'component_type_lookup_id'=>Input::get('comptypeid'),
        'product_lookup_id'=>Input::get('prodid'),
        'name'=>Input::get('pname'),
        'description'=>Input::get('description'), 
        'is_active'=>Input::get('is_active'),
        'price'=>Input::get('price'),
        'subscription_mode'=>Input::get('subscription_mode'),
        'min_subscription'=>Input::get('min_subscription'),
        'valid_from'=>Input::get('dtp_input1'),
        'valid_upto'=>Input::get('dtp_input2'),
        //'image_url'=>Input::file('image_url')->move($destinationPath, $filename),
        'currency_id'=>Input::get('currency_id'),
        'tax_class_id'=>Input::get('tax_class_id')
        ));
        
      $checkbox=Input::get('module_id');
        $users=Input::get('users');
      if(isset($checkbox) && isset($users))
           {
          DB::Table('module_users')
            ->where('product_plan_id',$id)
            ->delete();

            foreach($checkbox as $index=>$chk)
            {
          DB::Table('module_users')->insert([
                'product_plan_id'=>$id,
                'module_id'=>$chk,
                'users'=>$users[$index]
                ]);       
          }
        }

      return Redirect::to('pricemaster')->withFlashMessage('Price Plan updated Successfully');

    }
    public function destroyPriceMaster($id)
    {   
      $id = $this->roleRepo->decodeData($id);
            $password = Input::get();
        $userId = Session::get('userId');
        $verifiedUser = $this->roleRepo->verifyUser($password['password'], $userId);
        if($verifiedUser >= 1)
        {
      
            EsealPriceMaster::find($id)->delete();
            DB::Table('module_users')
        ->where('product_plan_id',$id)
        ->delete();
      return 1;
        }else{
            return "You have entered incorrect password !!";
        }
            return Redirect::to('pricemaster');
    }
    public function createLookup()
    {
        $lc = DB::Table('lookup_categories')
              ->select('id','name','description')
        ->get();

        $ml = DB::Table('master_lookup')
        ->select('id','category_id','name','description','value')
        ->get();

        return View::make('lookups.add')->with('lc',$lc)->with('ml',$ml);exit; 
           
    }

}



