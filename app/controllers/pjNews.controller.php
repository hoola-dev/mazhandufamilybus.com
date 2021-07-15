<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjNews extends pjFront
{
	public function __construct()
	{
		parent::__construct();
		
		//$this->setAjax(true);
		
		$this->setLayout('pjActionNews');
	}
	public function pjActionList()
	{
	    $this->appendCss('bootstrap.min.css?ver=4.4.1');
	    $this->appendCss('new-style.css');
	    $this->appendCss('animate.css');
	    $this->appendCss('new-style-blue.css');
        $this->appendJs('jquery.js');

        $news = pjNewsModel::factory()
            ->where('t1.nw_is_active =',1)
            ->findAll()->getData();
	    $this->set('news', $news);	 
	    
        $this->set('base_url', PJ_INSTALL_URL);	
	}
}
?>
