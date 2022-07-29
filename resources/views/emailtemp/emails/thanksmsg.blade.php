<html lang="en-US">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <div> 
            <p>  Hello {{ $firstname}} {{ $lastname}},</p>
            <br />      
            <p>Congratulations! You have successfully registered with eSeal.</p>
            <br />         
            <p>We are glad you chose the leading commerce platform providing distribution, technology, logistics, credit and payment solutions.</p>
            <p>You can login now with your credentials.</p>
            @if($admin_approved)
            <p>Your password is {{ $password }}.</p>
            @endif
            <br />
            <p>
                <div>Should you need any help or have a question, please feel free to reply to this email or reach out to our customer support at</div>

                <div>    Toll Free: 1-800-300-23305 </div>
                <div>    Chat: https://esealinc.com/chat </div>

                <div>    Email: support@esealinc.com</div>
            </p>    
            
        </div>
    </body>
</html>
    