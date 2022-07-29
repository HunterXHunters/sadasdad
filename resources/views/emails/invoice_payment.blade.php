<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <div>

			Your payment has been successful.  

			Total Invoice amount is {{$total}}.

			Amount recieved is {{$data['amount_recieved']}}.

			Balance amount to be paid is {{$total - $data['amount_recieved']}}.

		</div>

    </body>
</html>



