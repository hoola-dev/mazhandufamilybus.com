<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAppController extends pjController
{
	public $models = array();
	
	public $defaultLocale = 'admin_locale_id';
	
	public $defaultFields = 'fields';
	
	public $defaultFieldsIndex = 'fields_index';
	
	public function isOneAdminReady()
	{
		return $this->isAdmin();
	}
	public function isCountryReady()
    {
    	return $this->isAdmin();
    }
	
	public static function setTimezone($timezone="UTC")
    {
    	if (in_array(version_compare(phpversion(), '5.1.0'), array(0,1)))
		{
			date_default_timezone_set($timezone);
		} else {
			$safe_mode = ini_get('safe_mode');
			if ($safe_mode)
			{
				putenv("TZ=".$timezone);
			}
		}
    }

	public static function setMySQLServerTime($offset="-0:00")
    {
		pjAppModel::factory()->prepare("SET SESSION time_zone = :offset;")->exec(compact('offset'));
    }
    
	public function setTime()
	{
		if (isset($this->option_arr['o_timezone']))
		{
			$offset = $this->option_arr['o_timezone'] / 3600;
			if ($offset > 0)
			{
				$offset = "-".$offset;
			} elseif ($offset < 0) {
				$offset = "+".abs($offset);
			} elseif ($offset === 0) {
				$offset = "+0";
			}
	
			pjAppController::setTimezone('Etc/GMT' . $offset);
			if (strpos($offset, '-') !== false)
			{
				$offset = str_replace('-', '+', $offset);
			} elseif (strpos($offset, '+') !== false) {
				$offset = str_replace('+', '-', $offset);
			}
			pjAppController::setMySQLServerTime($offset . ":00");
		}
	}
    
	public function isEditor()
    {
    	return $this->getRoleId() == 2;
    }

    public function isBusAdmin()
    {
    	return $this->getRoleId() == 3;
    }

    public function isSuperAgent()
    {
    	return ($_SESSION['admin_user']['role_id'] == 2 && $_SESSION['admin_user']['agent_type'] == 2);
    }

    public function isAgentCommission()
    {
    	return ($_SESSION['admin_user']['role_id'] == 2 && $_SESSION['admin_user']['agent_type'] != 1);
    }

    public function isPolice()
    {
    	return $this->getRoleId() == 4;
    }

    public function isIntelligenceService()
    {
    	return $this->getRoleId() == 5;
    }

    public function getName()
    {
    	return $_SESSION['admin_user']['name'];
    }

    public function getRoleName()
    {
    	return ($_SESSION['admin_user']['role_id'] == 2) ? $_SESSION['admin_user']['at_type'] : $_SESSION['admin_user']['role_name'];
    }

    public function isZambiaPhoneNumber($phone_number)
    {
        if (substr($phone_number,0,4) == '+260') {
            return true;
        } else if (substr($phone_number,0,3) == '260') {
            return true;
        } else {
            return false;
        }
    }

    public function isNamibiaPhoneNumber($phone_number)
    {
        if (substr($phone_number,0,4) == '+264') {
            return true;
        } else if (substr($phone_number,0,3) == '264') {
            return true;
        } else {
            return false;
        }
    }

    public function isDRCongoPhoneNumber($phone_number)
    {
        if (substr($phone_number,0,4) == '+243') {
            return true;
        } else if (substr($phone_number,0,3) == '243') {
            return true;
        } else {
            return false;
        }
    }
    
    public function getForeignId()
    {
    	return 1;
    }
    
    public function beforeFilter()
    {
    	$this->appendJs('jquery.min.js', PJ_THIRD_PARTY_PATH . 'jquery/');
    	$baseDir = defined("PJ_INSTALL_PATH") ? PJ_INSTALL_PATH : null;
    	$dm = new pjDependencyManager($baseDir, PJ_THIRD_PARTY_PATH);
    	$dm->load(PJ_CONFIG_PATH . 'dependencies.php')->resolve();
    	$this->appendJs('jquery-migrate.min.js', $dm->getPath('jquery_migrate'), FALSE, FALSE);
    	$this->appendJs('pjAdminCore.js');
    	$this->appendCss('reset.css');
    		
    	$this->appendJs('js/jquery-ui.custom.min.js', PJ_THIRD_PARTY_PATH . 'jquery_ui/');
    	$this->appendCss('css/smoothness/jquery-ui.min.css', PJ_THIRD_PARTY_PATH . 'jquery_ui/');
    
    	$this->appendCss('pj-all.css', PJ_FRAMEWORK_LIBS_PATH . 'pj/css/');
    	$this->appendCss('admin.css?'.PJ_CSS_JS_VERSION);
    
    	if ($_GET['controller'] != 'pjInstaller')
    	{
    		$this->models['Option'] = pjOptionModel::factory();
    		$this->option_arr = $this->models['Option']->getPairs($this->getForeignId());
    		$this->set('option_arr', $this->option_arr);
    		$this->setTime();
    
    		if (!isset($_SESSION[$this->defaultLocale]))
    		{
    			$locale_arr = pjLocaleModel::factory()->where('is_default', 1)->limit(1)->findAll()->getData();
    			if (count($locale_arr) === 1)
    			{
    				$this->setLocaleId($locale_arr[0]['id']);
    			}
    		}
    		$this->loadSetFields();
    	}
    }
        
    public static function setFields($locale)
    {
   	 	if(isset($_SESSION['lang_show_id']) && (int) $_SESSION['lang_show_id'] == 1)
		{
			$fields = pjMultiLangModel::factory()
				->select('CONCAT(t1.content, CONCAT(":", t2.id, ":")) AS content, t2.key')
				->join('pjField', "t2.id=t1.foreign_id", 'inner')
				->where('t1.locale', $locale)
				->where('t1.model', 'pjField')
				->where('t1.field', 'title')
				->findAll()
				->getDataPair('key', 'content');
		}else{
			$fields = pjMultiLangModel::factory()
				->select('t1.content, t2.key')
				->join('pjField', "t2.id=t1.foreign_id", 'inner')
				->where('t1.locale', $locale)
				->where('t1.model', 'pjField')
				->where('t1.field', 'title')
				->findAll()
				->getDataPair('key', 'content');
		}
		$registry = pjRegistry::getInstance();
		$tmp = array();
		if ($registry->is('fields'))
		{
			$tmp = $registry->get('fields');
		}
		$arrays = array();
		foreach ($fields as $key => $value)
		{
			if (strpos($key, '_ARRAY_') !== false)
			{
				list($prefix, $suffix) = explode("_ARRAY_", $key);
				if (!isset($arrays[$prefix]))
				{
					$arrays[$prefix] = array();
				}
				$arrays[$prefix][$suffix] = $value;
			}
		}
		require PJ_CONFIG_PATH . 'settings.inc.php';
		$fields = array_merge($tmp, $fields, $settings, $arrays);
		$registry->set('fields', $fields);
    }

    public static function jsonDecode($str)
	{
		$Services_JSON = new pjServices_JSON();
		return $Services_JSON->decode($str);
	}
	
	public static function jsonEncode($arr)
	{
		$Services_JSON = new pjServices_JSON();
		return $Services_JSON->encode($arr);
	}
	
	public static function jsonResponse($arr)
	{
		header("Content-Type: application/json; charset=utf-8");
		echo pjAppController::jsonEncode($arr);
		exit;
	}

	public function getLocaleId()
	{
		return isset($_SESSION[$this->defaultLocale]) && (int) $_SESSION[$this->defaultLocale] > 0 ? (int) $_SESSION[$this->defaultLocale] : false;
	}
	public function getDirection()
	{
		$dir = 'ltr';
		if($this->getLocaleId() != false)
		{
			$locale_arr = pjLocaleModel::factory()->find($this->getLocaleId())->getData();
			$dir = $locale_arr['dir'];
		}
		return $dir;
	}
	public function setLocaleId($locale_id)
	{
		$_SESSION[$this->defaultLocale] = (int) $locale_id;
	}
	
	public function friendlyURL($str, $divider='-')
	{
		$str = mb_strtolower($str, mb_detect_encoding($str));
		$str = trim($str);
		$str = preg_replace('/[_|\s]+/', $divider, $str);
		$str = preg_replace('/\x{00C5}/u', 'AA', $str);
		$str = preg_replace('/\x{00C6}/u', 'AE', $str);
		$str = preg_replace('/\x{00D8}/u', 'OE', $str);
		$str = preg_replace('/\x{00E5}/u', 'aa', $str);
		$str = preg_replace('/\x{00E6}/u', 'ae', $str);
		$str = preg_replace('/\x{00F8}/u', 'oe', $str);
		$str = preg_replace('/[^a-z\x{0400}-\x{04FF}0-9-]+/u', '', $str);
		$str = preg_replace('/[-]+/', $divider, $str);
		$str = preg_replace('/^-+|-+$/', '', $str);
		return $str;
	}
	
	public function pjActionCheckInstall()
	{
		$this->setLayout('pjActionEmpty');
		
		$result = array('status' => 'OK', 'code' => 200, 'text' => 'Operation succeeded', 'info' => array());
		$folders = array(
							'app/web/upload',
							'app/web/upload/bus_types'
						);
		foreach ($folders as $dir)
		{
			if (!is_writable($dir))
			{
				$result['status'] = 'ERR';
				$result['code'] = 101;
				$result['text'] = 'Permission requirement';
				$result['info'][] = sprintf('Folder \'<span class="bold">%1$s</span>\' is not writable. You need to set write permissions (chmod 777) to directory located at \'<span class="bold">%1$s</span>\'', $dir);
			}
		}
		
		return $result;
	}
	
	public function getData($option_arr, $booking_arr, $salt, $locale_id)
	{
		$country = NULL;
		if (isset($booking_arr['c_country']) && !empty($booking_arr['c_country']))
		{
			$country_arr = pjCountryModel::factory()
						->select('t1.id, t2.content AS country_title')
						->join('pjMultiLang', "t2.model='pjCountry' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$locale_id."'", 'left outer')
						->find($booking_arr['c_country'])->getData();
			if (!empty($country_arr))
			{
				$country = $country_arr['country_title'];
			}
		}
		
		$seats = '';
		$booked_seat_id_arr = pjBookingSeatModel::factory()
			->select("DISTINCT (seat_id)")
			->where('booking_id', $booking_arr['id'])
			->findAll()
			->getDataPair('seat_id', 'seat_id');
		if(!empty($booked_seat_id_arr))
		{
			$selected_seat_arr = pjSeatModel::factory()->whereIn('t1.id', $booked_seat_id_arr)->findAll()->getDataPair('id', 'name');
			$seats = join(", ", $selected_seat_arr);
		}
		
        $row = array();
        $row_sms = array();
		if (isset($booking_arr['tickets']))
		{
			$ticket_arr = $booking_arr['tickets'];
			foreach ($ticket_arr as $v)
			{
				if($v['qty'] > 0)
				{
					$price = $v['amount'] / $v['qty'];
					$amount = $v['amount'];
					if(isset($v['price']))
					{
						$price = $v['price'];
						$amount = $price * $v['qty'];
					}
                    $row[] = stripslashes($v['title']) . ' '.$v['qty'].' x '.pjUtil::formatCurrencySign(number_format($price, 2), $option_arr['o_currency']) . ' = ' . pjUtil::formatCurrencySign(number_format($amount, 2), $option_arr['o_currency']);
                    $row_sms[] = stripslashes($v['title']) . ' '.$v['qty'].' x '.$option_arr['o_currency'].' '.number_format($price, 2) . ' = ' . $option_arr['o_currency'].' '.number_format($amount, 2);
				}
			}
		}
        $tickets = count($row) > 0 ? join("<br/>", $row) : NULL;
        $tickets_sms = count($row_sms) > 0 ? join(" ", $row_sms) : NULL;
		
		$bus = @$booking_arr['route_title'] . ', ' . date($option_arr['o_time_format'], strtotime(@$booking_arr['departure_time'])) . ' - ' . date($option_arr['o_time_format'], strtotime(@$booking_arr['arrival_time']));
		$route = mb_strtolower(__('lblFrom', true), 'UTF-8') . ' ' . @$booking_arr['from_location'] . ' ' . mb_strtolower(__('lblTo', true), 'UTF-8') . ' ' . @$booking_arr['to_location'];
		
		$time = $booking_arr['booking_time'];
        $total = pjUtil::formatCurrencySign($booking_arr['total'], $option_arr['o_currency']);
        $total_sms = $option_arr['o_currency'].' '.$booking_arr['total'];
        $tax = pjUtil::formatCurrencySign($booking_arr['tax'], $option_arr['o_currency']);
        $tax_sms = $option_arr['o_currency'].' '.$booking_arr['tax'];
		
		$booking_date = NULL;
		if (isset($booking_arr['booking_date']) && !empty($booking_arr['booking_date']))
		{
			$tm = strtotime(@$booking_arr['booking_date']);
			$booking_date = date($option_arr['o_date_format'], $tm);
		}
        $personal_titles = __('personal_titles', true, false);        
        
        //cash booking cancel hour
        $option_cash_cancel_arr = pjOptionModel::factory()
            ->where("`key` = 'o_cash_min_hour'")
            ->findAll()
            ->getData();
        $cash_cancel_time = $option_cash_cancel_arr[0]['value'];
		
		$cancelURL = PJ_INSTALL_URL . 'index.php?controller=pjFrontEnd&action=pjActionCancel&id='.@$booking_arr['id'].'&hash='.sha1(@$booking_arr['id'].@$booking_arr['created'].$salt);
		$printURL = PJ_INSTALL_URL . 'index.php?controller=pjFrontTicket&action=pjActionPrintTicket&id='.@$booking_arr['id'].'&hash='.sha1(@$booking_arr['id'].@$booking_arr['created'].$salt);
		$cancelURL = '<a href="'.$cancelURL.'">'.$cancelURL.'</a>';
		$printURL = '<a href="'.$printURL.'">'.$printURL.'</a>';
		$search = array(
            '{Title}', 
            '{FirstName}', 
            '{LastName}', 
            '{Email}', 
            '{Phone}', 
            '{Country}',
            '{City}', 
            '{State}', 
            '{Zip}', 
            '{Address}',
            '{Company}', 
            '{CCType}', 
            '{CCNum}', 
            '{CCExp}',
            '{CCSec}', 
            '{PaymentMethod}',
            '{UniqueID}', 
            '{Date}', 
            '{Bus}', 
            '{Route}', 
            '{Seats}', 
            '{Time}', 
            '{TicketTypesPrice}',
            '{TicketTypesPrice_Sms}',
            '{Total}', 
            '{Total_Sms}', 
            '{Tax}',            
            '{Tax_Sms}', 
            '{Notes}',
			'{PrintTickets}',
            '{CancelURL}',
            '{Cash_Time}',
            '{Cash_SMS_Time}'
        );
		$replace = array(
            (!empty($booking_arr['c_title']) ? $personal_titles[$booking_arr['c_title']] : null), 
            $booking_arr['c_fname'], 
            $booking_arr['c_lname'], 
            $booking_arr['c_email'], 
            $booking_arr['c_phone'], 
            $country,
            $booking_arr['c_city'], 
            $booking_arr['c_state'], 
            $booking_arr['c_zip'], 
            $booking_arr['c_address'],
            $booking_arr['c_company'], 
            @$booking_arr['cc_type'], 
            @$booking_arr['cc_num'], 
            (@$booking_arr['payment_method'] == 'creditcard' ? @$booking_arr['cc_exp'] : NULL), 
            @$booking_arr['cc_code'], 
            @$booking_arr['payment_method'],
            @$booking_arr['uuid'], 
            $booking_date, 
            $bus, 
            $route, 
            $seats, 
            $time, 
            $tickets,
            $tickets_sms,
            @$total, 
            $total_sms, 
            $tax, 
            $tax_sms,
            @$booking_arr['c_notes'],
			$printURL,
            $cancelURL,
            $cash_cancel_time,
            date('F j - Y, g:i A',strtotime("+$cash_cancel_time hours"))
        );

		return compact('search', 'replace');
	}
	
	public function getTemplate($option_arr, $booking_arr, $salt, $locale_id)
	{
		$country = NULL;
		if (isset($booking_arr['c_country']) && !empty($booking_arr['c_country']))
		{
			$country_arr = pjCountryModel::factory()
						->select('t1.id, t2.content AS country_title')
						->join('pjMultiLang', "t2.model='pjCountry' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$locale_id."'", 'left outer')
						->find($booking_arr['c_country'])->getData();
			if (!empty($country_arr))
			{
				$country = $country_arr['country_title'];
			}
		}
		
		$seats = '';
		$booked_seat_id_arr = pjBookingSeatModel::factory()
			->select("DISTINCT (seat_id)")
			->where('booking_id', $booking_arr['id'])
			->findAll()
			->getDataPair('seat_id', 'seat_id');
		$booked_seat_id_arr = $booked_seat_id_arr;
		if(!empty($booked_seat_id_arr))
		{
			$selected_seat_arr = pjSeatModel::factory()->whereIn('t1.id', $booked_seat_id_arr)->findAll()->getDataPair('id', 'name');
			$seats = join(", ", $selected_seat_arr);
		}
		$row = array();
		if (isset($booking_arr['tickets']))
		{
			$ticket_arr = $booking_arr['tickets'];
			foreach ($ticket_arr as $v)
			{
				if($v['qty'] > 0)
				{
					$price = $v['amount']/$v['qty'];
					$row[] = stripslashes($v['title']) . ' '.$v['qty'].' x '.pjUtil::formatCurrencySign(number_format($price, 2), $option_arr['o_currency']);
				}
			}
		}
		$ticket_type = count($row) > 0 ? join("<br/>", $row) : NULL;

		$booking_route_arr = explode("<br/>", $booking_arr['booking_route']);
		$bus = $booking_route_arr[0];
		$route = $booking_route_arr[1];
		$time = $booking_arr['booking_time'];
		$total = pjUtil::formatCurrencySign($booking_arr['total'], $option_arr['o_currency']);
		$tax = pjUtil::formatCurrencySign($booking_arr['tax'], $option_arr['o_currency']);
		
		$time_arr = explode(" - ", $time);
		
		$booking_date = NULL;
		if (isset($booking_arr['booking_date']) && !empty($booking_arr['booking_date']))
		{
			$tm = strtotime(@$booking_arr['booking_date']);
			$booking_date = date($option_arr['o_date_format'], $tm);
		}
		$personal_titles = __('personal_titles', true, false);
		
		$cancelURL = PJ_INSTALL_URL . 'index.php?controller=pjFrontEnd&action=pjActionCancel&id='.@$booking_arr['id'].'&hash='.sha1(@$booking_arr['id'].@$booking_arr['created'].$salt);
		$search = array(
			'{Title}', '{FirstName}', '{LastName}', '{Email}', '{Phone}', '{Country}',
			'{City}', '{State}', '{Zip}', '{Address}',
			'{Company}', '{CCType}', '{CCNum}', '{CCExp}','{CCSec}', '{PaymentMethod}',
			'{UniqueID}', '{Date}', '{Bus}', '{Route}', '{Seat}', '{Time}',
			'{From_Location}', '{To_Location}', '{Departure_Time}', '{Arrival_Time}',
			'{TicketType}',
			'{Total}', '{Tax}', '{Notes}',
			'{CancelURL}');
		$replace = array(
			(!empty($booking_arr['c_title']) ? $personal_titles[$booking_arr['c_title']] : null), $booking_arr['c_fname'], $booking_arr['c_lname'], $booking_arr['c_email'], $booking_arr['c_phone'], $country,
			$booking_arr['c_city'], $booking_arr['c_state'], $booking_arr['c_zip'], $booking_arr['c_address'],
			$booking_arr['c_company'], @$booking_arr['cc_type'], @$booking_arr['cc_num'], (@$booking_arr['payment_method'] == 'creditcard' ? @$booking_arr['cc_exp'] : NULL), @$booking_arr['cc_code'], @$booking_arr['payment_method'],
			@$booking_arr['uuid'], $booking_date, $bus, $route, $seats, $time,
			@$booking_arr['from_location'], @$booking_arr['to_location'], @$time_arr[0], @$time_arr[1],
			$ticket_type,
			@$total, $tax, @$booking_arr['c_notes'],
			@$cancelURL);

		return compact('search', 'replace');
	}
	
	public function getAdminEmail()
	{
		$arr = pjUserModel::factory()->find(1)->getData();
		return $arr['email'];
	}
	public function getAdminPhone()
	{
		$arr = pjUserModel::factory()->find(1)->getData();
		return !empty($arr['phone']) ? $arr['phone'] : null;
	}
	public function getAllEmails()
	{
		$user_arr = pjUserModel::factory()->where('t1.status', 'T')->findAll()->getData();
		$arr = array();
		foreach($user_arr as $v)
		{
			if(!empty($v['email']))
			{
				$arr[] = $v['email'];
			}
		}
		return $arr;
	}
	public function getAllPhones()
	{
		$user_arr = pjUserModel::factory()->where('t1.status', 'T')->findAll()->getData();
		$arr = array();
		foreach($user_arr as $v)
		{
			if(!empty($v['phone']))
			{
				$arr[] = $v['phone'];
			}
		}
		return $arr;
    }
    
    public function getAdminEmails()
	{
		$user_arr = pjUserModel::factory()->where('t1.role_id', 1)->findAll()->getData();
		$arr = array();
		foreach($user_arr as $v)
		{
			if(!empty($v['email']))
			{
				$arr[] = $v['email'];
			}
		}
		return $arr;
	}
	public function getAdminPhones()
	{
		$user_arr = pjUserModel::factory()->where('t1.role_id', 1)->findAll()->getData();
		$arr = array();
		foreach($user_arr as $v)
		{
			if(!empty($v['phone']))
			{
				$arr[] = $v['phone'];
			}
		}
		return $arr;
	}

	public function getBusAvailability($bus_id, $store, $option_arr) {
		$pickup_id = $store ['pickup_id'];
		$return_id = $store ['return_id'];
		$booked_seat_arr = array ();
		$bus_type_arr = array ();
		
		$bus_arr = pjBusModel::factory ()->find ( $bus_id )->getData ();
		$departure_time = null;
		$arrival_time = null;
		if (isset ( $store ['booking_period'] [$bus_id] )) {
			if (isset ( $store ['booking_period'] [$bus_id] ['departure_time'] )) {
				$departure_time = $store ['booking_period'] [$bus_id] ['departure_time'];
			}
			if (isset ( $store ['booking_period'] [$bus_id] ['arrival_time'] )) {
				$arrival_time = $store ['booking_period'] [$bus_id] ['arrival_time'];
			}
		}
		$and_where = '';
		if ($departure_time != null && $arrival_time != null) {
			$and_where .= " AND ((TB.booking_datetime BETWEEN '$departure_time' AND '$arrival_time') OR (TB.stop_datetime BETWEEN '$departure_time' AND '$arrival_time' ) OR ('$departure_time' BETWEEN TB.booking_datetime AND TB.stop_datetime ) OR ('$arrival_time' BETWEEN TB.booking_datetime AND TB.stop_datetime ))";
		}
		
		if (! empty ( $bus_arr )) {
			$location_id_arr = pjRouteCityModel::factory ()->getLocationIdPair ( $bus_arr ['route_id'], $pickup_id, $return_id );
			
			$booked_seat_arr = pjBookingSeatModel::factory ()->select ( "DISTINCT seat_id" )->where ( "t1.booking_id IN(SELECT TB.id
										  FROM `" . pjBookingModel::factory ()->getTable () . "` AS TB
										  WHERE (TB.status='confirmed' OR ((TB.payment_method != 'cash' AND TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL " . $option_arr ['o_min_hour'] . " MINUTE))) OR (TB.payment_method = 'cash' AND TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL " . $option_arr ['o_cash_min_hour'] . " HOUR)))))
					AND TB.bus_id = $bus_id $and_where)
					AND start_location_id IN(" . join ( ",", $location_id_arr ) . ")" )->findAll ()->getDataPair ( "seat_id", "seat_id" );
			
			$bus_type_arr = pjBusTypeModel::factory ()->find ( $bus_arr ['bus_type_id'] )->getData ();
		}
		
		return compact ( 'booked_seat_arr', 'bus_type_arr' );
	}
	public function getReturnBusAvailability($bus_id, $store, $option_arr) {
		$pickup_id = $store ['return_id'];
		$return_id = $store ['pickup_id'];
		$booked_seat_arr = array ();
		$bus_type_arr = array ();
		
		$bus_arr = pjBusModel::factory ()->find ( $bus_id )->getData ();
		
		$departure_time = null;
		$arrival_time = null;
		if (isset ( $store ['booking_period'] [$bus_id] )) {
			if (isset ( $store ['booking_period'] [$bus_id] ['departure_time'] )) {
				$departure_time = $store ['booking_period'] [$bus_id] ['departure_time'];
			}
			if (isset ( $store ['booking_period'] [$bus_id] ['arrival_time'] )) {
				$arrival_time = $store ['booking_period'] [$bus_id] ['arrival_time'];
			}
		}
		$and_where = '';
		if ($departure_time != null && $arrival_time != null) {
			$and_where .= " AND ((TB.booking_datetime BETWEEN '$departure_time' AND '$arrival_time') OR (TB.stop_datetime BETWEEN '$departure_time' AND '$arrival_time' ) OR ('$departure_time' BETWEEN TB.booking_datetime AND TB.stop_datetime ) OR ('$arrival_time' BETWEEN TB.booking_datetime AND TB.stop_datetime ))";
		}
		if (! empty ( $bus_arr )) {
			$location_id_arr = pjRouteCityModel::factory ()->getLocationIdPair ( $bus_arr ['route_id'], $pickup_id, $return_id );
			
			$booked_seat_arr = pjBookingSeatModel::factory ()->select ( "DISTINCT seat_id" )->where ( "t1.booking_id IN(SELECT TB.id
										  FROM `" . pjBookingModel::factory ()->getTable () . "` AS TB                                            
                                              WHERE (TB.status='confirmed' OR ((TB.payment_method != 'cash' AND TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL " . $option_arr ['o_min_hour'] . " MINUTE))) OR (TB.payment_method = 'cash' AND TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL " . $option_arr ['o_cash_min_hour'] . " HOUR)))))
												  		AND TB.bus_id = $bus_id $and_where)
												  		AND start_location_id IN('" . join ( "', '", $location_id_arr ) . "')" )->findAll ()->getDataPair ( "seat_id", "seat_id" );
			
			$bus_type_arr = pjBusTypeModel::factory ()->find ( $bus_arr ['bus_type_id'] )->getData ();
		}
		
		return compact ( 'booked_seat_arr', 'bus_type_arr' );
	}
	public function isBusReady() {
		$cnt_cities = pjCityModel::factory ()->where ( 'status', 'T' )->findCount ()->getData ();
		$cnt_bus_types = pjBusTypeModel::factory ()->where ( 'status', 'T' )->findCount ()->getData ();
		$cnt_routes = pjRouteModel::factory ()->where ( 'status', 'T' )->findCount ()->getData ();
		$cnt_routes_cities = pjRouteCityModel::factory ()->findCount ()->getData ();
		$cnt_route_details = pjRouteDetailModel::factory ()->findCount ()->getData ();
		$cnt_buses = pjBusModel::factory ()->findCount ()->getData ();
		
		if ($cnt_cities > 0 && $cnt_bus_types > 0 && $cnt_routes > 0 && $cnt_routes_cities > 0 && $cnt_route_details > 0 && $cnt_buses > 0) {
			return true;
		} else {
			return false;
		}
	}
	public function getBusList($pickup_id, $return_id, $bus_id_arr, $booking_period, $booked_data, $date, $is_return) {
		$pjBusLocationModel = pjBusLocationModel::factory ();
		$pjPriceModel = pjPriceModel::factory ();
		$pjBookingSeatModel = pjBookingSeatModel::factory ();
		$pjBookingModel = pjBookingModel::factory ();
		$pjBusTypeModel = pjBusTypeModel::factory ();
		$pjRouteCityModel = pjRouteCityModel::factory ();
		$pjSeatModel = pjSeatModel::factory ();
		$pjCityModel = pjCityModel::factory ();
		
		$pickup_location = $pjCityModel->reset ()->select ( 't1.*, t2.content as name' )->join ( 'pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )->find ( $pickup_id )->getData ();
		$return_location = $pjCityModel->reset ()->select ( 't1.*, t2.content as name' )->join ( 'pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )->find ( $return_id )->getData ();
		
		$ticket_columns = 0;
		$booking_date = pjUtil::formatDate ( $date, $this->option_arr ['o_date_format'] );
		
		$bus_arr = pjBusModel::factory ()->join ( 'pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjBusType', "t3.id=t1.bus_type_id", 'left outer' )->select ( " t1.*, t2.content AS route, t3.seats_map, DATE_FORMAT(t1.departure_time,'%r') as f_departure_time, DATE_FORMAT(t1.arrival_time,'%r') as f_arrival_time")->where("(t1.route_id IN(SELECT `TR`.id FROM `".pjRouteModel::factory()->getTable()."` AS `TR` WHERE `TR`.status='T') )")->where ( "(t1.id IN(" . join ( ',', $bus_id_arr ) . "))" )->index ( "FORCE KEY (`bus_type_id`)" )->orderBy ( "route asc" )->findAll ()->getData ();
		
		$location_id_arr = array ();
		foreach ( $bus_arr as $k => $bus ) {
			$locations = $pjRouteCityModel->reset ()->join ( 'pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.city_id AND t2.field='name' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjBusLocation', "(t3.bus_id='" . $bus ['id'] . "' AND t3.location_id=t1.city_id", 'inner' )->select ( "t1.*, t2.content, t3.departure_time, t3.arrival_time" )->where ( 't1.route_id', $bus ['route_id'] )->orderBy ( "`order` ASC" )->findAll ()->getData ();
			
			$bus ['locations'] = $locations;
			
			if (! empty ( $bus ['start_date'] ) && ! empty ( $bus ['end_date'] )) {
				$bus ['from_to'] = pjUtil::formatDate ( $bus ['start_date'], "Y-m-d", $this->option_arr ['o_date_format'] ) . ' - ' . pjUtil::formatDate ( $bus ['end_date'], "Y-m-d", $this->option_arr ['o_date_format'] );
			} else {
				$bus ['from_to'] = '';
			}
			if (! empty ( $bus ['departure'] ) && ! empty ( $bus ['arrive'] )) {
				$bus ['depart_arrive'] = pjUtil::formatTime ( $bus ['departure'], "H:i:s", $this->option_arr ['o_time_format'] ) . ' - ' . pjUtil::formatTime ( $bus ['arrive'], "H:i:s", $this->option_arr ['o_time_format'] );
			} else {
				$bus ['depart_arrive'] = '';
			}
			$bus_arr [$k] = $bus;
			
			$bus_id = $bus ['id'];
			
			$seat_booked_arr = array ();
			$seat_avail_arr = array ();
			$departure_time = '';
			$arrival_time = '';
			$duration = '';
			
			$pickup_arr = $pjBusLocationModel->reset ()->where ( 'bus_id', $bus_id )->where ( "location_id", $pickup_id )->limit ( 1 )->findAll ()->getData ();
			$return_arr = $pjBusLocationModel->reset ()->where ( 'bus_id', $bus_id )->where ( "location_id", $return_id )->limit ( 1 )->findAll ()->getData ();
			
			if (! empty ( $pickup_arr )) {
				$departure_time = pjUtil::formatTime ( $pickup_arr [0] ['departure_time'], 'H:i:s', $this->option_arr ['o_time_format'] );
				$booking_period [$bus_id] ['departure_time'] = $booking_date . ' ' . $pickup_arr [0] ['departure_time'];
			}
			if (! empty ( $return_arr )) {
				$arrival_time = pjUtil::formatTime ( $return_arr [0] ['arrival_time'], 'H:i:s', $this->option_arr ['o_time_format'] );
			}
			if (! empty ( $pickup_arr ) && ! empty ( $return_arr )) {
				$seconds = 0;
				$start_count = false;
				foreach ( $locations as $key => $lo ) {
					$next_location = $locations [$key + 1];
					
					if ($lo ['city_id'] == $pickup_id) {
						$start_count = true;
					}
					if (isset ( $next_location ) && $start_count == true) {
						$seconds += pjUtil::calSeconds ( $lo ['departure_time'], $next_location ['arrival_time'] );
						if ($key + 1 < count ( $locations ) && $key > 0 && $lo ['city_id'] != $pickup_id) {
							$seconds += pjUtil::calSeconds ( $lo ['arrival_time'], $lo ['departure_time'] );
						}
					}
					if ($next_location ['city_id'] == $return_id) {
						break;
					}
				}
				
				$minutes = ($seconds / 60) % 60;
				$hours = floor ( $seconds / (60 * 60) );
				
				$hour_str = $hours . ' ' . ($hours != 1 ? strtolower ( __ ( 'front_hours', true, false ) ) : strtolower ( __ ( 'front_hour', true, false ) ));
				$minute_str = $minutes > 0 ? '<br/>' . ($minutes . ' ' . ($minutes != 1 ? strtolower ( __ ( 'front_minutes', true, false ) ) : strtolower ( __ ( 'front_minute', true, false ) ))) : '';
				$duration = $hour_str . $minute_str;
				
				if (isset ( $booking_period [$bus_id] ['departure_time'] )) {
					$booking_period [$bus_id] ['arrival_time'] = date ( 'Y-m-d H:i:s', strtotime ( $booking_period [$bus_id] ['departure_time'] ) + $seconds );
				}
			}
			
			$temp_location_id_arr = $pjRouteCityModel->getLocationIdPair ( $bus ['route_id'], $pickup_id, $return_id );
			
			if (! empty ( $booked_data )) {
				if ($is_return == 'F') {
					if ($booked_data ['bus_id'] == $bus_id && empty ( $location_id_arr )) {
						$location_id_arr = $temp_location_id_arr;
					}
				} else {
					if ($booked_data ['return_bus_id'] == $bus_id && empty ( $location_id_arr )) {
						$location_id_arr = $temp_location_id_arr;
					}
				}
			}
			
			if (! empty ( $temp_location_id_arr )) {
				$ticket_price_arr = $pjPriceModel->getTicketPrice ( $bus_id, $pickup_id, $return_id, $booked_data, $this->option_arr, $this->getLocaleId (), $is_return );
				$ticket_arr = $ticket_price_arr ['ticket_arr'];
				
				if ($bus ['set_seats_count'] == 'F') {
					$departure_time = null;
					$arrival_time = null;
					if (isset ( $booking_period [$bus_id] )) {
						if (isset ( $booking_period [$bus_id] ['departure_time'] )) {
							$departure_time = $booking_period [$bus_id] ['departure_time'];
						}
						if (isset ( $booking_period [$bus_id] ['arrival_time'] )) {
							$arrival_time = $booking_period [$bus_id] ['arrival_time'];
						}
					}
					$and_where = '';
					if ($departure_time != null && $arrival_time != null) {
						$and_where .= " AND ((TB.booking_datetime BETWEEN '$departure_time' AND '$arrival_time') OR (TB.stop_datetime BETWEEN '$departure_time' AND '$arrival_time' ) OR ('$departure_time' BETWEEN TB.booking_datetime AND TB.stop_datetime ) OR ('$arrival_time' BETWEEN TB.booking_datetime AND TB.stop_datetime ))";
					}
					$bus_type_arr = $pjBusTypeModel->reset ()->find ( $bus ['bus_type_id'] )->getData ();
					$seats_available = $bus_type_arr ['seats_count'];
					$seat_booked_arr = $pjBookingSeatModel->reset ()->select ( "DISTINCT t1.seat_id" )->where ( "t1.start_location_id IN(" . join ( ",", $temp_location_id_arr ) . ")
								AND t1.booking_id IN(SELECT TB.id
													FROM `" . $pjBookingModel->getTable () . "` AS TB
													WHERE (TB.status='confirmed'
															OR ((TB.payment_method != 'cash' AND TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL " . $this->option_arr ['o_min_hour'] . " MINUTE))) OR (TB.payment_method = 'cash' AND TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL " . $this->option_arr ['o_cash_min_hour'] . " HOUR)))))
						AND TB.bus_id = $bus_id $and_where)" )->findAll ()->getDataPair ( "seat_id", "seat_id" );
					
					$cnt_booked = count ( $seat_booked_arr );
					$seats_available -= $cnt_booked;
					$bus_arr [$k] ['seats_available'] = $seats_available;
				}
				if (count ( $ticket_arr ) > $ticket_columns) {
					$ticket_columns = count ( $ticket_arr );
				}
				$bus_arr [$k] ['ticket_arr'] = $ticket_arr;
			}
			
			$seats = $pjSeatModel->reset ()->where ( 't1.bus_type_id', $bus ['bus_type_id'] )->findAll ()->getData ();
			foreach ( $seats as $v ) {
				if (! in_array ( $v ['id'], $seat_booked_arr )) {
					$seat_avail_arr [] = $v ['id'] . '#' . $v ['name'];
				}
			}
			
			$bus_arr [$k] ['seat_avail_arr'] = $seat_avail_arr;
			$bus_arr [$k] ['departure_time'] = $departure_time;
			$bus_arr [$k] ['arrival_time'] = $arrival_time;
			$bus_arr [$k] ['duration'] = $duration;
		}
		
		$bus_type_arr = array ();
		$booked_seat_arr = array ();
		$seat_arr = array ();
		$selected_seat_arr = array ();
		
		if (! empty ( $booked_data ) && ! empty ( $location_id_arr )) {
			$bus_id = ($is_return == 'F' ? $booked_data ['bus_id'] : $booked_data ['return_bus_id']);
			
			$arr = pjBusModel::factory ()->find ( $bus_id )->getData ();
			$bus_type_arr = $pjBusTypeModel->reset ()->find ( $arr ['bus_type_id'] )->getData ();
			
			$booked_seat_arr = $pjBookingSeatModel->reset ()->select ( "DISTINCT seat_id" )->where ( "t1.booking_id IN(SELECT TB.id
										FROM `" . pjBookingModel::factory ()->getTable () . "` AS TB
                                        WHERE (TB.status='confirmed' OR ((TB.payment_method != 'cash' AND TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL " . $this->option_arr ['o_min_hour'] . " MINUTE))) OR (TB.payment_method = 'cash' AND TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL " . $this->option_arr ['o_cash_min_hour'] . " HOUR)))))
				AND TB.bus_id = $bus_id
				AND TB.booking_date = '$booking_date')
				AND start_location_id IN(" . join ( ",", $location_id_arr ) . ")" )->index ( "FORCE KEY (`booking_id`)" )->findAll ()->getDataPair ( "seat_id", "seat_id" );
			
			$selected_seats_str = ($is_return == 'F' ? $booked_data ['selected_seats'] : $booked_data ['return_selected_seats']);
			$seat_arr = $pjSeatModel->reset ()->where ( 'bus_type_id', $arr ['bus_type_id'] )->findAll ()->getData ();
			$selected_seat_arr = $pjSeatModel->reset ()->whereIn ( 't1.id', explode ( "|", $selected_seats_str ) )->findAll ()->getDataPair ( 'id', 'name' );
		}
		
		$from_location = $pickup_location ['name'];
		$to_location = $return_location ['name'];
		
		return compact ( 'booking_period', 'bus_arr', 'bus_type_arr', 'booked_seat_arr', 'seat_arr', 'selected_seat_arr', 'ticket_columns', 'from_location', 'to_location' );
	}
	
	
	public function getBackupInfo()
	{
		$data = $id = $created = $type = array();
		if ($handle = opendir(PJ_WEB_PATH . 'backup'))
		{
			$i = 0;
			while (false !== ($entry = readdir($handle)))
			{
				preg_match('/(database-backup|files-backup)-(\d{10})\.(sql|zip)/', $entry, $m);
				if (isset($m[2]))
				{
					$id[$i] = $entry;
					$created[$i] = date($this->option_arr['o_date_format'] . ", H:i", $m[2]);
					$type[$i] = $m[1] == 'database-backup' ? 'database' : 'files';
					
					$data[$i]['id'] = $id[$i];
					$data[$i]['created'] = $created[$i];
					$data[$i]['type'] = $type[$i];
					$i++;
				}
			}
			closedir($handle);
		}
		array_multisort($created, SORT_DESC, $id, SORT_DESC, $type, SORT_ASC, $data);
		$total = count($data);
		$rowCount = 1;
		$pages = ceil($total / $rowCount);
		$page = 1;
		if ($page > $pages)
		{
			$page = $pages;
		}
					
		return compact('data', 'total', 'pages', 'page', 'rowCount');
	}
	
	protected function loadSetFields($force=FALSE, $locale_id=NULL, $fields=NULL)
	{
		if (is_null($locale_id))
		{
			$locale_id = $this->getLocaleId();
		}
	
		if (is_null($fields))
		{
			$fields = $this->defaultFields;
		}
	
		$registry = pjRegistry::getInstance();
		if ($force
				|| !isset($_SESSION[$this->defaultFieldsIndex])
				|| $_SESSION[$this->defaultFieldsIndex] != $this->option_arr['o_fields_index']
				|| !isset($_SESSION[$fields])
				|| empty($_SESSION[$fields]))
		{
			pjAppController::setFields($locale_id);
	
			# Update session
			if ($registry->is('fields'))
			{
				$_SESSION[$fields] = $registry->get('fields');
			}
			$_SESSION[$this->defaultFieldsIndex] = $this->option_arr['o_fields_index'];
		}
	
		if (isset($_SESSION[$fields]) && !empty($_SESSION[$fields]))
		{
			# Load fields from session
			$registry->set('fields', $_SESSION[$fields]);
		}
	
		return TRUE;
    }
    
    public function get_usd_conversion($curreny_code,$price) {
        $usd_rate = 0;
        $api_curreny = ($curreny_code == 'ZMK') ? 'ZMW' : $curreny_code;
        $curl_url = 'https://parezaonline.com/exchange/API/rates.php/'.$api_curreny.'/'.date('Y-m-d');
        
        for ($i = 1; $i<=100; $i++) {    
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $curl_url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
            $response = curl_exec($ch);
            $error = curl_errno($ch);
            curl_close($ch);

            if (!$error && !empty($response)) {
                $array_response = json_decode($response,true);
                $usd_rate = $array_response[$api_curreny];
                break;
            } else {
                $date=date_create(date('Y-m-d'));
                date_sub($date,date_interval_create_from_date_string("$i days"));
                $new_date = date_format($date,"Y-m-d");
    
                $curl_url = 'https://parezaonline.com/exchange/API/rates.php/'.$api_curreny.'/'.$new_date;
            }
        }
    
        if ($usd_rate) {
            $is_usd = 1;
            $currency_code = 'USD';
            $curreny_rate = $price/$usd_rate;
        } else {
            $is_usd = 0;
            $currency_code = $curreny_code;
            $curreny_rate = $price;
        }
    
        return array($is_usd,$currency_code,$curreny_rate);
    }
	
}
?>