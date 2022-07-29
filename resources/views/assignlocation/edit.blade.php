{{ Form::open(array('url' => 'assignlocation/update/'.$eseal->id, 'id'=>'editassignproduct')) }}
{{ Form::hidden('_method', 'PUT') }}
<div class="row">    
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Product</label>
         <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
            <select name="product_id" id="product_id" class="form-control" >
              @foreach($products as $key => $value)
              @if($key==$eseal->product_id)
              <option value="{{ $key}}" selected="selected">{{ $value}}</option>
              @else
              <option value="{{ $key}}">{{ $value}}</option>
              @endif
              @endforeach
            </select>
        </div>
    </div>
    <div class="form-group col-sm-6">
        <label class="control-label" for="exampleInputEmail">Product Location</label>
         <div class="input-group ">
            <span class="input-group-addon addon-red"><i class="fa fa-user"></i></span>
            <input type="text" name="locator" id="locator" value="{{$eseal->locator}}" class="form-control" readonly="true">
         </div>
    </div>
</div>
{{ Form::submit('Update Location', array('class' => 'btn btn-primary')) }}
{{ Form::close() }}

@section('style')
    {{HTML::style('css/style.css')}}
@stop

@section('script')
    {{HTML::script('js/jquery.js')}}
@stop
<script type="text/javascript">
  $(document).ready(function(){
        $('#editassignproduct').bootstrapValidator({
            message: 'This value is not valid',
            feedbackIcons: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                product_id: {
                    validators: {
                        callback: {
                            message: 'Please select Product',
                            callback: function(value, validator, $field){
                            var options = $('#editassignproduct [id="product_id"]').val();
                            return (options != 0);
                          }
                        }
                    }
                }
            }
        }).on('success.form.bv', function(event) {
            return true;
        });
    });
</script>

