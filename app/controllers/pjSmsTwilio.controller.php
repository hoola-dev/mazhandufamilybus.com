<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
require_once('app/lib/twilio/src/Twilio/autoload.php');
use Twilio\Rest\Client;

class pjSmsTwilio extends pjFront {	
	public function pjActionSendSms()
	{  
        
        $params = $this->getParams();	

        if ($params['number'] && $params['text']) {
            
            $client = new Client(PJ_SMS_Twilio_ACCOUNT_SID, PJ_SMS_Twilio_AUTH_TOKEN);

            try {
                // Use the client to do fun stuff like send text messages!
                    $response = $client->messages->create(
                        // the number you'd like to send the message to
                        $params['number'],
                        array(
                            // A Twilio phone number you purchased at twilio.com/console
                            'from' => PJ_SMS_Twilio_From_Number,
                            // the body of the text message you'd like to send
                            'body' => $params['text']
                        )
                    );

                return true;
            } catch (Exception $e) {
                //echo 'Caught exception: ',  $e->getMessage(), "\n";
                return false;
            }
        } else {
            return false;
        }
	}
}
?>