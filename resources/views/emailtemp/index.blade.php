@extends('layouts.default')
@extends('layouts.header')
@extends('layouts.sideview')
@section('content')

 
          <div class="main">
      
             <div class="tile-header">
                    <h1>Manage Email Templates</h1>
                  </div>
      
           <!-- show code btn -->      
        <button class="btn btn-primary" data-toggle="modal" data-target="#basicvalCodeModal">ADD Email Templates</button> 
        <br/> </br>
        
        <!-- Modal - Popup for ADD -->
  <div class="modal fade" id="basicvalCodeModal" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
    <div class="modal-dialog wide">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
          <h4 class="modal-title" id="basicvalCode">Add Template</h4>
        </div>
        <div class="modal-body">        

         {{ Form::open(array('url' => 'email/store', 'class' => 'form-horizontal form1' )) }}
            {{ Form::hidden('_method','POST') }}    

      
            <!-- tile body -->  

        <div class="row">
      <div class="form-group col-sm-6">
       <label for="exampleInputEmail">ID:</label>
        <div class="input-group ">
          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
          <input type="text"  id="id" name="id" placeholder="ID" class="form-control" required>
      </div>                        
      </div>
    </div> 
      
    <div class="row">
      <div class="form-group col-sm-6">
       <label for="exampleInputEmail">Code:</label>
        <div class="input-group ">
          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
          <input type="text"  id="code" name="code" placeholder="Code" class="form-control" required>
      </div>                        
      </div>

      <div class="form-group col-sm-6">
        <label for="exampleInputEmail">Name:</label>
        <div class="input-group ">
          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
          <input type="text"  id="name" name="name" placeholder="Name" class="form-control" required>
      </div>
      </div>
    </div>

    <div class="row">
      <div class="form-group col-sm-6">
        <label for="exampleInputEmail">From</label>
        <div class="input-group ">
          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
          <input type="text" id="from" name="from" placeholder="From" class="form-control" required>
        </div>
      </div>
      <div class="form-group col-sm-6">
       <label for="exampleInputEmail">Reply To:</label>
        <div class="input-group ">
          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
          <input type="text"  id="replyto" name="replyto" placeholder="Reply To" class="form-control" required>
      </div>                        
      </div>
  </div>
    
<div class="row">
 <div class="form-group col-sm-6">
      <label for="exampleInputEmail">Subject:</label>
      <div class="input-group">
        <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
        <input type="text" class="form-control" id="subject" value="" name="subject" required></textarea>
      </div>                        
  </div>
    <div class="form-group col-sm-6">
        <label for="exampleInputEmail">HTML Body:</label>
      <div class="input-group">
        <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
        <textarea class="form-control" id="htmlbody" value="" name="htmlbody" rows="3" required></textarea>
      </div> 
      </div>
</div>
<div class="row">
  <div class="form-group col-sm-6">
    <label for="exampleInputEmail">Text Body:</label>
      <div class="input-group">
        <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
        <textarea class="form-control" id="textbody" value="" name="textbody" rows="3" required></textarea>
      </div> 
  </div>
  <div class="form-group col-sm-6">
    <label for="exampleInputEmail">Signature:</label>
    <div class="input-group ">
      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
      <input type="text"  id="signature" name="signature" value="" class="form-control" required>
  </div>
  </div>
</div>
<div class="row">
  <div class="form-group col-sm-6">
    <label for="exampleInputEmail">Version:</label>
    <div class="input-group ">
      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
      <input type="text"  id="version" name="version" value="" class="form-control" required>
  </div>
  </div>
</div>
    
            {{ Form::submit('Add', array('class' => 'btn btn-primary')) }}
            {{Form::close()}}
        
    </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->

  
  <!-- /.Editmodal -->
<!-- Modal - Popup for EDIT -->
<div class="modal fade" id="basicvalCodeModal1" tabindex="-1" role="dialog" aria-labelledby="basicvalCode" aria-hidden="true">
<div class="modal-dialog wide">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h4 class="modal-title" id="basicvalCode">Edit Feature</h4>
    </div>
    <div class="modal-body">
    {{ Form::open(array('url' => 'email/update','data-url' => 'email/update/')) }}
        {{ Form::hidden('_method','PUT') }} 


    <div class="row">
      <div class="form-group col-sm-6">
       <label for="exampleInputEmail">Code:</label>
      <div class="input-group ">
      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
      <input type="text"  id="Code" name="Code" value="" class="form-control" required>
  </div>                       
      </div>

      <div class="form-group col-sm-6">
        <label for="exampleInputEmail">Name:</label>
        <div class="input-group ">
          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
          <input type="text"  id="Name" name="Name" value="" class="form-control" required>
      </div>
      </div>
    </div>

    <div class="row">
      <div class="form-group col-sm-6">
        <label for="exampleInputEmail">From</label>
        <div class="input-group ">
          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
          <input type="text" id="From" name="From" value="" class="form-control" required>
        </div>
      </div>
      <div class="form-group col-sm-6">
       <label for="exampleInputEmail">Reply To:</label>
      <div class="input-group ">
        <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
         <input type="text"  id="ReplyTo" name="ReplyTo" value="" class="form-control" required>
      </div>                       
      </div>
  </div>
    
<div class="row">
 <div class="form-group col-sm-6">
      <label for="exampleInputEmail">Subject:</label>
      <div class="input-group">
        <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
        <input type="text" class="form-control" id="Subject" value="" name="Subject" required></textarea>
      </div>                        
  </div>
    <div class="form-group col-sm-6">
        <label for="exampleInputEmail">HTML Body:</label>
      <div class="input-group">
        <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
        <textarea class="form-control" id="HtmlBody" value="" name="HtmlBody" rows="3" required></textarea>
      </div> 
      </div>
</div>
<div class="row">
  <div class="form-group col-sm-6">
    <label for="exampleInputEmail">Text Body:</label>
      <div class="input-group">
        <span class="input-group-addon addon-red"><i class="fa fa-newspaper-o"></i></span>
        <textarea class="form-control" id="TextBody" value="" name="TextBody" rows="3" required></textarea>
      </div> 
  </div>
  <div class="form-group col-sm-6">
    <label for="exampleInputEmail">Signature:</label>
    <div class="input-group ">
      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
      <input type="text"  id="Signature" name="Signature" value="" class="form-control" required>
  </div>
  </div>
</div>
<div class="row">
  <div class="form-group col-sm-6">
    <label for="exampleInputEmail">Version:</label>
    <div class="input-group ">
      <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
      <input type="text"  id="Version" name="Version" value="" class="form-control" required>
  </div>
  </div>
</div>
                                          
            {{ Form::submit('Update', array('class' => 'btn btn-primary')) }}
            {{Form::close()}}
                        
            </div>
                      </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                  </div><!-- /.modal -->
<!-- /.Editmodal -->

       <div id="jqxgrid">
        </div>
    
  </div> <!--/.main -->                           

@stop

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
    {{HTML::script('scripts/demos.js')}}
    {{HTML::script('jqwidgets/jqxlistbox.js')}}
    {{HTML::script('jqwidgets/jqxdropdownlist.js')}}
    {{HTML::script('jqwidgets/jqxgrid.pager.js')}}
    {{HTML::script('jqwidgets/jqxgrid.sort.js')}}
    {{HTML::script('jqwidgets/jqxgrid.filter.js')}}
    {{HTML::script('jqwidgets/jqxgrid.storage.js')}}
    {{HTML::script('jqwidgets/jqxgrid.columnsreorder.js')}}
    {{HTML::script('jqwidgets/jqxpanel.js')}}
    {{HTML::script('jqwidgets/jqxcheckbox.js')}}

    <!-- Include all compiled plugins (below), or include individual files as needed -->
  <script src="js/bootstrap.min.js"></script>
    
  <script type="text/javascript">
    $(document).ready(function () 
        {           
            var url = "email/show/";
            // prepare the data
            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'code', type: 'string' },
                    { name: 'name', type: 'string' },
                    { name: 'from', type: 'string' },
                    { name: 'replyto', type: 'string' },
                    { name: 'subject', type: 'string' },
                    { name: 'htmlbody', type: 'string' },
                    { name: 'textbody', type: 'string' },
                    { name: 'signature', type: 'string' },
                    { name: 'version', type: 'string' },
                    { name: 'actions', type: 'string' }
                   // { name: 'delete', type: 'string' }
                ],
                id: 'Id',
                url: url,
                pager: function (pagenum, pagesize, oldpagenum) {
                    // callback called when a page or page size is changed.
                }
            };
            var dataAdapter = new $.jqx.dataAdapter(source);
            $("#jqxgrid").jqxGrid(
            {
                width: "100%",
                source: source,
                selectionmode: 'multiplerowsextended',
                sortable: true,
                pageable: true,
                autoheight: true,
                autoloadstate: false,
                autosavestate: false,
                columnsresize: true,
                columnsreorder: true,
                showfilterrow: true,
                filterable: true,
                columns: [
                  { text: 'Code', filtercondition: 'starts_with', datafield: 'code', width: "20%" },
                  { text: 'Template Name', filtercondition: 'starts_with', datafield: 'name', width: "20%" },
                  { text: 'From', datafield: 'from', width: "20%" },
                  { text: 'Reply To', datafield: 'replyto', width:"20%"},
                  { text: 'Subject', datafield: 'subject', width:"20%"},
                  { text: 'HTML Body', datafield: 'htmlbody', width:"20%"},
                  { text: 'Text Body', datafield: 'textbody', width:"20%"},
                  { text: 'Signature', datafield: 'signature', width:"20%"},
                  { text: 'Version', datafield: 'version', width:"20%"},
                  //{ text: 'Edit', datafield: 'edit' },
                  { text: 'Actions', datafield: 'actions',width:"20%" }
                ]               
            });  
            makePopupAjax($('#basicvalCodeModal'));
            makePopupEditAjax($('#basicvalCodeModal1'), 'Id');
      
        });    
        function deleteEntityType(Id)
        {
            var decission = confirm("Are you sure you want to Delete.");
            if(decission==true)
                window.location.href='email/delete/'+Id;
        }
    </script>    
@stop