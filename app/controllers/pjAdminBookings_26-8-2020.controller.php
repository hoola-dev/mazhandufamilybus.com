<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAdminBookings extends pjAdmin
{
	public $defaultPeriod = 'booking_period';
	
	public $defaultReturnPeriod = 'booking_return_period';
	
	public function pjActionIndex()
	{    
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isEditor() || $this->isBusAdmin())
		{
			$_arr = pjBusModel::factory()
				->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->select("t1.*, t2.content AS route")
				->orderBy("route ASC")
				//->debug(true)
				->findAll()
				->getData();
				
			foreach($_arr as $k => $v)
			{
				if(!empty($v['departure_time']) && !empty($v['arrival_time']))
				{
					$v['depart_arrive'] = pjUtil::formatTime($v['departure_time'], "H:i:s", $this->option_arr['o_time_format']) . ' - ' . pjUtil::formatTime($v['arrival_time'], "H:i:s", $this->option_arr['o_time_format']);
				}else{
					$v['depart_arrive'] = '';
				}
				$_arr[$k] = $v;
			}
			
			$route_arr = pjRouteModel::factory()
				->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->select("t1.*, t2.content AS route")
				->orderBy("route ASC")
				->findAll()
				->getData();
			
			$this->set('route_arr', $route_arr);
			$this->set('bus_arr', $_arr);
			
			$this->appendJs('jquery.datagrid.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('pjAdminBookings.js');
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionGetBooking()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
            $pjUserModel = pjUserModel::factory();

            $pjBookingModel = pjBookingModel::factory()                
				->join('pjBus', "t2.id=t1.bus_id", 'left outer')
				->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
				->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
				->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer');
			
			if ($this->isEditor()) {
				$pjBookingModel->where('(user_id = '.$this->getUserId().' OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
            }
            if ($this->isBusAdmin()) {
				$pjBookingModel->where('(user_id = '.$this->getUserId().' OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().') OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().')))');	
			}
			if (isset($_GET['q']) && !empty($_GET['q']))
			{
				$q = pjObject::escapeString($_GET['q']);
				$pjBookingModel->where("(t1.id = '$q' OR t1.uuid = '$q' OR t1.c_fname LIKE '%$q%' OR t1.c_lname LIKE '%$q%' OR t1.c_email LIKE '%$q%')");
			}
			
			if (isset($_GET['bus_id']) && !empty($_GET['bus_id']))
			{
				$bus_id = pjObject::escapeString($_GET['bus_id']);
				$pjBookingModel->where("(t1.bus_id='".$bus_id."')");
			}
			if (isset($_GET['route_id']) && !empty($_GET['route_id']))
			{
				$route_id = pjObject::escapeString($_GET['route_id']);
				$pjBookingModel->where("(t1.bus_id IN (SELECT TB.id FROM `".pjBusModel::factory()->getTable()."` AS TB WHERE TB.route_id=$route_id))");
			}
			
			if (isset($_GET['status']) && !empty($_GET['status']) && in_array($_GET['status'], array('confirmed','cancelled','pending')))
			{
				$pjBookingModel->where('t1.status', $_GET['status']);
			}
			if (isset($_GET['date_from']) && !empty($_GET['date_from']) && isset($_GET['date_to']) && !empty($_GET['date_to']))
			{
				$df = pjUtil::formatDate($_GET['date_from'], $this->option_arr['o_date_format']);
				$dt = pjUtil::formatDate($_GET['date_to'], $this->option_arr['o_date_format']);
				$pjBookingModel->where("(t1.booking_date BETWEEN '$df' AND '$dt')");
				
			} else {
				if (isset($_GET['date_from']) && !empty($_GET['date_from']))
				{
					$df = pjUtil::formatDate($_GET['date_from'], $this->option_arr['o_date_format']);
					$pjBookingModel->where("(t1.booking_date >= '$df')");
				} elseif (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
					$dt = pjUtil::formatDate($_GET['date_to'], $this->option_arr['o_date_format']);
					$pjBookingModel->where("(t1.booking_date <= '$dt')");
				}
			}
				
			$column = 'created';
			$direction = 'DESC';
			if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
			{
				$column = $_GET['column'];
				$direction = strtoupper($_GET['direction']);
			}

			$total = $pjBookingModel->findCount()->getData();
			$rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
			$pages = ceil($total / $rowCount);
			$page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
			$offset = ((int) $page - 1) * $rowCount;
			if ($page > $pages)
			{
				$page = $pages;
			}

			$data = $pjBookingModel
				->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
				->orderBy("$column $direction")
				->limit($rowCount, $offset)
				->findAll()
				->getData();
			foreach($data as $k => $v)
			{
				$route_details = '';
				$client_arr = array();
				if(!empty($v['c_fname']))
				{
					$client_arr[] = $v['c_fname'];
				}
				if(!empty($v['c_lname']))
				{
					$client_arr[] = $v['c_lname'];
				}
				$v['client'] = join(" ", $client_arr) . "<br/>" . $v['c_email'];
				$v['date_time'] = date($this->option_arr['o_date_format'] . ', ' . $this->option_arr['o_time_format'], strtotime($v['booking_datetime'])) . '<br/>' . date($this->option_arr['o_date_format'] . ', ' . $this->option_arr['o_time_format'], strtotime($v['stop_datetime']));
				if(date($this->option_arr['o_date_format'], strtotime($v['booking_datetime'])) == date($this->option_arr['o_date_format'], strtotime($v['stop_datetime'])))
				{
					$v['date_time'] = date($this->option_arr['o_date_format'], strtotime($v['booking_datetime'])) . '<br/>' . date($this->option_arr['o_time_format'], strtotime($v['booking_datetime'])) . ' - ' . date($this->option_arr['o_time_format'], strtotime($v['stop_datetime']));
				}
				
				$route_details .= $v['route_title'];
				$route_details .= ', ' . date($this->option_arr['o_time_format'], strtotime($v['departure_time'])) . ' - ' . date($this->option_arr['o_time_format'], strtotime($v['arrival_time']));
				$route_details .= '<br/>'  . mb_strtolower(__('lblFrom', true), 'UTF-8') . ' ' . $v['from_location'] . ' ' . mb_strtolower(__('lblTo', true), 'UTF-8') . ' ' . $v['to_location'];
				
				$v['route_details'] = $route_details;
				$data[$k] = $v;
			}
						
			pjAppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
		}
		exit;
	}
	
	public function pjActionSaveBooking()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			pjBookingModel::factory()->where('id', $_GET['id'])->limit(1)->modifyAll(array($_POST['column'] => $_POST['value']));
		}
		exit;
	}
	
	public function pjActionExportBooking()
	{
		$this->checkLogin();
		
		if (isset($_POST['record']) && is_array($_POST['record']))
		{
			$arr = pjBookingModel::factory()->whereIn('id', $_POST['record'])->findAll()->getData();
			$csv = new pjCSV();
			$csv
				->setHeader(true)
				->setName("Bookings-".time().".csv")
				->process($arr)
				->download();
		}
		exit;
	}
	
	public function pjActionDeleteBooking()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isEditor() || $this->isBusAdmin())
		{
            if ($this->isXHR())
            {
                $pjUserModel = pjUserModel::factory();

                $pjBookingModel = pjBookingModel::factory();
                $arr = $pjBookingModel->where('id',$_GET['id']);
                

                if ($this->isEditor() || $this->isBusAdmin()) {
                    $pjBookingModel->where('(user_id = '.$this->getUserId().' OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
                }

                $arr1 = $pjBookingModel->findAll()->getData();

                $arr = (count($arr1)) ? $arr1[0] : array();

				if (!$arr)
				{
                    exit;
                }
                
                $user_id = $arr['user_id'];

                $user_arr = pjUserModel::factory()
                    ->find($user_id)->getData();

                $response = array();
                if (pjBookingModel::factory()->setAttributes(array('id' => $_GET['id']))->erase()->getAffectedRows() == 1)
                {
                    pjBookingTicketModel::factory()->where('booking_id', $_GET['id'])->eraseAll();
                    pjBookingSeatModel::factory()->where('booking_id', $_GET['id'])->eraseAll();
                    pjBookingPaymentModel::factory()->where('booking_id', $_GET['id'])->eraseAll();

                    if ($user_arr && $user_arr['role_id'] == 2) {
                        $user_credit = pjCreditModel::factory()
                        ->where('t1.user_id', $user_id)
                        ->findAll()
                        ->getData();

                        if ($arr['status'] == 'confirmed' && !empty($user_credit)) {
                            $increase_credit = $arr['total'] + $user_credit[0]['credit']; 
                            
                            //Return the credit taken while adding the booking.
                            $result = pjCreditModel::factory()->whereIn('user_id', $user_id)->modifyAll(array(
                                'credit' => $increase_credit
                            ));
                        }
                    }
                    
                    $response['code'] = 200;
                } else {
                    $response['code'] = 100;
                }
                pjAppController::jsonResponse($response);
            }
        }
		exit;
	}
	
	public function pjActionDeleteBookingBulk()
	{
		$this->setAjax(true);

        if ($this->isAdmin() || $this->isEditor() || $this->isBusAdmin()) {
            if ($this->isXHR())
            {
                if (isset($_POST['record']) && count($_POST['record']) > 0)
                {
                    $pjUserModel = pjUserModel::factory();

                    $booking_ids = $_POST['record'];
                    foreach ($booking_ids as $id) {                    
                        $pjBookingModel = pjBookingModel::factory();
                        $arr = $pjBookingModel->where('id',$id);
                        

                        if ($this->isEditor() || $this->isBusAdmin()) {
                            $pjBookingModel->where('(user_id = '.$this->getUserId().' OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
                        }

                        $arr1 = $pjBookingModel->findAll()->getData();

                        $arr = (count($arr1)) ? $arr1[0] : array();

                        if (!$arr)
                        {
                            continue;
                        }

                        $user_id = $arr['user_id'];

                        $user_arr = pjUserModel::factory()
                            ->find($user_id)->getData();

                        if (pjBookingModel::factory()->setAttributes(array('id' => $id))->erase()->getAffectedRows() == 1)
                        {
                            pjBookingTicketModel::factory()->where('booking_id', $id)->eraseAll();
                            pjBookingSeatModel::factory()->where('booking_id', $id)->eraseAll();
                            pjBookingPaymentModel::factory()->where('booking_id', $id)->eraseAll();
            
                            if ($user_arr && $user_arr['role_id'] == 2) {
                                $user_credit = pjCreditModel::factory()
                                ->where('t1.user_id', $user_id)
                                ->findAll()
                                ->getData();
            
                                if ($arr['status'] == 'confirmed' && !empty($user_credit)) {
                                    $increase_credit = $arr['total'] + $user_credit[0]['credit']; 
                                    
                                    //Return the credit taken while adding the booking.
                                    $result = pjCreditModel::factory()->whereIn('user_id', $user_id)->modifyAll(array(
                                        'credit' => $increase_credit
                                    ));
                                }
                            }
                            
                            $response['code'] = 200;
                        }
                    }

                    // pjBookingModel::factory()->whereIn('id', $_POST['record'])->eraseAll();
                    // pjBookingTicketModel::factory()->whereIn('booking_id', $_POST['record'])->eraseAll();
                    // pjBookingSeatModel::factory()->whereIn('booking_id', $_POST['record'])->eraseAll();
                    // pjBookingPaymentModel::factory()->whereIn('booking_id', $_POST['record'])->eraseAll();			
                }
            }
        }
		exit;
	}
	
	public function pjActionCreate()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isEditor() || $this->isBusAdmin())
		{
			if (isset($_POST['booking_create']))
			{
				$user_id = $this->getUserId();

				if ($this->isEditor()) {
					$booking_total = $_POST['pickup_total'];
					$booking_status = $_POST['status'];

					$user_credit = pjCreditModel::factory()
					->where('t1.user_id', $user_id)
					->findAll()
					->getData();

					if ($booking_status == 'confirmed' && (empty($user_credit) || $user_credit[0]['credit'] < $booking_total)) {
						$err = 'CR01';
						pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminBookings&action=pjActionCreate&err=$err");	
					}
				}			

				$pjBookingModel = pjBookingModel::factory();
				
				$bus_id = $_POST['bus_id'];
				$pickup_id = $_POST['pickup_id'];
				$return_id = $_POST['return_id'];
				
				$data = array();
				$data['uuid'] = time();
				$data['ip'] = pjUtil::getClientIp();
				$data['booking_date'] = pjUtil::formatDate($_POST['booking_date'], $this->option_arr['o_date_format']);
				if (isset($_POST['is_return'])) 
				{
					$data['return_date'] = pjUtil::formatDate($_POST['return_date'], $this->option_arr['o_date_format']);
				}
				$data['booking_datetime'] = $data['booking_date'];
				if(isset($_SESSION[$this->defaultPeriod]))
				{
					$data['booking_datetime'] = $_SESSION[$this->defaultPeriod]['departure_time'];
					$data['stop_datetime'] = $_SESSION[$this->defaultPeriod]['arrival_time'];
				}
				$data['bus_departure_date'] = $data['booking_date'];
				$bus_data = pjBusModel::factory()->find($bus_id)->getData();
				$depart_date_time_iso = $data['booking_date'] . ' ' . $bus_data['departure_time'];
				if($depart_date_time_iso > $data ['booking_datetime'])
				{
					$data['bus_departure_date'] = date('Y-m-d', strtotime($depart_date_time_iso) - 86400);
				}
				
				$bt_arr = array();
				$pjBusLocationModel = pjBusLocationModel::factory();
				$_arr = $pjBusLocationModel->where('bus_id', $bus_id)->where("location_id", $pickup_id)->findAll()->getData();
				if(count($_arr) > 0)
				{
					$bt_arr[] = pjUtil::formatTime($_arr[0]['departure_time'], "H:i:s", $this->option_arr['o_time_format']);
					if(!isset($_SESSION[$this->defaultPeriod]))
					{
						$data['booking_datetime'] .= ' ' . $_arr[0]['departure_time'];
					}
				}
				
				$_arr = $pjBusLocationModel->reset()->where('bus_id', $bus_id)->where("location_id", $return_id)->findAll()->getData();
				if(count($_arr) > 0)
				{
					$bt_arr[] = pjUtil::formatTime($_arr[0]['arrival_time'], "H:i:s", $this->option_arr['o_time_format']);
				}
				$data['booking_time'] = join(" - ", $bt_arr);

				$data['sub_total'] = $_POST['pickup_sub_total']; unset($_POST['sub_total']);
				$data['tax'] = $_POST['pickup_tax']; unset($_POST['tax']);
				$data['total'] = $_POST['pickup_total']; unset($_POST['total']);
				$data['deposit'] = $_POST['pickup_deposit']; unset($_POST['deposit']);
				$data['user_id'] = $this->getUserId();
				
				$id = pjBookingModel::factory(array_merge($_POST, $data))->insert()->getInsertId();
				
				if ($id !== false && (int) $id > 0)
				{
					if ($this->isEditor()) {
						$new_credit = $user_credit[0]['credit'] - $booking_total;
	
						if ($booking_status == 'confirmed') {
							$result = pjCreditModel::factory()->whereIn('user_id', $user_id)->modifyAll(array(
								'credit' => $new_credit
							));	
						}
					}	
					$bus_arr = pjBusModel::factory()
						->select('t1.*, t2.seats_map')
						->join('pjBusType', "t2.id=t1.bus_type_id", 'left')
						->find($bus_id)->getData();
					
					$ticket_arr = pjPriceModel::factory()
						 ->select("t1.*")
						 ->where('t1.bus_id', $bus_id)
						 ->where('t1.from_location_id', $pickup_id)
						 ->where('t1.to_location_id', $return_id)
						 ->where('is_return = "F"')
						 ->findAll()->getData();

					$location_pair = array();
					$location_arr = pjRouteCityModel::factory()->getLocations($bus_arr['route_id'], $pickup_id, $return_id);
						
					for($i = 0; $i < count($location_arr); $i++ )
					{
						$j = $i + 1;
						if($j < count($location_arr))
						{
							$location_pair[] = $location_arr[$i]['city_id'] . '-' . $location_arr[$j]['city_id'];
						}
					}
					
					$pjBookingTicketModel = pjBookingTicketModel::factory();
					foreach($ticket_arr as $v)
					{
						if (isset($_POST['ticket_cnt_' . $v['ticket_id']]) && $_POST['ticket_cnt_' . $v['ticket_id']] > 0)
						{
							$ticket_data = array();
							$ticket_data['booking_id'] = $id;
							$ticket_data['ticket_id'] = $v['ticket_id'];
							$ticket_data['qty'] = $_POST['ticket_cnt_' . $v['ticket_id']];
							$ticket_data['amount'] = $ticket_data['qty'] * $v['price'];
							$ticket_data['is_return'] = 'F';
							$pjBookingTicketModel->reset()->setAttributes($ticket_data)->insert();
						}
					}
					$pjBookingSeatModel = pjBookingSeatModel::factory();
					if(!empty($bus_arr['seats_map']))
					{
						$seat_id_arr = explode("|", $_POST['selected_seats']);
					}else{
						$seat_id_arr = $_POST['assigned_seats'];
					}
					foreach($location_pair as $pair)
					{
						$_arr = explode("-", $pair);
						$k = 0;
						foreach($ticket_arr as $v)
						{
							$qty = $_POST['ticket_cnt_' . $v['ticket_id']];
							for($i = 1; $i <= $qty; $i ++)
							{
								$seat_data = array();
								$seat_data['booking_id'] = $id;
								$seat_data['seat_id'] = $seat_id_arr[$k];
								$seat_data['ticket_id'] = $v['ticket_id'];
						
								$seat_data['start_location_id'] = $_arr[0];
								$seat_data['end_location_id'] = $_arr[1];
								$seat_data['is_return'] = 'F';
						
								$pjBookingSeatModel->reset()->setAttributes($seat_data)->insert();
						
								$k++;
							}
						}
					}
					
					if (isset($_POST['is_return']) && isset($_POST['return_bus_id'])) 
					{
						$data['bus_id'] = $_POST['return_bus_id'];
						$data['booking_date'] = pjUtil::formatDate($_POST['return_date'], $this->option_arr['o_date_format']);
						$data['booking_datetime'] = $data['booking_date'];
						if(isset($_SESSION[$this->defaultPeriod]))
						{
							$data['booking_datetime'] = $_SESSION[$this->defaultReturnPeriod]['departure_time'];
							$data['stop_datetime'] = $_SESSION[$this->defaultReturnPeriod]['arrival_time'];
						}
						$data['uuid'] = time() + 1;
						$data['pickup_id'] = $_POST['return_id'];
						$data['return_id'] = $_POST['pickup_id'];
						$data['is_return'] = 'F';
						unset($data['return_date']);
						$data['booking_route'] = $_POST['booking_return_route'];
					
						$data['bus_departure_date'] = $data['booking_date'];
						$bus_data = pjBusModel::factory()->find($_POST['return_bus_id'])->getData();
						$depart_date_time_iso = $data['booking_date'] . ' ' . $bus_data['departure_time'];
						if($depart_date_time_iso > $data['booking_datetime'])
						{
							$data['bus_departure_date'] = date('Y-m-d', strtotime($depart_date_time_iso) - 86400);
						}
						
						$bt_arr = array();
						$pjBusLocationModel = pjBusLocationModel::factory();
						$_arr = $pjBusLocationModel->where('bus_id', $_POST['return_bus_id'])->where("location_id", $return_id)->findAll()->getData();
						if(count($_arr) > 0)
						{
							$bt_arr[] = pjUtil::formatTime($_arr[0]['departure_time'], "H:i:s", $this->option_arr['o_time_format']);
							if(!isset($_SESSION[$this->defaultPeriod]))
							{
								$data['booking_datetime'] .= ' ' . $_arr[0]['departure_time'];
							}
						}
					
						$_arr = $pjBusLocationModel->reset()->where('bus_id', $_POST['return_bus_id'])->where("location_id", $pickup_id)->findAll()->getData();
						if(count($_arr) > 0)
						{
							$bt_arr[] = pjUtil::formatTime($_arr[0]['arrival_time'], "H:i:s", $this->option_arr['o_time_format']);
						}
						$data['booking_time'] = join(" - ", $bt_arr);
					
						$data['sub_total'] = $_POST['return_sub_total'];
						$data['tax'] = $_POST['return_tax'];
						$data['total'] = $_POST['return_total'];
						$data['deposit'] = $_POST['return_deposit'];
						
						$return_booking_id = pjBookingModel::factory(array_merge($_POST, $data))->insert()->getInsertId();
						if ($return_booking_id !== false && (int) $return_booking_id > 0)
						{	
							$pjBookingModel
								->reset()
								->set('id', $id)
								->modify(array('back_id' => $return_booking_id));
						
							$pjBookingModel
								->reset()
								->set('id', $return_booking_id)
								->modify(array('back_id' => $id));
						
							$return_bus_arr = pjBusModel::factory()
								->select('t1.*, t2.seats_map')
								->join('pjBusType', "t2.id=t1.bus_type_id", 'left')
								->find($_POST['return_bus_id'])->getData();
							$return_ticket_arr = pjPriceModel::factory()
								->reset()
								->select("t1.*, t2.discount")
								->join('pjBus', 't1.bus_id = t2.id', 'left')
								->where('t1.bus_id', $_POST['return_bus_id'])
								->where('t1.from_location_id', $return_id)
								->where('t1.to_location_id', $pickup_id)
								->where('is_return = "F"')
								->findAll()->getData();
							
							foreach($return_ticket_arr as $v)
							{
								if ($_POST['return_ticket_cnt_' . $v['ticket_id']] > 0) 
								{
									$price = $v['price'] - ($v['price'] * $v['discount'] / 100);
									$ticket_data = array();
									$ticket_data['booking_id'] = $return_booking_id;
									$ticket_data['ticket_id'] = $v['ticket_id'];
									$ticket_data['qty'] = $_POST['return_ticket_cnt_' . $v['ticket_id']];
									$ticket_data['amount'] = $ticket_data['qty'] * $price;
									$ticket_data['is_return'] = 'T';
									$pjBookingTicketModel->reset()->setAttributes($ticket_data)->insert();
								}
							}
							if(!empty($return_bus_arr['seats_map']))
							{
								$seat_id_arr = explode("|", $_POST['return_selected_seats']);
							}else{
								$seat_id_arr = $_POST['assigned_return_seats'];
							}
							foreach($location_pair as $pair)
							{
								$_arr = explode("-", $pair);
								$k = 0;
								foreach($return_ticket_arr as $v)
								{
									$qty = $_POST['return_ticket_cnt_' . $v['ticket_id']];
									for($i = 1; $i <= $qty; $i ++)
									{
										$seat_data = array();
										$seat_data['booking_id'] = $return_booking_id;
										$seat_data['seat_id'] = $seat_id_arr[$k];
										$seat_data['ticket_id'] = $v['ticket_id'];
				
										$seat_data['start_location_id'] = $_arr[0];
										$seat_data['end_location_id'] = $_arr[1];
										$seat_data['is_return'] = 'T';
										
										$pjBookingSeatModel->reset()->setAttributes($seat_data)->insert();
										
										$k++;
									}
								}
							}
						}
                    }
                    
                    $send_arr = pjBookingModel::factory ()
                    ->select ( 't1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location' )
                    ->join ( 'pjBus', "t2.id=t1.bus_id", 'left outer' )
                    ->join ( 'pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='" . $this->getLocaleId () . "'", 'left outer' )
                    ->join ( 'pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='" . $this->getLocaleId () . "'", 'left outer' )
                    ->join ( 'pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='" . $this->getLocaleId () . "'", 'left outer' )
                    ->find ( $id )
                    ->getData ();

                    $send_tickets = pjBookingTicketModel::factory ()
                    ->join ( 'pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )
                    ->join ( 'pjTicket', "t3.id=t1.ticket_id", 'left' )->select ( 't1.*, t2.content as title' )
                    ->where ( 'booking_id', $send_arr ['id'] )
                    ->findAll ()
                    ->getData ();
				
                    $send_arr['tickets'] = $send_tickets;

                    $this->pjActionNotificationSend($this->option_arr,$send_arr,PJ_SALT,$send_arr['status']);
					
					$err = 'ABB03';
				}else{
					$err = 'ABB04';
				}
				
				pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminBookings&action=pjActionIndex&err=$err");
			}else{
				
				$country_arr = pjCountryModel::factory()
							->select('t1.id, t2.content AS country_title')
							->join('pjMultiLang', "t2.model='pjCountry' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
							->orderBy('`country_title` ASC')->findAll()->getData();
						
				$this->set('country_arr', $country_arr);
				
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

				$this->set('from_location_arr', $from_location_arr);
				$this->set('to_location_arr', $to_location_arr);

				if(isset($_GET['bus_id']) && (int) $_GET['bus_id'] > 0)
				{
					$bus_id = $_GET['bus_id'];
					$pickup_id = $_GET['pickup_id'];
					$return_id = $_GET['return_id'];
										
					$date = pjUtil::formatDate($_GET['date'], $this->option_arr['o_date_format']);
					$day_of_week = strtolower(date('l', strtotime($date)));
					
					$pjBusModel = pjBusModel::factory();

					$data = $pjBusModel
						->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
						->join('pjBusType', "t3.id=t1.bus_type_id", 'left outer')
						->select(" t1.*, t2.content AS route, t3.seats_map")
						->where("(t1.start_date <= '$date' AND '$date' <= t1.end_date) AND (t1.recurring LIKE '%$day_of_week%') AND t1.id NOT IN (SELECT TSD.bus_id FROM `".pjBusDateModel::factory()->getTable()."` AS TSD WHERE TSD.`date` = '$date')")
						->where("(t1.route_id IN(SELECT TRD.route_id FROM `".pjRouteDetailModel::factory()->getTable()."` AS TRD WHERE (TRD.from_location_id = ".$pickup_id." AND TRD.to_location_id = ".$return_id.")))")
						->orderBy("route asc")
						->findAll()
						->getData();
									 	
					foreach($data as $k => $v)
					{
						if(!empty($v['start_date']) && !empty($v['end_date']))
						{
							$v['from_to'] = pjUtil::formatDate($v['start_date'], "Y-m-d", $this->option_arr['o_date_format']) . ' - ' . pjUtil::formatDate($v['end_date'], "Y-m-d", $this->option_arr['o_date_format']);
						}else{
							$v['from_to'] = '';
						}
						if(!empty($v['departure_time']) && !empty($v['arrival_time']))
						{
							$v['depart_arrive'] = pjUtil::formatTime($v['departure_time'], "H:i:s", $this->option_arr['o_time_format']) . ' - ' . pjUtil::formatTime($v['arrival_time'], "H:i:s", $this->option_arr['o_time_format']);
						}else{
							$v['depart_arrive'] = '';
						}
						$data[$k] = $v;
					}
					$this->set('bus_arr', $data);
					
					$bus = $pjBusModel->reset()->find($bus_id)->getData();
					$this->set('bus', $bus);
					
					$route_id = $bus['route_id'];
					
					$location_id_arr = pjRouteCityModel::factory()->getLocationIdPair($route_id, $pickup_id, $return_id);
					
					$ticket_arr = pjPriceModel::factory()
						->reset()
						->join('pjTicket', 't1.ticket_id = t2.id', 'left')
						->join('pjMultiLang', "t3.model='pjTicket' AND t3.foreign_id=t1.ticket_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
						->join('pjBus', 't1.bus_id = t4.id', 'left')
						->select("t1.*, t2.seats_count, t3.content as ticket, t4.discount")
						->where('t1.bus_id', $bus_id)
						->where('t1.from_location_id', $pickup_id)
						->where('t1.to_location_id', $return_id)
						->where('is_return = "F"')
						->index("FORCE KEY (`ticket_id`)")
						->orderBy("ticket ASC")
						->findAll()
						->getData();
					
					$bus_type_arr = pjBusTypeModel::factory()->find($bus['bus_type_id'])->getData();
					if($bus['set_seats_count'] == 'F')
					{
						$seats_available = $bus_type_arr['seats_count'];
						$cnt_arr = pjBookingSeatModel::factory()
							->select("COUNT(DISTINCT t1.seat_id) as cnt_booked")
							->where("t1.start_location_id IN(".join(",", $location_id_arr).") 
							         AND t1.booking_id IN(SELECT TB.id 
							                              FROM `".pjBookingModel::factory()->getTable()."` AS TB 
							                              WHERE (TB.status='confirmed' 
							                                     OR (TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$this->option_arr['o_min_hour']." MINUTE)))) 
							                                     AND TB.bus_id = $bus_id AND TB.booking_date = '$date')")
							->findAll()
							->getData();
						
						$cnt_booked = 0;
						if(count($cnt_arr) > 0)
						{
							$cnt_booked = $cnt_arr[0]['cnt_booked'];
						}
						$seats_available -= $cnt_booked;
						$this->set('seats_available', $seats_available);
					}
					
					$this->set('ticket_arr', $ticket_arr);
					$this->set('bus_type_arr', $bus_type_arr);
				}
				
				$this->appendJs('chosen.jquery.js', PJ_THIRD_PARTY_PATH . 'chosen/');
				$this->appendCss('chosen.css', PJ_THIRD_PARTY_PATH . 'chosen/');
				$this->appendJs('jquery.multiselect.min.js', PJ_THIRD_PARTY_PATH . 'multiselect/');
				$this->appendCss('jquery.multiselect.css', PJ_THIRD_PARTY_PATH . 'multiselect/');
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('additional-methods.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('pjAdminBookings.js');
				$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionUpdate()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isEditor()  || $this->isBusAdmin())
		{
            $pjUserModel = pjUserModel::factory();

			if (isset($_POST['booking_update']))
			{   
                $pjBookingModel = pjBookingModel::factory();
                $pjBookingModel->where('id =',$_POST['id']);

                if ($this->isEditor() || $this->isBusAdmin()) {  
                    if ($_POST['is_from_outside_booking'] == 1 || $_POST['is_from_search_outside_booking'] == 1) {
                        $pjBookingModel->where('payment_method','cash')
                            ->where('user_id',0);
                    } else {                   
                        if ($this->isEditor()) {
                            $sub_query = 'SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().' OR id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().')';                            
                        }
                        if ($this->isBusAdmin()) {
                            $sub_query = 'SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().' OR id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))';                            
                        } 

                        $pjBookingModel->where('(user_id = '.$this->getUserId().' OR updated_by = '.$this->getUserId().' OR status_updated_by = '.$this->getUserId().' OR updated_by IN ('.$sub_query.') OR status_updated_by IN ('.$sub_query.') OR user_id IN ('.$sub_query.'))');
                    }
                }

                $arr1 = $pjBookingModel->findAll()->getData();

                $arr = (count($arr1)) ? $arr1[0] : array();

				if (!$arr)
				{
                    if ($_POST['is_from_outside_booking'] == 1) {
                        pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBookings&action=pjActionOutsideBooking&err=ABB08");
                    } else {
                        pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBookings&action=pjActionIndex&err=ABB08");                        
                    }
				}

				$user_id = $this->getUserId();
				$user_arr = pjUserModel::factory()
				->find($user_id)->getData();

				if ($user_arr && $user_arr['role_id'] == 2) {
					$booking_edit_id = $_POST['id'];
					
					$user_credit = pjCreditModel::factory()
					->where('t1.user_id', $user_id)
					->findAll()
					->getData();

					$user_current_credit = (empty($user_credit)) ? 0 : $user_credit[0]['credit'];
					if ($arr['status'] == 'confirmed') {
						//Return the credit taken while adding the booking
						$increase_credit = $arr['total'] + $user_current_credit; 
					} else {
						$increase_credit = $user_current_credit;
					}										

					$booking_total = $_POST['total'];
					$booking_status = $_POST['status'];					

					if ($arr['status'] != $booking_status && $booking_status == 'confirmed' && (empty($user_credit) || $increase_credit < $booking_total)) {
                        $err = 'CRU01';
                        $ob = ($_POST['is_from_outside_booking'] == 1 || $_POST['is_from_search_outside_booking'] == 1) ? '&ob=1' : '';
						pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminBookings&action=pjActionUpdate&id=".$booking_edit_id."&err=$err$ob");	
					} else {
						//Return the credit taken while adding the booking. This will get reduced after editing.
						$result = pjCreditModel::factory()->whereIn('user_id', $user_id)->modifyAll(array(
							'credit' => $increase_credit
						));
					}
				}

				$bus_id = $_POST['bus_id'];
				$pickup_id = $_POST['pickup_id'];
				$return_id = $_POST['return_id'];
				$booking_date = pjUtil::formatDate($_POST['booking_date'], $this->option_arr['o_date_format']);
				$data = array();
				$data['ip'] = pjUtil::getClientIp();
				$data['booking_date'] = $booking_date;
				unset($_POST['return_date']);
				$data['booking_datetime'] = $data['booking_date'];
				$data['stop_datetime'] = $data['booking_date'];
                $data['bus_departure_date'] = $data['booking_date'];
                $data['updated_by'] = $user_id;
                $data['updated_on'] = date("Y-m-d H:i:s");                

                if ($arr['status'] != $_POST['status']) {
                    $data['status_updated_by'] = $user_id;
                    $data['status_updated_on'] = date("Y-m-d H:i:s");
                    if ($arr['user_id'] == 0) {
                        $data['user_id'] = $user_id;
                    }
                }

				$bt_arr = array();
				
				$index = $this->defaultPeriod;
					
				if (isset($_SESSION[$index]))
				{
					$data['booking_datetime'] = $_SESSION[$index]['departure_time'];
					$data['stop_datetime'] = $_SESSION[$index]['arrival_time'];
				}
				
				$pjBusLocationModel = pjBusLocationModel::factory();
				$_arr = $pjBusLocationModel
					->where('bus_id', $bus_id)
					->where("location_id", $pickup_id)
					->limit(1)
					->findAll()
					->getDataIndex(0);
				if ($_arr)
				{
					$bt_arr[] = pjUtil::formatTime($_arr['departure_time'], "H:i:s", $this->option_arr['o_time_format']);
					if (!isset($_SESSION[$index]))
					{
						$data['booking_datetime'] .= ' ' . $_arr['departure_time'];
					}
				}
				
				$_arr = $pjBusLocationModel
					->reset()
					->where('bus_id', $bus_id)
					->where("location_id", $return_id)
					->limit(1)
					->findAll()
					->getDataIndex(0);
				if ($_arr)
				{
					$bt_arr[] = pjUtil::formatTime($_arr['arrival_time'], "H:i:s", $this->option_arr['o_time_format']);
					if (!isset($_SESSION[$index]))
					{
						$data['stop_datetime'] .=  ' ' . $_arr['arrival_time'];
					}
				}
				$data['booking_time'] = join(" - ", $bt_arr);
				
				$pjBookingModel->reset()->set('id', $_POST['id'])->modify(array_merge($_POST, $data));

				if ($user_arr && $user_arr['role_id'] == 2) {
					$new_credit = $increase_credit - $booking_total;

					if ($booking_status == 'confirmed') {
						$result = pjCreditModel::factory()->whereIn('user_id', $user_id)->modifyAll(array(
							'credit' => $new_credit
						));	
					}
                }
                
                if (PJ_COMBINED_API_IS_ACTIVE && $arr['is_combined_api'] == 1 && $arr['status'] != $_POST['status']) {
                    $result = $this->requestAction(array('controller' => 'pjCombinedApi', 'action' => 'pjActionSetStatus', 'params' => array('uuid'=>$arr['uuid'],'status'=>$_POST['status'])), array('return'));
                }
				
				$bus_arr = pjBusModel::factory()
					->select('t1.*, t2.seats_map')
					->join('pjBusType', "t2.id=t1.bus_type_id", 'left')
					->find($bus_id)->getData();
				$ticket_arr = pjPriceModel::factory()
					->select("t1.*")
					->where('t1.bus_id', $bus_id)
					->where('t1.from_location_id', $pickup_id)
					->where('t1.to_location_id', $return_id)
					->where('t1.is_return = "F"')
					->findAll()
					->getData();
								 
				$location_pair = array();
				$location_arr = pjRouteCityModel::factory()->getLocations($bus_arr['route_id'], $pickup_id, $return_id);
				
				for($i = 0; $i < count($location_arr); $i++ )
				{
					$j = $i + 1;
					if($j < count($location_arr))
					{
						$location_pair[] = $location_arr[$i]['city_id'] . '-' . $location_arr[$j]['city_id'];
					}
				}
				
				$pjBookingTicketModel = pjBookingTicketModel::factory();
				$pjBookingTicketModel->where('booking_id', $_POST['id'])->where('is_return = "F"')->eraseAll();
				
				foreach($ticket_arr as $v)
				{
					if (isset($_POST['ticket_cnt_' . $v['ticket_id']]) && $_POST['ticket_cnt_' . $v['ticket_id']] > 0)
					{
						$data = array();
						$data['booking_id'] = $_POST['id'];
						$data['ticket_id'] = $v['ticket_id'];
						$data['qty'] = $_POST['ticket_cnt_' . $v['ticket_id']];
						$data['amount'] = $data['qty'] * $v['price'];
						$data['is_return'] = 'F';
						$pjBookingTicketModel->reset()->setAttributes($data)->insert();
					}
				}
				if (($arr['is_return'] == 'F') && !empty($arr['back_id'])) {
					$pjBookingModel->reset()->set('id', $arr['back_id'])->modify(array('return_date' => $booking_date));
					$pjBookingTicketModel->reset()->where('booking_id', $arr['back_id'])->where('is_return = "T"')->eraseAll();
						
					$return_ticket_arr = pjPriceModel::factory()
						->select("t1.*, t2.discount")
						->join('pjBus', 't1.bus_id = t2.id', 'left')
						->where('t1.bus_id', $bus_id)
						->where('t1.from_location_id', $pickup_id)
						->where('t1.to_location_id', $return_id)
						->where('t1.is_return = "T"')
						->findAll()
						->getData();
						
					foreach($return_ticket_arr as $v)
					{
						if (isset($_POST['ticket_cnt_' . $v['ticket_id']]) && $_POST['ticket_cnt_' . $v['ticket_id']] > 0)
						{
							$price = $v['price'] - ($v['price'] * $v['discount'] / 100);
							$data = array();
							$data['booking_id'] = $arr['back_id'];
							$data['ticket_id'] = $v['ticket_id'];
							$data['qty'] = $_POST['ticket_cnt_' . $v['ticket_id']];
							$data['amount'] = $data['qty'] * $price;
							$data['is_return'] = 'T';
							$pjBookingTicketModel->reset()->setAttributes($data)->insert();
						}
					}
				}				 
				$pjBookingSeatModel = pjBookingSeatModel::factory();
				$pjBookingSeatModel->where('booking_id', $_POST['id'])->eraseAll();
				if(!empty($bus_arr['seats_map']))
				{
					$seat_id_arr = explode("|", $_POST['selected_seats']);
				}else{
					$seat_id_arr = $_POST['assigned_seats'];
				}
				$is_return = $arr['is_return'] == 'F' && $arr['back_id'] ? 'T' : 'F';
				
				foreach($location_pair as $pair)
				{
					$_arr = explode("-", $pair);
					$k = 0;
					foreach($ticket_arr as $v)
					{
						$qty = $_POST['ticket_cnt_' . $v['ticket_id']];
						for($i = 1; $i <= $qty; $i ++)
						{
							$data = array();
							$data['booking_id'] = $_POST['id'];
							$data['seat_id'] = $seat_id_arr[$k];
							$data['ticket_id'] = $v['ticket_id'];
							
							$data['start_location_id'] = $_arr[0];
							$data['end_location_id'] = $_arr[1];
							$data['is_return'] = $is_return;
							
							$pjBookingSeatModel->reset()->setAttributes($data)->insert();
							
							$k++;
						}
					}
				}
                
                if ($arr['status'] != $_POST['status']) {
                    $id = $_POST['id'];

                    $send_arr = pjBookingModel::factory ()
                    ->select ( 't1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location' )
                    ->join ( 'pjBus', "t2.id=t1.bus_id", 'left outer' )
                    ->join ( 'pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='" . $this->getLocaleId () . "'", 'left outer' )
                    ->join ( 'pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='" . $this->getLocaleId () . "'", 'left outer' )
                    ->join ( 'pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='" . $this->getLocaleId () . "'", 'left outer' )
                    ->find ($id)
                    ->getData ();

                    $send_tickets = pjBookingTicketModel::factory ()
                    ->join ( 'pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )
                    ->join ( 'pjTicket', "t3.id=t1.ticket_id", 'left' )->select ( 't1.*, t2.content as title' )
                    ->where ( 'booking_id', $send_arr ['id'] )
                    ->findAll ()
                    ->getData ();
				
                    $send_arr['tickets'] = $send_tickets;

                    $this->pjActionNotificationSend($this->option_arr,$send_arr,PJ_SALT,$_POST['status']);
                }

                $err = 'ABB01';
                if ($_POST['is_from_outside_booking'] == 1) {
                    pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBookings&action=pjActionOutsideBooking&err=$err");
                } else {
                    pjUtil::redirect(PJ_INSTALL_URL. "index.php?controller=pjAdminBookings&action=pjActionIndex&err=$err");
                }
			}else{				
				unset($_SESSION[$this->defaultPeriod]);
				unset($_SESSION[$this->defaultReturnPeriod]);
                
                $pjBookingModel = pjBookingModel::factory()
                    ->select('t1.*,t2.name AS updated_name,t2.email AS updated_email,t3.name AS status_updated_name,t3.email AS status_updated_email')
                    ->join('pjUser','t2.id = t1.updated_by','left')
                    ->join('pjUser','t3.id = t1.status_updated_by','left')
                    ->where('t1.id =',$_GET['id']);                    

                if ($this->isEditor() || $this->isBusAdmin()) {  
                    //outside booking
                    if ((isset($_GET['ob']) && $_GET['ob'] == 1) || (isset($_GET['sob']) && $_GET['sob'] == 1)) {
                        $pjBookingModel->where('payment_method','cash')
                            ->where('user_id',0);
                    } else {  
                        if ($this->isEditor()) {
                            $sub_query = 'SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().' OR id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().')';                
                        }
                        if ($this->isBusAdmin()) {
                            $sub_query = 'SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().' OR id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))';                            
                        }                        

                        $pjBookingModel->where('(user_id = '.$this->getUserId().' OR updated_by = '.$this->getUserId().' OR status_updated_by = '.$this->getUserId().' OR updated_by IN ('.$sub_query.') OR status_updated_by IN ('.$sub_query.') OR user_id IN ('.$sub_query.'))');
                    }
                }

                $arr1 = $pjBookingModel->findAll()->getData();

                $arr = (count($arr1)) ? $arr1[0] : array();

				if(count($arr) <= 0)
				{
					if (isset($_GET['ob']) && $_GET['ob'] == 1) {
                        pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBookings&action=pjActionOutsideBooking&err=ABB08");                        
                    } else {
                        pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBookings&action=pjActionIndex&err=ABB08");    
                    }
				}
				
				$country_arr = pjCountryModel::factory()
							->select('t1.id, t2.content AS country_title')
							->join('pjMultiLang', "t2.model='pjCountry' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
							->orderBy('`country_title` ASC')->findAll()->getData();
						
				$this->set('country_arr', $country_arr);
				
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
					
				$this->set('from_location_arr', $from_location_arr);
				$this->set('to_location_arr', $to_location_arr);
				
				$date = $arr['booking_date'];
				$day_of_week = strtolower(date('l', strtotime($arr['booking_date'])));

				$data = pjBusModel::factory()
					->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjBusType', "t3.id=t1.bus_type_id", 'left outer')
					->select(" t1.*, t2.content AS route, t3.seats_map")
					->where("(t1.start_date <= '$date' AND '$date' <= t1.end_date) AND (t1.recurring LIKE '%$day_of_week%') AND t1.id NOT IN (SELECT TSD.bus_id FROM `".pjBusDateModel::factory()->getTable()."` AS TSD WHERE TSD.`date` = '$date')")
					->where("(t1.route_id IN(SELECT TRD.route_id FROM `".pjRouteDetailModel::factory()->getTable()."` AS TRD WHERE (TRD.from_location_id = ". $arr['pickup_id']." AND TRD.to_location_id = ".$arr['return_id'].")))")
					->orderBy("route asc")
					->findAll()
					->getData();
						
				foreach($data as $k => $v)
				{
					if(!empty($v['departure_time']) && !empty($v['arrival_time']))
					{
						$v['depart_arrive'] = pjUtil::formatTime($v['departure_time'], "H:i:s", $this->option_arr['o_time_format']) . ' - ' . pjUtil::formatTime($v['arrival_time'], "H:i:s", $this->option_arr['o_time_format']);
					}else{
						$v['depart_arrive'] = '';
					}
					$data[$k] = $v;
				}
				$this->set('bus_arr', $data);
				
				$bus_arr = pjBusModel::factory()->find($arr['bus_id'])->getData();
				$this->set('bus', $bus_arr);
								
				$bus_id = $arr['bus_id'];
				$pickup_id = $arr['pickup_id'];
				$return_id = $arr['return_id'];
				$booking_date = $arr['booking_date'];

				$location_id_arr = pjRouteCityModel::factory()->getLocationIdPair($bus_arr['route_id'], $pickup_id, $return_id);
				if (count($location_id_arr) == 0) 
				{
					$location_id_arr[] = 0;
				}
				
				$pjPriceModel = pjPriceModel::factory();
				$pjBookingSeatModel = pjBookingSeatModel::factory();
				
				$ticket_arr = $pjPriceModel
					->reset()
					->join('pjTicket', 't1.ticket_id = t2.id', 'left')
					->join('pjMultiLang', "t3.model='pjTicket' AND t3.foreign_id=t1.ticket_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjBus', 't1.bus_id = t4.id', 'left')
					->select("t1.*, t2.seats_count, t3.content as ticket, t4.discount")
					->where('t1.bus_id', $bus_id)
					->where('t1.from_location_id', $pickup_id)
					->where('t1.to_location_id', $return_id)
					->where('is_return = "F"')
					->orderBy("ticket ASC")
					->findAll()->getData();
				
				$bus_type_arr = pjBusTypeModel::factory()->find($bus_arr['bus_type_id'])->getData();
				if($bus_arr['set_seats_count'] == 'F')
				{
					$seats_available = $bus_type_arr['seats_count'];
					$cnt_arr = $pjBookingSeatModel
						->reset()
						->select("COUNT(DISTINCT t1.seat_id) as cnt_booked")
						->where("t1.start_location_id IN(".join(",", $location_id_arr).") 
						         AND t1.booking_id <> ".$_GET['id']." 
						         AND t1.booking_id IN(SELECT TB.id 
						                              FROM `".pjBookingModel::factory()->getTable()."` AS TB 
						                              WHERE (TB.status='confirmed' OR (TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$this->option_arr['o_min_hour']." MINUTE)))) 
						                                    AND TB.bus_id = $bus_id 
						                                    AND ( ('".$arr['booking_datetime']."' BETWEEN TB.booking_datetime AND TB.stop_datetime) OR ('".$arr['stop_datetime']."' BETWEEN TB.booking_datetime AND TB.stop_datetime) OR (TB.booking_datetime BETWEEN '".$arr['booking_datetime']."' AND '".$arr['stop_datetime']."')OR (TB.stop_datetime BETWEEN '".$arr['booking_datetime']."' AND '".$arr['stop_datetime']."') ) )")
						->findAll()
						->getData();
					
					$cnt_booked = 0;
					if(count($cnt_arr) > 0)
					{
						$cnt_booked = $cnt_arr[0]['cnt_booked'];
					}
					$seats_available -= $cnt_booked;
					$this->set('seats_available', $seats_available);
				}
				
				$this->set('ticket_arr', $ticket_arr);
				$this->set('bus_type_arr', $bus_type_arr);

				$this->set('seat_arr', pjSeatModel::factory()->where('bus_type_id', $bus_arr['bus_type_id'])->findAll()->getData());

				$ticket_pair_arr = $pjBookingSeatModel->reset()->select("t1.ticket_id, COUNT(DISTINCT seat_id) as qty")->where('booking_id', $_GET['id'])->groupBy("t1.ticket_id")->findAll()->getDataPair("ticket_id", 'qty');
				$this->set('ticket_pair_arr', $ticket_pair_arr);
				
				$seat_pair_arr = $pjBookingSeatModel->reset()->where('booking_id', $_GET['id'])->findAll()->getDataPair("seat_id", 'seat_id');
				$this->set('seat_pair_arr', $seat_pair_arr);
				
				$selected_seats = array();
				if(!empty($seat_pair_arr))
				{
					$selected_seats = pjSeatModel::factory()->whereIn('id', $seat_pair_arr)->findAll()->getDataPair("id", 'name');
				}
				$booked_seat_arr = $pjBookingSeatModel
					->reset()
					->select("DISTINCT seat_id")
					->where("t1.booking_id IN(SELECT TB.id 
					                          FROM `".pjBookingModel::factory()->getTable()."` AS TB 
					                          WHERE (TB.status='confirmed' OR (TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$this->option_arr['o_min_hour']." MINUTE)))) 
					                                 AND TB.id <> '".$_GET['id']."' 
					                                 AND TB.bus_id = $bus_id 
					                                 AND TB.booking_date = '$booking_date') 
					                                 AND start_location_id IN(".join(",", $location_id_arr).")")
					->findAll()
					->getDataPair("seat_id", "seat_id");
				
				$this->set('selected_seats', $selected_seats);
				$this->set('arr', $arr);
                $this->set('booked_seat_arr', $booked_seat_arr);

                $is_from_outside_booking = (isset($_GET['ob']) && $_GET['ob'] == 1) ? 1 : 0;
                $this->set('is_from_outside_booking',$is_from_outside_booking);

                $is_from_search_outside_booking = (isset($_GET['sob']) && $_GET['sob'] == 1) ? 1 : 0;
                $this->set('is_from_search_outside_booking',$is_from_search_outside_booking);
				
				$this->appendJs('chosen.jquery.js', PJ_THIRD_PARTY_PATH . 'chosen/');
				$this->appendCss('chosen.css', PJ_THIRD_PARTY_PATH . 'chosen/');
				$this->appendJs('jquery.multiselect.min.js', PJ_THIRD_PARTY_PATH . 'multiselect/');
				$this->appendCss('jquery.multiselect.css', PJ_THIRD_PARTY_PATH . 'multiselect/');
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('additional-methods.js', PJ_THIRD_PARTY_PATH . 'validate/');
                $this->appendJs('pjAdminBookings.js');
				$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionGetReturnBuses()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$bus_arr = array();
				
			if($_POST['pickup_id'] != $_POST['return_id'])
			{
				$pickup_id = $_POST['return_id'];
				$return_id = $_POST['pickup_id'];
				$date = pjUtil::formatDate($_POST['return_date'], $this->option_arr['o_date_format']);
				$day_of_week = strtolower(date('l', strtotime($date)));
	
				$bus_arr = pjBusModel::factory()
					->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjBusType', "t3.id=t1.bus_type_id", 'left outer')
					->select(" t1.*, t2.content AS route, t3.seats_map")
					->where("(t1.start_date <= '$date' AND '$date' <= t1.end_date) AND (t1.recurring LIKE '%$day_of_week%') AND t1.id NOT IN (SELECT TSD.bus_id FROM `".pjBusDateModel::factory()->getTable()."` AS TSD WHERE TSD.`date` = '$date')")
					->where("(t1.route_id IN(SELECT TRD.route_id FROM `".pjRouteDetailModel::factory()->getTable()."` AS TRD WHERE (TRD.from_location_id = $pickup_id AND TRD.to_location_id = $return_id)))")
					->orderBy("route asc")
					->findAll()
					->getData();
	
				foreach($bus_arr as $k => $v)
				{
					if(!empty($v['departure_time']) && !empty($v['arrival_time']))
					{
						$v['depart_arrive'] = pjUtil::formatTime($v['departure_time'], "H:i:s", $this->option_arr['o_time_format']) . ' - ' . pjUtil::formatTime($v['arrival_time'], "H:i:s", $this->option_arr['o_time_format']);
					}else{
						$v['depart_arrive'] = '';
					}
					$bus_arr[$k] = $v;
				}
			}
			$this->set('bus_arr', $bus_arr);
		}
	}
	
	public function pjActionGetBuses()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$bus_arr = array();
			
			if($_POST['pickup_id'] != $_POST['return_id'])
			{
				$pickup_id = $_POST['pickup_id'];
				$return_id = $_POST['return_id'];
				$date = pjUtil::formatDate($_POST['booking_date'], $this->option_arr['o_date_format']);
				$day_of_week = strtolower(date('l', strtotime($date)));
				
				$bus_arr = pjBusModel::factory()
					->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjBusType', "t3.id=t1.bus_type_id", 'left outer')
					->select(" t1.*, t2.content AS route, t3.seats_map")
					->where("(t1.start_date <= '$date' AND '$date' <= t1.end_date) AND (t1.recurring LIKE '%$day_of_week%') AND t1.id NOT IN (SELECT TSD.bus_id FROM `".pjBusDateModel::factory()->getTable()."` AS TSD WHERE TSD.`date` = '$date')")
					->where("(t1.route_id IN(SELECT TRD.route_id FROM `".pjRouteDetailModel::factory()->getTable()."` AS TRD WHERE (TRD.from_location_id = $pickup_id AND TRD.to_location_id = $return_id)))")
					->orderBy("route asc")
					->findAll()
					->getData();
				
				foreach($bus_arr as $k => $v)
				{
					if(!empty($v['departure_time']) && !empty($v['arrival_time']))
					{
						$v['depart_arrive'] = pjUtil::formatTime($v['departure_time'], "H:i:s", $this->option_arr['o_time_format']) . ' - ' . pjUtil::formatTime($v['arrival_time'], "H:i:s", $this->option_arr['o_time_format']);
					}else{
						$v['depart_arrive'] = '';
					}
					$bus_arr[$k] = $v;
				}
			}
			$this->set('bus_arr', $bus_arr);
		}
	}
	
	public function pjActionChangeDate()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
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

			$this->set('from_location_arr', $from_location_arr);
			$this->set('to_location_arr', $to_location_arr);
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
			$this->set('location_arr', $location_arr);
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
			$this->set('location_arr', $location_arr);
		}
	}
	
	public function pjActionGetTickets()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$booking_date = pjUtil::formatDate($_POST['booking_date'], $this->option_arr['o_date_format']);
			$bus_id = $_POST['bus_id'];
			$pickup_id = $_POST['pickup_id'];
			$return_id = $_POST['return_id'];
			$departure_time = '';
			$arrival_time = '';
			$departure_dt = '';
			$arrival_dt = '';
			$pjBusLocationModel = pjBusLocationModel::factory();
			$pickup_arr = $pjBusLocationModel->where('bus_id', $bus_id)->where("location_id", $pickup_id)->limit(1)->findAll()->getData();
			$return_arr = $pjBusLocationModel->reset()->where('bus_id', $bus_id)->where("location_id", $return_id)->limit(1)->findAll()->getData();

			if(!empty($pickup_arr))
			{
				$departure_time = __('lblDepartureTime', true, false) . ': ' . pjUtil::formatTime($pickup_arr[0]['departure_time'], 'H:i:s', $this->option_arr['o_time_format']);
				$departure_dt = $booking_date . ' ' . $pickup_arr[0]['departure_time'];
			}
			if(!empty($return_arr))
			{
				$arrival_time = __('lblArrivalTime', true, false) . ': ' . pjUtil::formatTime($return_arr[0]['arrival_time'], 'H:i:s', $this->option_arr['o_time_format']);
				$arrival_dt = $booking_date . ' ' . $return_arr[0]['arrival_time'];
			}

			$arr = pjBusModel::factory()->find($bus_id)->getData();
			$locations = pjRouteCityModel::factory()
				->join('pjBusLocation', "(t2.bus_id='" . $bus_id . "' AND t2.location_id=t1.city_id", 'inner' )
				->select("t1.*, t2.departure_time, t2.arrival_time" )
				->where('t1.route_id', $arr['route_id'] )
				->orderBy("`order` ASC" )->findAll ()->getData ();
			
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
				$arrival_dt = date('Y-m-d H:i:s', strtotime ($departure_dt) + $seconds );
			}
				
			$location_id_arr = pjRouteCityModel::factory()->getLocationIdPair($arr['route_id'], $pickup_id, $return_id);
			if(!empty($location_id_arr))
			{
				$ticket_arr = pjPriceModel::factory()
					->reset()
					->join('pjTicket', 't1.ticket_id = t2.id', 'left')
					->join('pjMultiLang', "t3.model='pjTicket' AND t3.foreign_id=t1.ticket_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjBus', 't1.bus_id = t4.id', 'left')
					->select("t1.*, t2.seats_count, t3.content as ticket, t4.discount")
					->where('t1.bus_id', $bus_id)
					->where('t1.from_location_id', $pickup_id)
					->where('t1.to_location_id', $return_id)
					->where('is_return = "F"')
					->index("FORCE KEY (`ticket_id`)")
					->orderBy("ticket ASC")
					->findAll()
					->getData();
				
				if($arr['set_seats_count'] == 'F')
				{
					$and_where = '';
					if ($departure_dt != null && $arrival_dt != null) {
						$and_where .= " AND ((TB.booking_datetime BETWEEN '$departure_dt' AND '$arrival_dt') OR (TB.stop_datetime BETWEEN '$departure_dt' AND '$arrival_dt' ) OR ('$departure_dt' BETWEEN TB.booking_datetime AND TB.stop_datetime ) OR ('$arrival_dt' BETWEEN TB.booking_datetime AND TB.stop_datetime ))";
					}
					$bus_type_arr = pjBusTypeModel::factory()->find($arr['bus_type_id'])->getData();
					$seats_available = $bus_type_arr['seats_count'];
					$cnt_arr = pjBookingSeatModel::factory()
						->select("COUNT(DISTINCT t1.seat_id) as cnt_booked")
						->where("t1.start_location_id IN(".join(",", $location_id_arr).") AND t1.booking_id IN(SELECT TB.id FROM `".pjBookingModel::factory()->getTable()."` AS TB WHERE (TB.status='confirmed' OR (TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$this->option_arr['o_min_hour']." MINUTE)))) AND TB.bus_id = $bus_id $and_where)")
						->findAll()
						->getData();
					
					$cnt_booked = 0;
					if(count($cnt_arr) > 0)
					{
						$cnt_booked = $cnt_arr[0]['cnt_booked'];
					}
					$seats_available -= $cnt_booked;
					$this->set('seats_available', $seats_available);
				}
							 
				$this->set('ticket_arr', $ticket_arr);
				$this->set('arr', $arr);
				$this->set('departure_time', $departure_time);
				$this->set('arrival_time', $arrival_time);
				$booking_arr = array();
				if (isset($_POST['id']) && (int)$_POST['id'] > 0) {
					$booking_arr = pjBookingModel::factory()->find($_POST['id'])->getData();
				}
				$this->set('booking_arr', $booking_arr);
			}
		}
	}
	
	public function pjActionGetReturnTickets()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			if(isset($_POST['return_bus_id']) && (int) $_POST['return_bus_id'] > 0)
			{
				$booking_date = pjUtil::formatDate($_POST['return_date'], $this->option_arr['o_date_format']);
				$bus_id = $_POST['return_bus_id'];
				$pickup_id = $_POST['return_id'];
				$return_id = $_POST['pickup_id'];
				$departure_time = '';
				$arrival_time = '';
				$departure_dt = '';
				$arrival_dt = '';
				$pjBusLocationModel = pjBusLocationModel::factory();
				$pickup_arr = $pjBusLocationModel->where('bus_id', $bus_id)->where("location_id", $pickup_id)->limit(1)->findAll()->getData();
				$return_arr = $pjBusLocationModel->reset()->where('bus_id', $bus_id)->where("location_id", $return_id)->limit(1)->findAll()->getData();
		
				if(!empty($pickup_arr))
				{
					$departure_time = __('lblDepartureTime', true, false) . ': ' . pjUtil::formatTime($pickup_arr[0]['departure_time'], 'H:i:s', $this->option_arr['o_time_format']);
					$departure_dt = $booking_date . ' ' . $pickup_arr[0]['departure_time'];
				}
				if(!empty($return_arr))
				{
					$arrival_time = __('lblArrivalTime', true, false) . ': ' . pjUtil::formatTime($return_arr[0]['arrival_time'], 'H:i:s', $this->option_arr['o_time_format']);
					$arrival_dt = $booking_date . ' ' . $return_arr[0]['arrival_time'];
				}
				
				$arr = pjBusModel::factory()->find($bus_id)->getData();
				$locations = pjRouteCityModel::factory()
					->join('pjBusLocation', "(t2.bus_id='" . $bus_id . "' AND t2.location_id=t1.city_id", 'inner' )
					->select("t1.*, t2.departure_time, t2.arrival_time" )
					->where('t1.route_id', $arr['route_id'] )
					->orderBy("`order` ASC" )->findAll ()->getData ();
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
					$arrival_dt = date('Y-m-d H:i:s', strtotime ($departure_dt) + $seconds );
				}
				
				$location_id_arr = pjRouteCityModel::factory()->getLocationIdPair($arr['route_id'], $pickup_id, $return_id);
					
				if(!empty($location_id_arr))
				{
					$pjPriceModel = pjPriceModel::factory();
		
					$ticket_arr = $pjPriceModel
						->reset()
						->join('pjTicket', 't1.ticket_id = t2.id', 'left')
						->join('pjMultiLang', "t3.model='pjTicket' AND t3.foreign_id=t1.ticket_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
						->join('pjBus', 't1.bus_id = t4.id', 'left')
						->select("t1.*, t2.seats_count, t3.content as ticket, t4.discount")
						->where('t1.bus_id', $bus_id)
						->where('t1.from_location_id', $pickup_id)
						->where('t1.to_location_id', $return_id)
						->where('is_return = "F"')
						->index("FORCE KEY (`ticket_id`)")
						->orderBy("ticket ASC")
						->findAll()
						->getData();
					
					if($arr['set_seats_count'] == 'F')
					{
						$and_where = '';
						if ($departure_dt != null && $arrival_dt != null) {
							$and_where .= " AND ((TB.booking_datetime BETWEEN '$departure_dt' AND '$arrival_dt') OR (TB.stop_datetime BETWEEN '$departure_dt' AND '$arrival_dt' ) OR ('$departure_dt' BETWEEN TB.booking_datetime AND TB.stop_datetime ) OR ('$arrival_dt' BETWEEN TB.booking_datetime AND TB.stop_datetime ))";
						}
						$bus_type_arr = pjBusTypeModel::factory()->find($arr['bus_type_id'])->getData();
						$seats_available = $bus_type_arr['seats_count'];
						$cnt_arr = pjBookingSeatModel::factory()
							->select("COUNT(DISTINCT t1.seat_id) as cnt_booked")
							->where("t1.start_location_id IN(".join(",", $location_id_arr).") AND t1.booking_id IN(SELECT TB.id FROM `".pjBookingModel::factory()->getTable()."` AS TB WHERE (TB.status='confirmed' OR (TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$this->option_arr['o_min_hour']." MINUTE)))) AND TB.bus_id = $bus_id $and_where)")
							->findAll()
							->getData();
							
						$cnt_booked = 0;
						if(count($cnt_arr) > 0)
						{
							$cnt_booked = $cnt_arr[0]['cnt_booked'];
						}
						$seats_available -= $cnt_booked;
						$this->set('seats_available', $seats_available);
					}
					
					$this->set('ticket_arr', $ticket_arr);
					$this->set('arr', $arr);
					$this->set('departure_time', $departure_time);
					$this->set('arrival_time', $arrival_time);
				}
			}
		}
	}
	
	public function pjActionGetSeats()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$bus_id = $_POST['bus_id'];
			$pickup_id = $_POST['pickup_id'];
			$return_id = $_POST['return_id'];

			$booking_date = pjUtil::formatDate($_POST['booking_date'], $this->option_arr['o_date_format']);
			
			$arr = pjBusModel::factory()->find($bus_id)->getData();			
			
			$pjRouteCityModel = pjRouteCityModel::factory();
			$location_id_arr = $pjRouteCityModel->getLocationIdPair($arr['route_id'], $pickup_id, $return_id);
			
			$bus_type_arr = pjBusTypeModel::factory()->find($arr['bus_type_id'])->getData();
			
			$pickup_arr = pjBusLocationModel::factory()->where('bus_id', $bus_id)->where("location_id", $pickup_id)->limit(1)->findAll()->getData();
			$locations = $pjRouteCityModel
				->reset()
				->join('pjBusLocation', "(t2.bus_id='".$bus_id."' AND t2.location_id=t1.city_id", 'inner')
				->select("t1.*, t2.departure_time, t2.arrival_time")
				->where('t1.route_id', $arr['route_id'])
				->orderBy("`order` ASC")
				->findAll()
				->getData();
			
			$seconds = 0;
			$start_count = false;
			foreach($locations as $key => $lo)
			{
				$next_location = $locations[$key + 1];
			
				if($lo['city_id'] == $pickup_id)
				{
					$start_count = true;
				}
				if(isset($next_location) && $start_count == true)
				{
					$seconds += pjUtil::calSeconds($lo['departure_time'], $next_location['arrival_time']);
					if($key + 1 < count($locations) && $key > 0 && $lo['city_id'] != $pickup_id)
					{
						$seconds += pjUtil::calSeconds($lo['arrival_time'], $lo['departure_time']);
					}
				}
				if($next_location['city_id'] == $return_id)
				{
					break;
				}
			}
			$departure_time = null;
			$arrival_time = null;
			if(!empty($pickup_arr))
			{
				$departure_time = $booking_date . ' ' . $pickup_arr[0]['departure_time'];
				$arrival_time = date('Y-m-d H:i:s', strtotime($departure_time) + $seconds);
				$_SESSION[$this->defaultPeriod]['departure_time'] = $departure_time;
				$_SESSION[$this->defaultPeriod]['arrival_time'] = $arrival_time;
			}
			$and_where = '';
			if($departure_time != null && $arrival_time != null)
			{
				$and_where .= " AND ((TB.booking_datetime BETWEEN '$departure_time' AND '$arrival_time') OR (TB.stop_datetime BETWEEN '$departure_time' AND '$arrival_time' ) OR ('$departure_time' BETWEEN TB.booking_datetime AND TB.stop_datetime ) OR ('$arrival_time' BETWEEN TB.booking_datetime AND TB.stop_datetime ))";
			}
			if(isset($_POST['booking_create']))
			{
				$booked_seat_arr = pjBookingSeatModel::factory()
					->select("DISTINCT seat_id")
					->where("t1.booking_id IN(SELECT TB.id 
					                          FROM `".pjBookingModel::factory()->getTable()."` AS TB 
					                          WHERE (TB.status='confirmed' OR ((TB.payment_method != 'cash' AND TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL " . $this->option_arr ['o_min_hour'] . " MINUTE))) OR (TB.payment_method = 'cash' AND TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL " . $this->option_arr ['o_cash_min_hour'] . " HOUR))))) 
					                                AND TB.bus_id = $bus_id $and_where) 
					                                AND start_location_id IN(".join(",", $location_id_arr).")")
					->findAll()
					->getDataPair("seat_id", "seat_id");
			}else{
				$booking_id = $_POST['id'];
				$seat_pair_arr = array();
				$booking_arr = pjBookingModel::factory()->find($booking_id)->getData();
				$pjBookingSeatModel = pjBookingSeatModel::factory();
				$booked_seat_arr = pjBookingSeatModel::factory()
					->select("DISTINCT seat_id")
					->where("t1.booking_id IN(SELECT TB.id 
					                          FROM `".pjBookingModel::factory()->getTable()."` AS TB 
					                          WHERE (TB.status='confirmed' OR ((TB.payment_method != 'cash' AND TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL " . $this->option_arr ['o_min_hour'] . " MINUTE))) OR (TB.payment_method = 'cash' AND TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL " . $this->option_arr ['o_cash_min_hour'] . " HOUR))))) 
					                                AND TB.id <> $booking_id 
					                                AND TB.bus_id = $bus_id $and_where) 
					                                AND start_location_id IN(".join(",", $location_id_arr).")")
					->findAll()
					->getDataPair("seat_id", "seat_id");
				if($booking_arr['pickup_id'] == $pickup_id && $booking_arr['return_id'] == $return_id)
				{
					$seat_pair_arr = $pjBookingSeatModel->reset()->where('booking_id', $booking_id)->findAll()->getDataPair("seat_id", 'seat_id');
				}
				
				$this->set('seat_pair_arr', $seat_pair_arr);
			}
			
			$this->set('bus_type_arr', $bus_type_arr);
			$this->set('booked_seat_arr', $booked_seat_arr);
			$this->set('seat_arr', pjSeatModel::factory()->where('bus_type_id', $arr['bus_type_id'])->findAll()->getData());
		}
	}

	public function pjActionGetReturnSeats()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			if (isset($_POST['return_bus_id']) && (int)$_POST['return_bus_id'] > 0) 
			{
				$bus_id = $_POST['return_bus_id'];
				$pickup_id = $_POST['return_id'];
				$return_id = $_POST['pickup_id'];

				$booking_date = pjUtil::formatDate($_POST['return_date'], $this->option_arr['o_date_format']);
				
				$arr = pjBusModel::factory()->find($bus_id)->getData();			
				
				$pjRouteCityModel = pjRouteCityModel::factory();
				$location_id_arr = $pjRouteCityModel->getLocationIdPair($arr['route_id'], $pickup_id, $return_id);
								
				$bus_type_arr = pjBusTypeModel::factory()->find($arr['bus_type_id'])->getData();
				
				$pickup_arr = pjBusLocationModel::factory()->where('bus_id', $bus_id)->where("location_id", $pickup_id)->limit(1)->findAll()->getData();
				$locations = $pjRouteCityModel
					->reset()
					->join('pjBusLocation', "(t2.bus_id='".$bus_id."' AND t2.location_id=t1.city_id", 'inner')
					->select("t1.*, t2.departure_time, t2.arrival_time")
					->where('t1.route_id', $arr['route_id'])
					->orderBy("`order` ASC")
					->findAll()
					->getData();
				
				$seconds = 0;
				$start_count = false;
				foreach($locations as $key => $lo)
				{
					$next_location = $locations[$key + 1];
						
					if($lo['city_id'] == $pickup_id)
					{
						$start_count = true;
					}
					if(isset($next_location) && $start_count == true)
					{
						$seconds += pjUtil::calSeconds($lo['departure_time'], $next_location['arrival_time']);
						if($key + 1 < count($locations) && $key > 0 && $lo['city_id'] != $pickup_id)
						{
							$seconds += pjUtil::calSeconds($lo['arrival_time'], $lo['departure_time']);
						}
					}
					if($next_location['city_id'] == $return_id)
					{
						break;
					}
				}
				$departure_time = null;
				$arrival_time = null;
				if(!empty($pickup_arr))
				{
					$departure_time = $booking_date . ' ' . $pickup_arr[0]['departure_time'];
					$arrival_time = date('Y-m-d H:i:s', strtotime($departure_time) + $seconds);
					$_SESSION[$this->defaultReturnPeriod]['departure_time'] = $departure_time;
					$_SESSION[$this->defaultReturnPeriod]['arrival_time'] = $arrival_time;
				}
				$and_where = '';
				if($departure_time != null && $arrival_time != null)
				{
					$and_where .= " AND ((TB.booking_datetime BETWEEN '$departure_time' AND '$arrival_time') OR (TB.stop_datetime BETWEEN '$departure_time' AND '$arrival_time' ) OR ('$departure_time' BETWEEN TB.booking_datetime AND TB.stop_datetime ) OR ('$arrival_time' BETWEEN TB.booking_datetime AND TB.stop_datetime ))";
				}
				
				if(isset($_POST['booking_create']))
				{
					$booked_seat_arr = pjBookingSeatModel::factory()
						->select("DISTINCT seat_id")
						->where("t1.booking_id IN(SELECT TB.id 
						                          FROM `".pjBookingModel::factory()->getTable()."` AS TB 
						                          WHERE (TB.status='confirmed' OR (TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$this->option_arr['o_min_hour']." MINUTE)))) 
						                                AND TB.bus_id = $bus_id $and_where) 
						                                AND start_location_id IN(".join(",", $location_id_arr).")")
						->findAll()
						->getDataPair("seat_id", "seat_id");
				}else{
					$booking_id = $_POST['id'];
					$seat_pair_arr = array();
					$booking_arr = pjBookingModel::factory()->find($booking_id)->getData();
					$pjBookingSeatModel = pjBookingSeatModel::factory();
					$booked_seat_arr = pjBookingSeatModel::factory()
						->select("DISTINCT seat_id")
						->where("t1.booking_id IN(SELECT TB.id 
						                          FROM `".pjBookingModel::factory()->getTable()."` AS TB 
						                          WHERE (TB.status='confirmed' OR (TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$this->option_arr['o_min_hour']." MINUTE)))) 
						                                AND TB.id <> $booking_id 
						                                AND TB.bus_id = $bus_id $and_where) 
						                                AND start_location_id IN(".join(",", $location_id_arr).")")
						->findAll()
						->getDataPair("seat_id", "seat_id");
					if($booking_arr['pickup_id'] == $pickup_id && $booking_arr['return_id'] == $return_id)
					{
						$seat_pair_arr = $pjBookingSeatModel->reset()->where('booking_id', $booking_id)->findAll()->getDataPair("seat_id", 'seat_id');
					}
					
					$this->set('seat_pair_arr', $seat_pair_arr);
				}
				
				$this->set('bus_type_arr', $bus_type_arr);
				$this->set('booked_seat_arr', $booked_seat_arr);
				$this->set('seat_arr', pjSeatModel::factory()->where('bus_type_id', $arr['bus_type_id'])->findAll()->getData());
			}
		}
	}
	
	public function pjActionPrintTickets()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() || $this->isEditor())
		{
            require_once('app/lib/qr_code/qrlib.php');
            $print_ticket_id = $_GET['id'];
            $print_ticket_hash = $_GET['hash'];
            $qr_code_png_folder = PJ_INSTALL_PATH.'app/lib/qr_code/temp/';
            $qr_code_png_web_folder = PJ_INSTALL_URL.'app/lib/qr_code/temp/';
            $qr_code_filename = $qr_code_png_folder.'ticket_'.$print_ticket_id.'_'.time().'.png';
            $qr_error_correction_level = 'L';
            $qr_code_matrix_point_size = min(max((int)6, 1), 10);            

			$this->setLayout('pjActionPrint');
			
			$pjBookingModel = pjBookingModel::factory();
							
			$arr = $pjBookingModel->find($_GET['id'])->getData();
			if (empty($arr))
			{
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBookings&action=pjActionIndex&err=ABB08");
			}
			$hash = sha1($arr['id'].$arr['created'].PJ_SALT);
			if($hash != $_GET['hash'])
			{
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBookings&action=pjActionIndex&err=ABB08");
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

                $qr_code_data = PJ_INSTALL_URL.'index.php?controller=pjFrontTicket&action=pjActionViewTicket&id='.$print_ticket_id.'&hash='.$print_ticket_hash;            
                QRcode::png($qr_code_data, $qr_code_filename, $qr_error_correction_level, $qr_code_matrix_point_size, 2);  
               
                $data['search'][] = '{QRCode}';
                $data['replace'][] = '<img src="'.$qr_code_png_web_folder.basename($qr_code_filename).'" />';

                $template_arr = str_replace($data['search'], $data['replace'], $template);
                
				$this->set('template_arr', $template_arr);
			}elseif ($arr['status'] == 'pending'){
				$this->set('pending_booking', true);
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionResend()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() || $this->isEditor())
		{
			if (isset($_POST['reminder']))
			{
				$pjEmail = new pjEmail();
				$pjEmail->setContentType('text/html');
				if ($this->option_arr['o_send_email'] == 'smtp')
				{
					$pjEmail
						->setTransport('smtp')
						->setSmtpHost($this->option_arr['o_smtp_host'])
						->setSmtpPort($this->option_arr['o_smtp_port'])
						->setSmtpUser($this->option_arr['o_smtp_user'])
						->setSmtpPass($this->option_arr['o_smtp_pass'])
						->setSender($this->option_arr['o_smtp_user'])
					;
				}
				$from_email = $this->getAdminEmail();
				if(!empty($this->option_arr['o_sender_email']))
				{
					$from_email = $this->option_arr['o_sender_email'];
				}
				$pjEmail
					->setTo($_POST['to'])
					->setFrom($from_email)
					->setSubject($_POST['subject']);
				if ($pjEmail->send(pjUtil::textToHtml($_POST['message'])))
				{
					$err = 'AB09';
				} else {
					$err = 'AB10';
				}
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBookings&action=pjActionIndex&err=$err");
			} else {
				
				$arr = pjBookingModel::factory()
					->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
					->join('pjBus', "t2.id=t1.bus_id", 'left outer')
					->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
					->find($_GET['id'])
					->getData();
				if (count($arr) === 0)
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBookings&action=pjActionIndex&err=AB08");
				}
				$price_tbl = pjPriceModel::factory()->getTable();
				
				$arr['tickets'] = pjBookingTicketModel::factory()
					->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjTicket', "t3.id=t1.ticket_id", 'left')
					->select('t1.*, t2.content as title')
					->where('booking_id', $arr['id'])
					->findAll()
					->getData();
				
				$arr['data'] = pjAppController::getData($this->option_arr, $arr, PJ_SALT, $this->getLocaleId());
				
				$lang_message = pjMultiLangModel::factory()
					->select('t1.*')
				 	->where('t1.model','pjOption')
				 	->where('t1.locale', $this->getLocaleId())
				 	->where('t1.field', 'o_email_confirmation_message')
				 	->limit(0, 1)
				 	->findAll()->getData();
				$lang_subject = pjMultiLangModel::factory()
					->select('t1.*')
				 	->where('t1.model','pjOption')
				 	->where('t1.locale', $this->getLocaleId())
				 	->where('t1.field', 'o_email_confirmation_subject')
				 	->limit(0, 1)
				 	->findAll()->getData();
				
				$this->set('arr', $arr);
				$this->set('lang_subject', $lang_subject[0]['content']);
				$this->set('lang_message', $lang_message[0]['content']);
				
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('pjAdminBookings.js');
			}
		} else {
			$this->set('status', 2);
		}
    }
    
    public function pjActionNotificationSend($option_arr, $booking_arr, $salt, $opt){
        $user_id = $this->getUserId();

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
    
        $super_admin_email = $this->getAdminEmail();
        $from_email = $super_admin_email;
        if(!empty($option_arr['o_sender_email'])) {
			$from_email = $option_arr['o_sender_email'];
		}

        $admin_email = '';
        $admin_phone = '';        
        if ($this->isEditor()) {
            $admin_detail = pjUserModel::factory()
            ->select('t2.*')
            ->join('pjUser', "t2.id=t1.added_by", 'left outer')
            ->find($user_id)->getData();

            $admin_email = $admin_detail['email'];
            $admin_phone = $admin_detail['phone'];
        }     
        
        //pending
        if ($booking_arr['c_email'] && $booking_arr['status'] == 'pending')
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
        if(!empty($booking_arr['c_phone']) && $booking_arr['status'] == 'pending')
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
        if (!empty($admin_email) && ($booking_arr['status'] == 'confirmed' || $booking_arr['status'] == 'pending'))
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
				$message = pjUtil::textToHtml($message);
				$subject = $lang_subject[0]['content'];

                $Email
                    ->setTo($admin_email)
                    ->setFrom($from_email)
                    ->setSubject($subject)
                    ->send($message);
			}
        }
        
        if(!empty($admin_phone) &&  ($booking_arr['status'] == 'confirmed' || $booking_arr['status'] == 'pending'))
		{
			$lang_message = $pjMultiLangModel->reset()->select('t1.*')
												 ->where('t1.model','pjOption')
												 ->where('t1.locale', $locale_id)
												 ->where('t1.field', 'o_admin_sms_confirmation_message')
												 ->limit(0, 1)
												 ->findAll()->getData();
			
			if (count($lang_message) === 1 && !empty($admin_phone))
			{
				$message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);
                
                $params = array(
					'text' => $message,
					'type' => 'unicode',
					'key' => md5($option_arr['private_key'] . PJ_SALT)
				);
                $params['number'] = $admin_phone;
                //$this->requestAction(array('controller' => 'pjSms', 'action' => 'pjActionSend', 'params' => $params), array('return'));
                $this->requestAction(array('controller' => 'pjSendSms', 'action' => 'pjActionSendSms', 'params' => $params), array('return'));
            }
        }

        if ($booking_arr['c_email'] && $booking_arr['status'] == 'confirmed')
		{            
			$lang_message = $pjMultiLangModel->reset()->select('t1.*')
											 ->where('t1.model','pjOption')
											 ->where('t1.locale', $locale_id)
											 ->where('t1.field', 'o_email_confirmation_message')
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

                // $handle = fopen(PJ_INSTALL_PATH.'message.txt','wb');
                // fwrite($handle, $message);
                
				$Email
					->setTo($booking_arr['c_email'])
					->setFrom($from_email)
					->setSubject($lang_subject[0]['content'])
					->send(pjUtil::textToHtml($message));
			}
		}
        
        if(!empty($booking_arr['c_phone']) && $booking_arr['status'] == 'confirmed')
		{
			$lang_message = $pjMultiLangModel->reset()->select('t1.*')
				->where('t1.model','pjOption')
				->where('t1.locale', $locale_id)
				->where('t1.field', 'o_sms_confirmation_message')
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

        //cancelled
        if (!empty($admin_email) && $booking_arr['status'] == 'cancelled')
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
				$message = pjUtil::textToHtml($message);
				$subject = $lang_subject[0]['content'];

                $Email
                    ->setTo($admin_email)
                    ->setFrom($from_email)
                    ->setSubject($subject)
                    ->send($message);
			}
        }
        
        if(!empty($admin_phone) && $booking_arr['status'] == 'cancelled')
		{
			$lang_message = $pjMultiLangModel->reset()->select('t1.*')
												 ->where('t1.model','pjOption')
												 ->where('t1.locale', $locale_id)
												 ->where('t1.field', 'o_admin_sms_cancellation_message')
												 ->limit(0, 1)
												 ->findAll()->getData();
			
			if (count($lang_message) === 1 && !empty($admin_phone))
			{
				$message = str_replace($tokens['search'], $tokens['replace'], $lang_message[0]['content']);

                $params = array(
					'text' => $message,
					'type' => 'unicode',
					'key' => md5($option_arr['private_key'] . PJ_SALT)
				);
                $params['number'] = $admin_phone;
                //$this->requestAction(array('controller' => 'pjSms', 'action' => 'pjActionSend', 'params' => $params), array('return'));
                $this->requestAction(array('controller' => 'pjSendSms', 'action' => 'pjActionSendSms', 'params' => $params), array('return'));
            }
        }

        if ($booking_arr['c_email'] && $booking_arr['status'] == 'cancelled')
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
        
        if(!empty($booking_arr['c_phone']) && $booking_arr['status'] == 'cancelled')
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
        //cancelled
    } 
    
    public function pjActionOutsideBooking()
	{    
		$this->checkLogin();
		
		if ($this->isEditor() || $this->isBusAdmin())
		{
			$_arr = pjBusModel::factory()
				->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->select("t1.*, t2.content AS route")
				->orderBy("route ASC")
				->findAll()
				->getData();
				
			foreach($_arr as $k => $v)
			{
				if(!empty($v['departure_time']) && !empty($v['arrival_time']))
				{
					$v['depart_arrive'] = pjUtil::formatTime($v['departure_time'], "H:i:s", $this->option_arr['o_time_format']) . ' - ' . pjUtil::formatTime($v['arrival_time'], "H:i:s", $this->option_arr['o_time_format']);
				}else{
					$v['depart_arrive'] = '';
				}
				$_arr[$k] = $v;
			}
			
			$route_arr = pjRouteModel::factory()
				->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->select("t1.*, t2.content AS route")
				->orderBy("route ASC")
				->findAll()
				->getData();
			
			$this->set('route_arr', $route_arr);
			$this->set('bus_arr', $_arr);
			
			$this->appendJs('jquery.datagrid.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('pjAdminOutsideBookings.js');
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
    }
    
    public function pjActionGetOutsideBooking()
	{
		$this->setAjax(true);
    
        if ($this->isEditor() || $this->isBusAdmin()) {
            if ($this->isXHR())
            {
                $pjUserModel = pjUserModel::factory();

                $pjBookingModel = pjBookingModel::factory()
                    ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                    ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                    ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                    ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                    ->where('user_id',0);  

                //$sub_query = 'SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId();

                //$pjBookingModel->where('(updated_by = '.$this->getUserId().' OR status_updated_by = '.$this->getUserId().' OR updated_by IN ('.$sub_query.') OR status_updated_by IN ('.$sub_query.'))');	

                if (isset($_GET['q']) && !empty($_GET['q']))
                {
                    $q = pjObject::escapeString($_GET['q']);
                    $pjBookingModel->where("(t1.id = '$q' OR t1.uuid = '$q' OR t1.c_fname LIKE '%$q%' OR t1.c_lname LIKE '%$q%' OR t1.c_email LIKE '%$q%')");
                }
                
                if (isset($_GET['bus_id']) && !empty($_GET['bus_id']))
                {
                    $bus_id = pjObject::escapeString($_GET['bus_id']);
                    $pjBookingModel->where("(t1.bus_id='".$bus_id."')");
                }
                if (isset($_GET['route_id']) && !empty($_GET['route_id']))
                {
                    $route_id = pjObject::escapeString($_GET['route_id']);
                    $pjBookingModel->where("(t1.bus_id IN (SELECT TB.id FROM `".pjBusModel::factory()->getTable()."` AS TB WHERE TB.route_id=$route_id))");
                }
                
                if (isset($_GET['status']) && !empty($_GET['status']) && in_array($_GET['status'], array('confirmed','cancelled','pending')))
                {
                    $pjBookingModel->where('t1.status', $_GET['status']);
                }
                if (isset($_GET['date_from']) && !empty($_GET['date_from']) && isset($_GET['date_to']) && !empty($_GET['date_to']))
                {
                    $df = pjUtil::formatDate($_GET['date_from'], $this->option_arr['o_date_format']);
                    $dt = pjUtil::formatDate($_GET['date_to'], $this->option_arr['o_date_format']);
                    $pjBookingModel->where("(t1.booking_date BETWEEN '$df' AND '$dt')");
                    
                } else {
                    if (isset($_GET['date_from']) && !empty($_GET['date_from']))
                    {
                        $df = pjUtil::formatDate($_GET['date_from'], $this->option_arr['o_date_format']);
                        $pjBookingModel->where("(t1.booking_date >= '$df')");
                    } elseif (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
                        $dt = pjUtil::formatDate($_GET['date_to'], $this->option_arr['o_date_format']);
                        $pjBookingModel->where("(t1.booking_date <= '$dt')");
                    }
                }
                    
                $column = 'created';
                $direction = 'DESC';
                if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
                {
                    $column = $_GET['column'];
                    $direction = strtoupper($_GET['direction']);
                }

                $total = $pjBookingModel->findCount()->getData();
                $rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
                $pages = ceil($total / $rowCount);
                $page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
                $offset = ((int) $page - 1) * $rowCount;
                if ($page > $pages)
                {
                    $page = $pages;
                }

                $data = $pjBookingModel
                    ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
                    ->orderBy("$column $direction")
                    ->limit($rowCount, $offset)
                    ->findAll()
                    ->getData();
                foreach($data as $k => $v)
                {
                    $route_details = '';
                    $client_arr = array();
                    if(!empty($v['c_fname']))
                    {
                        $client_arr[] = $v['c_fname'];
                    }
                    if(!empty($v['c_lname']))
                    {
                        $client_arr[] = $v['c_lname'];
                    }
                    $v['client'] = join(" ", $client_arr) . "<br/>" . $v['c_email'];
                    $v['date_time'] = date($this->option_arr['o_date_format'] . ', ' . $this->option_arr['o_time_format'], strtotime($v['booking_datetime'])) . '<br/>' . date($this->option_arr['o_date_format'] . ', ' . $this->option_arr['o_time_format'], strtotime($v['stop_datetime']));
                    if(date($this->option_arr['o_date_format'], strtotime($v['booking_datetime'])) == date($this->option_arr['o_date_format'], strtotime($v['stop_datetime'])))
                    {
                        $v['date_time'] = date($this->option_arr['o_date_format'], strtotime($v['booking_datetime'])) . '<br/>' . date($this->option_arr['o_time_format'], strtotime($v['booking_datetime'])) . ' - ' . date($this->option_arr['o_time_format'], strtotime($v['stop_datetime']));
                    }
                    
                    $route_details .= $v['route_title'];
                    $route_details .= ', ' . date($this->option_arr['o_time_format'], strtotime($v['departure_time'])) . ' - ' . date($this->option_arr['o_time_format'], strtotime($v['arrival_time']));
                    $route_details .= '<br/>'  . mb_strtolower(__('lblFrom', true), 'UTF-8') . ' ' . $v['from_location'] . ' ' . mb_strtolower(__('lblTo', true), 'UTF-8') . ' ' . $v['to_location'];
                    
                    $v['route_details'] = $route_details;
                    $data[$k] = $v;
                }
                            
                pjAppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
            }
        }
		exit;
    }
    
    public function pjActionSearchOutsideBooking()
	{
		$this->checkLogin();
		
		if ($this->isEditor()  || $this->isBusAdmin())
		{
            if(isset($_POST['search_outside_booking'])) {
                $unique_id = $_POST['unique_id'];

                $pjBookingModel = pjBookingModel::factory()
                    ->where('uuid =',$unique_id)
                    ->where('payment_method','cash')
                    ->where('user_id',0);

                $arr1 = $pjBookingModel->findAll()->getData();
            
                $arr = (count($arr1)) ? $arr1[0] : array();

                if (!$arr)
                {
                    pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBookings&action=pjActionOutsideBooking&err=ABB08");
                } else {
                    pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBookings&action=pjActionUpdate&id=".$arr['id']."&ob=1&&sob=1");
                }
            }

            $this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
            $this->appendJs('pjAdminOutsideBookings.js');
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
        } else {
			$this->set('status', 2);
		}
    }
}
?>