<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjFrontMobile extends pjAppMobileController
{	
	public $defaultCaptcha = 'BusReservation_Captcha';
	
	public $defaultLocale = 'BusReservation_LocaleId';
	
	public $defaultStore = 'BusReservation_Store';
	
	public $defaultForm = 'BusReservation_Form';
	
	public $defaultStep = 'BusReservation_Step';
	
	public function __construct()
	{
		$this->setLayout('pjActionFront');
		self::allowCORS();
	}
	
	public function afterFilter()
	{		
		if (!isset($_GET['hide']) || (isset($_GET['hide']) && (int) $_GET['hide'] !== 1) &&
			in_array($_GET['action'], array('pjActionSearch', 'pjActionSeats', 'pjActionCheckout', 'pjActionPreview', 'pjActionDone')))
		{
			$locale_arr = pjLocaleModel::factory()->select('t1.*, t2.file, t2.title')
				->join('pjLocaleLanguage', 't2.iso=t1.language_iso', 'left')
				->where('t2.file IS NOT NULL')
				->orderBy('t1.sort ASC')->findAll()->getData();
			
			$this->set('locale_arr', $locale_arr);
		}
	}
	
	public function beforeFilter()
	{
		$OptionModel = pjOptionModel::factory();
		$this->option_arr = $OptionModel->getPairs($this->getForeignId());
		$this->set('option_arr', $this->option_arr);
		$this->setTime();

		$forced = false;
		if (!isset($_SESSION[$this->defaultLocale]))
		{
		    $locale_arr = pjLocaleModel::factory()->where('is_default', 1)->limit(1)->findAll()->getData();
		    if (count($locale_arr) === 1)
		    {
		        $this->setLocaleId($locale_arr[0]['id']);
		        $forced = true;
		    }
		}
		if(isset($_GET['locale']) && (int) $_GET['locale'] > 0 && (!isset($_SESSION[$this->defaultLocale]) || (isset($_SESSION[$this->defaultLocale]) && $_SESSION[$this->defaultLocale] != (int) $_GET['locale'] ) ) )
		{
		    $_SESSION[$this->defaultLocale] = (int) $_GET['locale'];
		    $forced = true;
		}
		
		if (!in_array($_GET['action'], array('pjActionLoadCss')))
		{
		    if($_GET['action'] == 'pjActionLoad' && isset($_SESSION[$this->defaultLocale]))
		    {
		        $forced = true;
		    }
		    $this->loadSetFields($forced);
		}
	}
	
	public function beforeRender()
	{
		
	}
	
	public function pjActionLocale()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			if (isset($_GET['locale_id']))
			{
				$this->pjActionSetLocale($_GET['locale_id']);
				$this->loadSetFields(true);
				pjAppController::setFields($this->getLocaleId());
				
				$day_names = __('day_names', true);
				ksort($day_names, SORT_NUMERIC);
				
				$months = __('months', true);
				ksort($months, SORT_NUMERIC);
				
				pjAppController::jsonResponse(array('status' => 'OK', 'code' => 200, 'text' => 'Locale have been changed.', 'opts' => array(
					'day_names' => array_values($day_names),
					'month_names' => array_values($months)
				)));
			}
		}
		exit;
	}
	
	public function pjActionGetLocale()
	{
		return isset($_SESSION[$this->defaultLocale]) && (int) $_SESSION[$this->defaultLocale] > 0 ? (int) $_SESSION[$this->defaultLocale] : FALSE;
	}
		
		
	public function isXHR()
	{
		return parent::isXHR() || isset($_SERVER['HTTP_ORIGIN']);
	}
	
	protected static function allowCORS()
	{
		$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
		header('P3P: CP="ALL DSP COR CUR ADM TAI OUR IND COM NAV INT"');
		header("Access-Control-Allow-Origin: $origin");
		header("Access-Control-Allow-Credentials: true");
		header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
		header("Access-Control-Allow-Headers: Origin, X-Requested-With");
	}
	
	protected function _get($key)
	{
		if ($this->_is($key))
		{
			return $_SESSION[$this->defaultStore][$key];
		}
		return false;
	}
	
	protected function _is($key)
	{
		return isset($_SESSION[$this->defaultStore]) && isset($_SESSION[$this->defaultStore][$key]);
	}
	
	protected function _set($key, $value)
	{
		$_SESSION[$this->defaultStore][$key] = $value;
		return $this;
	}
	
	private function pjActionSetLocale($locale)
	{
		if ((int) $locale > 0)
		{
			$_SESSION[$this->defaultLocale] = (int) $locale;
		}
		return $this;
	}
}
?>