 {{ Form::open(array('url' => 'gdschannels/updatecred/'.$credential_data[0]->channel_id)) }}

                            {{ Form::hidden('_method', 'PUT') }}

 

 @if(count($credential_data)>0)
 @foreach ($credential_data as $cred)
                    <input type="hidden" name="channel_configuration_id[]" value="{{ $cred->channel_configuration_id }}">
                    <div class="row">
                   
                    </div>
                       <div class="row" id= "New">                
                      <div class="form-group col-sm-5">
                        <label for="exampleInputEmail">Key Name</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                          <input type="text" id="Key_name" name="Key_name[]" value="{{$cred->Key_name}}" class="form-control required"  aria-describedby="basic-addon1" required>
                        </div>
                      </div>
                      

                                                               
                      <div class="form-group col-sm-5">
                        <label for="exampleInputEmail">Key Value</label>
                        <div class="input-group ">
                          <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
                          <input type="text"  id="Key_value" name="Key_value[]" value="{{$cred->Key_value}}" class="form-control"  aria-describedby="basic-addon1" required>
                      </div>
                      </div>
                    
                    <div class="form-group col-sm-1">
                             <button style="margin-top: 36px;" type="button" class="close close_div" >×</button>
                        </div>
                   
                    </div>
                    
                    @endforeach
                    @endif

                      <div class="row">
                     
                      <div class="form-group col-sm-6">
                      <a href="#" title="" class="add-author">Add Credential</a>
                      </div>
                      
                     
<br/><br/>
<div class="form-group col-sm-1">
{{ Form::submit('Update', array('class' => 'btn btn-warning')) }}
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

        $(this).closest('div.row').before($('<div/>',{"class":"row", html:row.html()}) );

        $(".close_div").on("click", function(){
          // console.log("sadf");
          $(this).parent().parent().remove();
          console.log($(this).parent().parent().remove());
        });
    });

    
});
</script>