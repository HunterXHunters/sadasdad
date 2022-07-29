<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" integrity="sha256-NuCn4IvuZXdBaFKJOAcsU2Q3ZpwbdFisd5dux4jkQ5w=" crossorigin="anonymous" />
<!------ Include the above in your HEAD tag ---------->

<style>
    body {
        background: #dedede;
    }
    .page-wrap {
        min-height: 100vh;
    }
</style>

<div class="page-wrap d-flex flex-row align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12 text-center">
                <span class="display-1 d-block"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></span>
                <div class="mb-4 lead">Connecting to Authorization Server. </div>
                <a href={{url('/authorize/abort')}} class="btn btn-link">Abort Authorization</a>
            </div>
        </div>
    </div>
</div>

<script>
    let userID = "<?php echo $encryptUserID ?>";
    let customerID = "<?php echo $encryptCustomerID ?>";
   let redURL = '/login/authorize/grant?subscriberID='+ userID +'&tenantID='+ customerID;
    
    setTimeout(function() {
        window.history.pushState({}, null, "<?php echo url('/') ?>" + redURL);
    }, 2000);
    
    window.location = "<?php echo url('/') ?>" + redURL;
    
</script>