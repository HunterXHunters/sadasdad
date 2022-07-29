<script type="text/javascript">
$(document).ready(function() {
    $('#form-AddLookup').bootstrapValidator({
//        live: 'disabled',
/*      
  live: 'enabled',
*/  message: 'This value is not valid',

        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {

            name: {
                validators: {
                  notEmpty: {
                        message: 'Category name is required'
                    }
                }
            },
            mdescription: {
                
                validators: {
                  notEmpty: {
                        message: 'Description is required'
                    }
                }
              },
                mname: {
                 validators: {
                  notEmpty: {
                        message: 'Lookup Key name is required'
                    }
                }
              },
                mvalue: {
                  validators: {
                  notEmpty: {
                        message: 'Value is required'
                    }
                }
            }

        }

    });

});
</script>    

{{ Form::open(array('url' => 'lookups/storeLookup/','id'=>'form-AddLookup')) }}
                              {{ Form::hidden('_method', 'POST') }}
                     
                      <div class="row">
                        <div class="form-group col-sm-6">
                          <label for="exampleInputEmail">Category Name *</label>
                          <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-arrow-shrink"></i></span>
                             <select name="name" id="name" class="form-control">
                              <option value="">Select Category</option>
                               @foreach($lc as $loc)  
                                      <option value="{{$loc->id}}">{{$loc->name}}</option>
                               @endforeach
                             </select>
                           <!-- <input type="text"  id="name" name="name" placeholder="name" class="form-control" required> -->
                          </div>
                        </div>
                       <div class="form-group col-sm-6">
                          <label for="exampleInputEmail">Lookup Key name *</label>
                          <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-key"></i></span>
                            <input type="text"  id="mname" name="mname" placeholder="Master Name" class="form-control"> 
                          </div>
                        </div>
                       </div>
                      <div class="row">
                        
                        <div class="form-group col-sm-6">
                          <label for="exampleInputEmail">Master Description *</label>
                          <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-clipboard"></i></span>
                           <textarea type="text"  id="mdescription" name="mdescription" placeholder="Description" class="form-control"></textarea>
                          </div>
                        </div>
                       
                        
                        <div class="form-group col-sm-6">
                          <label for="exampleInputEmail">Value *</label>
                          <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-document-text"></i></span>
                           <input type="text"  id="mvalue" name="mvalue" placeholder="Enter lookup value" class="form-control" >
                          </div>
                        </div>
                      </div>
                      

                       {{ Form::submit('Submit', array('class' => 'btn btn-warning'))}}
                       {{ Form::close() }}
