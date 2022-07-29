<script type="text/javascript">
$(document).ready(function() {
    $('#form-UploadAppVersion').bootstrapValidator({
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



{{ Form::open(array('url' => 'appVersion/store/','id'=>'form-UploadAppVersion', 'files'=>true,'enctype'=>'multipart/form-data')) }}
{{ Form::hidden('_method', 'POST') }}
                     
                      <div class="row">
                        
                       <div class="form-group col-sm-6">
                          <label for="exampleInputEmail">DB Update *</label>
                          <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-key"></i></span>
                            <select name="dbupdate" class="form-control">
                              <option value="">Please Select</option>
                              <option  value="1">Yes</option>
                              <option  value="0">No</option>
                            </select>
                          </div>
                        </div>

                        <div class="form-group col-sm-6">
                          <label for="exampleInputEmail">Configuration Reset *</label>
                          <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-clipboard"></i></span>
                           <!-- <input type="text"  id="configreset" name="configreset" placeholder="Configuration Reset" class="form-control"> -->
                           <select name="configreset" class="form-control">
                              <option value="">Please Select</option>
                              <option  value="1">Yes</option>
                              <option  value="0">No</option>
                            </select>
                          </div>
                        </div>
                       </div>
                      <div class="row">
                        <div class="form-group col-sm-6">
                          <label for="exampleInputEmail">Latest Version *</label>
                          <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-document-text"></i></span>
                           <input type="text"  id="version" name="version" placeholder="Version" class="form-control" >
                          </div>
                        </div>

                        <div class="form-group col-sm-6">
                          <label for="exampleInputEmail">Release Date *</label>
                          <div class="input-group ">
                            <span class="input-group-addon addon-red"><span class="glyphicon glyphicon-calendar"></span></span>
                           <input type="text"  id="release_date" name="release_date" placeholder="0000-00-00 00:00:00" class="form-control">
                          </div>
                        <!-- <div class="input-append date form_datetime" data-date="2013-02-21T15:25:00Z">
                            
                            <span class="input-group-addon addon-red"><i class="icon-remove"></i></span>
                            <span class="input-group-addon addon-red"><i class="icon-calendar"></i></span>
                            <input size="16" type="text" value="" name="release_date" id="release_date" readonly>
                        </div> -->
                    
                    <!--  <div class="col-sm-10">
                        <div class="input-group input-append date" id="datetimepicker2">
                            <span class="input-group-addon addon-red"><span class="glyphicon glyphicon-calendar"></span></span>
                            <input type="text" class="form-control" name="release_date" id="release_date" />                                    
                        </div> 

                    </div> -->
                 
                        </div>

                      </div>

                      <div class="row">
                        <div class="form-group col-sm-6">
                          <label for="exampleInputEmail">Application Id *</label>
                          <div class="input-group ">
                            <span class="input-group-addon addon-red"><i class="ion-document-text"></i></span>
                           <input type="text"  id="app_id" name="app_id" placeholder="Enter Application Id" class="form-control" >
                          </div>
                        </div>


                  <div class="form-group col-sm-4" >
                    <div class="image-block">
                    </div>
                  <div class="input-group input-group-sm">
                      <span class="btn btn-success fileinput-button">
                          <i class="glyphicon glyphicon-plus"></i>
                          <span>Upload Image </span>
                          <input id="fileupload" type="file" name="files" multiple>
                      </span>
                  </div>
                </div>
                </div>

                       {{ Form::submit('Submit', array('class' => 'btn btn-warning'))}}
                       {{ Form::close() }}





