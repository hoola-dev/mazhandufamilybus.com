<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjFrontPublic extends pjFront
{
	public function __construct()
	{
		parent::__construct();
		
		$this->setAjax(true);
		
		$this->setLayout('pjActionEmpty');
	}

	public function pjActionSearch()
	{
		$this->setAjax(true);
	
		if ($this->isXHR() || isset($_GET['_escaped_fragment_']))
		{
			$_SESSION[$this->defaultStep]['1_passed'] = true;
	
			$pjCityModel = pjCityModel::factory();
			$pjRouteDetailModel = pjRouteDetailModel::factory();
				
			$from_location_arr = $pjCityModel
				->reset()
				->select('t1.*, t2.content as name')
				->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->where("t1.id IN(SELECT TRD.from_location_id FROM `".$pjRouteDetailModel->getTable()."` AS TRD)")
				->orderBy("t2.content ASC")
				->findAll()
				->getData();
				
			$to_location_arr = $pjCityModel
				->reset()
				->select('t1.*, t2.content as name')
				->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->where("t1.id IN(SELECT TRD.to_location_id FROM `".$pjRouteDetailModel->getTable()."` AS TRD)")
				->orderBy("t2.content ASC")
				->findAll()
				->getData();
			if($this->_is('pickup_id'))
			{
				$pickup_id = $this->_get('pickup_id');
				$where = "WHERE TRD.from_location_id=" . $pickup_id;
				$return_location_arr = pjCityModel::factory()
					->reset()
					->select('t1.*, t2.content as name')
					->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->where("t1.id IN(SELECT TRD.to_location_id FROM `".$pjRouteDetailModel->getTable()."` AS TRD $where)")
					->orderBy("t2.content ASC")
					->findAll()
					->getData();
	
				$this->set('return_location_arr', $return_location_arr);
			}
			$image = pjOptionModel::factory()
				->where('t1.foreign_id', $this->getForeignId())
				->where('t1.key', 'o_image_path')
				->orderBy('t1.order ASC')
				->findAll()
				->getData();
			$content = pjMultiLangModel::factory()->select('t1.*')
				->where('t1.model','pjOption')
				->where('t1.locale', $this->getLocaleId())
				->where('t1.field', 'o_content')
				->limit(0, 1)
				->index("FORCE KEY (`foreign_id`)")
				->findAll()->getData();
	
			$this->set('from_location_arr', $from_location_arr);
			$this->set('to_location_arr', $to_location_arr);
			$this->set('content_arr', compact('content', 'image'));
			$this->set('status', 'OK');
		}
	}
	
	public function pjActionSeats()
	{
		if ($this->isXHR() || isset($_GET['_escaped_fragment_']))
		{
			$_SESSION[$this->defaultStep]['2_passed'] = true;
	
			if (isset($_SESSION[$this->defaultStore]) && count($_SESSION[$this->defaultStore]) > 0 && $this->isBusReady() == true)
			{
				$booking_period = array();
				if($this->_is('booking_period'))
				{
					$booking_period = $this->_get('booking_period');
				}
				$booked_data = array();
				if($this->_is('booked_data'))
				{
					$booked_data = $this->_get('booked_data');
				}
	
				if($this->_is('bus_id_arr'))
				{
					$bus_id_arr = $this->_get('bus_id_arr');
					$pickup_id = $this->_get('pickup_id');
					$return_id = $this->_get('return_id');
					$date = $this->_get('date');
						
					$bus_list = $this->getBusList($pickup_id, $return_id, $bus_id_arr, $booking_period, $booked_data, $date, 'F');
						
					$booking_period = $bus_list['booking_period'];
						
					$this->_set('booking_period', $booking_period);
						
					$this->set('bus_type_arr', $bus_list['bus_type_arr']);
					$this->set('booked_seat_arr', $bus_list['booked_seat_arr']);
					$this->set('seat_arr', $bus_list['seat_arr']);
					$this->set('selected_seat_arr', $bus_list['selected_seat_arr']);
					$this->set('bus_arr', $bus_list['bus_arr']);
					$this->set('ticket_columns', $bus_list['ticket_columns']);						
				}
				
				$pjCityModel = pjCityModel::factory();
				$pickup_location = $pjCityModel->select ( 't1.*, t2.content as name' )->join ( 'pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )->find ( $this->_get('pickup_id') )->getData ();
				$return_location = $pjCityModel->reset ()->select ( 't1.*, t2.content as name' )->join ( 'pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )->find ( $this->_get('return_id') )->getData ();
				$this->set('from_location', $pickup_location['name']);
                $this->set('to_location', $return_location['name']);
                
					
				if($this->_is('return_bus_id_arr'))
				{
					$bus_id_arr = $this->_get('return_bus_id_arr');
					$pickup_id = $this->_get('return_id');
					$return_id = $this->_get('pickup_id');
					$date = $this->_get('return_date');
						
					$bus_list = $this->getBusList($pickup_id, $return_id, $bus_id_arr, $booking_period, $booked_data, $date, 'T');
						
					$booking_period = $bus_list['booking_period'];
	
					$this->_set('booking_period', $booking_period);
						
					$this->set('return_bus_type_arr', $bus_list['bus_type_arr']);
					$this->set('booked_return_seat_arr', $bus_list['booked_seat_arr']);
					$this->set('return_seat_arr', $bus_list['seat_arr']);
					$this->set('return_selected_seat_arr', $bus_list['selected_seat_arr']);
					$this->set('return_bus_arr', $bus_list['bus_arr']);
					$this->set('return_ticket_columns', $bus_list['ticket_columns']);
					$this->set('return_from_location', $bus_list['from_location']);
					$this->set('return_to_location', $bus_list['to_location']);
				}
	
				$this->set('status', 'OK');
			}else{
				$this->set('status', 'ERR');
			}
		}
	}
	
	public function pjActionCheckout()
	{
		if ($this->isXHR() || isset($_GET['_escaped_fragment_']))
		{
			$_SESSION[$this->defaultStep]['3_passed'] = true;
	
			if (isset($_SESSION[$this->defaultStore]) && count($_SESSION[$this->defaultStore]) > 0 && $this->isBusReady() == true)
			{
				$booked_data = $this->_get('booked_data');
				$pickup_id = $this->_get('pickup_id');
				$return_id = $this->_get('return_id');
				$is_return = $this->_get('is_return');
				$bus_id = $booked_data['bus_id'];
				$departure_time = NULL;
				$_departure_time = NULL;
				$arrival_time = NULL;
				$_arrival_time = NULL;
				$duration = NULL;
				$_duration = NULL;
	
				$pjBusLocationModel = pjBusLocationModel::factory();
				$pickup_arr = $pjBusLocationModel->where('bus_id', $bus_id)->where("location_id", $pickup_id)->limit(1)->findAll()->getData();
				$return_arr = $pjBusLocationModel->reset()->where('bus_id', $bus_id)->where("location_id", $return_id)->limit(1)->findAll()->getData();
	
				if(!empty($pickup_arr))
				{
					$departure_time = pjUtil::formatTime($pickup_arr[0]['departure_time'], 'H:i:s', $this->option_arr['o_time_format']);
				}
				if(!empty($return_arr))
				{
					$arrival_time = pjUtil::formatTime($return_arr[0]['arrival_time'], 'H:i:s', $this->option_arr['o_time_format']);
				}
				if(!empty($pickup_arr) && !empty($return_arr))
				{
					$duration_arr = pjUtil::calDuration($pickup_arr[0]['departure_time'], $return_arr[0]['arrival_time']);
						
					$hour_str = $duration_arr['hours'] . ' ' . ($duration_arr['hours'] != 1 ? strtolower(__('front_hours', true, false)) : strtolower(__('front_hour', true, false)));
					$minute_str = $duration_arr['minutes'] > 0 ? ($duration_arr['minutes'] . ' ' . ($duration_arr['minutes'] != 1 ? strtolower(__('front_minutes', true, false)) : strtolower(__('front_minute', true, false))) ) : '';
					$duration = $hour_str . ' ' . $minute_str;
				}
	
				$pjCityModel = pjCityModel::factory();
				$pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($pickup_id)->getData();
				$return_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($return_id)->getData();
				$from_location = $pickup_location['name'];
				$to_location = $return_location['name'];
	
				$pjBusModel= pjBusModel::factory();
				$bus_arr = $pjBusModel
					->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->select("t1.*, t2.content as route_title")
					->find($bus_id)
					->getData();
				$bus_arr['departure_time'] = $departure_time;
				$bus_arr['arrival_time'] = $arrival_time;
				$bus_arr['duration'] = $duration;
	
				$pjPriceModel = pjPriceModel::factory();
	
				$ticket_price_arr = $pjPriceModel->getTicketPrice($bus_id, $pickup_id, $return_id, $booked_data, $this->option_arr, $this->getLocaleId(), 'F');
	
				$this->set('from_location', $from_location);
				$this->set('to_location', $to_location);
				$this->set('bus_arr', $bus_arr);
				$this->set('ticket_arr', $ticket_price_arr['ticket_arr']);
				$this->set('price_arr', $ticket_price_arr);
				if ($is_return == "T")
				{
					$return_bus_id = $booked_data['return_bus_id'];
						
					$return_ticket_price_arr = $pjPriceModel->getTicketPrice($return_bus_id, $return_id, $pickup_id, $booked_data, $this->option_arr, $this->getLocaleId(), 'T');
						
					$this->set('return_ticket_arr', $return_ticket_price_arr['ticket_arr']);
					$this->set('return_price_arr', $return_ticket_price_arr);
	
					$_bus_arr = $pjBusModel
						->reset()
						->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
						->select("t1.*, t2.content as route_title")
						->find($return_bus_id)
						->getData();
	
					$_pickup_arr = $pjBusLocationModel->reset()->where('bus_id', $return_bus_id)->where("location_id", $return_id)->limit(1)->findAll()->getData();
					$_return_arr = $pjBusLocationModel->reset()->where('bus_id', $return_bus_id)->where("location_id", $pickup_id)->limit(1)->findAll()->getData();
	
					if(!empty($_pickup_arr))
					{
						$_departure_time = pjUtil::formatTime($_pickup_arr[0]['departure_time'], 'H:i:s', $this->option_arr['o_time_format']);
					}
					if(!empty($_return_arr))
					{
						$_arrival_time = pjUtil::formatTime($_return_arr[0]['arrival_time'], 'H:i:s', $this->option_arr['o_time_format']);
					}
					if(!empty($_pickup_arr) && !empty($_return_arr))
					{
						$_duration_arr = pjUtil::calDuration($_pickup_arr[0]['departure_time'], $_return_arr[0]['arrival_time']);
	
						$_hour_str = $_duration_arr['hours'] . ' ' . ($_duration_arr['hours'] != 1 ? strtolower(__('front_hours', true, false)) : strtolower(__('front_hour', true, false)));
						$_minute_str = $_duration_arr['minutes'] > 0 ? ($_duration_arr['minutes'] . ' ' . ($_duration_arr['minutes'] != 1 ? strtolower(__('front_minutes', true, false)) : strtolower(__('front_minute', true, false))) ) : '';
						$_duration = $_hour_str . ' ' . $_minute_str;
					}
	
					$_bus_arr['departure_time'] = $_departure_time;
					$_bus_arr['arrival_time'] = $_arrival_time;
					$_bus_arr['duration'] = $_duration;
	
					$_pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($return_id)->getData();
					$_return_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($pickup_id)->getData();
					$_from_location = $_pickup_location['name'];
					$_to_location = $_return_location['name'];
	
					$this->set('is_return', $is_return);
					$this->set('return_from_location', $_from_location);
					$this->set('return_to_location', $_to_location);
					$this->set('return_bus_arr', $_bus_arr);
                }
                
                $price_deposit = $ticket_price_arr['deposit'];                
                $price_return_deposit = isset($return_ticket_price_arr) ? $return_ticket_price_arr['deposit'] : 0;
                $total_price = $price_deposit + $price_return_deposit;
                $conversion_detail = $this->get_usd_conversion($this->option_arr['o_currency'],$total_price);
                $this->set('conversion_detail', $conversion_detail);
	
				$country_arr = pjCountryModel::factory()
					->select('t1.id, t2.content AS country_title,t1.phone_code')
					->join('pjMultiLang', "t2.model='pjCountry' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->orderBy('`country_title` ASC')->findAll()->getData();
	
				$terms_conditions = pjMultiLangModel::factory()->select('t1.*')
					->where('t1.model','pjOption')
					->where('t1.locale', $this->getLocaleId())
					->where('t1.field', 'o_terms')
					->limit(0, 1)
					->findAll()->getData();
	
				$pjSeatModel = pjSeatModel::factory();
		
				$selected_seat_arr = $pjSeatModel->whereIn('t1.id', explode("|", $booked_data['selected_seats']))->findAll()->getDataPair('id', 'name');
				$return_selected_seat_arr = (isset($booked_data['return_selected_seats']) && !empty($booked_data['return_selected_seats'])) ? $pjSeatModel->reset()->whereIn('t1.id', explode("|", $booked_data['return_selected_seats']))->findAll()->getDataPair('id', 'name') : array();
				
				$this->set('selected_seat_arr', $selected_seat_arr);
				$this->set('return_selected_seat_arr', $return_selected_seat_arr);
				$this->set('country_arr', $country_arr);
				$this->set('terms_conditions', $terms_conditions[0]['content']);
	
                $this->set('status', 'OK');
                
                $this->appendJs('chosen.jquery.js', PJ_THIRD_PARTY_PATH . 'chosen/');
                $this->appendCss('chosen.css', PJ_THIRD_PARTY_PATH . 'chosen/');
                $this->appendJs('booking.js');
			}else{
				$this->set('status', 'ERR');
			}
		}
	}
	
	public function pjActionPreview()
	{
		if ($this->isXHR() || isset($_GET['_escaped_fragment_']))
		{
			$_SESSION[$this->defaultStep]['4_passed'] = true;
	
			if (isset($_SESSION[$this->defaultForm]) && count($_SESSION[$this->defaultForm]) > 0 && $this->isBusReady() == true)
			{
				$booked_data = $this->_get('booked_data');
				$pickup_id = $this->_get('pickup_id');
				$return_id = $this->_get('return_id');
				$bus_id = $booked_data['bus_id'];
				$is_return = $this->_get('is_return');
				$departure_time = NULL;
				$arrival_time = NULL;
				$duration = NULL;
				$_departure_time = NULL;
				$_arrival_time = NULL;
				$_duration = NULL;
	
				$pjBusLocationModel = pjBusLocationModel::factory();
				$pickup_arr = $pjBusLocationModel->where('bus_id', $bus_id)->where("location_id", $pickup_id)->limit(1)->findAll()->getData();
				$return_arr = $pjBusLocationModel->reset()->where('bus_id', $bus_id)->where("location_id", $return_id)->limit(1)->findAll()->getData();
	
				if(!empty($pickup_arr))
				{
					$departure_time = pjUtil::formatTime($pickup_arr[0]['departure_time'], 'H:i:s', $this->option_arr['o_time_format']);
				}
				if(!empty($return_arr))
				{
					$arrival_time = pjUtil::formatTime($return_arr[0]['arrival_time'], 'H:i:s', $this->option_arr['o_time_format']);
				}
				if(!empty($pickup_arr) && !empty($return_arr))
				{
					$duration_arr = pjUtil::calDuration($pickup_arr[0]['departure_time'], $return_arr[0]['arrival_time']);
						
					$hour_str = $duration_arr['hours'] . ' ' . ($duration_arr['hours'] != 1 ? strtolower(__('front_hours', true, false)) : strtolower(__('front_hour', true, false)));
					$minute_str = $duration_arr['minutes'] > 0 ? ($duration_arr['minutes'] . ' ' . ($duration_arr['minutes'] != 1 ? strtolower(__('front_minutes', true, false)) : strtolower(__('front_minute', true, false))) ) : '';
					$duration = $hour_str . ' ' . $minute_str;
				}
	
				$pjCityModel = pjCityModel::factory();
				$pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($pickup_id)->getData();
				$return_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($return_id)->getData();
				$from_location = $pickup_location['name'];
				$to_location = $return_location['name'];
	
				$pjBusModel = pjBusModel::factory();
				$bus_arr = $pjBusModel
					->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->select("t1.*, t2.content as route_title")
					->find($bus_id)
					->getData();
				$bus_arr['departure_time'] = $departure_time;
				$bus_arr['arrival_time'] = $arrival_time;
				$bus_arr['duration'] = $duration;
	
				$pjPriceModel = pjPriceModel::factory();
				$ticket_price_arr = $pjPriceModel->getTicketPrice($bus_id, $pickup_id, $return_id, $booked_data, $this->option_arr, $this->getLocaleId(), 'F');
    
                $this->set('from_location', $from_location);
				$this->set('to_location', $to_location);
				$this->set('bus_arr', $bus_arr);
				$this->set('ticket_arr', $ticket_price_arr['ticket_arr']);
				$this->set('price_arr', $ticket_price_arr);
	
				if ($is_return == "T")
				{
					$return_bus_id = $booked_data['return_bus_id'];
						
					$return_ticket_price_arr = $pjPriceModel->getTicketPrice($return_bus_id, $return_id, $pickup_id, $booked_data, $this->option_arr, $this->getLocaleId(), 'T');
						
					$this->set('return_ticket_arr', $return_ticket_price_arr['ticket_arr']);
					$this->set('return_price_arr', $return_ticket_price_arr);
	
					$_bus_arr = $pjBusModel
						->reset()
						->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
						->select("t1.*, t2.content as route_title")
						->find($return_bus_id)
						->getData();
	
					$_pickup_arr = $pjBusLocationModel->reset()->where('bus_id', $return_bus_id)->where("location_id", $return_id)->limit(1)->findAll()->getData();
					$_return_arr = $pjBusLocationModel->reset()->where('bus_id', $return_bus_id)->where("location_id", $pickup_id)->limit(1)->findAll()->getData();
	
					if(!empty($_pickup_arr))
					{
						$_departure_time = pjUtil::formatTime($_pickup_arr[0]['departure_time'], 'H:i:s', $this->option_arr['o_time_format']);
					}
					if(!empty($_return_arr))
					{
						$_arrival_time = pjUtil::formatTime($_return_arr[0]['arrival_time'], 'H:i:s', $this->option_arr['o_time_format']);
					}
					if(!empty($_pickup_arr) && !empty($_return_arr))
					{
						$_duration_arr = pjUtil::calDuration($_pickup_arr[0]['departure_time'], $return_arr[0]['arrival_time']);
	
						$hour_str = $_duration_arr['hours'] . ' ' . ($_duration_arr['hours'] != 1 ? strtolower(__('front_hours', true, false)) : strtolower(__('front_hour', true, false)));
						$minute_str = $_duration_arr['minutes'] > 0 ? ($duration_arr['minutes'] . ' ' . ($_duration_arr['minutes'] != 1 ? strtolower(__('front_minutes', true, false)) : strtolower(__('front_minute', true, false))) ) : '';
						$_duration = $hour_str . ' ' . $minute_str;
					}
	
					$_bus_arr['departure_time'] = $_departure_time;
					$_bus_arr['arrival_time'] = $_arrival_time;
					$_bus_arr['duration'] = $_duration;
	
					$_pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($return_id)->getData();
					$_return_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($pickup_id)->getData();
					$_from_location = $_pickup_location['name'];
					$_to_location = $_return_location['name'];
	
					$this->set('is_return', $is_return);
					$this->set('return_from_location', $_from_location);
					$this->set('return_to_location', $_to_location);
					$this->set('return_bus_arr', $_bus_arr);
                }
                
                $price_deposit = $ticket_price_arr['deposit'];                
                $price_return_deposit = isset($return_ticket_price_arr) ? $return_ticket_price_arr['deposit'] : 0;
                $total_price = $price_deposit + $price_return_deposit;
                $conversion_detail = $this->get_usd_conversion($this->option_arr['o_currency'],$total_price);
                $this->set('conversion_detail', $conversion_detail);

				$country_arr = array();
				if(isset($_SESSION[$this->defaultForm]['c_country']) && !empty($_SESSION[$this->defaultForm]['c_country']))
				{
					$country_arr = pjCountryModel::factory()
						->select('t1.id, t2.content AS country_title')
						->join('pjMultiLang', "t2.model='pjCountry' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
						->find($_SESSION[$this->defaultForm]['c_country'])->getData();
				}
				$pjSeatModel = pjSeatModel::factory();
					
				$selected_seat_arr = $pjSeatModel->whereIn('t1.id', explode("|", $booked_data['selected_seats']))->findAll()->getDataPair('id', 'name');
				$return_selected_seat_arr = (isset($booked_data['return_selected_seats']) && !empty($booked_data['return_selected_seats'])) ? $pjSeatModel->reset()->whereIn('t1.id', explode("|", $booked_data['return_selected_seats']))->findAll()->getDataPair('id', 'name') : array();
				
				$this->set('selected_seat_arr', $selected_seat_arr);
				$this->set('return_selected_seat_arr', $return_selected_seat_arr);
				$this->set('country_arr', $country_arr);
	
				$this->set('status', 'OK');
			}else{
				$this->set('status', 'ERR');
			}
		}
	}
	

	public function pjActionGetPaymentForm()
	{
		if ($this->isXHR())
		{            
			$arr = pjBookingModel::factory()
			->select('t1.*')
			->find($_GET['booking_id'])->getData();
    
            $uuid = $arr['uuid'];
            $uuid_mtn = $arr['uuid'];

			if (!empty($arr['back_id'])) {
				$back_arr = pjBookingModel::factory()
					->select('t1.*')
					->find($arr['back_id'])->getData();
                $arr['deposit'] += $back_arr['deposit'];
                
                $uuid .= '_'.$back_arr['uuid'];
                $uuid_mtn .= '-'.$back_arr['uuid'];
            }
            
            $country_title = '';
            if ($arr['c_country']) {
                $checkout_country_arr = pjCountryModel::factory()
						->select('t1.id, t2.content AS country_title')
						->join('pjMultiLang', "t2.model='pjCountry' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                        ->find($arr['c_country'])->getData();
                $country_title = $checkout_country_arr['country_title'];
            }

            $conversion_detail = $this->get_usd_conversion($this->option_arr['o_currency'],$arr['deposit']);
            $currency_code = $conversion_detail[1];
            $curreny_rate = $conversion_detail[2];  
            
            $locale_id = isset($arr['locale_id']) && (int) $arr['locale_id'] > 0 ? (int) $arr['locale_id'] : $this->getLocaleId();

            $phone = (strpos($arr['c_phone'],'+') !== false) ? substr($arr['c_phone'],1) : $arr['c_phone'];

            $pjFrontEnd = new pjFrontEnd();
			switch ($arr['payment_method'])
			{
                case 'cgrate':                    
					$url    = 'https://543.cgrate.co.zm/Konik/KonikWs?wsdl';
                    $headers  = array(
                    'Content-Type: text/xml'
                    );

                    $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:kon="http://konik.cgrate.com">
                    <soapenv:Header>
                        <wsse:Security soapenv:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                            <wsse:UsernameToken wsu:Id="UsernameToken-1" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                            <wsse:Username>'.PJ_CGRATE_USERNAME.'</wsse:Username>
                            <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.PJ_CGRATE_PASSWORD.'</wsse:Password>
                            </wsse:UsernameToken>
                        </wsse:Security>
                </soapenv:Header>
                <soapenv:Body>
                <kon:processCustomerPayment>
                <transactionAmount>'.$curreny_rate.'</transactionAmount>
                <customerMobile>'.$phone.'</customerMobile>
                <paymentReference>M'.$arr['uuid'].'</paymentReference>
                </kon:processCustomerPayment>
                </soapenv:Body>
                </soapenv:Envelope>';

                    $curl = curl_init($url);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_HEADER, 0);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
                    $response = $fail_message = curl_exec($curl);
                    //echo $response.'<br><br>';
                    curl_close($curl);
                    $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
                    $x = new SimpleXMLElement($response);
                    $body = $x->xpath('//envBody')[0];
                    $array = json_decode(json_encode((array)$body), TRUE); 
                    
                    $response = (isset($array['ns2processCustomerPaymentResponse']['return'])) ? $array['ns2processCustomerPaymentResponse']['return'] : '';

                    //$response['responseCode'] = 1;
                    if (isset($response['responseCode']) && $response['responseCode'] == 0) {
                        $pjFrontEnd->pjActionConfirmBooking(array('options_arr' => $this->option_arr,'uuid'=>$arr['uuid']));

                        $tokens = pjAppController::getData($this->option_arr, $arr, PJ_SALT, $this->getLocaleId());
                        $pjMultiLangModel = pjMultiLangModel::factory();
                        $locale_id = isset($arr['locale_id']) && (int) $arr['locale_id'] > 0 ? (int) $arr['locale_id'] : $this->getLocaleId();

                        $lang_message = $pjMultiLangModel->reset()->select('t1.*')
                                        ->where('t1.model','pjOption')
                                        ->where('t1.locale', $locale_id)
                                        ->where('t1.field', 'o_sms_cgrate_site_success_message')
                                        ->limit(0, 1)
                                        ->findAll()->getData();
                          
                        $success = true;
                        $message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);         
                    } else {
                        //$pjFrontEnd->pjActionCancelBooking(array('options_arr' => $this->option_arr,'uuid'=>$arr['uuid']));

                        pjBookingModel::factory()->setAttributes(array('id' => $arr['id']))->modify(array(
                            'status' => 'cancelled',
                            'processed_on' => ':NOW()'
                        ));
                        if (!empty($arr['back_id'])) 
                        {
                            pjBookingModel::factory()->setAttributes(array('id' => $arr['back_id']))->modify(array(
                                'status' => 'cancelled',
                                'processed_on' => ':NOW()'
                            ));
                        }

                        $tokens = pjAppController::getData($this->option_arr, $arr, PJ_SALT, $this->getLocaleId());
                        $pjMultiLangModel = pjMultiLangModel::factory();
                        $locale_id = isset($arr['locale_id']) && (int) $arr['locale_id'] > 0 ? (int) $arr['locale_id'] : $this->getLocaleId();

                        $lang_message = $pjMultiLangModel->reset()->select('t1.*')
                                        ->where('t1.model','pjOption')
                                        ->where('t1.locale', $locale_id)
                                        ->where('t1.field', 'o_sms_cgrate_site_failed_message')
                                        ->limit(0, 1)
                                        ->findAll()->getData();
                          
                        $success = false;
                        $message = $fail_message.'<br><br>'.str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);                            
                    }
                    $this->set('params', array(
                        'success' => $success,
                        'message' => $message
                    ));
                    break;
                case 'wallet':
                    /*lipila code
                    $params = array(
                        "AUTHENTICATION" => array(
                            "IDENTIFIER"=>PJ_LIPILA_WALLET_IDENTIFIER,
                            "KEY"=>PJ_LIPILA_WALLET_KEY
                        ),
                        "TRANSACTION" => array(
                            "REQUESTTYPE"=>"collection",
                            "REQUESTID"=>$arr['uuid'],
                            "AMOUNT" => $curreny_rate,
                            "MSISDN"=>PJ_LIPILA_WALLET_MSISDN
                        )
                    );
            
                    $postData = json_encode($params);
            
                    $url = 'http://41.175.8.69:8181/payments/lipila';
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    //curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n\"AUTHENTICATION\":{\"IDENTIFIER\":\"p4rez4\",\"KEY\":\"aSBjYW4gZG8gYWxsIHRoaW5ncyB0aHJvdWdoIGNocmlzdCB3aG8gc3RyZW5ndGhlbnMgbWU=\"},\"TRANSACTION\":{\"AMOUNT\":\".1\",\"MSISDN\":260963841554,\"REQUESTID\":\"000007\",\"REQUESTTYPE\":\"collection\"}}");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); 
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    $json_response = curl_exec($ch);
                    if (curl_errno($ch)) {
                        $error_msg = curl_error($ch);
                        echo $error_msg;
                    }
                    curl_close($ch);
            
                    $response = json_decode($json_response,true);
                    if (!empty($response) && $response['RESPONSECODE'] == 200) {
                        $success = true;
                        $message = '';
                    } else {
                        $pjFrontEnd->pjActionCancelBooking(array('options_arr' => $this->option_arr,'uuid'=>$arr['uuid']));
                        $pjMultiLangModel = pjMultiLangModel::factory();
                        $sms_field = 'o_sms_wallet_site_failed_message';
                        $lang_message = $pjMultiLangModel->reset()->select('t1.*')
                        ->where('t1.model','pjOption')
                        ->where('t1.locale', $locale_id)
                        ->where('t1.field', $sms_field)
                        ->limit(0, 1)
                        ->findAll()->getData();

                        $success = false;
                        $message = $lang_message[0]['content'];
                    }
                    $this->set('params', array(
                        'success' => $success,
                        'message' => $message
                    ));
                    */

					$reference_id = sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                        // 32 bits for "time_low"
                        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

                        // 16 bits for "time_mid"
                        mt_rand( 0, 0xffff ),

                        // 16 bits for "time_hi_and_version",
                        // four most significant bits holds version number 4
                        mt_rand( 0, 0x0fff ) | 0x4000,

                        // 16 bits, 8 bits for "clk_seq_hi_res",
                        // 8 bits for "clk_seq_low",
                        // two most significant bits holds zero and one for variant DCE1.1
                        mt_rand( 0, 0x3fff ) | 0x8000,

                        // 48 bits for "node"
                        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
                    );

                    // API User
                    $api_user = PJ_MTN_WALLET_API_USER;
                    // API Key
                    $api_key = PJ_MTN_WALLET_API_KEY;
                    //API User and Key
                    $api_user_and_key  = $api_user . ':' . $api_key;

                    // Basic Authorisation
                    $basic_auth = "Basic " . base64_encode($api_user_and_key);

                    $host = "proxy.momoapi.mtn.com";
                    $url = "https://proxy.momoapi.mtn.com/collection/token/";

                    $subscription_key = PJ_MTN_WALLET_SUBSCRIPTION_KEY;

                    // Submit for token creation
                    try {
                        $headers = array(
                            "Host: " . $host,
                            "Content-type: application/json",
                            "Authorization: " . $basic_auth,
                            "Ocp-Apim-Subscription-Key: " . $subscription_key,
                        ); 
                        $CURL = curl_init();
                    
                        curl_setopt($CURL, CURLOPT_URL, $url); 
                        curl_setopt($CURL, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); 
                        curl_setopt($CURL, CURLOPT_POST, 1); 
                        curl_setopt($CURL, CURLOPT_POSTFIELDS, NULL); 
                        curl_setopt($CURL, CURLOPT_HEADER, false); 
                        curl_setopt($CURL, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($CURL, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($CURL, CURLOPT_RETURNTRANSFER, true);
                        $full_response = curl_exec($CURL);
                        curl_close($CURL);

                        $decoded_full_response = json_decode($full_response);

                        if($decoded_full_response && !empty($decoded_full_response->access_token)) {
                            $bearer_token = 'Bearer ' . $decoded_full_response->access_token;

                            $amount = $curreny_rate;
                            $currency = ($this->option_arr['o_currency'] == 'ZMK') ? 'ZMW' : $this->option_arr['o_currency'];

                            $url = "https://proxy.momoapi.mtn.com/collection/v1_0/requesttopay";

                            $target_environment = "mtnzambia";

                            if (!empty($arr['back_id'])) {
                                $external_Id = $uuid_mtn.'_'.PJ_COMBINED_API_WEBSITE_ID.'-'.PJ_COMBINED_API_WEBSITE_ID;
                            } else {
                                $external_Id = $uuid_mtn.'_'.PJ_COMBINED_API_WEBSITE_ID;
                            }
                            $site_id = PJ_COMBINED_API_WEBSITE_ID;

                            $REQUEST_BODY= <<<json
                            {
                                "amount": "{$amount}",
                                "currency": "{$currency}",
                                "externalId": "{$external_Id}",
                                "payer": {
                                    "partyIdType": "MSISDN",
                                    "partyId": "{$phone}"
                                },
                                "payerMessage": "Payment of K{$amount} for {$uuid} in Website ID {$site_id}",
                                "payeeNote": "Payment of K{$amount} from {$phone} for {$uuid} in Website ID {$site_id}"
                            }
                            json;

                            $headers = array(
                                "Host: " . $host,
                                "Content-type: application/json",
                                "Authorization: " . $bearer_token,
                                "X-Reference-Id: " . $reference_id,
                                "X-Target-Environment: mtnzambia",
                                "X-Callback-Url: ".PJ_MTN_WALLET_CALLBACK_URL,
                                "Ocp-Apim-Subscription-Key: " . $subscription_key,
                            ); 
                            $CURL = curl_init();
                        
                            curl_setopt($CURL, CURLOPT_URL, $url); 
                            curl_setopt($CURL, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); 
                            curl_setopt($CURL, CURLOPT_POST, 1); 
                            curl_setopt($CURL, CURLOPT_POSTFIELDS, $REQUEST_BODY); 
                            curl_setopt($CURL, CURLOPT_HEADER, false); 
                            curl_setopt($CURL, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($CURL, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($CURL, CURLOPT_RETURNTRANSFER, true);
                            $full_response = curl_exec($CURL);
                            $httpcode = curl_getinfo($CURL, CURLINFO_HTTP_CODE);
                            curl_close($CURL);
                        
                            if($httpcode == 202) {
                                $success = true;
                            } else {
                                $success = false;
                            }
                        } else {
                            $success = false;
                        }
                    } catch(\Exception $e) {
                        $success = false;
                    }

                    if ($success) {
                        $message = '';
                    } else {
                        $pjFrontEnd->pjActionCancelBooking(array('options_arr' => $this->option_arr,'uuid'=>$arr['uuid']));
                        $pjMultiLangModel = pjMultiLangModel::factory();
                        $sms_field = 'o_sms_wallet_site_failed_message';
                        $lang_message = $pjMultiLangModel->reset()->select('t1.*')
                        ->where('t1.model','pjOption')
                        ->where('t1.locale', $locale_id)
                        ->where('t1.field', $sms_field)
                        ->limit(0, 1)
                        ->findAll()->getData();

                        $message = $lang_message[0]['content'];
                    }
                    $this->set('params', array(
                        'success' => $success,
                        'message' => $message
                    ));
                    break;
				case 'paypal':
					$this->set('params', array(
					'name' => 'bsPaypal',
					'id' => 'bsPaypal',
					'business' => $this->option_arr['o_paypal_address'],
					'item_name' => __('front_label_bus_schedule', true, false),
					'custom' => $arr['id'],
					'amount' => number_format($curreny_rate, 2, '.', ''),
                    //'currency_code' => $this->option_arr['o_currency'],
                    'currency_code' => $currency_code,
					'return' => $this->option_arr['o_thank_you_page'],
					'notify_url' => PJ_INSTALL_URL . 'index.php?controller=pjFrontEnd&action=pjActionConfirmPaypal',
					'target' => '_self'
							));
							break;
				case 'authorize':
					$this->set('params', array(
					'name' => 'bsAuthorize',
					'id' => 'bsAuthorize',
					'target' => '_self',
					'timezone' => $this->option_arr['o_timezone'],
					'transkey' => $this->option_arr['o_authorize_transkey'],
					'x_login' => $this->option_arr['o_authorize_merchant_id'],
					'x_description' => __('front_label_bus_schedule', true, false),
					'x_amount' => number_format($curreny_rate, 2, '.', ''),
					'x_invoice_num' => $arr['id'],
					'x_receipt_link_url' => $this->option_arr['o_thank_you_page'],
					'x_relay_url' => PJ_INSTALL_URL . 'index.php?controller=pjFrontEnd&action=pjActionConfirmAuthorize'
							));
                            break;
                case '2checkout':
                    $this->set('params', array(
                        'name' => 'bs2Checkout',
                        'id' => 'bs2Checkout',
                        'sid' => PJ_TEST_MODE ? '901421190' : $this->option_arr['o_2checkout_sid'],
                        'li_0_name' => __('front_label_bus_schedule', true, false),
                        'li_0_product_id' => $arr['id'],
                        'li_0_price' => number_format($curreny_rate, 2, '.', ''),
                        //'currency_code' => $this->option_arr['o_currency'],   
                        'currency_code' => $currency_code,  
                        'first_name' => $arr['c_fname'], 
                        'last_name' => $arr['c_lname'], 
                        'street_address' => $arr['c_address'],  
                        'city' => $arr['c_city'],
                        'state' => $arr['c_state'],
                        'zip' => $arr['c_zip'],
                        'country' => $country_title,
                        'email' => $arr['c_email'],
                        'phone' => $arr['c_phone'],
                        'x_receipt_link_url' => PJ_INSTALL_URL . 'index.php?controller=pjFrontEnd&action=pjActionConfirm2Checkout',
                        'target' => '_self'
                    ));
                    break;
                case 'flutterwave':
                        $this->set('params', array(
                            'name' => 'bsFlutterwave',
                            'id' => 'bsFlutterwave',
                            'public_key' => $this->option_arr['o_flutterwave_public_key'],
                            'sub_account' => (defined("PJ_FLUTTERWAVE_SUB_ACCOUNT")) ? PJ_FLUTTERWAVE_SUB_ACCOUNT : '',
                            'sub_account_commission' => (defined("PJ_FLUTTERWAVE_SUB_ACCOUNT_COMMISSION")) ? PJ_FLUTTERWAVE_SUB_ACCOUNT_COMMISSION : '',
                            'meta_token' => $arr['uuid'],
                            'tx_ref' => $arr['id'],
                            'amount' => $curreny_rate,
                            'currency' => ($this->option_arr['o_currency'] == 'ZMK') ? 'ZMW' : $this->option_arr['o_currency'],  
                            'customer_name' => $arr['c_fname'].' '.$arr['c_lname'], 
                            'last_name' => $arr['c_lname'], 
                            'email' => $arr['c_email'],
                            'phone' => $arr['c_phone'],
                            'redirect_url' => PJ_INSTALL_URL . 'index.php?controller=pjFrontEnd&action=pjActionConfirmFlutterwave',
                            'target' => '_self'
                        ));
                        break;
                case 'pesapal':
                    require_once('app/lib/pesapal/OAuth.php');
                    $api = 'https://demo.pesapal.com';
                    // $api = 'https://www.pesapal.com';
                        
                    $token = $params 	= NULL;
                    $iframelink 		= $api.'/api/PostPesapalDirectOrderV4';
                    
                    //Kenyan keys
                    $consumer_key 		= "zVrGi7VaxnT9QXWK433jm+9on5JuFlF/"; 
                    $consumer_secret 	= "86uJkXdACsTIlF8fKGpKeqYFols="; 
                    
                    $signature_method	= new OAuthSignatureMethod_HMAC_SHA1();
                    $consumer 			= new OAuthConsumer($consumer_key, $consumer_secret);
                    
                    //get form details
                    $ref				=  str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',5);
                    $reference	=  substr(str_shuffle($ref),0,10);
                    
                    $amount 		= number_format($curreny_rate, 2); //format amount to 2 decimal places
                    $desc 			= 'Test Payment';
                    $type 			= 'MERCHANT';	
                    $first_name 	= 'Anotny';
                    $last_name 		= 'P';
                    $email 			= 'test@gmail.com';
                    $phonenumber	= '12345';
                    $currency 		= 'USD';
                    $reference 		= $reference; //unique transaction id, generated by merchant.
                    $callback_url 	= 'http://localhost/babu/bus/pesapal/return.php'; //URL user to be redirected to after payment
                    
                    $post_xml	= "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                                <PesapalDirectOrderInfo 
                                        xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" 
                                        xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" 
                                        Currency=\"".$currency_code."\" 
                                        Amount=\"".$amount."\" 
                                        Description=\"".$desc."\" 
                                        Type=\"".$type."\" 
                                        Reference=\"".$reference."\" 
                                        FirstName=\"".$first_name."\" 
                                        LastName=\"".$last_name."\" 
                                        Email=\"".$email."\" 
                                        PhoneNumber=\"".$phonenumber."\" 
                                        xmlns=\"http://www.pesapal.com\" />";
                    $post_xml = htmlentities($post_xml);
                    
                    //post transaction to pesapal
                    $iframe_src = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $iframelink, $params);
                    $iframe_src->set_parameter("oauth_callback", $callback_url);
                    $iframe_src->set_parameter("pesapal_request_data", $post_xml);
                    $iframe_src->sign_request($signature_method, $consumer, $token);

                    $this->set('params',array('iframe_src' => $iframe_src));
                    break;
			}
	
			$this->set('arr', $arr);
			$this->set('get', $_GET);
		}
    } 
    
    function pjActionGetAgents() {
        $search_district = trim($_POST['search_district']);
        $is_home = trim($_POST['is_home']);

        $agents_obj = pjUserModel::factory()
        ->where('role_id',2)
        ->where('status','T');

        if ($search_district) {
            $agents_obj->where('district',$search_district);
        }

        if ($is_home == 1) {
            $agents_obj -> limit(15);
        }

        $agents = $agents_obj->findAll()
        ->getData();

        $this->set('agents',$agents);
    }
}
?>