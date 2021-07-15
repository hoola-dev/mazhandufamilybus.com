<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}

class pjSmsTumani extends pjFront {	
	public function pjActionSendSms()
	{  
        
        $params = $this->getParams();	

        if ($params['number'] && $params['text']) {
            try {
                $user_id = PJ_SMS_TUMANI_USER_ID;
                $key = PJ_SMS_TUMANI_KEY;

                $input_xml = '<REQUEST>
                    <REQUESTID>'.time().'</REQUESTID>
                    <REQUESTTYPE>message</REQUESTTYPE>
                    <DATE>'.date('d-m-Y H:i:s').'</DATE>
                    <AUTHENTICATION>
                    <USERID>'.$user_id.'</USERID>
                    <KEY>'.$key.'</KEY>
                    </AUTHENTICATION>
                    <MESSAGE>
                    <ORIGINATORID>tumanisms</ORIGINATORID>
                    <MSISDN>'.$params['number'].'</MSISDN>
                    <TEXT>'.$params['text'].'</TEXT>
                    </MESSAGE>
                    </REQUEST>';

                $url = 'http://41.175.8.68:8181/bulksms/sms/index.php';
                //$url = 'http://41.175.8.69:8181/bulksms/sms/index.php';
                
                $headers = array(
                    'Content-Type: text/xml',
                    'Authorization: Basic '. base64_encode("$user_id:$key")
                );
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $input_xml);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                
                $data = curl_exec($ch);
                curl_close($ch);
                
                //convert the XML result into array
                $array_data = json_decode(json_encode(simplexml_load_string($data)), true);

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