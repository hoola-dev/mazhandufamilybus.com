<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjSmsTwilio extends pjFront {	
	public function pjActionSendBookingSms()
	{
		require_once('app/lib/twilio/src/Twilio/autoload.php');
		
		// Use the REST API Client to make requests to the Twilio REST API
        use Twilio\Rest\Client;

        // Your Account SID and Auth Token from twilio.com/console
        // $sid = 'AC6e2d4f3f53ec38317904826ccd65072d';
        // $token = '1f1e30e4f93fc5803345216377a2d5ea';
        $client = new Client(PJ_SMS_Twilio_ACCOUNT_SID, PJ_SMS_Twilio_AUTH_TOKEN);

        try {
            // Use the client to do fun stuff like send text messages!
            $response = $client->messages->create(
                // the number you'd like to send the message to
                '+919447580215',
                array(
                    // A Twilio phone number you purchased at twilio.com/console
                    'from' => '+12026014900',
                    // the body of the text message you'd like to send
                    'body' => "Hey Babu! Good luck on the bar exam!"
                )
            );
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
	}
}
?>