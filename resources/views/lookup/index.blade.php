@extends('layouts.default')

@extends('layouts.header')

@extends('layouts.sideview')

@section('content')


<div class="container" style="padding:left:125px;">
   
       <button class="btn btn-primary " data-toggle="modal" data-target="#basicvalCodeModal2">
                    Add Category
                  </button> 
                  <br/><br/>

           <div id="treeGrid">
            </div>

                   <!-- /show code btn -->
                   <!-- Modal -->
             <div class="modal fade" id="basicvalCodeModal2" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
                    <div class="modal-dialog wide">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                          <h4 class="modal-title" id="basicvalCode">Add Category</h4>
                        </div>
                        <div class="modal-body">
                          <!-- <h1>Add </h1> -->
                            {{ Form::open(array('url' => 'lookupcategories/storelc')) }}
                            {{ Form::hidden('_method', 'POST') }}  



                      <div class="row">
                      <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Category Name</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-puzzle-piece"></i></span>
                        <input type="text"  id="name2" name="name" placeholder="name" class="form-control" required>
                        </div>
                      </div>
                      <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Description</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
                         <textarea type="text"  id="description" name="description" placeholder="description" class="form-control" required></textarea>
                        </div>
                      </div>
                     </div>


                     {{ Form::submit('Submit') }}
                            {{ Form::close() }}

                        </div>
                      </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->



                  <!-- Modal -->
                  <div class="modal fade" id="basicvalCodeModal3" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
                    <div class="modal-dialog wide">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                          <h4 class="modal-title" id="basicvalCode">Edit Category</h4>
                        </div>
                        <div class="modal-body">
                        {{ Form::open(array('url' => 'lookupcategories/updatelc', 'data-url' => 'lookupcategories/updatelc/')) }} 
                            {{ Form::hidden('_method', 'PUT') }}
                   

                      <div class="row">
                      <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Category Name</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-puzzle-piece"></i></span>
                         <input type="text"  id="name1" name="name1" value="" class="form-control" required>
                        </div>
                      </div>
                      <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Description</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
                         <textarea type="text"  id="desc1" name="desc1" value="" class="form-control" required>
                         </textarea>
                        </div>
                      </div>
                     </div>

                     {{ Form::submit('Update') }}
                        {{ Form::close() }}

                        </div>
                      </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->

                 
   

                  <!-- Modal -->
                  <div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
                    <div class="modal-dialog wide">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                          <h4 class="modal-title" id="basicvalCode">Add</h4>
                        </div>
                        <div class="modal-body">
                          <!-- <h1>Add </h1> -->
                            {{ Form::open(array('url' => 'lookupcategories/store')) }}
                            {{ Form::hidden('_method', 'POST') }}
                   
                    <div class="row">
                      <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Category Name</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-puzzle-piece"></i></span>
                           <select name="name" id="name5"  required class="form-control">
                             @foreach($lc as $loc)  
                                   <option value="{{$loc->id}}">{{$loc->name}}</option>
                             @endforeach
                           </select>
                         </div>
                      </div>
                    <!--   <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Description</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                           <select name="name" id="name6"  required class="form-control">
                             @foreach($lc as $loc)  
                                   <option value="{{$loc->id}}">{{$loc->description}}</option>
                             @endforeach
                           </select>
                         </div>
                      </div> -->
                     </div>
                    <div class="row">
                      <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Lookup Attribute Name</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-cogs"></i></span>
                      <input type="text"  id="mname" name="mname" placeholder="mname" class="form-control"required> 
                      </div>
                      </div>
                      <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Attribute Description</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
                         <textarea type="text"  id="mdescription" name="mdescription" placeholder="mdescription" class="form-control" required></textarea>
                        </div>
                      </div>
                     </div>
                      <div class="row">
                      <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Value</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-slack"></i></span>
                         <input type="text"  id="mvalue" name="mvalue" placeholder="mvalue" class="form-control" required>
                        </div>
                      </div>

                      <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Sort Order</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-sort"></i></span>
                         <input type="text"  id="sort_order" name="sort_order" placeholder="sort_order" class="form-control" required>
                        </div>
                      </div>
                     </div>



                    <div class="row">
                    <div class="form-group col-sm-6">
                      <label  for="exampleInputEmail">Status</label>
                      <div class="input-group " id="myproperlabel">
                        <span class="input-group-addon addon-red"><i class="fa fa-check-square-o"></i></span>
                          <div class="checkbox">
                         <input type="checkbox" value="1"  id="opt01" id="is_active" name="is_active" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated" checked required>
                         <label for="opt01">Is Active</label>
                           </div>
                          </div>
                     </div>
                    </div>

                    
                            {{ Form::submit('Submit') }}
                            {{ Form::close() }}

                        </div>
                      </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->


                   <!-- Modal -->
                  <div class="modal fade" id="basicvalCodeModal1" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
                    <div class="modal-dialog wide">
                      <div class="modal-content">
                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                          <h4 class="modal-title" id="basicvalCode">Edit</h4>
                        </div>
                        <div class="modal-body">
                        {{ Form::open(array('url' => 'lookupcategories/update', 'data-url' => 'lookupcategories/update/')) }} 
                            {{ Form::hidden('_method', 'PUT') }}
                         

                        <div class="row">
                      <div class="form-group col-sm-6">
                        <label for="exampleInputEmail"> Category Name</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-puzzle-piece"></i></span>
                          <select name="lookup_id" id="name"  required class="form-control">
                             @foreach($lc as $loc)  
                                   <option value="{{$loc->id}}">{{$loc->name}}</option>
                             @endforeach
                           </select>
                        </div>
                      </div>
                      <!-- 
                      <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Description</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                          <input type="text"  id="description" name="lookup_desc" value="" class="form-control" readonly>
                        </div>
                      </div> -->
                     </div>
                    <div class="row">
                      <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Lookup Attribute Name</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-cogs"></i></span>
                          <input type="text"  id="mname" name="name" value="" class="form-control" required> 
                        </div>
                      </div>
                      <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Attribute Description</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
                         <textarea type="text"  id="mdescription" name="description" value="" class="form-control" required></textarea>
                        </div>
                      </div>
                     </div>
                      <div class="row">
                      <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Value</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-slack"></i></span>
                         <input type="text"  id="mvalue" name="value" placeholder="mvalue" class="form-control" required>
                        </div>
                      </div>
                        <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Sort Order</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-sort"></i></span>
                         <input type="text"  id="sort_order" name="sort_order" placeholder="sort_order" class="form-control" required>
                        </div>
                      </div>
                    </div>

                   <!--  <div class="row">
                    <div class="form-group col-sm-6">
                      <label  for="exampleInputEmail">Status</label>
                      <div class="input-group " id="myproperlabel">
                        <span class="input-group-addon addon-red"><i class="fa fa-check-square-o"></i></span>
                          <div class="checkbox">
                            @if(['is_active'] == null)
                         <input type="checkbox" name="status" value="">
                            @else
                         <input type="checkbox" name="status" value="" checked="checked">
                         @endif
                         <label for="opt01">Is Active</label>
                           </div>
                          </div>
                     </div>
                    </div> -->

                      <div class="row">
                    <div class="form-group col-sm-6">
                      <label  for="exampleInputEmail">Status</label>
                      <div class="input-group " id="myproperlabel">
                        <span class="input-group-addon addon-red"><i class="fa fa-check-square-o"></i></span>
                          <div class="checkbox">
                         <input type="checkbox"  value="1"  id="opt03" id="is_active" name="is_active" parsley-group="mygroup" parsley-trigger="change" parsley-required="true" parsley-mincheck="2" parsley-error-container="#myproperlabel .last" class="parsley-validated" checked required>
                         <label for="opt03">Is Active</label>
                           </div>
                          </div>
                     </div>
                    </div>


                    


                        {{ Form::submit('Update') }}
                        {{ Form::close() }}

                        </div>
                      </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->

@stop


@section('style')
    {{HTML::style('jqwidgets/styles/jqx.base.css')}}
     
@stop

@section('script')
    
    {{HTML::script('jqwidgets/jqxcore.js')}}
    {{HTML::script('jqwidgets/jqxdata.js')}}
    {{HTML::script('jqwidgets/jqxbuttons.js')}}
    {{HTML::script('jqwidgets/jqxscrollbar.js')}}
    {{HTML::script('jqwidgets/jqxdatatable.js')}}
    {{HTML::script('jqwidgets/jqxtreegrid.js')}}
    {{HTML::script('scripts/demos.js')}}
  
    <script type="text/javascript">
        $(document).ready(function () 
        {
            
            ajaxCall();
            makePopupAjax($('#basicvalCodeModal'));
            makePopupEditAjax($('#basicvalCodeModal1'));
            makePopupAjax($('#basicvalCodeModal2'));
            makePopupEditAjax($('#basicvalCodeModal3'));

        });

function ajaxCall()
{
  $.ajax(
            {
                url: "lookupcategories/getTreeData",
                success: function(result)
                {
                    var employees = result;
                    // prepare the data
                    var source =
                    {
                        datatype: "json",
                        datafields: [
                        { name: 'name', type: 'string' },
                        { name: 'mname', type: 'string' },
                        { name: 'mvalue', type: 'string' },
                        { name: 'is_active', type: 'integer' },
                        { name: 'sort_order', type: 'integer' },
                        { name: 'actions', type: 'string' },
                        { name: 'children', type: 'array' },
                        { name: 'expanded', type: 'bool' }
                       ],
                        hierarchy:
                        {
                            root: 'children'
                        },
                        id: 'mid',
                        localData: employees
                    };
                    var dataAdapter = new $.jqx.dataAdapter(source);
                    // create Tree Grid
                    $("#treeGrid").jqxTreeGrid(
                    {
                        width: '100%',
                        source: dataAdapter,
                        sortable: true,
                        //autoheight: true,
                        //autowidth: true,
                        columns: [
                  { text: 'Category Name', datafield: 'name', width:200},
                  { text: 'Lookup Attribute Name ',  datafield: 'mname', width: 200 },
                  { text: ' Value',  datafield: 'mvalue', width: 140 },
                  { text: ' Status',  datafield: 'is_active', width: 120},
                  { text: ' Sort Order',  datafield: 'sort_order', width: 130 },
                  { text: 'Actions', datafield: 'actions',width:120 }
                        ]
                    });


                }
            });
}    

function deleteEntityType(id)
        {
            var decission = confirm("Are you sure you want to Delete.");
            if(decission==true)
                window.location.href='lookupcategories/delete/'+id;
        }



function deleteEntityTypelc(id)
        {
            var decission = confirm("Are you sure you want to Delete.");
            if(decission==true)
                window.location.href='lookupcategories/deletelc/'+id;
        }

 function getlookupCategoryName(el){
      var loc = $(el).closest('tr').find('td:eq(0) span:last').text();
       $('#name5').find('option').prop('disabled', true);
      $('#name5').find('option').filter(function(){
        return ($(this).text() == loc);
      }).prop({'selected':true, 'disabled': false});
    }

   /* function getDescriptionName(el){
      var des = $(el).closest('tr').find('td:eq(1)').text();
       $('#name6').find('option').prop('disabled', true);
      $('#name6').find('option').filter(function(){
        return ($(this).text() == des);
      }).prop({'selected':true, 'disabled': false});
    }*/
    </script>    
@stop

