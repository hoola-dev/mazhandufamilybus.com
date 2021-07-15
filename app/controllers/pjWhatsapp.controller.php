<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}

class pjWhatsapp extends pjFront {	
	public function pjActionSendMsg()
	{  
        
        $params = $this->getParams();	

        if ($params['number'] && $params['text']) {
            try {
                $phone = (strpos($params['number'],'+') !== false) ? substr($params['number'],1) : $params['number'];

                $url = "http://139.59.90.11/api?token=".PJ_WHATSAPP_TOKEN."&message=".urlencode($params['text'])."&to=".$phone;
                $CURL = curl_init();
                curl_setopt($CURL, CURLOPT_URL, $url); 
                curl_setopt($CURL, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); 
                curl_setopt($CURL, CURLOPT_POST, 0); 
                curl_setopt($CURL, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($CURL, CURLOPT_RETURNTRANSFER, true);
                $full_response = curl_exec($CURL);               

                $response = json_decode($full_response,true);

                // if (curl_errno($CURL)) {
                //     $error_msg = curl_error($CURL);
                //     echo $error_msg;
                // }

                curl_close($CURL);

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