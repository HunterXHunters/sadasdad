<?php


class LookupController extends BaseController {
 /**
  * Display a listing of the resource.
  *
  * @return Response
  */
 public function index()
 {
   
  $lc = DB::Table('lookup_categories')
              ->select('id','name','description')
        ->get();

    $ml = DB::Table('master_lookup')
        ->select('id','category_id','name','description','value','is_active','sort_order')
        ->get();
 
    return View::make('lookup.index')->with('lc',$lc)->with('ml',$ml); 
 }
 /**
  * Show the form for creating a new resource.
  *
  * @return Response
  */
  
  
 public function create()
 {
  return View::make('lookupcategories.create');
 }
 /**
  * Store a newly created resource in storage.
  *
  * @return Response
  */
 public function store()
 {

  
 DB::table('master_lookup')->insert([
      'category_id'=> Input::get('name'),
      'name' => Input::get('mname'),
      'description'=>Input::get('mdescription'),
      'value'=>Input::get('mvalue'),
      'is_active'=>Input::get('is_active'),
      'sort_order'=>Input::get('sort_order')
      //'created_by' => Input::get('created_by'),
      //'created_date'=>Input::get('created_date'),
      //'modified_by'=>Input::get('modified_by'),
      //'modified_on'=>Input::get('modified_on')
    ]);

    return Response::json([
       'status' => true
      ]);

    //return Redirect::to('lookup_categories');
}



 public function storelc()
 {

//return 'abc';
 DB::table('lookup_categories')->insert([
      'name' => Input::get('name'),
      'description'=>Input::get('description')
      //'created_by' => Input::get('created_by'),
      //'created_date'=>Input::get('created_date'),
      //'modified_by'=>Input::get('modified_by'),
      //'modified_on'=>Input::get('modified_on')
    ]);

    return Response::json([
       'status' => true
      ]);

    //return Redirect::to('lookup_categories');
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

 // return 'abc';
   $lookupcat = DB::table('master_lookup')
                ->Join('lookup_categories', 'lookup_categories.id', '=', 'master_lookup.category_id')
                ->select('lookup_categories.id as lookup_id','lookup_categories.name as lookup_name','lookup_categories.description as lookup_desc','master_lookup.category_id','master_lookup.name','master_lookup.value','master_lookup.description','master_lookup.is_active','master_lookup.sort_order','master_lookup.id')
                ->where('master_lookup.id',$mid)
                ->first();

//return View::make('lookup_categories.index')->with('lookupcat',$lookupcat);  

  return Response::json($lookupcat);
}


public function editlc($id)
 {

 // return 'abc';
   $lookupctg = DB::table('lookup_categories')
                ->select('lookup_categories.id','lookup_categories.name as name1','lookup_categories.description as desc1')
                ->where('lookup_categories.id',$id)
                ->first();

                             
  return Response::json($lookupctg);
}

public function updatelc($id)
{
  DB::table('lookup_categories')
  ->where('id',$id)
  ->update(array(
    'name'=>Input::get('name1'),
    'description'=>Input::get('desc1')
    ));


return Response::json([
        'status' => true,
        'message'=>'Sucessfully updated lookup_category.'
      ]);
}



 public function update($mid)
{


 /* * Update the specified resource in storage.
  *
  * @param  int  $id
  * @return Response
  */    
        //create a rule validation
        /*DB::table('lookup_categories')
            ->where('id', $id)
            ->update(array(
              'name' => Input::get('name'),
              'description' => Input::get('description'),
              'is_active'=>Input::get('is_active'),
              'created_by' => Input::get('created_by'),
              'created_date' => Input::get('created_date'),
              'modified_by' => Input::get('modified_by'),
              'modified_on' => Input::get('modified_on')));
*/

DB::table('master_lookup')
            ->where('id', $mid)
            ->update(array(
              'name' => Input::get('name'),
              'description' => Input::get('description'),
              'is_active'=>Input::get('is_active'),
              'sort_order' => Input::get('sort_order'),
              'category_id'=>Input::get('lookup_id'),
/*              'created_date' => Input::get('created_date'),
              'modified_by' => Input::get('modified_by'),
              'modified_on' => Input::get('modified_on')*/
              'value' => Input::get('value')));

return Response::json([
        'status' => true,
        'message'=>'Sucessfully updated.'
      ]);
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
    return Redirect::to('lookupcategories');
  }


  public function deletelc($id)
 {
 
//return 'abc';
        DB::Table('lookup_categories')->where('id', '=', $id)->delete();
    return Redirect::to('lookupcategories');
  }


public function getTreeData()
{
    
    $lukcat = DB::table('lookup_categories')
            ->select('lookup_categories.id','lookup_categories.name','lookup_categories.description')
            ->get();
    
    $finalLcArrs = array();
    $lcs = array();
        $customers_details = json_decode(json_encode($lukcat), true);
        foreach($customers_details as $valus)
    {
      $mlu = DB::table('master_lookup')
                ->Join('lookup_categories', 'lookup_categories.id', '=', 'master_lookup.category_id')
                ->select('lookup_categories.id','lookup_categories.name','lookup_categories.description','master_lookup.category_id','master_lookup.name as mname','master_lookup.value as mvalue','master_lookup.description as mdesc','master_lookup.id as mid','master_lookup.is_active','master_lookup.sort_order')
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
          $ml['is_active'] = $valu['is_active'];
          if($ml['is_active'] == null)
          $ml['is_active'] = '<center><i class="fa fa-square-o"></i></center>'; 
          //$ml['is_active'] = '<input type="checkbox" name="status" value="" disabled>';
          else
          $ml['is_active'] = '<center><i class="fa fa-check-square-o" ></i></center>';
           //$ml['is_active'] ='<input type="checkbox" name="status" value="" checked="checked" disabled>';
          $ml['sort_order'] = $valu['sort_order'];
          $ml['actions'] = '<a data-href="lookupcategories/edit/'.$valu['mid'].'" data-toggle="modal" data-target="#basicvalCodeModal1" ><img src="img/edit.png" /></a><span style="padding-left:10px;" ></span>
                    <a onclick = "deleteEntityType('.$valu['mid'].')" ><img src="img/delete.png" /></a><span style="padding-left:50px;" ></span>';
          
          
      $finalMlArr[] = $ml;
        }
      
          $lcs['name'] = $valus['name'];
          $lcs['description'] = $valus['description'];
          $lcs['actions'] = '<a  data-toggle="modal" onclick="getlookupCategoryName(this);" data-target="#basicvalCodeModal" >
          <img src="img/add.png" /></a><span style="padding-left:1px;" ></span>
          </span><a data-href="lookupcategories/editlc/'.$valus['id'].'" data-toggle="modal" data-target="#basicvalCodeModal3" ><img src="img/edit.png" /></a><span style="padding-left:1px;" ></span>
                    <a onclick = "deleteEntityTypelc('.$valus['id'].')" ><img src="img/delete.png" /></a><span style="padding-left:50px;" ></span>';



          $lcs['children']=$finalMlArr;
          
      $finalLcArrs[] = $lcs;
  }
       return json_encode($finalLcArrs);
}

}

