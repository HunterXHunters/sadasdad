<script type="text/javascript">
$(document).ready(function() {
    $('#form-EditLookup').bootstrapValidator({
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
                  notEmpty: {
                        message: 'Name is required'
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
                        message: 'Name is required'
                    }
                }
              },
                value: {
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
   {{ Form::open(array('url' => 'lookups/updateLookup/'.$lc->id,'method'=>'POST', 'id'=>'form-EditLookup')) }} 
                              {{ Form::hidden('_method', 'PUT') }}
                           

                     <div class="row">
                        <div class="form-group col-sm-6">
                          <label for="exampleInputEmail">Category Name *</label>
                          <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-arrow-shrink"></i></span>
                             <select name="name" id="name"  class="form-control">
                               @foreach($lc as $loc)  
                                     <option value="{{$lc->id}}">{{$lc->lname}}</option>
                               @endforeach
                             </select>
                           <!-- <input type="text"  id="name" name="name" placeholder="name" class="form-control" required> -->
                          </div>
                        </div>
                         <div class="form-group col-sm-6">
                          <label for="exampleInputEmail">Lookup Key Name*</label>
                          <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-key"></i></span>
                            <input type="text"  id="mname" name="mname" value="{{$lc->name}}" class="form-control"> 
                          </div>
                        </div>
                       </div>
                      <div class="row">
                       
                        <div class="form-group col-sm-6">
                          <label for="exampleInputEmail">Master Description *</label>
                          <div class="input-group ">                        
                            <span class="input-group-addon addon-red"><i class="ion-clipboard"></i></span>
                            <textarea type="text"  id="mdescription" name="mdescription" class="form-control" >{{$lc->mdescription}}</textarea>
                          </div>
                        </div>
                       
                        <div class="form-group col-sm-6">
                          <label for="exampleInputEmail">Value *</label>
                          <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-document-text"></i></span>
                           <input type="text"  id="mvalue" name="value" value="{{$lc->mvalue}}" class="form-control">
                          </div>
                        </div>
                      </div>

                      


                          {{ Form::submit('Update', array('class' => 'btn btn-warning'))}}
                          {{ Form::close() }}
