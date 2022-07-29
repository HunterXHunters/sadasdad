<?php 

use Central\Repositories\RoleRepo;


class ManualController extends BaseController{
    
    public $roleAccess;
    
    public function __construct(RoleRepo $roleAccess) {

        $this->roleAccess = $roleAccess;
    }

    public function index()
    {
        parent::Breadcrumbs(array('Home'=>'/','Manual'=>'#')); 

        $editRole = $this->roleAccess->checkPermissionByFeatureCode('UM003');
        $deleteRole = $this->roleAccess->checkPermissionByFeatureCode('UM004');

        $results = Manuals::where('parent_screen_id','=','0')->get();

        foreach ($results as $key=>$result) {
            $child = Manuals::where('parent_screen_id','=',$result->manual_id)->get(); 
            if(!empty($child))
            {
                $results[$key]->child = $child;
            }
        }

    	return View::make('manual.index')->with(array('results' => $results));
    }

    public function getManualbyId($manualId)
    {
    	$result = Manuals::where('manual_id','=',$manualId)->get();
        return json_encode($result);exit;
    }

    public function manualList()
    {
        parent::Breadcrumbs(array('Home'=>'/','Manual'=>'#')); 

        $addPermission = $this->roleAccess->checkPermissionByFeatureCode('UM002');
        //$results = Manuals::all();
        return View::make('manual.list')->with('addPermission',$addPermission);
    }


    public function getManualList()
    {
        $results = Manuals::where('parent_screen_id','=',0)
                    ->select(DB::raw('manual_id,screen_name,(select pm.screen_name from manuals as pm where pm.manual_id=manuals.previous_screen_id) as previous_screen_name,(select nm.screen_name  from manuals as nm where nm.manual_id=manuals.next_screen_id) as next_screen_name'))
                    ->get();
        
        $editPermission = $this->roleAccess->checkPermissionByFeatureCode('UM002');
        $DeletePermission = $this->roleAccess->checkPermissionByFeatureCode('UM005');

        foreach ($results as $key=>$result) {
            

            $childs = Manuals::where('parent_screen_id','=',$result->manual_id)
                    ->select(DB::raw('manual_id,screen_name as child_screen_name,(select pm.screen_name from manuals as pm where pm.manual_id=manuals.previous_screen_id) as previous_screen_name,(select nm.screen_name  from manuals as nm where nm.manual_id=manuals.next_screen_id) as next_screen_name'))
                    ->get();
            foreach ($childs as $k => $value) {
                if($editPermission) {
                    $childs[$k]->actions = '<span style="padding-left:20px;" ><a href="edit/'.$value->manual_id.'"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></span>';
                }
                 if($DeletePermission) {
                    $childs[$k]->actions .= '<span style="padding-left:20px;" ><a href="javascript:void(0)" onclick="deleteManual('.$value->manual_id.')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                }    
            }

            if(!empty($childs))
            {
                if($editPermission) {
                    $results[$key]->actions .= '<span style="padding-left:20px;" ><a href="edit/'.$result->manual_id.'"><span class="badge bg-light-blue"><i class="fa fa-pencil"></i></span></span>';
                }
                if($DeletePermission) {
                    $results[$key]->actions .= '<span style="padding-left:20px;" ><a href="javascript:void(0)" onclick="deleteManual('.$result->manual_id.')"><span class="badge bg-red"><i class="fa fa-trash-o"></i></span></a></span>';
                }
                $results[$key]->children = $childs;
            }
        }

        return json_encode($results); die;

    }

    public function add()
    {
        parent::Breadcrumbs(array('Home'=>'/','Manual'=>'manual/list','AddNew'=>'#')); 

        $results = Manuals::select('manual_id','screen_name')->get();
        
        return View::make('manual.add')->with(array('manuals' => $results,'manual_id'=>0));       
    }

    public function edit($manualId)
    {

        parent::Breadcrumbs(array('Home'=>'/','Manual'=>'manual/list','Edit'=>'#')); 

        $manuals = Manuals::select('manual_id','screen_name')->get();

        $row = Manuals::find($manualId);

        $row = array($row);
        
        return View::make('manual.add')->with(array('manuals' => $manuals,'manual_id'=>$manualId,'row'=>$row[0]));       
    }

    public function saveManual()
    {
        $data = Input::get();
        unset($data['_token']);
        
        if(isset($data['manual_id']) && $data['manual_id'] == 0){
            unset($data['manual_id']);
           
            DB::table('manuals')->insert($data);
        }else{
            DB::table('manuals')->where('manual_id',$data['manual_id'])->update($data);
        }
        return Redirect::to('manual/list');
    }

    public function deleteManual($manualId)
    {
        manuals::destroy($manualId);

        return Redirect::to('manual/list');   
    }

    public function uploadFile()
    {
        $filename = Input::file('file')->getClientOriginalName();
        $destinationPath = $_SERVER['DOCUMENT_ROOT'].'/uploads/manuals/'; 
        $filename = date('YmdHis').$filename;
        Input::file('file')->move($destinationPath, $filename);
        echo $filename; die;
    }

}