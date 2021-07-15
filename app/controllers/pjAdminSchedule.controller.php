<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAdminSchedule extends pjAdmin
{
	public function pjActionIndex()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() ||$this->isEditor() || $this->isPolice() || $this->isIntelligenceService() || $this->isInvestor())
		{
			$date = date('Y-m-d');
			$day_of_week = strtolower(date('l', strtotime($date)));
			
			$column = 'departure';
			$direction = 'ASC';
			if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
			{
				$column = $_GET['column'];
				$direction = strtoupper($_GET['direction']);
			}
			
			$pjBusLocationModel = pjBusLocationModel::factory();
				
			$bus_arr = pjBusModel::factory()
				->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->select(" t1.*, t2.content AS route,
							(SELECT CONCAT(TSL1.departure_time, '~:~', TSL1.location_id) FROM `".$pjBusLocationModel->getTable()."` AS TSL1 WHERE TSL1.bus_id = t1.id AND TSL1.arrival_time IS NULL AND TSL1.departure_time IS NOT NULL LIMIT 1) AS departure,
							(SELECT CONCAT(TSL2.arrival_time, '~:~', TSL2.location_id) FROM `".$pjBusLocationModel->getTable()."` AS TSL2 WHERE TSL2.bus_id = t1.id AND TSL2.departure_time IS NULL AND TSL2.arrival_time IS NOT NULL LIMIT 1) AS arrive,
							(SELECT SUM(TBT.qty) FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT WHERE TBT.booking_id IN (SELECT TB.id FROM `".pjBookingModel::factory()->getTable()."` AS TB WHERE TB.bus_id = t1.id AND TB.booking_date = '$date' AND TB.pickup_id = (SELECT TL1.city_id FROM `".pjRouteCityModel::factory()->getTable()."` AS TL1 WHERE TL1.route_id=t1.route_id ORDER BY `order` ASC LIMIT 1 ) AND TB.return_id = (SELECT TL2.city_id FROM `".pjRouteCityModel::factory()->getTable()."` AS TL2 WHERE TL2.route_id=t1.route_id ORDER BY `order` DESC LIMIT 1 ) ) LIMIT 1) AS tickets,
							(SELECT SUM(TBT.qty) FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT WHERE TBT.booking_id IN (SELECT TB.id FROM `".pjBookingModel::factory()->getTable()."` AS TB WHERE TB.bus_id = t1.id AND TB.booking_date = '$date') LIMIT 1) AS total_tickets")
				->where("(t1.start_date <= '$date' AND '$date' <= t1.end_date) AND (t1.recurring LIKE '%$day_of_week%') AND t1.id NOT IN (SELECT TSD.bus_id FROM `".pjBusDateModel::factory()->getTable()."` AS TSD WHERE TSD.`date` = '$date')")
				->orderBy("$column $direction")
				->findAll()
				->getData();
						
			$this->set('bus_arr', $bus_arr);
			
			$route_arr = pjRouteModel::factory()
				->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->select("t1.*, t2.content AS route")
				->orderBy("route ASC")->findAll()->getData();
			
			$this->set('route_arr', $route_arr);
			
			$this->appendJs('pjAdminSchedule.js');
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionTimetable()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() ||$this->isEditor() || $this->isPolice() || $this->isIntelligenceService() || $this->isInvestor())
		{
			$route_arr = pjRouteModel::factory()
				->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->select(" t1.*, t2.content AS route")
				->orderBy("route ASC")
				->findAll()
				->getData();
			
			$route_id = null;
			
			$this->set('route_arr', $route_arr);

			foreach($route_arr as $k => $v)
			{
				if($k == 0)
				{
					$route_id = $v['id'];
				}
			}
			
			list($week_start_date, $week_end_date) = pjUtil::getWeekRange($this->option_arr['o_week_start']);

			$pjBusDateModel = pjBusDateModel::factory();
			$pjBusModel = pjBusModel::factory()
					->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjBusType', "t3.id=t1.bus_type_id", 'left outer');
				
			if (isset($_GET['route_id']) && !empty($_GET['route_id']))
			{
				$route_id = pjObject::escapeString($_GET['route_id']);
				$pjBusModel->where("t1.route_id", $route_id);
				$pjBusDateModel->where("t1.bus_id IN(SELECT TB.id FROM `".$pjBusModel->getTable()."` AS TB WHERE TB.route_id = $route_id)");
			}else{
				if($route_id != null)
				{
					$pjBusModel->where("t1.route_id", $route_id);
					$pjBusDateModel->where("t1.bus_id IN(SELECT TB.id FROM `".$pjBusModel->getTable()."` AS TB WHERE TB.route_id = $route_id)");
				}
			}
			$pjBusModel->where("( (t1.start_date <= '$week_start_date' AND '$week_start_date' <= t1.end_date) OR (t1.start_date <= '$week_end_date' AND '$week_end_date' <= t1.end_date) )");
			$column = 'departure_time';
			$direction = 'ASC';
			$bus_arr = $pjBusModel
				->select(" t1.*, t2.content AS route")
				->orderBy("$column $direction")
				->findAll()
				->getData();
						 	
			$this->set('bus_arr', $bus_arr);

			$_arr = $pjBusDateModel->findAll()->getData();
			$date_arr = array();
			foreach($_arr as $v)
			{
				$date_arr[$v['bus_id']][] = $v['date'];
			}
			
			$ticket_arr = array();
			if($route_id != null)
			{
				$booking_arr = pjBookingModel::factory()
					->select("t1.bus_id, t1.booking_date, t1.bus_departure_date, (SELECT SUM(TBT.qty) 
					                                       FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT 
					                                       WHERE TBT.booking_id=t1.id) AS tickets")
					->where("t1.bus_id IN(SELECT TB.id FROM `".$pjBusModel->getTable()."` AS TB WHERE TB.route_id = $route_id)")
					->where("t1.bus_departure_date BETWEEN '$week_start_date' AND '$week_end_date'")
					->findAll()
					->getData();
				foreach($booking_arr as $v)
				{
					isset($ticket_arr[$v['bus_id'] . '~:~' . $v['bus_departure_date']]) ? $ticket_arr[$v['bus_id'] . '~:~' . $v['bus_departure_date']] += $v['tickets'] : $ticket_arr[$v['bus_id'] . '~:~' . $v['bus_departure_date']] = $v['tickets'];
				}
			}

			$this->set('date_arr', $date_arr);
			$this->set('week_start_date', $week_start_date);
			$this->set('week_end_date', $week_end_date);
			$this->set('ticket_arr', $ticket_arr);
			
			$this->appendJs('jquery.tipsy.js', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendCss('jquery.tipsy.css', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendJs('pjAdminSchedule.js');
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionBookings()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() ||$this->isEditor() || $this->isPolice() || $this->isIntelligenceService() || $this->isInvestor())
		{
			if(isset($_GET['bus_id']) && isset($_GET['date']))
			{
                $pjUserModel = pjUserModel::factory();

				$bus_id = $_GET['bus_id'];
				$date = pjUtil::formatDate($_GET['date'], $this->option_arr['o_date_format']);

				$bus_arr = pjBusModel::factory()
					->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->select(" t1.*, t2.content AS route")
					->find($bus_id)
					->getData();
				
				$location_arr = pjRouteCityModel::factory()
					->select('t1.*, t2.content as location')
					->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.city_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->where('route_id', $bus_arr['route_id'])
					->orderBy("t1.order ASC")
					->findAll()->getData();
				$this->set('location_arr', $location_arr);
				
				$pjBookingModel = pjBookingModel::factory()
					->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.pickup_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjMultiLang', "t3.model='pjCity' AND t3.foreign_id=t1.return_id AND t3.field='name' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
					->select("t1.*, t2.content as from_location, t3.content as to_location, 
							(SELECT GROUP_CONCAT(CONCAT_WS(' x ', TML.content, TBT.qty) SEPARATOR '~:~') FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT LEFT OUTER JOIN `".pjMultiLangModel::factory()->getTable()."` AS TML ON (TML.model='pjTicket' AND TML.foreign_id=TBT.ticket_id AND TML.field='title' AND TML.locale='".$this->getLocaleId()."') WHERE TBT.booking_id = t1.id AND TBT.qty > 0) as tickets")
					->where('status', 'confirmed')
					->where('bus_id', $bus_id)
                    ->where('bus_departure_date', $date);
                    

                if ($this->isEditor()) {
                    $pjBookingModel->where('(user_id = '.$this->getUserId().' OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
                }
                if ($this->isBusAdmin()) {
                    //$pjBookingModel->where('(user_id = '.$this->getUserId().' OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().') OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().')) OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id IN (1,3))))');	
                }

				$booking_arr =	$pjBookingModel->orderBy("t1.created DESC")
					->findAll()
					->toArray('tickets', '~:~')
					->getData();
									
				$pjBookingSeatModel = pjBookingSeatModel::factory();
				foreach($booking_arr as $k => $v)
				{					
					$booking_arr[$k]['seats'] = $pjBookingSeatModel
						->reset()
						->join('pjSeat', "t2.id=t1.seat_id", 'left outer')
						->select("t1.seat_id, t2.name")
						->where("t1.booking_id", $v['id'])
						->findAll()
						->getDataPair("seat_id", 'name');
				}
                
                if ($this->isEditor()) {
                    $ticket_arr = pjBookingTicketModel::factory()
                        ->select("t1.ticket_id, t2.content as title, SUM(qty) AS total_tickets")
                        ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                        ->where("t1.booking_id IN (SELECT TB.id FROM `".pjBookingModel::factory()->getTable()."` AS TB WHERE TB.status='confirmed' AND (TB.user_id = ".$this->getUserId()." OR TB.user_id IN (SELECT id FROM ".$pjUserModel->getTable()." WHERE added_by = ".$this->getUserId().")) AND TB.bus_id = ".$bus_id." AND TB.bus_departure_date='$date')")
                        ->groupBy("t1.ticket_id")
                        ->findAll()
                        ->getData();
                /*} else if ($this->isBusAdmin()) {
                    $ticket_arr = pjBookingTicketModel::factory()
                        ->select("t1.ticket_id, t2.content as title, SUM(qty) AS total_tickets")
                        ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                        ->where("t1.booking_id IN (SELECT TB.id FROM `".pjBookingModel::factory()->getTable()."` AS TB WHERE TB.status='confirmed' AND (TB.user_id = ".$this->getUserId()." OR TB.user_id IN (SELECT id FROM ".$pjUserModel->getTable()." WHERE added_by = ".$this->getUserId().") OR TB.user_id IN (SELECT id FROM ".$pjUserModel->getTable()." WHERE added_by IN(SELECT id FROM ".$pjUserModel->getTable()." WHERE added_by = ".$this->getUserId().")) OR TB.user_id IN (SELECT id FROM ".$pjUserModel->getTable()." WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM ".$pjUserModel->getTable()." WHERE role_id IN (1,3)))) AND TB.bus_id = ".$bus_id." AND TB.bus_departure_date='$date')")
                        ->groupBy("t1.ticket_id")
                        ->findAll()
                        ->getData();*/
                } else {
                    $ticket_arr = pjBookingTicketModel::factory()
                        ->select("t1.ticket_id, t2.content as title, SUM(qty) AS total_tickets")
                        ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                        ->where("t1.booking_id IN (SELECT TB.id FROM `".pjBookingModel::factory()->getTable()."` AS TB WHERE TB.status='confirmed' AND TB.bus_id = ".$bus_id." AND TB.bus_departure_date='$date')")
                        ->groupBy("t1.ticket_id")
                        ->findAll()
                        ->getData();
                }
					
				$total_passengers = 0;
				foreach($ticket_arr as $v)
				{
					$total_passengers += intval($v['total_tickets']);
				}
				
				$this->set('total_passengers', $total_passengers);
				$this->set('total_bookings', count($booking_arr));
				$this->set('ticket_arr', $ticket_arr);
				
				$this->set('booking_arr', $booking_arr);
				$this->set('bus_arr', $bus_arr);
				
				$this->appendJs('pjAdminSchedule.js');
				$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionSeats()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() ||$this->isEditor() || $this->isPolice() || $this->isIntelligenceService() || $this->isInvestor())
		{
            $pjUserModel = pjUserModel::factory();

			$bus_id = $_GET['bus_id'];
			$date = pjUtil::formatDate($_GET['date'], $this->option_arr['o_date_format']);
			
			$bus_arr = pjBusModel::factory()
				->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->select(" t1.*, t2.content AS route")
				->find($bus_id)
				->getData();
			$this->set('bus_arr', $bus_arr);
			
			$location_arr = pjBusLocationModel::factory()
				->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.location_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->join('pjRouteCity', "t3.city_id=t1.location_id AND t3.route_id=" . $bus_arr['route_id'], 'left outer')
				->select('t1.*, t2.content as location, t3.order')
				->where('bus_id', $bus_id)
				->orderBy("t3.order ASC")
				->findAll()
				->getData();
								
			$this->set('location_arr', $location_arr);

			$pjSeatModel = pjSeatModel::factory();
			$seat_arr = $pjSeatModel->where('t1.bus_type_id', $bus_arr['bus_type_id'])->orderBy("name+0 ASC")->findAll()->getData();
			
			$this->set('seat_arr', $seat_arr);
			
			$pjBookingModel = pjBookingModel::factory();
			$pjBookingModel
				->select("DISTINCT t1.id, t1.bus_id, t1.c_title, t1.c_fname, t1.c_lname, t1.c_phone, t1.pickup_id, t1.return_id, t1.created, t2.order as pickup_order, t3.order as return_order, t4.seat_id, 
						  (SELECT GROUP_CONCAT(CONCAT_WS(' x ', TML.content, TBT.qty) SEPARATOR '~:~') 
						   FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT LEFT OUTER JOIN `".pjMultiLangModel::factory()->getTable()."` AS TML ON (TML.model='pjTicket' AND TML.foreign_id=TBT.ticket_id AND TML.field='title' AND TML.locale='".$this->getLocaleId()."') 
						   WHERE TBT.booking_id = t1.id AND TBT.qty > 0) as tickets")
				->join("pjRouteCity", "t2.city_id=t1.pickup_id AND t2.route_id=" . $bus_arr['route_id'], 'left outer')
				->join("pjRouteCity", "t3.city_id=t1.return_id AND t3.route_id=" . $bus_arr['route_id'], 'left outer')
				->join("pjBookingSeat", "t4.booking_id=t1.id", 'left outer')
				->where('t1.bus_id', $bus_id)
                //->where("(DATE(t1.booking_datetime)='$date' AND DATE(t1.stop_datetime)='$date')")
                ->where('status <>', 'cancelled')
                ->where("t1.bus_departure_date='$date'");
                
            if ($this->isEditor()) {
                $pjBookingModel->where('(user_id = '.$this->getUserId().' OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
            }
            if ($this->isBusAdmin()) {
                //$pjBookingModel->where('(user_id = '.$this->getUserId().' OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().') OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().')))');	
            }

			$booking_arr = $pjBookingModel->orderBy("pickup_order ASC")
				->findAll()
				->toArray('tickets', '~:~')
                ->getData();
                
			$pjBookingSeatModel = pjBookingSeatModel::factory();

			$booking_id_arr = array();
			foreach($booking_arr as $v)
			{
				$booking_id_arr[] = $v['id'];
			}
			
			$seats_arr = array();
			if(!empty($booking_id_arr))
			{
				$temp_seats_arr = $pjBookingSeatModel
					->reset()
					->join('pjSeat', "t2.id=t1.seat_id", 'left outer')
					->select("t1.booking_id, t1.seat_id, t2.name")
					->whereIn("t1.booking_id", $booking_id_arr)
					->findAll()
					->getData();
				
				foreach($temp_seats_arr as $v)
				{
					$seats_arr[$v['booking_id']][$v['seat_id']] = $v['name'];
				}
			}
			
			$bs_arr = array();
			foreach($booking_arr as $v)
			{
				$v['seats'] = (isset($seats_arr[$v['id']]) && count($seats_arr[$v['id']]) > 0) ? $seats_arr[$v['id']] : array();
				if(isset($bs_arr[$v['seat_id']]) && count($bs_arr[$v['seat_id']]) > 0)
				{
					$can_added = true;
					foreach($bs_arr[$v['seat_id']] as $k => $booking)
					{
						if(($booking['pickup_order'] >= $v['pickup_order'] && $booking['pickup_order'] < $v['return_order']) ||
								($booking['return_order'] > $v['pickup_order'] && $booking['return_order'] <= $v['return_order']))
						{
							if($v['created'] < $booking['created'])
							{
								$can_added = false;
								break;
							}
						}
					}
					if($can_added == true)
					{
						$bs_arr[$v['seat_id']][] = $v;
					}
				}else{
					$bs_arr[$v['seat_id']][] = $v;
				}
			}			
			$this->set('bs_arr', $bs_arr);
			
			$booking_arr = $pjBookingModel
				->reset()
				->select(sprintf("t1.*,
					(SELECT SUM(TBT.qty) FROM `%1\$s` AS TBT WHERE TBT.booking_id = t1.id) AS tickets
					", pjBookingTicketModel::factory()->getTable())	)
				->where('t1.bus_id', $bus_id)
				->where("t1.booking_date='$date'")
				->findAll()
				->getData();
			$on_arr = $off_arr = array();
			foreach($booking_arr as $v)
			{
				$on_arr[$v['pickup_id']][] = intval($v['tickets']);
				$off_arr[$v['return_id']][] = intval($v['tickets']);
			}
			
			$this->set('on_arr', $on_arr);
			$this->set('off_arr', $off_arr);
			
			$this->appendJs('jquery.tipsy.js', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendCss('jquery.tipsy.css', PJ_THIRD_PARTY_PATH . 'tipsy/');
			$this->appendJs('pjAdminSchedule.js');
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionGetSchedule()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$date = pjUtil::formatDate($_GET['date'], $this->option_arr['o_date_format']);
			$day_of_week = strtolower(date('l', strtotime($date)));
			
			$column = 'departure';
			$direction = 'ASC';
			if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
			{
				$column = $_GET['column'];
				$direction = strtoupper($_GET['direction']);
			}
			
			$pjBusLocationModel = pjBusLocationModel::factory();
			$pjBusModel = pjBusModel::factory()
					->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='from' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t1.route_id AND t3.field='to' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjBusType', "t4.id=t1.bus_type_id", 'left outer')
					->join('pjMultiLang', "t5.model='pjRoute' AND t5.foreign_id=t1.route_id AND t5.field='title' AND t5.locale='".$this->getLocaleId()."'", 'left outer');					
					
					
			$pjBusModel->where("(t1.start_date <= '$date' AND '$date' <= t1.end_date) AND (t1.recurring LIKE '%$day_of_week%') AND t1.id NOT IN (SELECT TSD.bus_id FROM `".pjBusDateModel::factory()->getTable()."` AS TSD WHERE TSD.`date` = '$date')");
			if (isset($_GET['route_id']) && !empty($_GET['route_id']))
			{
				$route_id = pjObject::escapeString($_GET['route_id']);
				$pjBusModel->where("t1.route_id", $route_id);
			}
			
			$bus_arr = $pjBusModel->select(" t1.*, t5.content AS route,											  														  
												(SELECT CONCAT(TSL1.departure_time, '~:~', TSL1.location_id) FROM `".$pjBusLocationModel->getTable()."` AS TSL1 WHERE TSL1.bus_id = t1.id AND TSL1.arrival_time IS NULL LIMIT 1) AS departure,
												(SELECT CONCAT(TSL2.arrival_time, '~:~', TSL2.location_id) FROM `".$pjBusLocationModel->getTable()."` AS TSL2 WHERE TSL2.bus_id = t1.id AND TSL2.departure_time IS NULL LIMIT 1) AS arrive,
												(SELECT SUM(TBT.qty) FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT WHERE TBT.booking_id IN (SELECT TB.id FROM `".pjBookingModel::factory()->getTable()."` AS TB WHERE TB.bus_id = t1.id AND TB.bus_departure_date = '$date' AND TB.pickup_id = (SELECT TL1.city_id FROM `".pjRouteCityModel::factory()->getTable()."` AS TL1 WHERE TL1.route_id=t1.route_id ORDER BY `order` ASC LIMIT 1 ) AND TB.return_id = (SELECT TL2.city_id FROM `".pjRouteCityModel::factory()->getTable()."` AS TL2 WHERE TL2.route_id=t1.route_id ORDER BY `order` DESC LIMIT 1 ) ) LIMIT 1) AS tickets,
												(SELECT SUM(TBT.qty) FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT WHERE TBT.booking_id IN (SELECT TB.id FROM `".pjBookingModel::factory()->getTable()."` AS TB WHERE TB.bus_id = t1.id AND TB.bus_departure_date = '$date') LIMIT 1) AS total_tickets")
								 ->orderBy("$column $direction")->findAll()->getData();
						 	
			$this->set('bus_arr', $bus_arr);
		}
	}
	
	public function pjActionGetBookings()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
            $pjUserModel = pjUserModel::factory();

			$bus_id = $_GET['bus_id'];			
			$date = $_GET['date'];
			$location_arr = pjBusLocationModel::factory()
				->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.location_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->select('t1.*, t2.content as location')
				->where('bus_id', $bus_id)
				->findAll()->getData();
			
			$this->set('location_arr', $location_arr);	
							
			$pjBookingModel = pjBookingModel::factory();
			$and_where = '';
			if(isset($_GET['location_id']) && (int) $_GET['location_id'] > 0)
			{
				$pjBookingModel->where('pickup_id', $_GET['location_id']);
				$and_where = " AND TB.pickup_id = '" . $_GET['location_id'] . "'";
			}
			$pjBookingModel->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.pickup_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->join('pjMultiLang', "t3.model='pjCity' AND t3.foreign_id=t1.return_id AND t3.field='name' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
				->select("t1.*, t2.content as from_location, t3.content as to_location, 
						(SELECT GROUP_CONCAT(CONCAT_WS(' x ', TML.content, TBT.qty) SEPARATOR '~:~') 
						 FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT LEFT OUTER JOIN `".pjMultiLangModel::factory()->getTable()."` AS TML ON (TML.model='pjTicket' AND TML.foreign_id=TBT.ticket_id AND TML.field='title' AND TML.locale='".$this->getLocaleId()."') 
						 WHERE TBT.booking_id = t1.id AND TBT.qty > 0) as tickets")
                ->where('bus_id', $bus_id)
                ->where('status', 'confirmed')
                ->where('bus_departure_date', $date);
                
            if ($this->isEditor()) {
                $pjBookingModel->where('(user_id = '.$this->getUserId().' OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
            }
            if ($this->isBusAdmin()) {
                $pjBookingModel->where('(user_id = '.$this->getUserId().' OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().') OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().')) OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id IN (1,3))))');	
            }

			$booking_arr = 	$pjBookingModel->orderBy("t1.created DESC")
				->findAll()
				->toArray('tickets', '~:~')
				->getData();
			
			$booking_id_arr = array();
			foreach($booking_arr as $v)
			{
				$booking_id_arr[] = $v['id'];
			}
				
			$seats_arr = array();
			if(!empty($booking_id_arr))
			{
				$temp_seats_arr = pjBookingSeatModel::factory()
					->join('pjSeat', "t2.id=t1.seat_id", 'left outer')
					->select("t1.booking_id, t1.seat_id, t2.name")
					->whereIn("t1.booking_id", $booking_id_arr)
					->findAll()
					->getData();
			
				foreach($temp_seats_arr as $v)
				{
					$seats_arr[$v['booking_id']][$v['seat_id']] = $v['name'];
				}
			}
			foreach($booking_arr as $k => $v)
			{
				$booking_arr[$k]['seats'] =  (isset($seats_arr[$v['id']]) && count($seats_arr[$v['id']]) > 0) ? $seats_arr[$v['id']] : array();
			}
            
            if ($this->isEditor()) {
                $ticket_arr = pjBookingTicketModel::factory()
                    ->select("t1.ticket_id, t2.content as title, SUM(qty) AS total_tickets")
                    ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                    ->where("t1.booking_id IN (SELECT TB.id FROM `".pjBookingModel::factory()->getTable()."` AS TB WHERE TB.status = 'confirmed' AND (TB.user_id = ".$this->getUserId()." OR TB.user_id IN (SELECT id FROM ".$pjUserModel->getTable()." WHERE added_by = ".$this->getUserId().")) AND TB.bus_id = ".$bus_id." AND TB.bus_departure_date='$date'$and_where)")
                    ->groupBy("t1.ticket_id")
                    ->findAll()->getData();
            } else if ($this->isBusAdmin()) {
                $ticket_arr = pjBookingTicketModel::factory()
                    ->select("t1.ticket_id, t2.content as title, SUM(qty) AS total_tickets")
                    ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                    ->where("t1.booking_id IN (SELECT TB.id FROM `".pjBookingModel::factory()->getTable()."` AS TB WHERE TB.status = 'confirmed' AND (TB.user_id = ".$this->getUserId()." OR TB.user_id IN (SELECT id FROM ".$pjUserModel->getTable()." WHERE added_by = ".$this->getUserId().") OR TB.user_id IN (SELECT id FROM ".$pjUserModel->getTable()." WHERE added_by IN(SELECT id FROM ".$pjUserModel->getTable()." WHERE added_by = ".$this->getUserId().")) OR TB.user_id IN (SELECT id FROM ".$pjUserModel->getTable()." WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM ".$pjUserModel->getTable()." WHERE role_id IN (1,3)))) AND TB.bus_id = ".$bus_id." AND TB.bus_departure_date='$date'$and_where)")
                    ->groupBy("t1.ticket_id")
                    ->findAll()->getData();
            } else {
                $ticket_arr = pjBookingTicketModel::factory()
                    ->select("t1.ticket_id, t2.content as title, SUM(qty) AS total_tickets")
                    ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                    ->where("t1.booking_id IN (SELECT TB.id FROM `".pjBookingModel::factory()->getTable()."` AS TB WHERE TB.status = 'confirmed' AND TB.bus_id = ".$bus_id." AND TB.bus_departure_date='$date'$and_where)")
                    ->groupBy("t1.ticket_id")
                    ->findAll()->getData();
            }
				
			$total_passengers = 0;
			foreach($ticket_arr as $v)
			{
				$total_passengers += intval($v['total_tickets']);
			}
			
			$this->set('total_passengers', $total_passengers);
			$this->set('total_bookings', count($booking_arr));
			$this->set('ticket_arr', $ticket_arr);
			$this->set('booking_arr', $booking_arr);
		}
	}
	
	public function pjActionGetTimetable()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$selected_date = date('Y-m-d');
			if (isset($_GET['selected_date']) && !empty($_GET['selected_date']))
			{
				$selected_date = pjUtil::formatDate($_GET['selected_date'], $this->option_arr['o_date_format']);
			}
			$day= date("w", strtotime($selected_date));
				
			if($day > 1)
			{
				$days = $day - 1;
				$week_start_date = date("Y-m-d", strtotime($selected_date . "-$days days"));
				$week_end_date = date("Y-m-d", strtotime($selected_date . "-$days days") + 24 * 60 * 60 * 6);
			}else{
				$week_start_date = $selected_date;
				$week_end_date = date("Y-m-d", strtotime($week_start_date) + 24 * 60 * 60 * 6);
			}
			
			if (isset($_GET['week_start_date']) && !empty($_GET['week_start_date']))
			{
				$week_start_date = $_GET['week_start_date'];
				$week_end_date = date("Y-m-d", strtotime($_GET['week_start_date']) + 24 * 60 * 60 * 6);
			}

			$pjBusDateModel = pjBusDateModel::factory();
			
			$pjBusModel = pjBusModel::factory()
					->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjBusType', "t3.id=t1.bus_type_id", 'left outer');
				
			if (isset($_GET['route_id']) && !empty($_GET['route_id']))
			{
				$route_id = pjObject::escapeString($_GET['route_id']);
				$pjBusModel->where("t1.route_id", $route_id);
				$pjBusDateModel->where("t1.bus_id IN(SELECT TB.id FROM `".$pjBusModel->getTable()."` AS TB WHERE TB.route_id = $route_id)");
			}
			$pjBusModel->where("( (t1.start_date <= '$week_start_date' AND '$week_start_date' <= t1.end_date) OR (t1.start_date <= '$week_end_date' AND '$week_end_date' <= t1.end_date) )");
			$column = 'departure_time';
			$direction = 'ASC';
			$bus_arr = $pjBusModel->select(" t1.*, t2.content AS route")
				->orderBy("$column $direction")
				->findAll()
				->getData();
						 	
			$this->set('bus_arr', $bus_arr);

			$_arr = $pjBusDateModel->findAll()->getData();
			$date_arr = array();
			foreach($_arr as $v)
			{
				$date_arr[$v['bus_id']][] = $v['date'];
			}
			$ticket_arr = array();
			if (isset($_GET['route_id']) && !empty($_GET['route_id']))
			{
				$route_id = pjObject::escapeString($_GET['route_id']);
				$booking_arr = pjBookingModel::factory()
					->select("t1.bus_id, t1.booking_date, (SELECT SUM(TBT.qty) FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT WHERE TBT.booking_id=t1.id) AS tickets")
					->where("t1.bus_id IN(SELECT TB.id FROM `".$pjBusModel->getTable()."` AS TB WHERE TB.route_id = $route_id)")
					->where("t1.booking_date BETWEEN '$week_start_date' AND '$week_end_date'")
					->findAll()
					->getData();
				foreach($booking_arr as $v)
				{
					isset($ticket_arr[$v['bus_id'] . '~:~' . $v['booking_date']]) ? $ticket_arr[$v['bus_id'] . '~:~' . $v['booking_date']] += $v['tickets'] : $ticket_arr[$v['bus_id'] . '~:~' . $v['booking_date']] = $v['tickets'];
				}
			}
			$this->set('date_arr', $date_arr);
			$this->set('week_start_date', $week_start_date);
			$this->set('week_end_date', $week_end_date);
			$this->set('ticket_arr', $ticket_arr);
		}
	}
	
	public function pjActionPrintSchedule()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() ||$this->isEditor() || $this->isPolice() || $this->isIntelligenceService() || $this->isInvestor())
		{
			$this->setLayout('pjActionPrintTable');
			$bus_arr = array();
			
			if(isset($_GET['date']))
			{
				$date = pjObject::escapeString($_GET['date']);
				if(pjUtil::checkDateFormat($date))
				{
					$day_of_week = strtolower(date('l', strtotime($date)));
					
					$column = 'departure_time';
					$direction = 'ASC';
					if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
					{
						$column = ($_GET['column'] == 'departure' ? 'departure_time' : $_GET['column']);
						$direction = strtoupper($_GET['direction']);
					}

					$bus_arr = pjBusModel::factory()
						->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
						->join('pjBusType', "t3.id=t1.bus_type_id", 'left outer')
						->where("(t1.start_date <= '$date' AND '$date' <= t1.end_date) AND (t1.recurring LIKE '%$day_of_week%') AND t1.id NOT IN (SELECT TSD.bus_id FROM `".pjBusDateModel::factory()->getTable()."` AS TSD WHERE TSD.`date` = '$date')")
						->select(" t1.*, t2.content AS route,
									(SELECT SUM(TBT.qty) FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT WHERE TBT.booking_id IN (SELECT TB.id FROM `".pjBookingModel::factory()->getTable()."` AS TB WHERE TB.bus_id = t1.id AND TB.bus_departure_date = '$date' AND TB.pickup_id = (SELECT TL1.city_id FROM `".pjRouteCityModel::factory()->getTable()."` AS TL1 WHERE TL1.route_id=t1.route_id ORDER BY `order` ASC LIMIT 1 ) AND TB.return_id = (SELECT TL2.city_id FROM `".pjRouteCityModel::factory()->getTable()."` AS TL2 WHERE TL2.route_id=t1.route_id ORDER BY `order` DESC LIMIT 1 ) ) LIMIT 1) AS tickets,
									(SELECT SUM(TBT.qty) FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT WHERE TBT.booking_id IN (SELECT TB.id FROM `".pjBookingModel::factory()->getTable()."` AS TB WHERE TB.bus_id = t1.id AND TB.bus_departure_date = '$date') LIMIT 1) AS total_tickets")
						->orderBy("$column $direction")
						->findAll()
						->getData();
								
					$this->set('status', 200);
				}else{
					$this->set('status', 100);
				}
			}else{
				$this->set('status', 101);
			}				 	
			$this->set('bus_arr', $bus_arr);
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionPrintBookings()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() ||$this->isEditor() || $this->isPolice() || $this->isIntelligenceService() || $this->isInvestor())
		{
			$this->setLayout('pjActionPrintTable');
			if(isset($_GET['bus_id']))
			{
                $pjUserModel = pjUserModel::factory();

				$bus_id = pjObject::escapeString($_GET['bus_id']);
				$pjBusModel = pjBusModel::factory();
				if((int) $bus_id > 0 && $pjBusModel->where('t1.id', $bus_id)->findCount()->getData() > 0)
				{			
					if(isset($_GET['date']))
					{
						$date = pjObject::escapeString($_GET['date']);
						if(pjUtil::checkDateFormat($date))
						{
							$and_where = '';
							$pjBookingModel = pjBookingModel::factory();
							if(isset($_GET['location_id']) && (int) $_GET['location_id'] > 0)
							{
								$and_where = " AND TB.pickup_id = '" . $_GET['location_id'] . "'";
							}
							$pjBookingModel->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.pickup_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                                ->join('pjMultiLang', "t3.model='pjCity' AND t3.foreign_id=t1.return_id AND t3.field='name' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                                ->select("t1.*, t2.content as from_location, t3.content as to_location, 
                                        (SELECT GROUP_CONCAT(CONCAT_WS(' x ', TML.content, TBT.qty) SEPARATOR '~:~') 
                                            FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT LEFT OUTER JOIN `".pjMultiLangModel::factory()->getTable()."` AS TML ON (TML.model='pjTicket' AND TML.foreign_id=TBT.ticket_id AND TML.field='title' AND TML.locale='".$this->getLocaleId()."') 
                                            WHERE TBT.booking_id = t1.id AND TBT.qty > 0) as tickets")
                                ->where('status', 'confirmed')
                                ->where('bus_id', $bus_id)
                                ->where('bus_departure_date', $date);                                                

                            if ($this->isEditor()) {
                                $pjBookingModel->where('(user_id = '.$this->getUserId().' OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
                            }
                            if ($this->isBusAdmin()) {
                                //$pjBookingModel->where('(user_id = '.$this->getUserId().' OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().') OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().')) OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id IN (1,3))))');	
                            }

                            $booking_arr = $pjBookingModel->orderBy("t1.created DESC")
                            ->findAll()
                            ->toArray('tickets', '~:~')
                            ->getData();
							$booking_id_arr = array();
							foreach($booking_arr as $v)
							{
								$booking_id_arr[] = $v['id'];
							}
							
							$seats_arr = array();
							if(!empty($booking_id_arr))
							{
								$temp_seats_arr = pjBookingSeatModel::factory()
									->join('pjSeat', "t2.id=t1.seat_id", 'left outer')
									->select("t1.booking_id, t1.seat_id, t2.name")
									->whereIn("t1.booking_id", $booking_id_arr)
									->findAll()
									->getData();
									
								foreach($temp_seats_arr as $v)
								{
									$seats_arr[$v['booking_id']][$v['seat_id']] = $v['name'];
								}
							}
							foreach($booking_arr as $k => $v)
							{
								$booking_arr[$k]['seats'] =  (isset($seats_arr[$v['id']]) && count($seats_arr[$v['id']]) > 0) ? $seats_arr[$v['id']] : array();
							}
							
							$bus_arr = $pjBusModel->reset()
								->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
								->select(" t1.*, t2.content AS route")
								->find($bus_id)
								->getData();
										
							$ticket_arr = pjBookingTicketModel::factory()
								->select("t1.ticket_id, t2.content as title, SUM(qty) AS total_tickets")
								->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
								->where("t1.booking_id IN (SELECT TB.id 
														   FROM `".pjBookingModel::factory()->getTable()."` AS TB 
														   WHERE TB.status='confirmed' AND TB.bus_id = ".$bus_id." AND TB.bus_departure_date='$date'$and_where)")
								->groupBy("t1.ticket_id")
								->findAll()->getData();
								
							$total_passengers = 0;
							foreach($ticket_arr as $v)
							{
								$total_passengers += intval($v['total_tickets']);
							}
							
							$this->set('total_passengers', $total_passengers);
							$this->set('total_bookings', count($booking_arr));
							$this->set('ticket_arr', $ticket_arr);					 
												 
							$this->set('booking_arr', $booking_arr);
							$this->set('bus_arr', $bus_arr);
							$this->set('status', 200);
						}else{
							$this->set('status', 100);
						}
					}else{
						$this->set('status', 101);
					}
				}else{
					$this->set('status', 103);
				}
			}else{
				$this->set('status', 102);
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionPrintSeats()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() ||$this->isEditor() || $this->isPolice() || $this->isIntelligenceService() || $this->isInvestor())
		{
			$this->setLayout('pjActionPrintTable');
			
			if(isset($_GET['bus_id']))
			{
                $pjUserModel = pjUserModel::factory();

				$bus_id = pjObject::escapeString($_GET['bus_id']);
				$pjBusModel = pjBusModel::factory();
				if((int) $bus_id > 0 && $pjBusModel->where('t1.id', $bus_id)->findCount()->getData() > 0)
				{
					if(isset($_GET['date']))
					{
						$date = pjObject::escapeString($_GET['date']);
						if(pjUtil::checkDateFormat($date))
						{
							$bus_arr = pjBusModel::factory()
								->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
								->select(" t1.*, t2.content AS route")
								->find($bus_id)
								->getData();
							$this->set('bus_arr', $bus_arr);
							
							$location_arr = pjBusLocationModel::factory()
								->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.location_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
								->join('pjRouteCity', "t3.city_id=t1.location_id AND t3.route_id=" . $bus_arr['route_id'], 'left outer')
								->select('t1.*, t2.content as location, t3.order')
								->where('bus_id', $bus_id)
								->orderBy("t3.order ASC")
								->findAll()
								->getData();
												
							$this->set('location_arr', $location_arr);
							$this->set('bus_arr', $bus_arr);
				
							$pjSeatModel = pjSeatModel::factory();
							$seat_arr = $pjSeatModel->where('t1.bus_type_id', $bus_arr['bus_type_id'])->orderBy("name+0 ASC")->findAll()->getData();
							
							$this->set('seat_arr', $seat_arr);
							
							$pjBookingModel = pjBookingModel::factory();
							$pjBookingModel->select("DISTINCT t1.id, t1.bus_id, t1.c_title, t1.c_fname, t1.c_lname, t1.c_phone, t1.pickup_id, t1.return_id, t1.created, t2.order as pickup_order, t3.order as return_order, t4.seat_id, 
										  (SELECT GROUP_CONCAT(CONCAT_WS(' x ', TML.content, TBT.qty) SEPARATOR '~:~') 
										   FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT LEFT OUTER JOIN `".pjMultiLangModel::factory()->getTable()."` AS TML ON (TML.model='pjTicket' AND TML.foreign_id=TBT.ticket_id AND TML.field='title' AND TML.locale='".$this->getLocaleId()."') 
										   WHERE TBT.booking_id = t1.id AND TBT.qty > 0) as tickets")
								->join("pjRouteCity", "t2.city_id=t1.pickup_id AND t2.route_id=" . $bus_arr['route_id'], 'left outer')
								->join("pjRouteCity", "t3.city_id=t1.return_id AND t3.route_id=" . $bus_arr['route_id'], 'left outer')
                                ->join("pjBookingSeat", "t4.booking_id=t1.id", 'left outer')
                                ->where('status <>', 'cancelled')
								->where('t1.bus_id', $bus_id)
                                ->where("t1.bus_departure_date='$date'");
                                
                            if ($this->isEditor()) {
                                $pjBookingModel->where('(user_id = '.$this->getUserId().' OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
                            }
                            if ($this->isBusAdmin()) {
                                //$pjBookingModel->where('(user_id = '.$this->getUserId().' OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().') OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().')))');	
                            }

                            $booking_arr = $pjBookingModel->orderBy("pickup_order ASC")
								->findAll()
								->toArray('tickets', '~:~')
								->getData();
							$pjBookingSeatModel = pjBookingSeatModel::factory();
							
							$booking_id_arr = array();
							foreach($booking_arr as $v)
							{
								$booking_id_arr[] = $v['id'];
							}
							
							$seats_arr = array();
							if(!empty($booking_id_arr))
							{
								$temp_seats_arr = $pjBookingSeatModel
									->reset()
									->join('pjSeat', "t2.id=t1.seat_id", 'left outer')
									->select("t1.booking_id, t1.seat_id, t2.name")
									->whereIn("t1.booking_id", $booking_id_arr)
									->findAll()
									->getData();
								
								foreach($temp_seats_arr as $v)
								{
									$seats_arr[$v['booking_id']][$v['seat_id']] = $v['name'];
								}
							}
							
							$bs_arr = array();
							foreach($booking_arr as $v)
							{
								$v['seats'] = (isset($seats_arr[$v['id']]) && count($seats_arr[$v['id']]) > 0) ? $seats_arr[$v['id']] : array();
								if(isset($bs_arr[$v['seat_id']]) && count($bs_arr[$v['seat_id']]) > 0)
								{
									$can_added = true;
									foreach($bs_arr[$v['seat_id']] as $k => $booking)
									{
										if(($booking['pickup_order'] >= $v['pickup_order'] && $booking['pickup_order'] < $v['return_order']) ||
												($booking['return_order'] > $v['pickup_order'] && $booking['return_order'] <= $v['return_order']))
										{
											if($v['created'] < $booking['created'])
											{
												$can_added = false;
												break;
											}
										}
									}
									if($can_added == true)
									{
										$bs_arr[$v['seat_id']][] = $v;
									}
								}else{
									$bs_arr[$v['seat_id']][] = $v;
								}
							}			
							$this->set('bs_arr', $bs_arr);
							
							$booking_arr = $pjBookingModel->reset()
								->select(sprintf("t1.*,
									(SELECT SUM(TBT.qty) FROM `%1\$s` AS TBT WHERE TBT.booking_id = t1.id) AS tickets
									", pjBookingTicketModel::factory()->getTable())	)
								->where('t1.bus_id', $bus_id)
								->where("t1.booking_date='$date'")
								->findAll()
								->getData();
							$on_arr = $off_arr = array();
							foreach($booking_arr as $v)
							{
								$on_arr[$v['pickup_id']][] = intval($v['tickets']);
								$off_arr[$v['return_id']][] = intval($v['tickets']);
							}
							
							$this->set('on_arr', $on_arr);
							$this->set('off_arr', $off_arr);
							$this->set('status', 200);
						}else{
							$this->set('status', 100);
						}
					}else{
						$this->set('status', 101);
					}
				}else{
					$this->set('status', 103);
				}
			}else{
				$this->set('status', 102);
			}
		} else {
			$this->set('status', 2);
		}
	}
}
?>