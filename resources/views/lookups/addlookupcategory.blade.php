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
            name: {
                validators: {
                    remote: {
                      message : 'Name already exists.Please enter a new name',
                      url: '/lookupscategory/validatename',
                      type: 'GET',
                      //data: ['name': $('#name').val()];
                      delay: 2000     // Send Ajax request every 2 seconds
                  },
                
                  notEmpty: {
                        message: 'Name is required'
                    }
                }
            },
           description: {
                validators: {
                  notEmpty: {
                        message: 'Description is required'
                    }
                }
            },
             is_active: {
                validators: {
                  notEmpty: {
                        message: 'is_active is required'
                    }
                }
            }
        }
    });
   });
 

 $("#name").on('blur', function () {

//check_availability();   
 });
 function check_availability(){  
  
        var lkpcatval=$('#name').val();
        var temp = false;
    $.ajax
          (
            {
              url: "/lookupscategory/validatename", 
              type: "GET", 
              data: "cname=" +lkpcatval,
              success: function(response)
              {
                if(response == 'fail')
                {
                  //alert('enter new name');  
                  temp = false;
                }else{
                  //console.log('we are in else');
                  temp = true;
                }
                console.log('temp => '+temp);
                return temp;
              }
            });          
}
</script>

                                {{ Form::open(array('url' => 'lookupscategory/store','class'=>'form-addcategory','id'=>'form-addcategory')) }}
                                {{ Form::hidden('_method', 'POST') }}
                             
                   
                        <div class="row">
                          
                          <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Name*</label>
                            <div class="input-group ">
                              <span class="input-group-addon addon-red"><i class="ion-arrow-shrink"></i></span>
                              <input type="text"  id="name" name="name"  placeholder="name" class="form-control" >
                          </div>
                           </div>
                         </div>

                          <div class="row">
                           <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Description*</label>
                            <div class="input-group ">
                              <span class="input-group-addon addon-red"><i class="ion-document-text"></i></span>
                              <textarea type="text" id="description" name="description"  placeholder="description" class="form-control"></textarea> 
                            </div>
                          </div>
                        </div>

                        <div class="row">
                          <div class="form-group col-sm-6">
                            <label for="exampleInputEmail">Is active*</label>
                            <div class="input-group">
                              <span class="input-group-addon addon-red"><i class="ion-alert-circled"></i></span>  
                             <select name="is_active"  required class="form-control">
                                  <option  value="1">Active</option>
                                  <option  value="0">Inactive</option>
                                </select>

                            </div>                        
                          </div>
                           </div> 
                                
                                {{ Form::submit('Submit',array('class' => 'btn btn-primary')) }}
                                {{ Form::close() }}

                                