<?php

Class CommunicationController extends BaseController{

     public function sendLogEmail(){
      try{
           $status = 0;
           $sub = Input::get('subject');
           $body = Input::get('body');

           if(!empty($sub) && !empty($body)){
   				$status1 = Mail::send('emails.tracker', array('body' => $body), function($message) use ($sub)
				{
				    $message->to('app.support@esealinc.com')->subject($sub);
				});

				if($status1)
					throw new Exception('couldnt send email tho the email id '.$email);            
				else{
					$status =1;
					$message = 'A log mail has been successfully sent to the tracker team.';         
				}
			}
			else{
				throw new Exception ('Parameters are empty.');
			}
      }
      catch(Exception $e){
        $message = $e->getMessage();
      }
      return Response::json(['Status'=>$status,'Message'=>$message]);
     }


	}