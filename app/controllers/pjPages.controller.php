<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjPages extends pjFront
{
	public function __construct()
	{
		parent::__construct();
		
		//$this->setAjax(true);
		
		$this->setLayout('pjActionInner');
	}
	public function pjActionFAQ()
	{
	  
	    $this->appendCss('bootstrap.min.css?ver=4.4.1');
	    $this->appendCss('new-style.css');
	    $this->appendCss('animate.css');
	    $this->appendCss('new-style-blue.css');
        $this->appendJs('jquery.js');
	    $this->set('base_url', PJ_INSTALL_URL);	    
	}
	public function pjActionTerms()
	{
	    
	    $this->appendCss('bootstrap.min.css?ver=4.4.1');
	    $this->appendCss('new-style.css');
	    $this->appendCss('animate.css');
	    $this->appendCss('new-style-blue.css');
        $this->appendJs('jquery.js');

        $lang_terms = pjMultiLangModel::factory()->select('t1.*')
            ->where('t1.model','pjOption')
            ->where('t1.locale', 1)
            ->where('t1.field', 'o_terms')
            ->limit(0, 1)
            ->findAll()->getData();

        $terms = (count($lang_terms) === 1) ? $lang_terms[0]['content'] : '';
            
        $this->set('base_url', PJ_INSTALL_URL);
        $this->set('terms', $terms);
	}
	public function pjActionPrivacy()
	{
	    
	    $this->appendCss('bootstrap.min.css?ver=4.4.1');
	    $this->appendCss('new-style.css');
	    $this->appendCss('animate.css');
	    $this->appendCss('new-style-blue.css');
        $this->appendJs('jquery.js');

        $lang_privacy = pjMultiLangModel::factory()->select('t1.*')
            ->where('t1.model','pjOption')
            ->where('t1.locale', 1)
            ->where('t1.field', 'o_policy')
            ->limit(0, 1)
            ->findAll()->getData();

        $privacy = (count($lang_privacy) === 1) ? $lang_privacy[0]['content'] : '';

        $this->set('base_url', PJ_INSTALL_URL);
        $this->set('privacy', $privacy);
	}
	public function pjActionDisclaimer()
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
