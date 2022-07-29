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
          
           channel_id: {
                validators: {
                  notEmpty: {
                        message: 'channel_id is required'
                    }
                }
            },
             channnel_name: {
                validators: {
                  notEmpty: {
                        message: 'channnel_name is required'
                    }
                }
            },
            channel_url: {
                validators: {
                  notEmpty: {
                        message: 'channel_url is required'
                    }
                }
            },
        }
    });

});
</script>





 {{ Form::open(array('url' => 'gdschannels/update/'.$channel_data->channel_id , 'class'=>'form-editcategory','id'=>'form-editcategory' )) }}

                            {{ Form::hidden('_method', 'PUT') }}


                            <div class="row">
                      <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Channel Id</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                          <input type="text" id="channel_id" name="channel_id"  value="{{$channel_data->channel_id}}" class="form-control "  readonly>
                        
                      </div>
                    </div>
                                      
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Channel Name</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                          <input type="text" id="channnel_name" name="channnel_name" value="{{$channel_data->channnel_name}}" class="form-control "  aria-describedby="basic-addon1">
                        </div>
                      </div>
</div>
                     <div class="row">                                           
                      <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Channel Url</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                          <input type="text"  id="channel_url" name="channel_url" value="{{$channel_data->channel_url}}" class="form-control"  aria-describedby="basic-addon1" required>
                      </div>
                      </div>
                    <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Price Url</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                          <input type="text"  id="price_url" name="price_url" value="{{$channel_data->price_url}}" class="form-control"  aria-describedby="basic-addon1" required>
                      </div>
                      </div>
                    
                   
                    </div>
                    <div class = "row">
                     <div class="form-group col-sm-6">
                        <label for="exampleInputEmail">Tnc Url</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                          <input type="text"  id="tnc_url" name="tnc_url" value="{{$channel_data->tnc_url}}" class="form-control"  aria-describedby="basic-addon1" required>
                      </div>
                      </div>
                    </div>
                  
                        {{ Form::submit('Update', array('class' =>'btn btn-warning'))}}

                        {{ Form::close() }}




 </body>
 </html>