<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjSendSms extends pjFront
{
	public function pjActionSendSms() {
        $params = $this->getParams();
        
        $is_zambia_phone_number = $this->isZambiaPhoneNumber($params['number']);
        $is_namibia_phone_number = $this->isNamibiaPhoneNumber($params['number']);
        $is_dr_congo_phone_number = $this->isDRCongoPhoneNumber($params['number']);

        if ($is_zambia_phone_number || $is_namibia_phone_number || $is_dr_congo_phone_number) {
            $controller = 'pjSmsRouteMobile';            
        } else {
            $controller = 'pjSmsInfoBip';        
        }

        $result = $this->requestAction(array('controller' => $controller, 'action' => 'pjActionSendSms', 'params' => $params), array('return'));

        //whataspp
        $is_whatsapp = (isset($params['no_whatsapp']) && $params['no_whatsapp'] == 1) ? false : true;

        //if ($is_whatsapp) {
            $this->requestAction(array('controller' => 'pjWhatsapp', 'action' => 'pjActionSendMsg', 'params' => $params), array('return'));            
        //}

        return $result;
	}	
}
?>