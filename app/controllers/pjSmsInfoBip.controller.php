<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}

class pjSmsInfoBip extends pjFront {	
	public function pjActionSendSms()
	{  
        
        $params = $this->getParams();	

        if ($params['number'] && $params['text']) {
            try {
                $user_name = PJ_SMS_INFOBIP_USER_NAME;
                $password = PJ_SMS_INFOBIP_PASSWORD;
                $to = $params['number'];
                $text = $params['text'];

                if (strpos($params['number'],'+91') !== false) {            
                    //$result = file_put_contents('message.txt', $text."\n\n\n----------------".PHP_EOL , FILE_APPEND | LOCK_EX);
                } 

                $from = PJ_SMS_INFOBIP_FROM;
                $destination = array(
                    "messageId" => time(), 
                    "to" => $to
                );
                $message = array(
                    "from" => $from,
                    "destinations" => array($destination),
                    "text" => $text
                );
                $postData = array(
                    "messages" => array($message)
                );
                // encoding object
                $postDataJson = json_encode($postData);

                $ch = curl_init();
                $url = 'https://api.infobip.com/sms/2/text/advanced';
                $header = array(
                    'Authorization: Basic '. base64_encode("$user_name:$password"),
                    "Content-Type: application/json",
                    "Accept: application/json"
                );
                // setting options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataJson);
                // response of the POST request
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $responseBody = json_decode($response);
                curl_close($ch);

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