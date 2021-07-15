<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjFrontEnd extends pjFront
{
	public function __construct()
	{
		parent::__construct();
		$this->setAjax(true);
		$this->setLayout('pjActionEmpty');
	}

	public function pjActionLoad()
	{
		$this->setAjax(false);
		$this->setLayout('pjActionFront');
		ob_start();
		header("Content-Type: text/javascript; charset=utf-8");
	}
	
	public function pjActionLoadCss()
	{
		$dm = new pjDependencyManager(PJ_INSTALL_PATH, PJ_THIRD_PARTY_PATH);
		$dm->load(PJ_CONFIG_PATH . 'dependencies.php')->resolve();
		
		$theme = $this->option_arr['o_theme'];
		$fonts = $this->option_arr['o_layout'];
		if(isset($_GET['theme']) && in_array($_GET['theme'], array('theme1','theme2','theme3','theme4','theme5','theme6','theme7','theme8','theme9','theme10')))
		{
			$theme = $_GET['theme'];
			$fonts = $_GET['theme'];
		}
		
		$arr = array(
				array('file' => "$fonts.css", 'path' => PJ_CSS_PATH . "fonts/"),
				array('file' => 'font-awesome.min.css', 'path' => $dm->getPath('font_awesome')),
				array('file' => 'perfect-scrollbar.min.css', 'path' => $dm->getPath('pj_perfect_scrollbar')),
				array('file' => 'select2.min.css', 'path' => $dm->getPath('pj_select2')),
				array('file' => 'bootstrap-datetimepicker.min.css', 'path' => $dm->getPath('pj_bootstrap_datetimepicker')),
				array('file' => "style.css", 'path' => PJ_CSS_PATH),
				array('file' => "$theme.css", 'path' => PJ_CSS_PATH . "themes/"),
				array('file' => 'transitions.css', 'path' => PJ_CSS_PATH)
		);
		
		header("Content-Type: text/css; charset=utf-8");
		foreach ($arr as $item)
		{
			$string = FALSE;
			if ($stream = fopen($item['path'] . $item['file'], 'rb'))
			{
				$string = stream_get_contents($stream);
				fclose($stream);
			}
			
			if ($string !== FALSE)
			{
				echo str_replace(
					array('../fonts/fontawesome', 'pjWrapper'),
					array(
						PJ_INSTALL_URL . $dm->getPath('font_awesome') . 'fonts/fontawesome',
						"pjWrapperBusReservation_" . $theme),
					$string
				) . "\n";
			}
		}
		exit;
	}
	
	public function pjActionCaptcha()
	{
		$this->setAjax(true);
		header("Cache-Control: max-age=3600, private");
		$Captcha = new pjCaptcha(PJ_WEB_PATH.'obj/Anorexia.ttf', $this->defaultCaptcha, 6);
        $Captcha->setImage(PJ_IMG_PATH.'button.png')->init(isset($_GET['rand']) ? $_GET['rand'] : null);
		exit;
	}

	public function pjActionCheckCaptcha()
	{
		$this->setAjax(true);
		if (!isset($_GET['captcha']) || empty($_GET['captcha']) || strtoupper($_GET['captcha']) != $_SESSION[$this->defaultCaptcha]){
			echo 'false';
		}else{
			echo 'true';
		}
		exit;
	}

	public function pjActionCheck()
	{
		if ($this->isXHR() || isset($_GET['_escaped_fragment_']))
		{
			$resp = array();
			$return_bus_id_arr = array();

			if($_GET['pickup_id'] != $_GET['return_id'])
			{
				$resp['code'] = 200;
	
				$pjBusModel = pjBusModel::factory();
	
				$pickup_id = $_GET['pickup_id'];
				$return_id = $_GET['return_id'];
	
				$date = pjUtil::formatDate($_GET['date'], $this->option_arr['o_date_format']);
				if(isset($_GET['final_check']))
				{
					$date = pjUtil::formatDate($this->_get('date'), $this->option_arr['o_date_format']);
				} else {
					$this->_set('date', $_GET['date']);
				}

				$bus_id_arr = $pjBusModel->getBusIds($date, $pickup_id, $return_id);
				if(empty($bus_id_arr))
				{
					$resp['code'] = 100;
					if(!isset($_GET['final_check']))
					{
						if($this->_is('bus_id_arr'))
						{
							unset($_SESSION[$this->defaultStore]['bus_id_arr']);
						}
					}
					pjAppController::jsonResponse($resp);
				}
	
				if (isset($_GET['is_return']) && $_GET['is_return'] == 'T')
				{
					$pickup_id = $_GET['return_id'];
					$return_id = $_GET['pickup_id'];
						
					$date = pjUtil::formatDate($_GET['return_date'], $this->option_arr['o_date_format']);
					$return_bus_id_arr = $pjBusModel->getBusIds($date, $pickup_id, $return_id);
					if(!isset($_GET['final_check'])) {
						$this->_set('return_date', $_GET['return_date']);	
					}
					if(empty($return_bus_id_arr))
					{
						$resp['code'] = 101;
						if(!isset($_GET['final_check']))
						{
							if($this->_is('return_bus_id_arr'))
							{
								unset($_SESSION[$this->defaultStore]['return_bus_id_arr']);
							}
						}
						pjAppController::jsonResponse($resp);
					}
				}else{
					if(!isset($_GET['final_check']))
					{
						if($this->_is('return_bus_id_arr'))
						{
							unset($_SESSION[$this->defaultStore]['return_bus_id_arr']);
						}
						if($this->_is('return_date'))
						{
							unset($_SESSION[$this->defaultStore]['return_date']);
						}
					}
				}
	
				if(!isset($_GET['final_check']))
				{
					$this->_set('pickup_id', $_GET['pickup_id']);
					$this->_set('return_id', $_GET['return_id']);
					$this->_set('bus_id_arr', $bus_id_arr);
					$this->_set('is_return', $_GET['is_return']);
	
					if (isset($_GET['is_return']) && $_GET['is_return'] == 'T')
					{
						$this->_set('return_bus_id_arr', $return_bus_id_arr);
					}
					if($this->_is('booked_data'))
					{
						unset($_SESSION[$this->defaultStore]['booked_data']);
					}
					if($this->_is('bus_id'))
					{
						unset($_SESSION[$this->defaultStore]['bus_id']);
					}
					$resp['code'] = 200;
					pjAppController::jsonResponse($resp);
				}else{
					$STORE = @$_SESSION[$this->defaultStore];
					$avail_arr = $this->getBusAvailability($STORE['booked_data']['bus_id'], $STORE, $this->option_arr);
					$booked_seat_arr = $avail_arr['booked_seat_arr'];
					$seat_id_arr = explode("|", $STORE['booked_data']['selected_seats']);
					$intersect = array_intersect($booked_seat_arr, $seat_id_arr);
					if(!empty($intersect))
					{
						$resp['code'] = 100;
					}else{
						$resp['code'] = 200;
					}
					pjAppController::jsonResponse($resp);
				}
			}
			pjAppController::jsonResponse($resp);
		}
	}
	
	public function pjActionSaveTickets()
	{
		$this->setAjax(true);
		$resp = array();
		$resp['code'] = 200;
		$this->_set('booked_data', $_POST);
		pjAppController::jsonResponse($resp);
	}
	
	public function pjActionSaveForm()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			if (!isset($_SESSION[$this->defaultForm]) || count($_SESSION[$this->defaultForm]) === 0)
			{
				$_SESSION[$this->defaultForm] = array();
			}
			if(isset($_POST['step_checkout'])){
				$_SESSION[$this->defaultForm] = $_POST;
			}
			if ((int) $this->option_arr['o_bf_include_captcha'] === 3 && (!isset($_SESSION[$this->defaultForm]['captcha']) ||
					!pjCaptcha::validate($_SESSION[$this->defaultForm]['captcha'], $_SESSION[$this->defaultCaptcha]) ))
			{
				pjAppController::jsonResponse(array('status' => 'ERR', 'code' => 110));
            }
            //echo "<pre>here2";
            //print_r($_SESSION[$this->defaultForm]);
			$resp = array('code' => 200);
			pjAppController::jsonResponse($resp);
		}
	}
		
	public function pjActionSaveBooking() {
        $params = $this->getParams();
        $is_api = (isset($params['is_api'])) ? true : false;

		$this->setAjax ( true );

		if ($is_api || $this->isXHR ()) {
			if ((int) $this->option_arr['o_bf_include_captcha'] === 3 && (!isset($_SESSION[$this->defaultForm]['captcha']) ||
					!pjCaptcha::validate($_SESSION[$this->defaultForm]['captcha'], $_SESSION[$this->defaultCaptcha]) ))
			{
				pjAppController::jsonResponse(array('status' => 'ERR', 'code' => 110));
			}
            
            if ($is_api) {
                $STORE = $params['api_store'];
                $FORM = $params['api_form'];
                $booked_data = $params['api_store']['booked_data'];
            } else {
                $STORE = @$_SESSION [$this->defaultStore];
                $FORM = @$_SESSION [$this->defaultForm];
                $booked_data = @$STORE ['booked_data'];
            }

			$pjBookingModel = pjBookingModel::factory ();
			
			$bus_id = $booked_data ['bus_id'];
			$return_bus_id = isset ( $booked_data ['return_bus_id'] ) ? $booked_data ['return_bus_id'] : 0;
			$pickup_id = ($is_api) ? $params['api_store']['pickup_id'] :  $this->_get ( 'pickup_id' );
			$return_id = ($is_api) ? $params['api_store']['return_id'] :  $this->_get ( 'return_id' );
			$is_return = ($is_api) ? $params['api_store']['is_return'] :  $this->_get ( 'is_return' );
            $booking_date = ($is_api) ? $params['api_store']['date'] :  $this->_get ( 'date' );
            
			$depart_arrive = '';
			$depart_time = null;
			
			$bus_arr = pjBusModel::factory ()->join ( 'pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjBusType', "t3.id=t1.bus_type_id", 'left' )->select ( 't1.*, t3.seats_map, t2.content as route' )->find ( $bus_id )->getData ();
			if (! empty ( $bus_arr ['departure_time'] ) && ! empty ( $bus_arr ['arrival_time'] )) {
				$depart_arrive = pjUtil::formatTime ( $bus_arr ['departure_time'], "H:i:s", $this->option_arr ['o_time_format'] ) . ' - ' . pjUtil::formatTime ( $bus_arr ['arrival_time'], "H:i:s", $this->option_arr ['o_time_format'] );
				$depart_time = $bus_arr ['departure_time'];
			}
			
			$pjCityModel = pjCityModel::factory ();
			$pickup_location = $pjCityModel->reset ()->select ( 't1.*, t2.content as name' )->join ( 'pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )->find ( $pickup_id )->getData ();
			$return_location = $pjCityModel->reset ()->select ( 't1.*, t2.content as name' )->join ( 'pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )->find ( $return_id )->getData ();
			$from_location = $pickup_location ['name'];
			$to_location = $return_location ['name'];
			
			$data = array ();
			$data ['bus_id'] = $bus_id;
			$data ['uuid'] = time ();
			$data ['ip'] = pjUtil::getClientIp ();
			$data ['booking_date'] = pjUtil::formatDate ( $booking_date, $this->option_arr ['o_date_format'] );
			if ($is_return == 'T') {
				$data ['return_date'] = pjUtil::formatDate ( $this->_get ( 'return_date' ), $this->option_arr ['o_date_format'] );
			}
			$data ['booking_datetime'] = $data ['booking_date'];
			if (isset ( $STORE ['booking_period'] [$bus_id] )) {
				$data ['booking_datetime'] = $STORE ['booking_period'] [$bus_id] ['departure_time'];
				$data ['stop_datetime'] = $STORE ['booking_period'] [$bus_id] ['arrival_time'];
			}
			$data ['status'] = $this->option_arr ['o_booking_status'];
			
			$data['bus_departure_date'] = $data ['booking_date'];
			$depart_date_time_iso = pjUtil::formatDate ( $booking_date, $this->option_arr ['o_date_format'] ) . ' ' . pjUtil::formatTime ( $depart_time, "H:i:s", $this->option_arr ['o_time_format'] );
			if($depart_date_time_iso > $data ['booking_datetime'])
			{
				$data['bus_departure_date'] = date('Y-m-d', strtotime($depart_date_time_iso) - 86400);
			}
			$payment = 'none';
			if (isset ( $FORM ['payment_method'] )) {
				if ($FORM ['payment_method'] && $FORM ['payment_method'] == 'creditcard') {
					$data ['cc_exp'] = $FORM ['cc_exp_year'] . '-' . $FORM ['cc_exp_month'];
				}
				
				if ($FORM ['payment_method']) {
					$payment = $FORM ['payment_method'];
				}
			}
			
			$bt_arr = array ();
			$pjBusLocationModel = pjBusLocationModel::factory ();
			$_arr = $pjBusLocationModel->where ( 'bus_id', $bus_id )->where ( "location_id", $pickup_id )->limit ( 1 )->findAll ()->getData ();
			if (count ( $_arr ) > 0) {
				$bt_arr [] = pjUtil::formatTime ( $_arr [0] ['departure_time'], "H:i:s", $this->option_arr ['o_time_format'] );
				$data ['booking_datetime'] .= ' ' . $_arr [0] ['departure_time'];
			}
			
			$_arr = $pjBusLocationModel->reset ()->where ( 'bus_id', $bus_id )->where ( "location_id", $return_id )->limit ( 1 )->findAll ()->getData ();
			if (count ( $_arr ) > 0) {
				$bt_arr [] = pjUtil::formatTime ( $_arr [0] ['arrival_time'], "H:i:s", $this->option_arr ['o_time_format'] );
			}
			$data ['booking_time'] = join ( " - ", $bt_arr );
			$data ['pickup_id'] = $pickup_id;
			$data ['return_id'] = $return_id;
			$data ['is_return'] = $is_return;
			$data ['booking_route'] = $bus_arr ['route'] . ', ' . $depart_arrive . '<br/>';
			
			$data ['booking_route'] .= __ ( 'front_from', true, false ) . ' ' . $from_location . ' ' . __ ( 'front_to', true, false ) . ' ' . $to_location;
			
			$pjPriceModel = pjPriceModel::factory ();
			$ticket_price_arr = $pjPriceModel->getTicketPrice($bus_id, $pickup_id, $return_id, $booked_data, $this->option_arr, $this->getLocaleId(), 'F');
			
			$data ['sub_total'] = $ticket_price_arr['sub_total'];
			$data ['tax'] = $ticket_price_arr['tax'];
			$data ['total'] = $ticket_price_arr['total'];
            $data ['deposit'] = $ticket_price_arr['deposit'];
            $c_phone = ltrim(trim($FORM['c_phone']),0);

            if ($c_phone) {
                if (strpos($c_phone,'+') !==0 && $FORM['c_phone_country']) {
                    $phone_explode = explode('_',$FORM['c_phone_country']);
                    $country_arr = pjCountryModel::factory()                            
                            ->find($phone_explode[0])->getData();

                    if (isset($country_arr['phone_code']) && $country_arr['phone_code']) {
                        $c_phone = $country_arr['phone_code'].$c_phone;
                    }
                }
            }
            $data['c_phone'] = $c_phone;
			
			$id = $pjBookingModel->setAttributes ( array_merge ( $FORM, $data ) )->insert ()->getInsertId ();
			
			if ($id !== false && ( int ) $id > 0) {
				$back_insert_id = 0;
				if ($is_return == 'T') {
					$child_bus_arr = pjBusModel::factory ()->join ( 'pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjBusType', "t3.id=t1.bus_type_id", 'left' )->select ( 't1.*, t3.seats_map, t2.content as route' )->find ( $return_bus_id )->getData ();
					
					if (! empty ( $child_bus_arr ['departure_time'] ) && ! empty ( $child_bus_arr ['arrival_time'] )) {
						$depart_arrive = pjUtil::formatTime ( $child_bus_arr ['departure_time'], "H:i:s", $this->option_arr ['o_time_format'] ) . ' - ' . pjUtil::formatTime ( $child_bus_arr ['arrival_time'], "H:i:s", $this->option_arr ['o_time_format'] );
					}
					$bt_arr = array ();
					$pjBusLocationModel = pjBusLocationModel::factory ();
					$_arr = $pjBusLocationModel->where ( 'bus_id', $child_bus_arr ['id'] )->where ( "location_id", $return_id )->limit ( 1 )->findAll ()->getData ();
					if (count ( $_arr ) > 0) {
						$bt_arr [] = pjUtil::formatTime ( $_arr [0] ['departure_time'], "H:i:s", $this->option_arr ['o_time_format'] );
						$data ['booking_datetime'] .= ' ' . $_arr [0] ['departure_time'];
					}
					
					$_arr = $pjBusLocationModel->reset ()->where ( 'bus_id', $child_bus_arr ['id'] )->where ( "location_id", $pickup_id )->limit ( 1 )->findAll ()->getData ();
					if (count ( $_arr ) > 0) {
						$bt_arr [] = pjUtil::formatTime ( $_arr [0] ['arrival_time'], "H:i:s", $this->option_arr ['o_time_format'] );
					}
					$data ['booking_time'] = join ( " - ", $bt_arr );
					
					$data ['booking_route'] = $child_bus_arr ['route'] . ', ' . $depart_arrive . '<br/>';
					$data ['booking_route'] .= __ ( 'front_from', true, false ) . ' ' . $to_location . ' ' . __ ( 'front_to', true, false ) . ' ' . $from_location;
					$data ['booking_date'] = pjUtil::formatDate ( $this->_get ( 'return_date' ), $this->option_arr ['o_date_format'] );
					if (isset ( $STORE ['booking_period'] [$return_bus_id] )) {
						$data ['booking_datetime'] = $STORE ['booking_period'] [$return_bus_id] ['departure_time'];
						$data ['stop_datetime'] = $STORE ['booking_period'] [$return_bus_id] ['arrival_time'];
					}
					unset ( $data ['return_date'] );
					unset ( $data ['is_return'] );
					
					$data ['bus_id'] = $return_bus_id;
					$data ['uuid'] = time () + 1;
					$data ['pickup_id'] = $return_id;
					$data ['return_id'] = $pickup_id;
										
					$return_ticket_price_arr = $pjPriceModel->getTicketPrice($return_bus_id, $return_id, $pickup_id, $booked_data, $this->option_arr, $this->getLocaleId(), 'T');
					
					$data ['sub_total'] = isset($return_ticket_price_arr['sub_total']) ? $return_ticket_price_arr['sub_total'] : 0;
					$data ['tax'] = isset($return_ticket_price_arr['tax']) ? $return_ticket_price_arr['tax'] : 0;
					$data ['total'] = isset($return_ticket_price_arr['total']) ? $return_ticket_price_arr['total'] : 0;
					$data ['deposit'] = isset($return_ticket_price_arr['deposit']) ? $return_ticket_price_arr['deposit'] : 0;
					
					$back_insert_id = $pjBookingModel->reset ()->setAttributes ( array_merge ( $FORM, $data ) )->insert ()->getInsertId ();
					if ($back_insert_id !== false && ( int ) $back_insert_id > 0) {
						$pjBookingModel->reset ()->set ( 'id', $id )->modify ( array (
								'back_id' => $back_insert_id 
						) );
						
						$pjBookingModel->reset ()->set ( 'id', $back_insert_id )->modify ( array (
								'back_id' => $id 
						) );
					}
				}
				
				$ticket_arr = pjPriceModel::factory ()->select ( "t1.*" )->where ( 't1.bus_id', $bus_id )->where ( 't1.from_location_id', $pickup_id )->where ( 't1.to_location_id', $return_id )->where ( 'is_return = "F"' )->findAll ()->getData ();
				
				$location_arr = pjRouteCityModel::factory ()->getLocations ( $bus_arr ['route_id'], $pickup_id, $return_id );
				$location_pair = array ();
				for($i = 0; $i < count ( $location_arr ); $i ++) {
					$j = $i + 1;
					if ($j < count ( $location_arr )) {
						$location_pair [] = $location_arr [$i] ['city_id'] . '-' . $location_arr [$j] ['city_id'];
					}
				}
				$pjBookingTicketModel = pjBookingTicketModel::factory ();
				foreach ( $ticket_arr as $k => $v ) {
					if (isset ( $booked_data ['ticket_cnt_' . $v ['ticket_id']] ) && $booked_data ['ticket_cnt_' . $v ['ticket_id']] > 0) {
						$data = array ();
						$data ['booking_id'] = $id;
						$data ['ticket_id'] = $v ['ticket_id'];
						$data ['qty'] = $booked_data ['ticket_cnt_' . $v ['ticket_id']];
						$data ['amount'] = $data ['qty'] * $v ['price'];
						$data ['is_return'] = 'F';
						$pjBookingTicketModel->reset ()->setAttributes ( $data )->insert ();
					}
				}
				
				$pjBookingSeatModel = pjBookingSeatModel::factory ();
				
                $seat_id_arr = explode ( "|", $booked_data ['selected_seats'] );
				
				foreach ( $location_pair as $pair ) {
					$_arr = explode ( "-", $pair );
					$k = 0;
					foreach ( $ticket_arr as $j => $v ) {
						if (isset ( $booked_data ['ticket_cnt_' . $v ['ticket_id']] ) && $booked_data ['ticket_cnt_' . $v ['ticket_id']] > 0) {
                            $qty = $booked_data ['ticket_cnt_' . $v ['ticket_id']];
							if ($qty > 0) {
								for($i = 1; $i <= $qty; $i ++) {
									$data = array ();
									$data ['booking_id'] = $id;
									$data ['seat_id'] = $seat_id_arr [$k];
									$data ['ticket_id'] = $v ['ticket_id'];
									
									$data ['start_location_id'] = $_arr [0];
									$data ['end_location_id'] = $_arr [1];
									$data ['is_return'] = 'F';
									
									$pjBookingSeatModel->reset ()->setAttributes ( $data )->insert ();
									
									$k ++;
								}
							}
						}
					}
				}
				
				if ($is_return == 'T') {
					$ticket_arr = pjPriceModel::factory ()->select ( "t1.*, t2.discount" )->join ( 'pjBus', 't1.bus_id = t2.id', 'left' )->where ( 't1.bus_id', $return_bus_id )->where ( 't1.from_location_id', $return_id )->where ( 't1.to_location_id', $pickup_id )->where ( 'is_return = "F"' )->findAll ()->getData ();
					
					$location_arr = pjRouteCityModel::factory ()->getLocations ( $bus_arr ['route_id'], $pickup_id, $return_id );
					$location_pair = array ();
					for($i = 0; $i < count ( $location_arr ); $i ++) {
						$j = $i + 1;
						if ($j < count ( $location_arr )) {
							$location_pair [] = $location_arr [$i] ['city_id'] . '-' . $location_arr [$j] ['city_id'];
						}
					}
					$pjBookingTicketModel = pjBookingTicketModel::factory ();
					foreach ( $ticket_arr as $k => $v ) {
						if (isset ( $booked_data ['return_ticket_cnt_' . $v ['ticket_id']] ) && $booked_data ['return_ticket_cnt_' . $v ['ticket_id']] > 0) {
							$price = $v ['price'] - ($v ['price'] * $v ['discount'] / 100);
							$data = array ();
							$data ['booking_id'] = $back_insert_id;
							$data ['ticket_id'] = $v ['ticket_id'];
							$data ['qty'] = $booked_data ['return_ticket_cnt_' . $v ['ticket_id']];
							$data ['amount'] = $data ['qty'] * $price;
							$data ['is_return'] = 'T';
							$pjBookingTicketModel->reset ()->setAttributes ( $data )->insert ();
						}
					}
					
					$seat_id_arr = explode ( "|", $booked_data ['return_selected_seats'] );
					foreach ( $location_pair as $pair ) {
						$_arr = explode ( "-", $pair );
						$kk = 0;
						foreach ( $ticket_arr as $j => $v ) {
							if (isset ( $booked_data ['return_ticket_cnt_' . $v ['ticket_id']] ) && $booked_data ['return_ticket_cnt_' . $v ['ticket_id']] > 0) {
								$qty = $booked_data ['return_ticket_cnt_' . $v ['ticket_id']];
								if ($qty > 0) {
									for($i = 1; $i <= $qty; $i ++) {
										$data = array ();
										$data ['booking_id'] = $back_insert_id;
										$data ['seat_id'] = $seat_id_arr [$kk];
										$data ['ticket_id'] = $v ['ticket_id'];
										
										$data ['start_location_id'] = $_arr [1];
										$data ['end_location_id'] = $_arr [0];
										$data ['is_return'] = 'T';
										
										$pjBookingSeatModel->reset ()->setAttributes ( $data )->insert ();
										
										$kk ++;
									}
								}
							}
						}
					}
				}
				
				$arr = $pjBookingModel->reset ()->select ( 't1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location' )->join ( 'pjBus', "t2.id=t1.bus_id", 'left outer' )->join ( 'pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='" . $this->getLocaleId () . "'", 'left outer' )->find ( $id )->getData ();
				
				$tickets = pjBookingTicketModel::factory ()->join ( 'pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjTicket', "t3.id=t1.ticket_id", 'left' )->select ( 't1.*, t2.content as title' )->where ( 'booking_id', $arr ['id'] )->findAll ()->getData ();
				
				$arr ['tickets'] = $tickets;
				
				$payment_data = array ();
				$payment_data ['booking_id'] = $arr ['id'];
				$payment_data ['payment_method'] = $payment;
				$payment_data ['payment_type'] = 'online';
				$payment_data ['amount'] = $arr ['deposit'];
                $payment_data ['status'] = 'notpaid';

				pjBookingPaymentModel::factory ()->setAttributes ( $payment_data )->insert ();

                $_SESSION['api_arr'] = $arr; //used in pjApi and pjCombinedApi

				pjFrontEnd::pjActionConfirmSend ( $this->option_arr, $arr, PJ_SALT, 'pending');
				
				if ($is_return == 'T') {
					$return_arr = $pjBookingModel->reset ()->select ( 't1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location' )->join ( 'pjBus', "t2.id=t1.bus_id", 'left outer' )->join ( 'pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='" . $this->getLocaleId () . "'", 'left outer' )->find ( $arr ['back_id'] )->getData ();
					
					$return_tickets = pjBookingTicketModel::factory ()->join ( 'pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjTicket', "t3.id=t1.ticket_id", 'left' )->select ( 't1.*, t2.content as title' )->where ( 'booking_id', $arr ['back_id'] )->findAll ()->getData ();
					
					$return_arr ['tickets'] = $return_tickets;
					
					pjFrontEnd::pjActionConfirmSend ( $this->option_arr, $return_arr, PJ_SALT, 'pending',true,true);
				}
				
				unset ( $_SESSION [$this->defaultStore] );
				unset ( $_SESSION [$this->defaultForm] );
				unset ( $_SESSION [$this->defaultStep] );
				unset ( $_SESSION [$this->defaultCaptcha] );
				
				$json = array (
						'code' => 200,
						'text' => '',
						'booking_id' => $id,
						'payment' => $payment 
				);
			} else {
				$json = array (
						'code' => 100,
						'text' => '' 
				);
            }
            if ($is_api) {
                return $json;
            } else {
                pjAppController::jsonResponse ( $json );
            }
		}
	}
		
	public function pjActionGetLocations()
	{
		$this->setAjax(true);
	
		$pjCityModel = pjCityModel::factory();
		$pjRouteDetailModel = pjRouteDetailModel::factory();
	
		if(isset($_GET['pickup_id']))
		{
			$where = '';
			if(!empty($_GET['pickup_id']))
			{
				$where = "WHERE TRD.from_location_id=" . $_GET['pickup_id'];
			}
			$location_arr = $pjCityModel
				->reset()
				->select('t1.*, t2.content as name')
				->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->where("t1.id IN(SELECT TRD.to_location_id FROM `".$pjRouteDetailModel->getTable()."` AS TRD $where)")
				->orderBy("t2.content ASC")
				->findAll()
				->getData();
		}
		if(isset($_GET['return_id']))
		{
			$where = '';
			if(!empty($_GET['return_id']))
			{
				$where = "WHERE TRD.to_location_id=" . $_GET['return_id'];
			}
			$location_arr = $pjCityModel
				->reset()
				->select('t1.*, t2.content as name')
				->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->where("t1.id IN(SELECT TRD.from_location_id FROM `".$pjRouteDetailModel->getTable()."` AS TRD $where)")
				->orderBy("t2.content ASC")
				->findAll()
				->getData();
		}
	
		$this->set('location_arr', $location_arr);
	}
	
	public function pjActionGetRoundtripPrice()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			if (isset($_SESSION[$this->defaultStore]) && count($_SESSION[$this->defaultStore]) > 0 && $this->isBusReady() == true)
			{
				$pickup_id = $this->_get('pickup_id');
				$return_id = $this->_get('return_id');
				$is_return = $this->_get('is_return');
				$bus_id = isset($_GET['bus_id']) && (int) $_GET['bus_id'] > 0 ? $_GET['bus_id'] : 0;
				$return_bus_id = isset($_GET['return_bus_id']) && (int) $_GET['return_bus_id'] > 0 ? $_GET['return_bus_id'] : 0;
	
				$pjPriceModel = pjPriceModel::factory();
				if($bus_id > 0)
				{
					$ticket_price_arr = $pjPriceModel->getTicketPrice($bus_id, $pickup_id, $return_id, $_POST, $this->option_arr, $this->getLocaleId(), 'F');
					$this->set('price_arr', $ticket_price_arr);
				}
				if($return_bus_id > 0 && $is_return == "T")
				{
					$return_ticket_price_arr = $pjPriceModel->getTicketPrice($return_bus_id, $return_id, $pickup_id, $_POST, $this->option_arr, $this->getLocaleId(), 'T');
					$this->set('return_price_arr', $return_ticket_price_arr);
				}
				$this->set('status', 'OK');
			}else{
				$this->set('status', 'ERR');
			}
		}
	}

	public function pjActionGetSeats()
	{
		$this->setAjax(true);
	
		$bus_id = $_GET['bus_id'];
		$STORE = @$_SESSION[$this->defaultStore];
			
		$avail_arr = $this->getBusAvailability($bus_id, $STORE, $this->option_arr);
		
		$this->set('bus_arr', pjBusModel::factory()->find($bus_id)->getData());
		$this->set('bus_type_arr', $avail_arr['bus_type_arr']);
		$this->set('booked_seat_arr', $avail_arr['booked_seat_arr']);
		if(!empty($avail_arr['bus_type_arr']))
		{
			$this->set('seat_arr', pjSeatModel::factory()->where('bus_type_id', $avail_arr['bus_type_arr']['id'])->findAll()->getData());
		}else{
			$this->set('seat_arr', array());
		}
	}
	
	public function pjActionGetReturnSeats()
	{
		$this->setAjax(true);
	
		$bus_id = $_GET['bus_id'];
		$STORE = @$_SESSION[$this->defaultStore];
		$avail_arr = $this->getReturnBusAvailability($bus_id, $STORE, $this->option_arr);
		$this->set('bus_arr', pjBusModel::factory()->find($bus_id)->getData());
			
		$this->set('return_bus_type_arr', $avail_arr['bus_type_arr']);
		$this->set('booked_return_seat_arr', $avail_arr['booked_seat_arr']);
		if(!empty($avail_arr['bus_type_arr']))
		{
			$this->set('return_seat_arr', pjSeatModel::factory()->where('bus_type_id', $avail_arr['bus_type_arr']['id'])->findAll()->getData());
		}else{
			$this->set('return_seat_arr', array());
		}
	}
	
	public function pjActionConfirmAuthorize()
	{
		$this->setAjax(true);
		
		if (pjObject::getPlugin('pjAuthorize') === NULL)
		{
			$this->log('Authorize.NET plugin not installed');
			exit;
		}
		
		$pjBookingModel = pjBookingModel::factory();
		$booking_arr = $pjBookingModel
			->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
			->join('pjBus', "t2.id=t1.bus_id", 'left outer')
			->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
			->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
			->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
			->find($_POST['x_invoice_num'])
			->getData();
			
		$booking_arr['tickets'] = pjBookingTicketModel::factory()
			->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
			->join('pjTicket', "t3.id=t1.ticket_id", 'left')
			->select('t1.*, t2.content as title')
			->where('booking_id', $booking_arr['id'])
			->findAll()
			->getData();
	
		if (count($booking_arr) == 0)
		{
			$this->log('No such booking');
			pjUtil::redirect($this->option_arr['o_thank_you_page']);
		}					
		if (count($booking_arr) > 0)
		{
			$params = array(
				'transkey' => $this->option_arr['o_authorize_transkey'],
				'x_login' => $this->option_arr['o_authorize_merchant_id'],
				'md5_setting' => $this->option_arr['o_authorize_md5_hash'],
				'key' => md5($this->option_arr['private_key'] . PJ_SALT)
			);
				
			$response = $this->requestAction(array('controller' => 'pjAuthorize', 'action' => 'pjActionConfirm', 'params' => $params), array('return'));
		
			if ($response !== FALSE && $response['status'] === 'OK')
			{
				$pjBookingModel->reset()
					->setAttributes(array('id' => $response['transaction_id']))
					->modify(array('status' => $this->option_arr['o_payment_status'], 'processed_on' => ':NOW()'));

				if (!empty($booking_arr['back_id'])) 
				{
					$pjBookingModel->reset()->setAttributes(array('id' => $booking_arr['back_id']))->modify(array(
						'status' => $this->option_arr['o_payment_status'],
						'txn_id' => $response['transaction_id'],
						'processed_on' => ':NOW()'
					));
				}
				pjBookingPaymentModel::factory()
					->where('booking_id', $booking_arr['id'])
					->where('payment_type', 'online')
					->modifyAll(array('status' => 'paid'));
					
				pjFrontEnd::pjActionConfirmSend($this->option_arr, $booking_arr, PJ_SALT, 'payment');
				if ($booking_arr['is_return'] == 'T') {
				    $return_arr = $pjBookingModel
				    ->reset ()
				    ->select ( 't1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location' )
				    ->join ( 'pjBus', "t2.id=t1.bus_id", 'left outer' )
				    ->join ( 'pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='" . $this->getLocaleId () . "'", 'left outer' )
				    ->join ( 'pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='" . $this->getLocaleId () . "'", 'left outer' )
				    ->join ( 'pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='" . $this->getLocaleId () . "'", 'left outer' )
				    ->find ( $booking_arr ['back_id'] )->getData ();
				    
				    $return_tickets = pjBookingTicketModel::factory ()
				    ->join ( 'pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )
				    ->join ( 'pjTicket', "t3.id=t1.ticket_id", 'left' )
				    ->select ( 't1.*, t2.content as title' )
				    ->where ( 'booking_id', $booking_arr ['back_id'] )
				    ->findAll ()->getData ();
				    
				    $return_arr ['tickets'] = $return_tickets;
				    
				    pjFrontEnd::pjActionConfirmSend ( $this->option_arr, $return_arr, PJ_SALT, 'payment' );
				}
			} elseif (!$response) {
				$this->log('Authorization failed');
			} else {
				$this->log('Booking not confirmed. ' . $response['response_reason_text']);
			}
			pjUtil::redirect($this->option_arr['o_thank_you_page']);
		}
	}

	public function pjActionConfirmPaypal()
	{
		$this->setAjax(true);
		
		if (pjObject::getPlugin('pjPaypal') === NULL)
		{
			$this->log('Paypal plugin not installed');
			exit;
		}
		$pjBookingModel = pjBookingModel::factory();
		$booking_arr = $pjBookingModel
			->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
			->join('pjBus', "t2.id=t1.bus_id", 'left outer')
			->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
			->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
			->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
			->find($_POST['custom'])
			->getData();
			
		$booking_arr['tickets'] = pjBookingTicketModel::factory()
			->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
			->join('pjTicket', "t3.id=t1.ticket_id", 'left')
			->select('t1.*, t2.content as title')
			->where('booking_id', $booking_arr['id'])
			->findAll()
			->getData();
		
		if (count($booking_arr) == 0)
		{
			$this->log('No such booking');
			pjUtil::redirect($this->option_arr['o_thank_you_page']);
		}					
		if (!empty($booking_arr['back_id'])) {
			$back_arr = pjBookingModel::factory()
				->select('t1.*')
				->find($booking_arr['back_id'])->getData();
			$booking_arr['deposit'] += $back_arr['deposit'];
		}
		$params = array(
			'txn_id' => @$booking_arr['txn_id'],
			'paypal_address' => $this->option_arr['o_paypal_address'],
			'deposit' => @$booking_arr['deposit'],
			'currency' => $this->option_arr['o_currency'],
			'key' => md5($this->option_arr['private_key'] . PJ_SALT)
		);
		$response = $this->requestAction(array('controller' => 'pjPaypal', 'action' => 'pjActionConfirm', 'params' => $params), array('return'));
		
		if ($response !== FALSE && $response['status'] === 'OK')
		{
			$this->log('Booking confirmed');
			$pjBookingModel->reset()->setAttributes(array('id' => $booking_arr['id']))->modify(array(
				'status' => $this->option_arr['o_payment_status'],
				'txn_id' => $response['transaction_id'],
				'processed_on' => ':NOW()'
			));
			if (!empty($booking_arr['back_id'])) 
			{
				$pjBookingModel->reset()->setAttributes(array('id' => $booking_arr['back_id']))->modify(array(
						'status' => $this->option_arr['o_payment_status'],
						'txn_id' => $response['transaction_id'],
						'processed_on' => ':NOW()'
				));
			}
			pjBookingPaymentModel::factory()
				->where('booking_id', $booking_arr['id'])
				->where('payment_type', 'online')
				->modifyAll(array('status' => 'paid'));
				
			pjFrontEnd::pjActionConfirmSend($this->option_arr, $booking_arr, PJ_SALT, 'payment');
			if ($booking_arr['is_return'] == 'T') {
			    $return_arr = $pjBookingModel
			     ->reset ()
			     ->select ( 't1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location' )
			     ->join ( 'pjBus', "t2.id=t1.bus_id", 'left outer' )
			     ->join ( 'pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='" . $this->getLocaleId () . "'", 'left outer' )
			     ->join ( 'pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='" . $this->getLocaleId () . "'", 'left outer' )
			     ->join ( 'pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='" . $this->getLocaleId () . "'", 'left outer' )
			     ->find ( $booking_arr ['back_id'] )->getData ();
			    
			    $return_tickets = pjBookingTicketModel::factory ()
			     ->join ( 'pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )
			     ->join ( 'pjTicket', "t3.id=t1.ticket_id", 'left' )
			     ->select ( 't1.*, t2.content as title' )
			     ->where ( 'booking_id', $booking_arr ['back_id'] )
			     ->findAll ()->getData ();
			    
			    $return_arr ['tickets'] = $return_tickets;
			    
			    pjFrontEnd::pjActionConfirmSend ( $this->option_arr, $return_arr, PJ_SALT, 'payment',true,true );
			}
		} elseif (!$response) {
			$this->log('Authorization failed');
		} else {
			$this->log('Booking not confirmed');
		}
		pjUtil::redirect($this->option_arr['o_thank_you_page']);
    }
    
    public function pjActionConfirm2Checkout()
	{
		$this->setAjax(true);
		
		// if (pjObject::getPlugin('pj2Checkout') === NULL)
		// {
		// 	$this->log('2Checout plugin not installed');
		// 	exit;
		// }
		$pjBookingModel = pjBookingModel::factory();
		$booking_arr = $pjBookingModel
			->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
			->join('pjBus', "t2.id=t1.bus_id", 'left outer')
			->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
			->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
			->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
			->find($_POST['li_0_product_id'])
			->getData();
			
		$booking_arr['tickets'] = pjBookingTicketModel::factory()
			->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
			->join('pjTicket', "t3.id=t1.ticket_id", 'left')
			->select('t1.*, t2.content as title')
			->where('booking_id', $booking_arr['id'])
			->findAll()
			->getData();
		
		if (count($booking_arr) == 0)
		{
			$this->log('No such booking');
			pjUtil::redirect($this->option_arr['o_thank_you_page']);
		}					
		if (!empty($booking_arr['back_id'])) {
			$back_arr = pjBookingModel::factory()
				->select('t1.*')
				->find($booking_arr['back_id'])->getData();
			$booking_arr['deposit'] += $back_arr['deposit'];
		}	
		
		if ($_POST['credit_card_processed'] == 'Y')
		{
            $response = $_POST;
			$this->log('Booking confirmed');
			$pjBookingModel->reset()->setAttributes(array('id' => $booking_arr['id']))->modify(array(
				'status' => $this->option_arr['o_payment_status'],
				'txn_id' => $response['order_number'],
				'processed_on' => ':NOW()'
			));
			if (!empty($booking_arr['back_id'])) 
			{
				$pjBookingModel->reset()->setAttributes(array('id' => $booking_arr['back_id']))->modify(array(
						'status' => $this->option_arr['o_payment_status'],
						'txn_id' => $response['order_number'],
						'processed_on' => ':NOW()'
				));
			}
			pjBookingPaymentModel::factory()
				->where('booking_id', $booking_arr['id'])
				->where('payment_type', 'online')
				->modifyAll(array('status' => 'paid'));

			pjFrontEnd::pjActionConfirmSend($this->option_arr, $booking_arr, PJ_SALT, 'payment');
			if ($booking_arr['is_return'] == 'T') {
			    $return_arr = $pjBookingModel
			     ->reset ()
			     ->select ( 't1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location' )
			     ->join ( 'pjBus', "t2.id=t1.bus_id", 'left outer' )
			     ->join ( 'pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='" . $this->getLocaleId () . "'", 'left outer' )
			     ->join ( 'pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='" . $this->getLocaleId () . "'", 'left outer' )
			     ->join ( 'pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='" . $this->getLocaleId () . "'", 'left outer' )
			     ->find ( $booking_arr ['back_id'] )->getData ();
			    
			    $return_tickets = pjBookingTicketModel::factory ()
			     ->join ( 'pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )
			     ->join ( 'pjTicket', "t3.id=t1.ticket_id", 'left' )
			     ->select ( 't1.*, t2.content as title' )
			     ->where ( 'booking_id', $booking_arr ['back_id'] )
			     ->findAll ()->getData ();
			    
			    $return_arr ['tickets'] = $return_tickets;
			    
			    pjFrontEnd::pjActionConfirmSend ( $this->option_arr, $return_arr, PJ_SALT, 'payment',true,true);
			}
		} else {
			$this->log('Booking not confirmed');
		}
		pjUtil::redirect($this->option_arr['o_thank_you_page']);
	}
    
    public function pjActionConfirmFlutterwave()
	{
		$this->setAjax(true);
		
		$pjBookingModel = pjBookingModel::factory();
		$booking_arr = $pjBookingModel
			->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
			->join('pjBus', "t2.id=t1.bus_id", 'left outer')
			->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
			->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
			->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
			->find($_GET['tx_ref'])
			->getData();
			
		$booking_arr['tickets'] = pjBookingTicketModel::factory()
			->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
			->join('pjTicket', "t3.id=t1.ticket_id", 'left')
			->select('t1.*, t2.content as title')
			->where('booking_id', $booking_arr['id'])
			->findAll()
			->getData();
		
		if (count($booking_arr) == 0)
		{
			$this->log('No such booking');
			pjUtil::redirect($this->option_arr['o_thank_you_page']);
		}					
		if (!empty($booking_arr['back_id'])) {
			$back_arr = pjBookingModel::factory()
				->select('t1.*')
				->find($booking_arr['back_id'])->getData();
			$booking_arr['deposit'] += $back_arr['deposit'];
		}	
        
        $is_booking_confirmed = false;
		if ($_GET['status'] == 'successful') {
            //verify transaction
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.flutterwave.com/v3/transactions/".$_GET['transaction_id']."/verify",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer FLWSECK_TEST-45c97dcdbfe2caffd9b8593c4fff2396-X"
            ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $response_decode = json_decode($response,true);
            if ($response_decode['status'] == 'success') {
                $is_booking_confirmed = true;
            }
        }

        if ($is_booking_confirmed) {
			$this->log('Booking confirmed');
			$pjBookingModel->reset()->setAttributes(array('id' => $booking_arr['id']))->modify(array(
				'status' => $this->option_arr['o_payment_status'],
				'txn_id' => $response_decode['data']['id'],
				'processed_on' => ':NOW()'
			));
			if (!empty($booking_arr['back_id'])) 
			{
				$pjBookingModel->reset()->setAttributes(array('id' => $booking_arr['back_id']))->modify(array(
						'status' => $this->option_arr['o_payment_status'],
						'txn_id' => $response_decode['data']['id'],
						'processed_on' => ':NOW()'
				));
			}
			pjBookingPaymentModel::factory()
				->where('booking_id', $booking_arr['id'])
				->where('payment_type', 'online')
				->modifyAll(array('status' => 'paid'));

            pjFrontEnd::pjActionConfirmSend($this->option_arr, $booking_arr, PJ_SALT, 'confirm');
            
			if ($booking_arr['is_return'] == 'T') {
			    $return_arr = $pjBookingModel
			     ->reset ()
			     ->select ( 't1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location' )
			     ->join ( 'pjBus', "t2.id=t1.bus_id", 'left outer' )
			     ->join ( 'pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='" . $this->getLocaleId () . "'", 'left outer' )
			     ->join ( 'pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='" . $this->getLocaleId () . "'", 'left outer' )
			     ->join ( 'pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='" . $this->getLocaleId () . "'", 'left outer' )
			     ->find ( $booking_arr ['back_id'] )->getData ();
			    
			    $return_tickets = pjBookingTicketModel::factory ()
			     ->join ( 'pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )
			     ->join ( 'pjTicket', "t3.id=t1.ticket_id", 'left' )
			     ->select ( 't1.*, t2.content as title' )
			     ->where ( 'booking_id', $booking_arr ['back_id'] )
			     ->findAll ()->getData ();
			    
			    $return_arr ['tickets'] = $return_tickets;
			    
			    pjFrontEnd::pjActionConfirmSend ( $this->option_arr, $return_arr, PJ_SALT, 'confirm');
			}
		} else {
			$this->log('Booking not confirmed');
		}
		pjUtil::redirect($this->option_arr['o_thank_you_page']);
	}
    
	public function pjActionCancel()
	{
		$this->setLayout('pjActionCancel');
		
		$pjBookingModel = pjBookingModel::factory();
		
		if (isset($_POST['booking_cancel']))
		{
			$booking_arr = pjBookingModel::factory()
				->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
				->join('pjBus', "t2.id=t1.bus_id", 'left outer')
				->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
				->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
				->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
				->find($_POST['id'])
				->getData();
			if (count($booking_arr) > 0)
			{
				$sql = "UPDATE `".$pjBookingModel->getTable()."` SET status = 'cancelled' WHERE SHA1(CONCAT(`id`, `created`, '".PJ_SALT."')) = '" . $_POST['hash'] . "'";
				
				$pjBookingModel->reset()->execute($sql);

				$booking_arr['tickets'] = pjBookingTicketModel::factory()
					->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjTicket', "t3.id=t1.ticket_id", 'left')
					->select('t1.*, t2.content as title')
					->where('booking_id', $booking_arr['id'])
					->findAll()
					->getData();
				
				pjFrontEnd::pjActionConfirmSend($this->option_arr, $booking_arr, PJ_SALT, 'cancel');
				
				pjUtil::redirect($_SERVER['PHP_SELF'] . '?controller=pjFrontEnd&action=pjActionCancel&err=200');
			}
		}else{
			if (isset($_GET['hash']) && isset($_GET['id']))
			{
				$arr = $pjBookingModel
					->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location, t6.content as country_title')
					->join('pjBus', "t2.id=t1.bus_id", 'left outer')
					->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjMultiLang', "t6.model='pjCountry' AND t6.foreign_id=t1.c_country AND t6.field='name' AND t6.locale='".$this->getLocaleId()."'", 'left outer')
					->find($_GET['id'])->getData();
										
				if (count($arr) == 0)
				{
					$this->set('status', 2);
				}else{
					if ($arr['status'] == 'cancelled')
					{
						$this->set('status', 4);
					}else{
						$hash = sha1($arr['id'] . $arr['created'] . PJ_SALT);
						if ($_GET['hash'] != $hash)
						{
							$this->set('status', 3);
						}else{
							if($arr['booking_datetime'] > date('Y-m-d H:i:s'))
							{
								$arr['tickets'] = pjBookingTicketModel::factory()
									->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
									->join('pjTicket', "t3.id=t1.ticket_id", 'left')
									->select('t1.*, t2.content as title')
									->where('booking_id', $arr['id'])
									->findAll()
									->getData();
											 
								$this->set('arr', $arr);
							}else{
								$this->set('status', 5);
							}
						}
					}
                }
			}elseif (!isset($_GET['err'])) {
				$this->set('status', 1);
			}
		}
	}
	
	public function pjActionPrintTickets()
	{
        require_once('app/lib/qr_code/qrlib.php');
        $print_ticket_id = $_GET['id'];
        $print_ticket_hash = $_GET['hash'];
        $qr_code_png_folder = PJ_INSTALL_PATH.'app/lib/qr_code/temp/';
        $qr_code_png_web_folder = PJ_INSTALL_URL.'app/lib/qr_code/temp/';
        $qr_code_filename = $qr_code_png_folder.'ticket_'.$print_ticket_id.'_'.time().'.png';
        $qr_error_correction_level = 'L';
        $qr_code_matrix_point_size = min(max((int)6, 1), 10);

		$this->setLayout('pjActionPrint2');
	
		$pjBookingModel = pjBookingModel::factory();
	
		$arr = $pjBookingModel
			->select('t1.*, t2.content as from_location, t3.content as to_location')
			->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.pickup_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
			->join('pjMultiLang', "t3.model='pjCity' AND t3.foreign_id=t1.return_id AND t3.field='name' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
			->find($_GET['id'])
			->getData();
	
		if(!empty($arr))
		{
			if ($arr['is_return'] == 'T')
			{
				$arr['return_arr'] = $pjBookingModel
					->reset()
					->select('t1.*, t2.content as from_location, t3.content as to_location')
					->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.pickup_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjMultiLang', "t3.model='pjCity' AND t3.foreign_id=t1.return_id AND t3.field='name' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
					->find($arr['back_id'])->getData();
			}
				
			$hash = sha1($arr['id'].$arr['created'].PJ_SALT);
			if($hash == $_GET['hash'])
			{
				if($arr['status'] == 'confirmed')
				{
					$arr['tickets'] = pjBookingTicketModel::factory()->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
						->join('pjTicket', "t3.id=t1.ticket_id", 'left')
						->select('t1.*, t2.content as title, (SELECT TP.price FROM `'.pjPriceModel::factory()->getTable().'` AS TP WHERE TP.ticket_id = t1.ticket_id AND TP.bus_id = '.$arr['bus_id'].' AND TP.from_location_id = '.$arr['pickup_id'].' AND TP.to_location_id= '.$arr['return_id']. ' AND is_return = "F") as price')
						->where('booking_id', $arr['id'])
						->findAll()->getData();
	
					$pjCityModel = pjCityModel::factory();
					$pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($arr['pickup_id'])->getData();
					$to_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($arr['return_id'])->getData();
					$arr['from_location'] = $pickup_location['name'];
					$arr['to_location'] = $to_location['name'];
	
					$pjMultiLangModel = pjMultiLangModel::factory();
					$lang_template = $pjMultiLangModel
						->reset()
						->select('t1.*')
						->where('t1.model','pjOption')
						->where('t1.locale', $this->getLocaleId())
						->where('t1.field', 'o_ticket_template')
						->limit(0, 1)
						->findAll()->getData();
					$template = '';
					if (count($lang_template) === 1)
					{
						$template = $lang_template[0]['content'];
					}

					$data = pjAppController::getTemplate($this->option_arr, $arr, PJ_SALT, $this->getLocaleId());
                    
                    $qr_code_data = PJ_INSTALL_URL.'index.php?controller=pjFrontEnd&action=pjActionViewTicket&id='.$print_ticket_id.'&hash='.$print_ticket_hash;            

                    QRcode::png($qr_code_data, $qr_code_filename, $qr_error_correction_level, $qr_code_matrix_point_size, 2);  
                
                    $data['search'][] = '{QRCode}';
                    $data['replace'][] = '<img src="'.$qr_code_png_web_folder.basename($qr_code_filename).'" />';

                    $template_arr = str_replace($data['search'], $data['replace'], $template);
					$this->set('template_arr', $template_arr);
				}
			}elseif ($arr['status'] == 'pending'){
				$this->set('pending_booking', true);
			}
		} else {
			$this->set('status', 2);
		}
	}

	public function pjActionConfirmSend($option_arr, $booking_arr, $salt, $opt, $is_send_to_admin=true,$is_return=false)
	{
		$Email = new pjEmail();
		if ($option_arr['o_send_email'] == 'smtp')
		{
			$Email
				->setTransport('smtp')
				->setSmtpHost($option_arr['o_smtp_host'])
				->setSmtpPort($option_arr['o_smtp_port'])
				->setSmtpUser($option_arr['o_smtp_user'])
				->setSmtpPass($option_arr['o_smtp_pass'])
				->setSender($option_arr['o_smtp_user'])
			;
		}
		$Email->setContentType('text/html');

		$tokens = pjAppController::getData($option_arr, $booking_arr, PJ_SALT, $this->getLocaleId());
            		
		$pjMultiLangModel = pjMultiLangModel::factory();
		
		$locale_id = isset($booking_arr['locale_id']) && (int) $booking_arr['locale_id'] > 0 ? (int) $booking_arr['locale_id'] : $this->getLocaleId();
	
		$admin_email = $this->getAdminEmail();
		
		$admin_emails = $this->getAdminEmails();
        $admin_phones = $this->getAdminPhones();
		
        $from_email = $admin_email;
		if(!empty($option_arr['o_sender_email']))
		{
            $from_email = $option_arr['o_sender_email'];
        }
       
        
        //payment
        if ($is_send_to_admin && !empty($admin_emails) && $option_arr['o_admin_email_payment'] == 1 && $opt == 'payment')
		{	
			$lang_message = $pjMultiLangModel->reset()->select('t1.*')
											 ->where('t1.model','pjOption')
											 ->where('t1.locale', $locale_id)
											 ->where('t1.field', 'o_admin_email_payment_message')
											 ->limit(0, 1)
											 ->findAll()->getData();
			$lang_subject = $pjMultiLangModel->reset()->select('t1.*')
											 ->where('t1.model','pjOption')
											 ->where('t1.locale', $locale_id)
											 ->where('t1.field', 'o_admin_email_payment_subject')
											 ->limit(0, 1)
											 ->findAll()->getData();

			if (count($lang_message) === 1 && count($lang_subject) === 1)
			{
				$message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);
				$message = pjUtil::textToHtml($message);
				$subject = $lang_subject[0]['content'];
				
				foreach($admin_emails as $email)
				{
					$Email
						->setTo($email)
						->setFrom($from_email)
						->setSubject($subject)
						->send($message);
				}
			}
		}
		if ($booking_arr['c_email'] && $option_arr['o_email_payment'] == 1 && $opt == 'payment')
		{
			$lang_message = $pjMultiLangModel->reset()->select('t1.*')
											 ->where('t1.model','pjOption')
											 ->where('t1.locale', $locale_id)
											 ->where('t1.field', 'o_email_payment_message')
											 ->limit(0, 1)
											 ->findAll()->getData();
			$lang_subject = $pjMultiLangModel->reset()->select('t1.*')
											 ->where('t1.model','pjOption')
											 ->where('t1.locale', $locale_id)
											 ->where('t1.field', 'o_email_payment_subject')
											 ->limit(0, 1)
											 ->findAll()->getData();
				
			if (count($lang_message) === 1 && count($lang_subject) === 1)
			{
				$message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);
				
				$Email
					->setTo($booking_arr['c_email'])
					->setFrom($from_email)
					->setSubject($lang_subject[0]['content'])
					->send(pjUtil::textToHtml($message));
			}
		}		
		if($is_send_to_admin && !empty($admin_phones) && $opt == 'payment')
		{
            
			$lang_message = $pjMultiLangModel->reset()->select('t1.*')
												 ->where('t1.model','pjOption')
												 ->where('t1.locale', $locale_id)
												 ->where('t1.field', 'o_admin_sms_payment_message')
												 ->limit(0, 1)
												 ->findAll()->getData();
			
			if (count($lang_message) === 1 && !empty($admin_phones))
			{
				$message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);
                
                $params = array(
					'text' => $message,
					'type' => 'unicode',
					'key' => md5($option_arr['private_key'] . PJ_SALT)
				);
				foreach($admin_phones as $phone)
				{
                    if ($phone) { 
                        $params['number'] = $phone;
                        //$this->requestAction(array('controller' => 'pjSms', 'action' => 'pjActionSend', 'params' => $params), array('return'));
                        $this->requestAction(array('controller' => 'pjSendSms', 'action' => 'pjActionSendSms', 'params' => $params), array('return'));
                    }
				}
			}
		}
		if(!empty($booking_arr['c_phone']) && $opt == 'payment')
		{
			$lang_message = $pjMultiLangModel->reset()->select('t1.*')
				->where('t1.model','pjOption')
				->where('t1.locale', $locale_id)
				->where('t1.field', 'o_sms_payment_message')
				->limit(0, 1)
				->findAll()->getData();
				
			if (count($lang_message) === 1)
			{
				$message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);
				$params = array(
						'text' => $message,
						'type' => 'unicode',
						'key' => md5($option_arr['private_key'] . PJ_SALT)
				);
				$params['number'] = $booking_arr['c_phone'];
                //$this->requestAction(array('controller' => 'pjSms', 'action' => 'pjActionSend', 'params' => $params), array('return'));
                $this->requestAction(array('controller' => 'pjSendSms', 'action' => 'pjActionSendSms', 'params' => $params), array('return'));
			}
        }
        //payment

        //pending
        /*message for API starts*/
        $sms_field = 'o_api_cash_notify_message';
        $lang_message = $pjMultiLangModel->reset()->select('t1.*')
            ->where('t1.model','pjOption')
            ->where('t1.locale', $locale_id)
            ->where('t1.field', $sms_field)
            ->limit(0, 1)
            ->findAll()->getData();
    
        if (count($lang_message) === 1)
        {
            $message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);
            $_SESSION['api_message'] = $message; //Using in pjApi::pjActionBooking
        }
        /*message for API ends*/   

        /*cash message for website starts*/  
        if ($booking_arr['payment_method'] == 'cash' && $opt == 'pending') {
            $sms_field = 'o_sms_site_notify_message';
            $lang_message = $pjMultiLangModel->reset()->select('t1.*')
                    ->where('t1.model','pjOption')
                    ->where('t1.locale', $locale_id)
                    ->where('t1.field', $sms_field)
                    ->limit(0, 1)
                    ->findAll()->getData();

            $message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);

            if ($is_return) {
                $_SESSION['cash_message'] = $_SESSION['cash_message'].'<div style="width:100%;margin-bottom:30px;border-bottom:1px solid #999999">&nbsp;</div>'.$message;
            } else {
                $_SESSION['cash_message'] = $message;
            }
        }
        /*cash message for website ends*/  

        /*confirm message for website starts*/  
        if ($opt == 'confirm') {
            $sms_field = 'o_sms_site_common_notify_message';
            $lang_message = $pjMultiLangModel->reset()->select('t1.*')
                    ->where('t1.model','pjOption')
                    ->where('t1.locale', $locale_id)
                    ->where('t1.field', $sms_field)
                    ->limit(0, 1)
                    ->findAll()->getData();

            $message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);

            if ($is_return) {
                $_SESSION['site_common_message'] = $_SESSION['site_common_message'].'<div style="width:100%;margin-bottom:30px;border-bottom:1px solid #999999">&nbsp;</div>'.$message;
            } else {
                $_SESSION['site_common_message'] = $message;
            }
        }
        /*confirm message for website ends*/

        if ($booking_arr['c_email'] && $opt == 'pending')
		{  
            $email_field = ($booking_arr['payment_method'] == 'cash') ? 'o_email_cash_notify_message' : 'o_email_pending_message';

			$lang_message = $pjMultiLangModel->reset()->select('t1.*')
											 ->where('t1.model','pjOption')
											 ->where('t1.locale', $locale_id)
											 ->where('t1.field', $email_field)
											 ->limit(0, 1)
											 ->findAll()->getData();
			$lang_subject = $pjMultiLangModel->reset()->select('t1.*')
											 ->where('t1.model','pjOption')
											 ->where('t1.locale', $locale_id)
											 ->where('t1.field', 'o_email_pending_subject')
											 ->limit(0, 1)
                                             ->findAll()->getData();
                                             
            $email_subject = ($booking_arr['payment_method'] == 'cash') ? 'Your free seat reservation' : $lang_message[0]['content'];
						   
			if (count($lang_message) === 1)
			{
                $message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);
            
                $Email
                    ->setTo($booking_arr['c_email'])
                    ->setFrom($from_email)
                    ->setSubject($email_subject)
                    ->send(pjUtil::textToHtml($message));
			}
        }
        if(!empty($booking_arr['c_phone']) && $opt == 'pending')
		{
            $sms_field = ($booking_arr['payment_method'] == 'cash') ? 'o_sms_cash_notify_message' : 'o_sms_pending_message';

			$lang_message = $pjMultiLangModel->reset()->select('t1.*')
				->where('t1.model','pjOption')
				->where('t1.locale', $locale_id)
				->where('t1.field', $sms_field)
				->limit(0, 1)
				->findAll()->getData();
		
			if (count($lang_message) === 1)
			{
                $message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);

                $params = array(
						'text' => $message,
						'type' => 'unicode',
						'key' => md5($option_arr['private_key'] . PJ_SALT)
				);
				$params['number'] = $booking_arr['c_phone'];
                //$this->requestAction(array('controller' => 'pjSms', 'action' => 'pjActionSend', 'params' => $params), array('return'));
                $this->requestAction(array('controller' => 'pjSendSms', 'action' => 'pjActionSendSms', 'params' => $params), array('return'));
            }
        }	
        //pending

        //confirm
        if ($is_send_to_admin && $admin_emails && $option_arr['o_admin_email_confirmation'] == 1 && ($opt == 'confirm' || $opt == 'pending'))
		{	
			$lang_message = $pjMultiLangModel->reset()->select('t1.*')
											 ->where('t1.model','pjOption')
											 ->where('t1.locale', $locale_id)
											 ->where('t1.field', 'o_admin_email_confirmation_message')
											 ->limit(0, 1)
											 ->findAll()->getData();
			$lang_subject = $pjMultiLangModel->reset()->select('t1.*')
											 ->where('t1.model','pjOption')
											 ->where('t1.locale', $locale_id)
											 ->where('t1.field', 'o_admin_email_confirmation_subject')
											 ->limit(0, 1)
											 ->findAll()->getData();
						   
			if (count($lang_message) === 1 && count($lang_subject) === 1)
			{
				$message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);
				
				foreach($admin_emails as $email)
				{
					$Email
						->setTo($email)
						->setFrom($from_email)
						->setSubject($lang_subject[0]['content'])
						->send(pjUtil::textToHtml($message));
				}
			}
		}
		if ($booking_arr['c_email'] && $option_arr['o_email_confirmation'] == 1 && $opt == 'confirm')
		{
            //$email_field = ($booking_arr['payment_method'] == 'cash') ? 'o_email_cash_notify_message' : 'o_email_confirmation_message';
            $email_field = 'o_email_confirmation_message';

			$lang_message = $pjMultiLangModel->reset()->select('t1.*')
											 ->where('t1.model','pjOption')
											 ->where('t1.locale', $locale_id)
											 ->where('t1.field', $email_field)
											 ->limit(0, 1)
											 ->findAll()->getData();
			$lang_subject = $pjMultiLangModel->reset()->select('t1.*')
											 ->where('t1.model','pjOption')
											 ->where('t1.locale', $locale_id)
											 ->where('t1.field', 'o_email_confirmation_subject')
											 ->limit(0, 1)
											 ->findAll()->getData();
						   
			if (count($lang_message) === 1 && count($lang_subject) === 1)
			{
                $message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);
                
				$Email
					->setTo($booking_arr['c_email'])
					->setFrom($from_email)
					->setSubject($lang_subject[0]['content'])
					->send(pjUtil::textToHtml($message));
			}
		}		
		if($is_send_to_admin && !empty($admin_phones) && ($opt == 'confirm' || $opt == 'pending'))
		{
			$lang_message = $pjMultiLangModel->reset()->select('t1.*')
												 ->where('t1.model','pjOption')
												 ->where('t1.locale', $locale_id)
												 ->where('t1.field', 'o_admin_sms_confirmation_message')
												 ->limit(0, 1)
												 ->findAll()->getData();
			if (count($lang_message) === 1)
			{
				$message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);

                $params = array(
					'text' => $message,
					'type' => 'unicode',						
					'key' => md5($option_arr['private_key'] . PJ_SALT)
                );

				foreach($admin_phones as $phone)
				{
                    if ($phone) { 
                        $params['number'] = $phone;
                        //$this->requestAction(array('controller' => 'pjSms', 'action' => 'pjActionSend', 'params' => $params), array('return'));
                        $this->requestAction(array('controller' => 'pjSendSms', 'action' => 'pjActionSendSms', 'params' => $params), array('return'));
                    }
				}
			}
		}
		if(!empty($booking_arr['c_phone']) && $opt == 'confirm')
		{
            //$sms_field = ($booking_arr['payment_method'] == 'cash') ? 'o_sms_cash_notify_message' : 'o_sms_confirmation_message';
            $sms_field = 'o_sms_confirmation_message';

			$lang_message = $pjMultiLangModel->reset()->select('t1.*')
				->where('t1.model','pjOption')
				->where('t1.locale', $locale_id)
				->where('t1.field', $sms_field)
				->limit(0, 1)
				->findAll()->getData();
		
			if (count($lang_message) === 1)
			{
				$message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);
				$params = array(
						'text' => $message,
						'type' => 'unicode',
						'key' => md5($option_arr['private_key'] . PJ_SALT)
				);
				$params['number'] = $booking_arr['c_phone'];
                //$this->requestAction(array('controller' => 'pjSms', 'action' => 'pjActionSend', 'params' => $params), array('return'));
                $this->requestAction(array('controller' => 'pjSendSms', 'action' => 'pjActionSendSms', 'params' => $params), array('return'));
			}
        }
        //confirm

        //cancel
        if ($is_send_to_admin && $option_arr['o_admin_email_cancel'] == 1 && $opt == 'cancel' && !empty($admin_emails))
		{	
			$lang_message = $pjMultiLangModel->reset()->select('t1.*')
											 ->where('t1.model','pjOption')
											 ->where('t1.locale', $locale_id)
											 ->where('t1.field', 'o_admin_email_cancel_message')
											 ->limit(0, 1)
											 ->findAll()->getData();
			$lang_subject = $pjMultiLangModel->reset()->select('t1.*')
											 ->where('t1.model','pjOption')
											 ->where('t1.locale', $locale_id)
											 ->where('t1.field', 'o_admin_email_cancel_subject')
											 ->limit(0, 1)
											 ->findAll()->getData();
						   
			if (count($lang_message) === 1 && count($lang_subject) === 1)
			{
				$message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);

				foreach($admin_emails as $email)
				{
					$Email
						->setTo($email)
						->setFrom($from_email)
						->setSubject($lang_subject[0]['content'])
						->send(pjUtil::textToHtml($message));
				}
			}
        }
        
        if($is_send_to_admin && !empty($admin_phones) && $opt == 'cancel')
		{
			$lang_message = $pjMultiLangModel->reset()->select('t1.*')
												 ->where('t1.model','pjOption')
												 ->where('t1.locale', $locale_id)
												 ->where('t1.field', 'o_admin_sms_cancellation_message')
												 ->limit(0, 1)
												 ->findAll()->getData();
			
			if (count($lang_message) === 1 && !empty($admin_phones))
			{
				$message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);

                $params = array(
                    'text' => $message,
                    'type' => 'unicode',
                    'key' => md5($option_arr['private_key'] . PJ_SALT)
                );

                foreach($admin_phones as $phone)
				{   
                    if ($phone) {                
                        $params['number'] = $phone;
                        //$this->requestAction(array('controller' => 'pjSms', 'action' => 'pjActionSend', 'params' => $params), array('return'));
                        $this->requestAction(array('controller' => 'pjSendSms', 'action' => 'pjActionSendSms', 'params' => $params), array('return'));
                    }
                }
            }
        }

		if ($booking_arr['c_email'] && $option_arr['o_email_cancel'] == 1 && $opt == 'cancel')
		{
			$lang_message = $pjMultiLangModel->reset()->select('t1.*')
											 ->where('t1.model','pjOption')
											 ->where('t1.locale', $locale_id)
											 ->where('t1.field', 'o_email_cancel_message')
											 ->limit(0, 1)
											 ->findAll()->getData();
			$lang_subject = $pjMultiLangModel->reset()->select('t1.*')
											 ->where('t1.model','pjOption')
											 ->where('t1.locale', $locale_id)
											 ->where('t1.field', 'o_email_cancel_subject')
											 ->limit(0, 1)
											 ->findAll()->getData();
						   
			if (count($lang_message) === 1 && count($lang_subject) === 1)
			{
				$message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);

				$Email
					->setTo($booking_arr['c_email'])
					->setFrom($from_email)
					->setSubject($lang_subject[0]['content'])
					->send(pjUtil::textToHtml($message));
			}
        }

        if(!empty($booking_arr['c_phone']) && $opt == 'cancel')
		{
			$lang_message = $pjMultiLangModel->reset()->select('t1.*')
				->where('t1.model','pjOption')
				->where('t1.locale', $locale_id)
				->where('t1.field', 'o_sms_cancellation_message')
				->limit(0, 1)
				->findAll()->getData();

			if (count($lang_message) === 1)
			{
				$message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);
 
                $params = array(
						'text' => $message,
						'type' => 'unicode',
						'key' => md5($option_arr['private_key'] . PJ_SALT)
				);
				$params['number'] = $booking_arr['c_phone'];
                //$this->requestAction(array('controller' => 'pjSms', 'action' => 'pjActionSend', 'params' => $params), array('return'));
                $this->requestAction(array('controller' => 'pjSendSms', 'action' => 'pjActionSendSms', 'params' => $params), array('return'));
			}
        }
        //cancel 
    }
    
    public function pjActionViewTicket()
	{		
        require_once('app/lib/qr_code/qrlib.php');
        $print_ticket_id = $_GET['id'];
        $print_ticket_hash = $_GET['hash'];
        $qr_code_png_folder = PJ_INSTALL_PATH.'app/lib/qr_code/temp/';
        $qr_code_png_web_folder = PJ_INSTALL_URL.'app/lib/qr_code/temp/';
        $qr_code_filename = $qr_code_png_folder.'ticket_'.$print_ticket_id.'_'.time().'.png';
        $qr_error_correction_level = 'L';
        $qr_code_matrix_point_size = min(max((int)6, 1), 10);              

        $this->setLayout('pjActionEmpty');
        
        $pjBookingModel = pjBookingModel::factory();
                        
        $arr = $pjBookingModel->find($_GET['id'])->getData();
        if (empty($arr))
        {
            exit('No ticket');
        }
        $hash = sha1($arr['id'].$arr['created'].PJ_SALT);
        if($hash != $_GET['hash'])
        {
            exit('No ticket');
        }
        if($arr['status'] == 'confirmed')
        {
            $price_tbl = pjPriceModel::factory()->getTable();
            
            $pjBookingTicketModel = pjBookingTicketModel::factory();
            $tickets = $pjBookingTicketModel
                ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                ->select('t1.*, t2.content as title, (SELECT TP.price FROM `'.$price_tbl.'` AS TP WHERE TP.ticket_id = t1.ticket_id AND TP.bus_id = '.$arr['bus_id'].' AND TP.from_location_id = '.$arr['pickup_id'].' AND TP.to_location_id= '.$arr['return_id']. ' AND is_return = "F" LIMIT 1) as price')
                ->where('booking_id', $arr['id'])
                ->findAll()->getData();

            $arr['tickets'] = $tickets;
            
            $pjCityModel = pjCityModel::factory();
            $pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($arr['pickup_id'])->getData();
            $to_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($arr['return_id'])->getData();
            $arr['from_location'] = $pickup_location['name'];
            $arr['to_location'] = $to_location['name'];	
                
            $pjMultiLangModel = pjMultiLangModel::factory();
            $lang_template = $pjMultiLangModel
                ->reset()->select('t1.*')
                ->where('t1.model','pjOption')
                ->where('t1.locale', $this->getLocaleId())
                ->where('t1.field', 'o_ticket_template')
                ->limit(0, 1)
                ->findAll()->getData();
            $template = '';											 
            if (count($lang_template) === 1)
            {
                $template = $lang_template[0]['content'];
            }                          

            $data = pjAppController::getTemplate($this->option_arr, $arr, PJ_SALT, $this->getLocaleId());       

            $qr_code_data = PJ_INSTALL_URL.'index.php?controller=pjFrontPublic&action=pjActionPrintTickets&id='.$print_ticket_id.'&hash='.$print_ticket_hash;            
            QRcode::png($qr_code_data, $qr_code_filename, $qr_error_correction_level, $qr_code_matrix_point_size, 2);   
            
            $data['search'][] = '{QRCode}';
            $data['replace'][] = '<img src="'.$qr_code_png_web_folder.basename($qr_code_filename).'" />';

            $template_arr = str_replace($data['search'], $data['replace'], $template);
            
            $this->set('template_arr', $template_arr);
        }elseif ($arr['status'] == 'pending'){
            exit('No ticket');
        }
    }	
    
    public function pjActionConfirmAgentEmail() {
        $code = $_GET['code'];
        $success = 1;

        if ($code) {
            $code_array = pjUserModel::factory()
            ->where('confirm_code',$code)
            ->findAll()
            ->getData();

            if (count($code_array)) {
                $result = pjUserModel::factory()->where('id', $code_array[0]['id'])->modifyAll(array(
                    'confirm_code' => '',
                    'status' => 'T',
                    'is_active' => 'T'
                ));
                $success = 1;
                $message = 'Your email has been verified successfully. You can login now.';
            } else {
                $success = 0;
                $message = 'Sorry, you are not authorized';
            }
        } else {
            $success = 0;
            $message = 'Sorry, you are not authorized';
        }

        $params = array(
            'success' => $success,
            'message' => $message
        );

        $this->set('params', $params);
    }

    public function pjActionAPI() {
        $postData = '';
        $params = array('transactionType'=>'processPayment',
        "paymentData" => array(
                "number" => "12312312313123123",
                "expirationMonth" => "12",
                "expirationYear" => "2022",
                "securityCode" => "123",
                "totalAmount" => 1,
                "currency" => "USD"
            ),
            "RequestID" => 23233333,
            "securityToken" => 'x121ADwrVG678GHB9087HYTRwwss$uj&ggg%ggDFrtPl(IkJ)#fFZXC'
        );
        $postData = http_build_query($params);

        $url = 'https://pgc16.com:8888/api/transactions';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);


        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            echo $error_msg;
        }
        //echo $response;
        curl_close($ch);

        $jsonArrayResponse = json_decode($response,true);

        echo "<pre>";
        print_r($jsonArrayResponse);
        exit;
    }

    function pjSendTicket() {
        $params = $this->getParams();
        $is_api = (isset($params['is_api'])) ? true : false;

        if ($is_api) {
            $mt_phone_country = '';
            $mt_phone = $params['phone'];
        } else {
            $mt_phone_country = trim($_POST['mt_phone_country']);
            $mt_phone = trim($_POST['mt_phone']);
        }        

        $country_arr = pjCountryModel::factory()                            
                ->find($mt_phone_country)->getData();

        $ticket_phone = $mt_phone;
        if ($mt_phone) {
            if (strpos($mt_phone,'+') !==0 && $mt_phone_country) {
                $phone_explode = explode('_',$mt_phone_country);
                $country_arr = pjCountryModel::factory()                            
                        ->find($phone_explode[0])->getData();

                if (isset($country_arr['phone_code']) && $country_arr['phone_code']) {
                    $ticket_phone = $country_arr['phone_code'].$mt_phone;
                }
            }
        }


        $booking_phone = pjBookingModel::factory()
        ->where('c_phone',$ticket_phone)
        ->where('booking_date >=',date('Y-m-d'))
        ->findAll()
        ->getData();

        if (count($booking_phone)) {
            $sent = false;
            foreach ($booking_phone as $booking) {
                $pjBookingModel = pjBookingModel::factory();

                $booking_arr = $pjBookingModel
                    ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
                    ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                    ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                    ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                    ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                    ->find($booking['id'])
                    ->getData();
                    
                $booking_arr['tickets'] = pjBookingTicketModel::factory()
                    ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                    ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                    ->select('t1.*, t2.content as title')
                    ->where('booking_id', $booking['id'])
                    ->findAll()
                    ->getData();

                switch($booking_arr['status']) {
                    case 'payment':
                        $status = 'payment';
                        break;

                    case 'confirmed':
                        $status = 'confirm';
                        break;

                    case 'cancelled':
                        $status = 'cancel';
                        break;

                    default:
                        $status = $booking_arr['status'];
                }

                if ($status != 'cancel') {
                    $sent = true;
                    pjFrontEnd::pjActionConfirmSend($this->option_arr, $booking_arr, PJ_SALT, $status, false);
                }
                
                if ($booking_arr['is_return'] == 'T') {
                    $return_arr = $pjBookingModel
                    ->reset ()
                    ->select ( 't1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location' )
                    ->join ( 'pjBus', "t2.id=t1.bus_id", 'left outer' )
                    ->join ( 'pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='" . $this->getLocaleId () . "'", 'left outer' )
                    ->join ( 'pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='" . $this->getLocaleId () . "'", 'left outer' )
                    ->join ( 'pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='" . $this->getLocaleId () . "'", 'left outer' )
                    ->find ( $booking_arr ['back_id'] )->getData ();
                    
                    $return_tickets = pjBookingTicketModel::factory ()
                    ->join ( 'pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )
                    ->join ( 'pjTicket', "t3.id=t1.ticket_id", 'left' )
                    ->select ( 't1.*, t2.content as title' )
                    ->where ( 'booking_id', $booking_arr ['back_id'] )
                    ->findAll ()->getData ();
                    
                    $return_arr ['tickets'] = $return_tickets;
                    
                    switch($return_arr['status']) {
                        case 'payment':
                            $status = 'payment';
                            break;

                        case 'confirmed':
                            $status = 'confirm';
                            break;

                        case 'cancelled':
                            $status = 'cancel';
                            break;

                        default:
                            $status = $booking_arr['status'];
                    }
                    
                    if ($status != 'cancel') {
                        $sent = true;
                        pjFrontEnd::pjActionConfirmSend ( $this->option_arr, $return_arr, PJ_SALT, $status, false);
                    }
                }
            }
            if ($sent) {
                if ($is_api) {
                    $message = 'Your booking detail has been sent to your Phone';
                } else {
                    $message = 'Your booking detail has been sent to your Phone and Email';
                }
            } else {
                $message = 'No booked tickets found with that Phone Number';
            }            
        } else {
            $message = 'No booked tickets found with that Phone Number';
        }
        $data['success'] = 1;
        $data['message'] = $message;

        if ($is_api) {
            return $data;
        } else {
            echo json_encode($data);
            exit;
        }
    } 

    public function pjActionBookingMessage() {
        if (isset($_SESSION['site_common_message']) && $_SESSION['site_common_message']) {
            $message = $_SESSION['site_common_message'];
        } else {
            $message = 'Sorry, your booking failed. Please try another one.';
        }
        $this->set('message', $message);
    }
}
?>