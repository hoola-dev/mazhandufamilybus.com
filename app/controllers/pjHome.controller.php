<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjHome extends pjFront
{
	public function __construct()
	{
		parent::__construct();
		
		//$this->setAjax(true);
		
		$this->setLayout('pjActionHome');
	}
	public function pjActionHome()
	{
	    $this->appendCss('bootstrap.min.css?ver=4.4.1');
	    $this->appendCss('new-style.css');
	    $this->appendCss('animate.css');
        $this->appendCss('new-style-blue.css');
        $this->appendCss('chosen.css', PJ_THIRD_PARTY_PATH . 'chosen/');
        $this->appendJs('jquery.js');
        $this->appendJs('chosen.jquery.js', PJ_THIRD_PARTY_PATH . 'chosen/');  
        $this->appendJs('booking.js?'.PJ_CSS_JS_VERSION);      
        
        $_SESSION['site_common_message'] = ''; //reset the booking message
        
        $districts = pjDistrictModel::factory()->orderBy('t1.dt_name ASC')->findAll()->getData();
        $district_id = $districts[0]['dt_id'];

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

        $country_arr = pjCountryModel::factory()
            ->select('t1.id, t2.content AS country_title,t1.phone_code')
            ->join('pjMultiLang', "t2.model='pjCountry' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
            ->orderBy('`country_title` ASC')->findAll()->getData();
            
        $this->set('country_arr', $country_arr);

        $news = pjNewsModel::factory()
            ->where('t1.nw_is_active =',1)
            ->findAll()->getData();
	    
        $this->set('base_url', PJ_INSTALL_URL);	 
        $this->set('news', $news);	   
	}
	public function pjActionLipila()
	{
		$this->setAjax(true);
		 $response = array(
                    'status' => 'true',
                    'message' => 'Callback for Lipila.'
                ); 
         
        
        pjAppController::jsonResponse($response);
	 
	}
}
?>
