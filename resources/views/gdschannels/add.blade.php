<script type="text/javascript">
$(document).ready(function() {
    $('#form-addcategory').bootstrapValidator({
//        live: 'disabled',
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
           Channel_Logo: {
                validators: {
                  notEmpty: {
                        message: 'Channel Logo is required'
                    }
                }
            },
             channnel_name: {
                validators: {
                  notEmpty: {
                        message: 'Channel Name is required'
                    }
                }
            },
              price_url: {
                validators: {
                  notEmpty: {
                        message: 'Price Url is required'
                    }
                }
            },
              tnc_url: {
                validators: {
                  notEmpty: {
                        message: 'Tnc url is required'
                    }
                }
            },
            channel_url: {
                validators: {
                  notEmpty: {
                        message: 'Channel Logo is required'
                    }
                }
            },
             key_name: {
                validators: {
                  notEmpty: {
                        message: 'Key Name is required'
                    }
                }
            },
             key_value: {
                validators: {
                  notEmpty: {
                        message: 'Key Value is required'
                    }
                }
            },

        }
    });

});
</script>
@section('main')

{{ Form::open(array('url' => 'gdschannels/store/', 'class'=>'form-addcategory','id'=>'form-addcategory','files'=>true,'enctype'=>'multipart/form-data')) }}
{{ Form::hidden('_method', 'POST') }}
 


    <div class="tile-body">
                    
                    
                    <div class="tab-content">
                      
                      <div id="tab1" class="tab-pane active">
                        <form parsley-validate="" role="form" class="form-horizontal form1">
                        <!-- tile header -->
                  <div class="tile-header">
                   
                  </div>
                  <!-- /tile header -->
                            <!-- tile body -->
                  <div class="tile-body">
                    
                    <div class="row">
                     <div class="col-md-4">
                                      <div class="box box-primary">
                                        <form role="form">
                                          <div class="box-body">
                                            <div class="form-group">
                                              <label for="Channel Image">Channel Logo</label>
                                              <div class="btn btn-default" > <i class="fa fa-cloud-upload"></i>
                                                <input id ="image" type="file" name="Channel_Logo">
                                              </div>
                                            </div>
                                          </div>
                                        </form>
                                      </div>
                                    </div>
                      <div class="hidden">
                        <label for="exampleInputEmail">Channel Id</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                          <input type="text"  id="channel_id" name="channel_id" placeholder="Channel Id" class="form-control" >
                      </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="form-group col-sm-5">
                        <label for="exampleInputEmail">Channel Name</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                          <input type="text" id="channnel_name" name="channnel_name" placeholder="Channel Name" class="form-control" >
                        
                      </div>
                    </div>
                    
                      <div class="form-group col-sm-5">
                        <label for="exampleInputEmail">Channel Url</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                          <input type="text" id="channel_url" name="channel_url" placeholder="Channel Url" class="form-control" >
                        </div>
                      </div>
                      </div>
                      <div class="row">
                      <div class="form-group col-sm-5">
                        <label for="exampleInputEmail">Price Url</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                          <input type="text" id="price_url" name="price_url" placeholder="Price Url" class="form-control" >
                        
                      </div>
                    </div>
                    
                      <div class="form-group col-sm-5">
                        <label for="exampleInputEmail">Terms and Conditions Url</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                          <input type="text" id="tnc_url" name="tnc_url" placeholder="Terms and Conditions" class="form-control" >
                        </div>
                      </div>
                      </div>

                      <div class="row" id= "New">

                        <div class="form-group col-sm-5">
                            <label for="exampleInputEmail">Key Name</label>
                            <div class="input-group ">
                              <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                              <input type="text" id="key_name" name="key_name[]" placeholder="Key Name" class="form-control" >
                            
                          </div>
                        </div>
                      
                        <div class="form-group col-sm-5">
                            <label for="exampleInputEmail">Key Value</label>
                            <div class="input-group ">
                              <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                              <input type="text" id="key_value" name="key_value[]" placeholder="Key Value" class="form-control" >
                            </div>
                        </div>
                      
                       

                      </div>
                   
                      <div class="row">
                      <div class="form-group col-sm-6">
                      <a href="#" title="" class="add-author">Add Credential</a>
                      </div>
                      

<br/><br/>
<div class="form-group col-sm-1">
{{ Form::submit('Submit', array('class' => 'btn btn-warning')) }}
</div>
</div>
</body>
</html>
<script type="text/javascript">
jQuery(function(){
    var counter = 1;
    jQuery('a.add-author').click(function(event){
        event.preventDefault();
        counter++;
        
        var row = $('#New').clone(true, true);
        var htmlvar = row.html()+'<div class="form-group col-sm-1"><button  style="margin-top: 36px;" type="button" class="close close_div" >×</button></div>';
                      
        $(this).closest('div.row').before($('<div/>',{"class":"row", html:htmlvar}) );
       // alert(row.html());
        $(".close_div").on("click", function(){
          // console.log("sadf");

          $(this).parent().parent().remove();
          console.log($(this).parent().parent().remove());

        });
    });

    
});
</script>


