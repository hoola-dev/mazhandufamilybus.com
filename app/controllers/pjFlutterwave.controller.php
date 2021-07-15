<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjFlutterwave extends pjFront {	
	public function pjActionFlutterwaveForm()
	{
		$this->setLayout('pjActionEmpty');
		
		$this->setAjax(true);
		//KEYS:
		//-------------
		//name
		//id
		//business
		//item_name
		//custom
		//amount
		//currency_code
		//return
		//notify_url
		//submit
		//submit_class
		//target
		$this->set('arr', $this->getParams());
	}
}
?>