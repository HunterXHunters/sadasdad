
<script type="text/javascript">
$(document).ready(function() {
    $('#form-editcategory').bootstrapValidator({
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
                      data: { 
                        'name': $('#name').val(), 
                        'id': $('#id').val() 
                      },
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
</script>
{{ Form::open(array('url' => 'lookupscategory/update/'.$loc->id, 'class'=>'form-editcategory','id'=>'form-editcategory')) }}
{{ Form::hidden('_method', 'PUT') }}
         
  <div class="row">
    
    <div class="form-group col-sm-6">
  <label for="exampleInputEmail">Name*</label>
  <div class="input-group ">
    <span class="input-group-addon addon-red"><i class="ion-arrow-shrink"></i></span>
    <input type="text"  id="name" name="name" value="{{$loc->name}}"   placeholder="name" class="form-control">
    <input type="hidden"  id="id" name="id" value="{{$loc->id}}" />
</div>
</div>
  </div>
    <div class="row">
     <div class="form-group col-sm-6">
      <label for="exampleInputEmail">Description*</label>
      <div class="input-group ">
        <span class="input-group-addon addon-red"><i class="ion-document-text"></i></span>
        <textarea type="text" id="description" name="description" value=""  placeholder="description" class="form-control">{{$loc->description}}</textarea> 
      </div>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-sm-6">
      <label for="exampleInputEmail">Is Active*</label>
      <div class="input-group">
        <span class="input-group-addon addon-red"><i class="ion-alert-circled"></i></span>
        <select name="is_active" class="form-control">
            <option value="0" {{ ( $loc->is_active == 0) ? 'selected' : '' }}>Inactive</option>
            <option value="1" {{ ( $loc->is_active == 1) ? 'selected' : '' }}>Active</option>
          </select>
      </div>                        
    </div>
  </div>                     
{{ Form::submit('Update',array('class' => 'btn btn-primary'))}}