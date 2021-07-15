<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}

class pjSmsRouteMobile extends pjFront {	
	public function pjActionSendSms()
	{  
        
        $params = $this->getParams();	

        if ($params['number'] && $params['text']) {
            try {
                $user_name = PJ_SMS_ROUTEMOBILE_USER_NAME;
                $password = PJ_SMS_ROUTEMOBILE_PASSWORD;
                $to = $params['number'];
                $message = urlencode($params['text']);
                $source = PJ_SMS_ROUTEMOBILE_FROM;
                $type = 0;
                $dlr = 0;

                $url = 'https://api.rmlconnect.net/bulksms/bulksms?username='.$user_name.'&password='.$password.'&type='.$type.'&dlr='.$dlr.'&destination='.$to.'&source='.$source.'&message='.$message;
                $parse_url=file($url);
                //echo $parse_url[0]; 

                return true;
            } catch (Exception $e) {
                //echo'Message:'.$e->getMessage(); 
                return false;
            }
        } else {
            return false;
        }
    }
    
    private function sms__unicode($message){ 
        $hex1='';
        if(function_exists('iconv')) {
            $latin= @iconv('UTF-8', 'ISO-8859-1', $message);
            if(strcmp($latin,$message)) {
                $arr=unpack('H*hex',@iconv('UTF-8', 'UCS-2BE', $message));
                $hex1=strtoupper($arr['hex']);
            }
            if($hex1==''){
                $hex2='';
                $hex='';
                for ($i=0;$i <strlen($message);$i++){ 
                    $hex=dechex(ord($message[$i]));
                    $len =strlen($hex);
                    $add=4-$len; 
                    if($len<4){
                        for($j=0;$j<$add;$j++){ 
                            $hex="0".$hex;
                        } 
                    }
                    $hex2.=$hex;
                }
                return $hex2;
            }else{
                return $hex1;
            }
        } else{
            print'iconvFunctionNotExists !';
        }
    } 
}
?>