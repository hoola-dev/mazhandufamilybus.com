<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAppAgent extends pjFront
{
	public function __construct()
	{
		parent::__construct();
		
		//$this->setAjax(true);
		
		$this->setLayout('pjActionAppAgent');
	}
	public function pjActionList()
	{
	    $this->appendCss('bootstrap.min.css?ver=4.4.1');
	    $this->appendCss('new-style.css');
	    $this->appendCss('animate.css');
	    $this->appendCss('new-style-blue.css');
        $this->appendJs('jquery.js');
        
        $districts = pjDistrictModel::factory()->orderBy('t1.dt_name ASC')->findAll()->getData();
        $district_id = $_GET['district'];

        $agents = pjUserModel::factory()
        ->where('role_id',2)
        ->where('status','T')
        ->where('district',$district_id)
        ->limit(15)
        ->findAll()
        ->getData();

        $this->set('districts',$districts);
        $this->set('district_id',$district_id);
        $this->set('agents',$agents);
	    
        $this->set('base_url', PJ_INSTALL_URL);	
	}
}
?>
