<script type="text/javascript">
$(document).ready(function() {
    $('#form-EditAppVersion').bootstrapValidator({
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

            dbupdate: {
                validators: {
                  notEmpty: {
                        message: 'DB update is a mandatory field'
                    }
                }
            },
            configreset: {
                
                validators: {
                  notEmpty: {
                        message: 'Configuration Reset is required'
                    }
                }
              },
                version: {
                 validators: {
                  notEmpty: {
                        message: 'Version is required'
                    }
                }
              },
              release_date: {
                 validators: {
                  notEmpty: {
                        message: 'Date is required'
                    }
                }
              },

               app_id: {
                 validators: {
                  notEmpty: {
                        message: 'Date is required'
                    }
                }
              },
                files: {
                  validators: {
                  notEmpty: {
                        message: 'Select File to upload'
                    }
                }
            }

        }

    });

});
</script>    



{{ Form::open(array('url' => 'appVersion/update/'.$data->id,'id'=>'form-EditAppVersion','files'=>true,'enctype'=>'multipart/form-data')) }}
{{ Form::hidden('_method', 'PUT') }}

 

                      <div class="row">
                        
                       <div class="form-group col-sm-6">
                          <label for="exampleInputEmail">DB Update *</label>
                          <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-key"></i></span>
                            <select name="dbupdate" required selected="selected" class="form-control">
                              <!-- <option  value="0" @if(isset($data->db_update_needed) && $data->db_update_needed=='') selected="selected" @endif>Please Select</option> -->
                              <option  value="1" @if(isset($data->db_update_needed) && $data->db_update_needed==1) selected="selected" @endif>Yes</option>
                              <option  value="0" @if(isset($data->db_update_needed) && $data->db_update_needed==0) selected="selected" @endif>No</option>
                             </select>
                          </div>
                        </div>

                        <div class="form-group col-sm-6">
                          <label for="exampleInputEmail">Configuration Reset *</label>
                          <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-clipboard"></i></span>
                           <!-- <input type="text"  id="configreset" name="configreset" placeholder="Configuration Reset" class="form-control"> -->
                           <select name="configreset" required selected="selected" class="form-control">
                            <!-- <option  value="0" @if(isset($data->config_reset) && $data->config_reset=='') selected="selected" @endif>Please Select</option> -->
                              <option  value="1" @if(isset($data->config_reset) && $data->config_reset==1) selected="selected" @endif>Yes</option>
                              <option  value="0" @if(isset($data->config_reset) && $data->config_reset==0) selected="selected" @endif>No</option>
                          </select>
                          </div>
                        </div>
                       </div>
                      <div class="row">
                        <div class="form-group col-sm-6">
                          <label for="exampleInputEmail">Latest Version *</label>
                          <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-document-text"></i></span>
                           <input type="text"  id="version" name="version" value="{{$data->latest_version}}" class="form-control" >
                          </div>
                        </div>

                        <div class="form-group col-sm-6">
                          <label for="exampleInputEmail">Release Date *</label>
                          <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-clipboard"></i></span>
                           <input type="text"  id="release_date" name="release_date" value="{{$data->release_date}}" class="form-control">
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="form-group col-sm-6">
                          <label for="exampleInputEmail">Application Id *</label>
                          <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-document-text"></i></span>
                           <input type="text"  id="app_id" name="app_id" value="{{$data->app_id}}" class="form-control" >
                          </div>
                        </div>


                      <!-- <div class="form-group col-sm-4" >
                        <div class="image-block">
                        </div>
                          <div class="input-group input-group-sm">
                              <span class="btn btn-success fileinput-button">
                                  <i class="glyphicon glyphicon-plus"></i>
                                  <span>Upload Image </span>
                                  <input id="fileupload" type="file" name="files[]" multiple>
                              </span>
                          </div>
                    </div> -->
                </div>

         {{ Form::submit('Submit', array('class' => 'btn btn-warning'))}}
         {{ Form::close() }}
