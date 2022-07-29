@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')
@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
     
@stop

@section('script')
    {{HTML::script('jqwidgets/jqxcore.js')}}
    {{HTML::script('jqwidgets/jqxbuttons.js')}}
    {{HTML::script('jqwidgets/jqxscrollbar.js')}}
    {{HTML::script('jqwidgets/jqxmenu.js')}}
    {{HTML::script('jqwidgets/jqxgrid.js')}}
    {{HTML::script('jqwidgets/jqxgrid.selection.js')}}
    {{HTML::script('jqwidgets/jqxgrid.columnsresize.js')}}
    {{HTML::script('jqwidgets/jqxdata.js')}}
    {{HTML::script('jqwidgets/jqxgrid.edit.js')}}
    {{HTML::script('jqwidgets/jqxlistbox.js')}}
    {{HTML::script('jqwidgets/jqxdropdownlist.js')}}
    {{HTML::script('jqwidgets/jqxgrid.pager.js')}}
    {{HTML::script('jqwidgets/jqxgrid.sort.js')}}
    {{HTML::script('jqwidgets/jqxgrid.filter.js')}}
    {{HTML::script('jqwidgets/jqxgrid.storage.js')}}
    {{HTML::script('jqwidgets/jqxgrid.columnsreorder.js')}}
    {{HTML::script('jqwidgets/jqxpanel.js')}}
    {{HTML::script('jqwidgets/jqxcheckbox.js')}}
    {{HTML::script('jqwidgets/jqxgrid.selection.js')}}
    {{HTML::script('jqwidgets/globalization/globalize.js')}}
    
    <script type="text/javascript">
    $(document).ready(function () 
        {           
            //var url = "rbac/getRoles";
            // var data = {{$results}};
            var data= <?=$results?>;
              //var data = generatedata(100);

            

      var source =
            {  
                localdata: data,
                datatype: "json",
                datafields: [
                    { name: 'name', type: 'string' },
                    { name: 'brand_name', type: 'string' },
                    //{ name: 'username', type: 'string' },
                    { name: 'actions', type: 'string' }
                ],
                
                id: 'role_id',
               // url: url,
                       
                 pager: function (pagenum, pagesize, oldpagenum) {
                    // callback called when a page or page size is changed.
                }
            };
            var dataAdapter = new $.jqx.dataAdapter(source);
            $("#jqxgrid").jqxGrid(
            {
                width: '100%',
		            source: dataAdapter,                
                pageable: true,
                autoheight: true,
                //sortable: true,
                //altrows: true,
                //sortcolumn: 'ShipName',
                editable: false,
                showfilterrow: true,
                filterable: true,
                selectionmode: 'multiplecellsadvanced',
                columns: [
                  { text: 'Role Name',  datafield: 'name',width: '40%' },
                  { text: 'Customer Name',  datafield: 'brand_name', align: 'left', width: '40%' },
                 //{ text: 'User Name',  datafield: 'username', width: '25%' },
                  //{ text: 'Action',  datafield: 'role_id', align: 'right', cellsalign: 'right', cellsformat: 'c2', width: 200 },
                  { text: 'Actions', datafield: 'actions',width:'20%' }
                ],
               
            });
        });
        
         </script> 
@stop         
@section('content')
<!--New Code-->
<div class="box">
  <div class="box-header">
    <h3 class="box-title"><strong>Role Base </strong>  Access Controller</h3>
    @if(Session::has('successMsg'))
    <?PHP 
        $successMsg = Session::get('successMsg');
    ?>
    <div style="color:#008a00"><h4>
        <?PHP echo $successMsg;?></h4>
    </div>
   @endif

    @if($addPermission) 
     <a href="{{URL::asset('rbac/add')}}" class="pull-right"><i class=" fa fa-user-plus"></i> <span style="font-size:11px;">Add New Role</span></a>
      <a href="/rbac/exportusers" class="btn btn-primary pull-right">Export to xls</a>
    @endif
  </div>
   
   <div class="col-sm-12">
     <div class="tile-body nopadding">                  
        <div id="jqxgrid"></div>  
    </div>

  </div>
<!--New Code-->
<!--OldCode-->
<!-- <div class="main pricemaster">
    <div class="row">
        <div class="col-md-12">
            <section class="tile cornered">

                   @if(Session::has('successMsg'))
                    <?PHP 
                        $successMsg = Session::get('successMsg');
                    ?>
                   <div style="color:#008a00"><h4>
                        <?PHP echo $successMsg;?></h4>
                    </div>
                   @endif
                   <div class="tile-header">
                    <h1><strong>Role Base </strong> Access Controller</h1>
                    <div class="controls plussymbl">
                       @if($addPermission) 
                      <a href="{{URL::asset('rbac/add')}}" class="btn btn-default">Add New Role</a>
                      @endif
                    </div>
                  </div>


                  <div class="tile-body nopadding">
                    
                    <div id="jqxgrid" class="col-lg-12 col-md-12" style="width:100% !important;"></div>  

                  </div>
 
                </section>
         </div>   
    </div>
</div> -->
<!--OldCode-->
<script type="text/javascript">
    
    function CheckedFeature(val, fromid, toid)
    { if(val==false){
             for(i=fromid;i<=toid;i++)
            {
                $( "#feature_name"+i ).prop( "checked", false );
            }
        }else {
            for(i=fromid;i<toid;i++)
            {
                $( "#feature_name"+i ).prop( "checked", true );
            }
        }
        
    }
    function getCustomerUser(id)
    {
        $.get('getUserDetail/'+id,function(data){
            var dataArr = $.parseJSON(data)
            var Str = '<table border="0">';
            Str += '<tr><th><input type="checkbox" value="1" name="multiSelect" id="multiSelect"> </th>';
            Str += '<th>User Name</th><th>Email</th></tr>';
             for(i=0;i< dataArr.length;i++){
                Str += '<tr><td><input type="checkbox" value="'+dataArr[i].user_id+'" name="user_id[]" id="user_id_'+dataArr[i].user_id+'"> </td>';
                Str +='<td>'+dataArr[i].username+'</td>';
                Str +='<td>'+dataArr[i].email+'</td>';
                Str +='</tr>'
            }
            Str += '</table>';
            
            $("#userTab").html(Str);
        });
        
    }
</script>   
@stop