<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjContact extends pjFront
{
	public function __construct()
	{
		parent::__construct();
		
		//$this->setAjax(true);
		
		$this->setLayout('pjActionContact');
	}
	public function pjActionList()
	{
	    $this->appendCss('bootstrap.min.css?ver=4.4.1');
	    $this->appendCss('new-style.css');
	    $this->appendCss('animate.css');
	    $this->appendCss('new-style-blue.css');
        $this->appendJs('jquery.js');
	    
        $this->set('base_url', PJ_INSTALL_URL);	
	}
}
?>
