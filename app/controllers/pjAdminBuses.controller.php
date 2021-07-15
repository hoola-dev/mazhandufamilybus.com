<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAdminBuses extends pjAdmin
{
	public function pjActionCreate()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin())
		{
			if (isset($_POST['bus_create']))
			{
				$data = array();
				if(!empty($_POST['start_date']))
				{
					$data['start_date'] = pjUtil::formatDate($_POST['start_date'], $this->option_arr['o_date_format']);
				}
				if(!empty($_POST['end_date']))
				{
					$data['end_date'] = pjUtil::formatDate($_POST['end_date'], $this->option_arr['o_date_format']);
				}	
				$data['recurring'] = !empty($_POST['recurring']) ? join("|", $_POST['recurring']) : ':NULL';
				
				$pjBusModel = pjBusModel::factory();
				$id = $pjBusModel->setAttributes(array_merge($_POST, $data))->insert()->getInsertId();
				if ($id !== false && (int) $id > 0)
				{
					$location_arr = pjRouteCityModel::factory()
						->where('route_id', $_POST['route_id'])
						->orderBy("t1.order ASC")
						->findAll()
						->getData();
									
					$pjBusLocationModel = pjBusLocationModel::factory();
					$number_of_locations = count($location_arr);
					$b_data = array();
					$today = date('Y-m-d');
					foreach($location_arr as $k => $v)
					{
						$data = array();
						$data['bus_id'] = $id;
						$data['location_id'] = $v['city_id'];
						if($k == 0)
						{
                            $data['day_num'] = 0;
							$data['arrival_time'] = ":NULL";
							$b_data['departure_time'] = date('H:i:s', strtotime($today . ' ' . $_POST['departure_time_' . $v['city_id']]));
						}else{
                            $data['day_num'] = $_POST['day_num_' . $v['city_id']];
							$data['arrival_time'] = date('H:i:s', strtotime($today . ' ' . $_POST['arrival_time_' . $v['city_id']]));
						}
						if($k == ($number_of_locations - 1))
						{
							$data['departure_time'] = ":NULL";
							$b_data['arrival_time'] = date('H:i:s', strtotime($today . ' ' . $_POST['arrival_time_' . $v['city_id']]));
						}else{
                            $data['departure_time'] = date('H:i:s', strtotime($today . ' ' . $_POST['departure_time_' . $v['city_id']]));                            
						}
						$pjBusLocationModel->reset()->setAttributes($data)->insert();
					}
					$pjBusModel->reset()->where('id', $id)->limit(1)->modifyAll($b_data);
					
					$err = 'ABS03';
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBuses&action=pjActionTime&id=$id&err=$err");
				}else{
					$err = 'ABS04';
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBuses&action=pjActionIndex&err=$err");
				}
							
			} else {
				$route_arr = pjRouteModel::factory()
					->select(" t1.*, t2.content as title")
					->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->where('t1.status', 'T')
					->orderBy("t2.content ASC")
					->findAll()
					->getData();
				
				$this->set('route_arr', $route_arr);
				
				$bus_type_arr = pjBusTypeModel::factory()
							->select(" t1.*, t2.content as name")
							->join('pjMultiLang', "t2.model='pjBusType' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
							->where('t1.status', 'T')
							->orderBy("t2.content ASC")->findAll()->getData();
							
				$this->set('bus_type_arr', $bus_type_arr);
				
				$locale_arr = pjLocaleModel::factory()->select('t1.*, t2.file')
					->join('pjLocaleLanguage', 't2.iso=t1.language_iso', 'left')
					->where('t2.file IS NOT NULL')
					->orderBy('t1.sort ASC')->findAll()->getData();
				
				$lp_arr = array();
				foreach ($locale_arr as $item)
				{
					$lp_arr[$item['id']."_"] = $item['file'];
				}
				$this->set('lp_arr', $locale_arr);
                $this->set('locale_str', pjAppController::jsonEncode($lp_arr));
                $this->set('is_edit', 0);
				
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('jquery.multilang.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
				$this->appendJs('jquery.tipsy.js', PJ_THIRD_PARTY_PATH . 'tipsy/');
				$this->appendCss('jquery.tipsy.css', PJ_THIRD_PARTY_PATH . 'tipsy/');
				$this->appendJs('jquery-ui-timepicker-addon.js', PJ_THIRD_PARTY_PATH . 'datetimepicker/');
				$this->appendCss('jquery-ui-timepicker-addon.css', PJ_THIRD_PARTY_PATH . 'datetimepicker/');
				$this->appendJs('pjAdminBuses.js');
				$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionDeleteBus()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin())
		{
            if ($this->isXHR())
            {
                $response = array();
                
                if (pjBusModel::factory()->setAttributes(array('id' => $_GET['id']))->erase()->getAffectedRows() == 1)
                {
                    $pjTicketModel = pjTicketModel::factory();
                    $ticket_id_arr = $pjTicketModel->where('t1.bus_id', $_GET['id'])->findAll()->getDataPair('id', 'id');
                    if(!empty($ticket_id_arr))
                    {
                        pjMultiLangModel::factory()->where('model', 'pjTicket')->whereIn('foreign_id', $ticket_id_arr)->eraseAll();
                    }
                    $pjTicketModel->reset()->where('bus_id', $_GET['id'])->eraseAll();
                    pjBusLocationModel::factory()->where('bus_id', $_GET['id'])->eraseAll();
                    pjPriceModel::factory()->where('bus_id', $_GET['id'])->eraseAll();
                    pjBusDateModel::factory()->where('bus_id', $_GET['id'])->eraseAll();
                    pjBusModel::factory()->where('id', $_GET['id'])->eraseAll();
                    
                    $response['code'] = 200;
                } else {
                    $response['code'] = 100;
                }
                
                pjAppController::jsonResponse($response);
            }
        }
		exit;
	}
	
	public function pjActionDeleteBusBulk()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin())
		{
            if ($this->isXHR())
            {
                if (isset($_POST['record']) && count($_POST['record']) > 0)
                {
                    $pjTicketModel = pjTicketModel::factory();
                    
                    $ticket_id_arr = $pjTicketModel->whereIn('t1.bus_id', $_POST['record'])->findAll()->getDataPair('id', 'id');
                    if(!empty($ticket_id_arr))
                    {
                        pjMultiLangModel::factory()->where('model', 'pjTicket')->whereIn('foreign_id', $ticket_id_arr)->eraseAll();
                    }
                    $pjTicketModel->reset()->whereIn('bus_id', $_POST['record'])->eraseAll();
                    pjBusLocationModel::factory()->whereIn('bus_id', $_POST['record'])->eraseAll();
                    pjPriceModel::factory()->whereIn('bus_id', $_POST['record'])->eraseAll();
                    pjBusDateModel::factory()->whereIn('bus_id', $_POST['record'])->eraseAll();
                    pjBusModel::factory()->whereIn('id', $_POST['record'])->eraseAll();
                }
            }
        }
		exit;
	}
	
	public function pjActionGetBus()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$pjBusModel = pjBusModel::factory()
				->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer');

			if (isset($_GET['q']) && !empty($_GET['q']))
			{
				$q = pjObject::escapeString($_GET['q']);
				$pjBusModel->where("(t2.content LIKE '%$q%')");
			}
			if (isset($_GET['route_id']) && !empty($_GET['route_id']))
			{
				$route_id = pjObject::escapeString($_GET['route_id']);
				$pjBusModel->where("route_id", $route_id);
			}
			$column = 'route';
			$direction = 'ASC';
			if (isset($_GET['direction']) && isset($_GET['column']) && in_array(strtoupper($_GET['direction']), array('ASC', 'DESC')))
			{
				$column = $_GET['column'];
				if($column == 'from_to')
				{
					$column = 'start_date';
				}
				if($column == 'depart_arrive')
				{
					$column = 'departure_time';
				}
				$direction = strtoupper($_GET['direction']);
			}

			$total = $pjBusModel->findCount()->getData();
			$rowCount = isset($_GET['rowCount']) && (int) $_GET['rowCount'] > 0 ? (int) $_GET['rowCount'] : 10;
			$pages = ceil($total / $rowCount);
			$page = isset($_GET['page']) && (int) $_GET['page'] > 0 ? intval($_GET['page']) : 1;
			$offset = ((int) $page - 1) * $rowCount;
			if ($page > $pages)
			{
				$page = $pages;
			}

			$data = $pjBusModel->select(" t1.*, t2.content AS route")
								 ->orderBy("$column $direction")->limit($rowCount, $offset)->findAll()->getData();
								 	
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
			pjAppController::jsonResponse(compact('data', 'total', 'pages', 'page', 'rowCount', 'column', 'direction'));
		}
		exit;
	}
	
	public function pjActionIndex()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() || $this->isEditor() || $this->isInvestor())
		{
			$route_arr = pjRouteModel::factory()
				->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->select(" t1.*, t2.content AS route")
				->orderBy("route ASC")
				->findAll()
				->getData();
			
			$this->set('route_arr', $route_arr);
			
			$this->appendJs('jquery.datagrid.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
			$this->appendJs('pjAdminBuses.js');
			$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionSaveBus()
	{
		$this->setAjax(true);
    
        if ($this->isAdmin() || $this->isBusAdmin())
		{
            if ($this->isXHR())
            {
                $pjBusModel = pjBusModel::factory();
                if (!in_array($_POST['column'], $pjBusModel->i18n))
                {
                    $value = $_POST['value'];
                    
                    $pjBusModel->where('id', $_GET['id'])->limit(1)->modifyAll(array($_POST['column'] => $value));
                } else {
                    pjMultiLangModel::factory()->updateMultiLang(array($this->getLocaleId() => array($_POST['column'] => $_POST['value'])), $_GET['id'], 'pjBus', 'data');
                }
            }
        }
		exit;
	}
	
	public function pjActionTime()
	{
		$this->checkLogin();

		if ($this->isAdmin() || $this->isBusAdmin() || $this->isStandardAgent())
		{
				
			if (isset($_POST['bus_update']))
			{
				$pjBusModel = pjBusModel::factory();
				
                $arr = $pjBusModel->find($_POST['id'])->getData();
                
                $location_arr = pjRouteCityModel::factory()->select('t1.*, t2.content as name')
                            ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.city_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                            ->where('route_id', $arr['route_id'])->orderBy("t1.order ASC")->findAll()->getData();
                                    
                $pjBusLocationModel = pjBusLocationModel::factory();

                $number_of_locations = count($location_arr);

                if ($this->isStandardAgent()) {      
                    $delay_date = NULL;
                    $delay_minutes = NULL;
                    $prev_delay_date = NULL;
                    $prev_delay_minutes = NULL;
                    $prev_day_num = NULL;
                    foreach($location_arr as $k => $v)
                    {
                        $data = array();

                        $current_data = $pjBusLocationModel->reset()
                        ->where('bus_id', $_POST['id'])
                        ->where('location_id', $v['city_id'])
                        ->findAll()
                        ->getData();

                        if (isset($current_data[0]) && count($current_data[0]) > 0) {
                            $existing_delay_date = $current_data[0]['delay_date'];
                            $existing_delay_minutes = $current_data[0]['delay_minutes'];
                            $day_num  = $current_data[0]['day_num'];
                        } else {
                            $existing_delay_date = NULL;
                            $existing_delay_minutes = NULL;
                            $day_num  = 0;
                        }

                        if($k != ($number_of_locations - 1))
                        {  
                            $current_delay_date = pjUtil::formatDate($_POST['delay_date_' . $v['city_id']],$this->option_arr['o_date_format']);
                            $current_delay_minutes = $_POST['delay_minutes_' . $v['city_id']];

                            if ($existing_delay_date && !$current_delay_minutes) {
                                $delay_date = NULL;
                                $delay_minutes = NULL;
                            } else if (!$existing_delay_date && !$existing_delay_minutes) {
                                if ($current_delay_date && $current_delay_minutes > 0) {
                                    $delay_date = $current_delay_date;
                                    $delay_minutes = $current_delay_minutes;
                                } else if ($prev_delay_date && $prev_delay_minutes) {                                 
                                    $delay_minutes = $prev_delay_minutes;

                                    if ($day_num != $prev_day_num) {
                                        $day_num_diff = $day_num - $prev_day_num;
                                        $delay_date = date('Y-m-d',strtotime("$prev_delay_date +$day_num_diff days"));
                                    } else {
                                        $delay_date = $prev_delay_date;
                                    }
                                }
                            } else {
                                $delay_date = $current_delay_date;
                                $delay_minutes = $current_delay_minutes;
                            }                            
                         

                            if ($delay_date && $delay_minutes) {                                
                                $data['delay_date'] = $delay_date;
                                $data['delay_minutes'] = $delay_minutes;
                            } else {
                                $data['delay_date'] = '';
                                $data['delay_minutes'] = '';
                            }
  
                            $pjBusLocationModel->reset()->where('bus_id', $_POST['id'])->where('location_id', $v['city_id'])->limit(1)->debug('true')->modifyAll($data); 

                            $current_data = $pjBusLocationModel->reset()
                            ->where('bus_id', $_POST['id'])
                            ->where('location_id', $v['city_id'])
                            ->findAll()
                            ->getData();

                            if (isset($current_data[0]) && count($current_data[0]) > 0) {
                                $prev_delay_date = $current_data[0]['delay_date'];
                                $prev_delay_minutes = $current_data[0]['delay_minutes'];
                                $prev_day_num = $current_data[0]['day_num'];
                            }
                        }
                    }
                } else {
                    $b_data = array();
                    $b_data['start_date'] = pjUtil::formatDate($_POST['start_date'], $this->option_arr['o_date_format']);
                    $b_data['end_date'] = pjUtil::formatDate($_POST['end_date'], $this->option_arr['o_date_format']);
                    $b_data['recurring'] = !empty($_POST['recurring']) ? join("|", $_POST['recurring']) : ':NULL';

                    $today = date('Y-m-d');
                    $delay_date = NULL;
                    $delay_minutes = NULL;
                    $prev_delay_date = NULL;
                    $prev_delay_minutes = NULL;
                    $prev_day_num = NULL;
                    $is_set_next_location = false;
                    foreach($location_arr as $k => $v)
                    {
                        $data = array();

                        $current_data = $pjBusLocationModel->reset()
                        ->where('bus_id', $_POST['id'])
                        ->where('location_id', $v['city_id'])
                        ->findAll()
                        ->getData();

                        if (isset($current_data[0]) && count($current_data[0]) > 0) {
                            $existing_delay_date = $current_data[0]['delay_date'];
                            $existing_delay_minutes = $current_data[0]['delay_minutes'];
                        } else {
                            $existing_delay_date = NULL;
                            $existing_delay_minutes = NULL;
                        }
   
                        if($k == 0)
                        {
                            $data['day_num'] = 0;
                            $data['arrival_time'] = ":NULL";
                            $b_data['departure_time'] = date('H:i:s', strtotime($today . ' ' . $_POST['departure_time_' . $v['city_id']]));
                        }else{
                            $data['day_num'] = $_POST['day_num_' . $v['city_id']];
                            $data['arrival_time'] = date('H:i:s', strtotime($today . ' ' . $_POST['arrival_time_' . $v['city_id']]));
                        }
                        if($k == ($number_of_locations - 1))
                        {
                            $data['departure_time'] = ":NULL";
                            $b_data['arrival_time'] = date('H:i:s', strtotime($today . ' ' . $_POST['arrival_time_' . $v['city_id']]));
                        }else{
                            $data['departure_time'] = date('H:i:s', strtotime($today . ' ' . $_POST['departure_time_' . $v['city_id']]));
                            
                            $current_delay_date = (isset($_POST['delay_date_' . $v['city_id']]) && $_POST['delay_date_' . $v['city_id']]) ? pjUtil::formatDate($_POST['delay_date_' . $v['city_id']],$this->option_arr['o_date_format']) : null;
                            $current_delay_minutes = (isset($_POST['delay_minutes_' . $v['city_id']]) && $_POST['delay_minutes_' . $v['city_id']]) ? $_POST['delay_minutes_' . $v['city_id']] : 0;

                            /*
                            if ($existing_delay_date && !$current_delay_minutes) {
                                $delay_date = NULL;
                                $delay_minutes = NULL;
                            } else if (!$existing_delay_date && !$existing_delay_minutes) {
                                if ($current_delay_date && $current_delay_minutes > 0) {
                                    $delay_date = $current_delay_date;
                                    $delay_minutes = $current_delay_minutes;
                                } else if ($prev_delay_date && $prev_delay_minutes) {                                
                                    $delay_minutes = $prev_delay_minutes;

                                    $day_num = $_POST['day_num_' . $v['city_id']];
                                    if ($day_num != $prev_day_num) {
                                        $day_num_diff = $day_num - $prev_day_num;
                                        $delay_date = date('Y-m-d',strtotime("$prev_delay_date +$day_num_diff days"));
                                    } else {
                                        $delay_date = $prev_delay_date;
                                    }
                                }
                            } else {
                                $delay_date = $current_delay_date;
                                $delay_minutes = $current_delay_minutes;
                            }  
                            */   

                            if (!$prev_delay_date && $current_delay_date) {
                                if ($is_set_next_location) {
                                      $delay_minutes = $prev_delay_minutes;

                                    $day_num = $_POST['day_num_' . $v['city_id']];
                                    if ($day_num != $prev_day_num) {
                                        $day_num_diff = $day_num - $prev_day_num;
                                        $delay_date = date('Y-m-d',strtotime("$prev_delay_date +$day_num_diff days"));
                                    } else {
                                        $delay_date = $prev_delay_date;
                                    }
                                } else {
                                    if ($existing_delay_minutes && $existing_delay_minutes != $current_delay_minutes) {
                                        $delay_date = $current_delay_date;
                                        $delay_minutes = $current_delay_minutes;
                                        $is_set_next_location = true;
                                     } else {
                                        $delay_date = $current_delay_date;
                                        $delay_minutes = $current_delay_minutes;

                                        if ($existing_delay_minutes != $current_delay_minutes) {
                                            $is_set_next_location = true;
                                        }
                                    }   
                                }
                            } else if ($prev_delay_date) {
                                if ($is_set_next_location) {
                                    $delay_minutes = $prev_delay_minutes;
                                } else {
                                    if ($existing_delay_minutes != $current_delay_minutes) {
                                        $delay_minutes = $current_delay_minutes;
                                        $is_set_next_location = true;
                                    } else {
                                        $delay_minutes = $current_delay_minutes;
                                    }
                                }

                                $day_num = $_POST['day_num_' . $v['city_id']];
                                if ($day_num != $prev_day_num) {
                                    $day_num_diff = $day_num - $prev_day_num;
                                    $delay_date = date('Y-m-d',strtotime("$prev_delay_date +$day_num_diff days"));
                                } else {
                                    $delay_date = $prev_delay_date;
                                }
                            } else { 
                                $delay_date = NULL;
                                $delay_minutes = NULL;
                            }                          

                            if ($delay_date && $delay_minutes) {                                
                                $data['delay_date'] = $delay_date;
                                $data['delay_minutes'] = $delay_minutes;
                            } else {
                                $data['delay_date'] = '';
                                $data['delay_minutes'] = '';
                            }
                        }

                        if($pjBusLocationModel->reset()->where('bus_id', $_POST['id'])->where('location_id', $v['city_id'])->findCount()->getData() > 0)
                        {
                            $pjBusLocationModel->reset()->where('bus_id', $_POST['id'])->where('location_id', $v['city_id'])->limit(1)->debug('true')->modifyAll($data);
				        } else {
                            $data['bus_id'] = $_POST['id'];
                            $data['location_id'] = $v['city_id'];
                            $pjBusLocationModel->reset()->setAttributes($data)->insert();
                        }

                        $current_data = $pjBusLocationModel->reset()
                        ->where('bus_id', $_POST['id'])
                        ->where('location_id', $v['city_id'])
                        ->findAll()
                        ->getData();

                        if (isset($current_data[0]) && count($current_data[0]) > 0) {
                            $prev_delay_date = $current_data[0]['delay_date'];
                            $prev_delay_minutes = $current_data[0]['delay_minutes'];
                            $prev_day_num = $current_data[0]['day_num'];
                        }
                    }

                    $pjBusModel->reset()->where('id', $_POST['id'])->limit(1)->debug('true')->modifyAll(array_merge($_POST, $b_data));
                }

				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBuses&action=pjActionTime&id=" . $_POST['id'] . "&err=ABS01");
				
			} else {
				$arr = pjBusModel::factory()->find($_GET['id'])->getData();
				if (count($arr) === 0)
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBuses&action=pjActionIndex&err=ABS08");
				}
				$this->set('arr', $arr);
				
				$route_arr = pjRouteModel::factory()
					->select(" t1.*, t2.content as title")
					->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->where('t1.status', 'T')
					->orderBy("t2.content ASC")
					->findAll()
					->getData();
							
				$this->set('route_arr', $route_arr);
				
				$bus_type_arr = pjBusTypeModel::factory()
							->select(" t1.*, t2.content as name")
							->join('pjMultiLang', "t2.model='pjBusType' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
							->where('t1.status', 'T')
							->orderBy("t2.content ASC")->findAll()->getData();
							
				$this->set('bus_type_arr', $bus_type_arr);
				
				$location_arr = pjRouteCityModel::factory()
					->select('t1.*, t2.content as name')
					->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.city_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->where('route_id', $arr['route_id'])
					->orderBy("t1.order ASC")
					->findAll()
                    ->getData();

				$this->set('location_arr', $location_arr);
				
				$sl_arr = array(); 
				$_sl_arr = pjBusLocationModel::factory()
					->where('bus_id', $_GET['id'])
					->findAll()
					->getData();
				foreach($_sl_arr as $k => $v)
				{
					$sl_arr[$v['location_id']] = $v;
				}
				$this->set('sl_arr', $sl_arr);
				
				$this->set('date_arr', pjBusDateModel::factory()->where('bus_id', $_GET['id'])->orderBy("`date` ASC")->findAll()->getData());
                $this->set('is_edit', 1);
                
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('jquery.multilang.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
				$this->appendJs('jquery.tipsy.js', PJ_THIRD_PARTY_PATH . 'tipsy/');
				$this->appendCss('jquery.tipsy.css', PJ_THIRD_PARTY_PATH . 'tipsy/');
				$this->appendJs('jquery-ui-timepicker-addon.js', PJ_THIRD_PARTY_PATH . 'datetimepicker/');
				$this->appendCss('jquery-ui-timepicker-addon.css', PJ_THIRD_PARTY_PATH . 'datetimepicker/');
				$this->appendJs('pjAdminBuses.js');
				$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionNotOperating()
	{
		$this->checkLogin();
		if ($this->isAdmin() || $this->isBusAdmin())
		{
			if (isset($_POST['bus_update']))
			{
				$pjBusDateModel = pjBusDateModel::factory();
				$pjBusDateModel->where('bus_id', $_POST['id'])->eraseAll();
				if(isset($_POST['date']) && count($_POST['date']) > 0)
				{
					foreach($_POST['date'] as $date)
					{
						if(!empty($date))
						{
							$data = array();
							$data['bus_id'] = $_POST['id'];
							$data['date'] = pjUtil::formatDate($date, $this->option_arr['o_date_format']);
							$pjBusDateModel->reset()->setAttributes($data)->insert();
						}
					}
				}
				
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBuses&action=pjActionNotOperating&id=" . $_POST['id'] . "&err=ABS11");
			}else{
				$arr = pjBusModel::factory()->find($_GET['id'])->getData();
				if (count($arr) === 0)
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBuses&action=pjActionIndex&err=ABS08");
				}
				$this->set('arr', $arr);
				
				$this->set('date_arr', pjBusDateModel::factory()->where('bus_id', $_GET['id'])->orderBy("`date` ASC")->findAll()->getData());
				
				$route_arr = pjRouteModel::factory()
					->select(" t1.*, t2.content as title")
					->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->where('t1.status', 'T')
					->find($arr['route_id'])
					->getData();
					
				$this->set('route_arr', $route_arr);
				
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('pjAdminBuses.js');
			}
		}else {
			$this->set('status', 2);
		}
    }
    
    public function pjActionCheckBookedSeats()
	{
		$this->checkLogin();
		if ($this->isAdmin() || $this->isBusAdmin())
		{
            $bus_id = $_POST['bus_id'];
            $date = $_POST['date'];
            $date = pjUtil::formatDate($date, $this->option_arr['o_date_format']);
            $option_arr = $this->option_arr;

            $booked_seat_arr = pjBookingSeatModel::factory ()->select ( "DISTINCT seat_id" )->where ( "t1.booking_id IN(SELECT TB.id
                                    FROM `" . pjBookingModel::factory ()->getTable () . "` AS TB
                                    WHERE (TB.status='confirmed' OR (TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL " . $option_arr ['o_min_hour'] . " MINUTE))))
            AND TB.bus_id = $bus_id AND TB.bus_departure_date = '$date')")->findAll ()->getData ();
            
            if (count($booked_seat_arr) > 0) {
                $data['success'] = 0;
            } else {
                $data['success'] = 1;
            }

        }
        echo json_encode($data);
        exit;
    }
	
	public function pjActionTicket()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin())
		{
			if (isset($_POST['bus_update']))
			{
				$pjBusModel = pjBusModel::factory();
				$arr = pjBusModel::factory()->find($_POST['id'])->getData();
				if (empty($arr))
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBuses&action=pjActionIndex&err=AR08");
				}
				
				$s_arr = array();
				if(isset($_POST['set_seats_count']))
				{
					$s_arr['set_seats_count'] = 'T';
				}else{
					$s_arr['set_seats_count'] = 'F';
				}
				$pjBusModel->reset()->where('id', $_POST['id'])->limit(1)->modifyAll($s_arr);
				
				$pjMultiLangModel = pjMultiLangModel::factory();
				$pjTicketModel = pjTicketModel::factory();
				if (isset($_POST['i18n']))
				{
					if(isset($_POST['index_arr']) && $_POST['index_arr'] != '')
					{
						$index_arr = explode("|", $_POST['index_arr']);
						
						foreach($index_arr as $k => $v)
						{
							if(strpos($v, 'bs') !== false)
							{
								$t_data = array();
								$t_data['bus_id'] = $_POST['id'];
								if(isset($_POST['set_seats_count']))
								{
									$t_data['seats_count'] = $_POST['seats_count'][$v];
								}
								$ticket_id = $pjTicketModel->reset()->setAttributes($t_data)->insert()->getInsertId();
								if ($ticket_id !== false && (int) $ticket_id > 0)
								{
									foreach ($_POST['i18n'] as $locale => $locale_arr)
									{
										foreach ($locale_arr as $field => $content)
										{
											if(is_array($content))
											{
												$insert_id = $pjMultiLangModel->reset()->setAttributes(array(
													'foreign_id' => $ticket_id,
													'model' => 'pjTicket',
													'locale' => $locale,
													'field' => $field,
													'content' => $content[$v],
													'source' => 'data'
												))->insert()->getInsertId();
											}
										}
									}
								}
							}else{
								$t_data = array();
								if(isset($_POST['set_seats_count']))
								{
									$t_data['seats_count'] = $_POST['seats_count'][$v];
								}else{
									$t_data['seats_count'] = ':NULL';
								}
								$pjTicketModel->reset()->where('id', $v)->limit(1)->modifyAll($t_data);
								foreach ($_POST['i18n'] as $locale => $locale_arr)
								{
									foreach ($locale_arr as $field => $content)
									{
										if(is_array($content))
										{
											$sql = sprintf("INSERT INTO `%1\$s` (`id`, `foreign_id`, `model`, `locale`, `field`, `content`, `source`)
												VALUES (NULL, :foreign_id, :model, :locale, :field, :update_content, :source)
												ON DUPLICATE KEY UPDATE `content` = :update_content, `source` = :source;",
												$pjMultiLangModel->getTable()
											);
											$foreign_id = $v;
											$model = 'pjTicket';
											$source = 'data';
											$update_content = $content[$v];
											$pjMultiLangModel->prepare($sql)->exec(compact('foreign_id', 'model', 'locale', 'field', 'update_content', 'source'));
										}
									}
								}
							}
						}
					}
				}
				if(isset($_POST['remove_arr']) && $_POST['remove_arr'] != '')
				{
					$remove_arr = explode("|", $_POST['remove_arr']);
					
					$pjMultiLangModel->reset()->where('model', 'pjTicket')->whereIn('foreign_id', $remove_arr)->eraseAll();
					pjPriceModel::factory()->whereIn('ticket_id', $remove_arr)->eraseAll();
					$pjTicketModel->reset()->whereIn('id', $remove_arr)->eraseAll();
				}
				
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBuses&action=pjActionTicket&id=" . $_POST['id'] . "&err=ABS09");
			} else {
				$arr = pjBusModel::factory()->find($_GET['id'])->getData();
				if (count($arr) === 0)
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBuses&action=pjActionIndex&err=ABS08");
				}
				
				$seats_available = 0;
				if(!empty($arr['bus_type_id']))
				{
					$bus_type_arr = pjBusTypeModel::factory()->find($arr['bus_type_id'])->getData();
					if(!empty($bus_type_arr))
					{
						$seats_available = $bus_type_arr['seats_count'];
					}
				}
				
				$ticket_arr = pjTicketModel::factory()->where('bus_id', $_GET['id'])->findAll()->getData();
				foreach($ticket_arr as $k => $v)
				{
					$ticket_arr[$k]['i18n'] = pjMultiLangModel::factory()->getMultiLang($v['id'], 'pjTicket');
				}

				$route_arr = pjRouteModel::factory()
					->select(" t1.*, t2.content as title")
					->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->where('t1.status', 'T')
					->find($arr['route_id'])
					->getData();
					
				
				$locale_arr = pjLocaleModel::factory()->select('t1.*, t2.file')
					->join('pjLocaleLanguage', 't2.iso=t1.language_iso', 'left')
					->where('t2.file IS NOT NULL')
					->orderBy('t1.sort ASC')->findAll()->getData();
						
				$lp_arr = array();
				foreach ($locale_arr as $item)
				{
					$lp_arr[$item['id']."_"] = $item['file'];
				}
				$this->set('lp_arr', $locale_arr);
				$this->set('locale_str', pjAppController::jsonEncode($lp_arr));
				$this->set('arr', $arr);
				$this->set('ticket_arr', $ticket_arr);
				$this->set('seats_available', $seats_available);
				$this->set('route_arr', $route_arr);
								
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('jquery.multilang.js', PJ_FRAMEWORK_LIBS_PATH . 'pj/js/');
				$this->appendJs('jquery.tipsy.js', PJ_THIRD_PARTY_PATH . 'tipsy/');
				$this->appendCss('jquery.tipsy.css', PJ_THIRD_PARTY_PATH . 'tipsy/');
				$this->appendJs('pjAdminBuses.js');
				$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionPrice()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() || $this->isEditor())
		{
			if (isset($_POST['bus_update']))
			{
				$pjBusModel = pjBusModel::factory();
				$arr = $pjBusModel->find($_POST['id'])->getData();
				
				if (count($arr) === 0)
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBuses&action=pjActionIndex&err=AR08");
				}
				
				$discount = 0;
				if((float) $_POST['discount'] > 0)
				{
					$discount = $_POST['discount'];
				}
				$pjBusModel->reset()->where('id', $_POST['id'])->limit(1)->modifyAll(array('discount' => $discount));
				
				$pjPriceModel = pjPriceModel::factory();
				$location_arr = pjRouteCityModel::factory()
					->select('t1.*')
					->where('route_id', $arr['route_id'])
					->orderBy("t1.order ASC")
					->findAll()
					->getData();
				
				$ticket_id = $_POST['ticket_id'];
				$number_of_locations = count($location_arr);
				foreach($location_arr as $k => $row)
				{
					if($k <= ($number_of_locations - 2))
					{
						$j = 1;
						foreach($location_arr as $col)
						{
							if($j > 1)
							{
								$cnt = $pjPriceModel
									->reset()
									->where('ticket_id', $ticket_id)
									->where('from_location_id', $row['city_id'])
									->where('to_location_id', $col['city_id'])
									->where('is_return = "F"')
									->findCount()
									->getData();
									
								$price = $_POST['price_' . $row['city_id'] . '_' . $col['city_id']];
								if($price != '')
								{
									if (!is_numeric($price)) 
									{
										$price = ':NULL';
									}else{
										if($price < 0)
										{
											$price = ':NULL';
										}
									}
								}else{
									$price = ':NULL';
								}
								if($cnt == 0)
								{
									$data = array();
									$data['bus_id'] = $_POST['id'];
									$data['ticket_id'] = $ticket_id;
									$data['from_location_id'] = $row['city_id'];
									$data['to_location_id'] = $col['city_id'];
									$data['price'] = $price;
									$data['is_return'] = 'F';
									$pjPriceModel->reset()->setAttributes($data)->insert();
								}else{
									$pjPriceModel->reset()
										->where('bus_id', $_POST['id'])
										->where('ticket_id', $ticket_id)
										->where('from_location_id', $row['city_id'])
										->where('to_location_id', $col['city_id'])
										->where('is_return = "F"')
										->limit(1)
										->modifyAll(array('price' => $price));
								}
							}
							$j++;
						}
					}
				}
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBuses&action=pjActionPrice&id=" . $_POST['id'] . "&ticket_id=$ticket_id&err=AS10");
				
			} else {
				$arr = pjBusModel::factory()->find($_GET['id'])->getData();
				
				if (count($arr) === 0)
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminBuses&action=pjActionIndex&err=AR08");
				}
				
				$ticket_arr = pjTicketModel::factory()
					->select(" t1.*, t2.content as title")
					->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->where('t1.bus_id', $arr['id'])
					->orderBy("t2.content ASC")
					->findAll()
					->getData();
							
				$this->set('ticket_arr', $ticket_arr);

				$pjRouteCityModel = pjRouteCityModel::factory();

				$location_arr = $pjRouteCityModel->select('t1.*, t2.content as name')
					->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.city_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->where('route_id', $arr['route_id'])
					->orderBy("t1.order ASC")
					->findAll()->getData();
				
				if(count($ticket_arr) > 0)
				{
					if(!isset($_GET['ticket_id']))
					{
						$ticket_id = $ticket_arr[0]['id'];
					}else{
						$ticket_id = $_GET['ticket_id'];
					}
					$location_id_arr = $pjRouteCityModel
						->reset()
						->where('t1.route_id', $arr['route_id'])
						->findAll()
						->getDataPair('city_id', 'city_id');
						
					$price_arr = array();
					if(!empty($location_id_arr))
					{
						$_price_arr = pjPriceModel::factory()
							->where('ticket_id', $ticket_id)
							->whereIn('from_location_id', $location_id_arr)
							->where('is_return = "F"')
							->findAll()
							->getData();
						if(!empty($_price_arr))
						{
							foreach($_price_arr as $v)
							{
								$price_arr[$v['from_location_id'] . '_' . $v['to_location_id']] = $v['price'];
								$price_arr[$v['from_location_id'] . '_' . $v['to_location_id']] = $v['price'];
							}
						}
					}
					$this->set('price_arr', $price_arr);
					
					$return_price_arr = array();
					if(!empty($location_id_arr))
					{
						$_price_arr = pjPriceModel::factory()
							->where('ticket_id', $ticket_id)
							->where('is_return = "T"')
							->whereIn('from_location_id', $location_id_arr)
							->findAll()->getData();
						if(!empty($_price_arr))
						{
							foreach($_price_arr as $v)
							{
								$return_price_arr[$v['from_location_id'] . '_' . $v['to_location_id']] = $v['price'];
								$return_price_arr[$v['from_location_id'] . '_' . $v['to_location_id']] = $v['price'];
							}
						}
					}
					
					$this->set('return_price_arr', $return_price_arr);
					
					$this->set('ticket_id', $ticket_id);
				}
				
				$this->set('arr', $arr);
				$this->set('location_arr', $location_arr);
							
				$locale_arr = pjLocaleModel::factory()->select('t1.*, t2.file')
					->join('pjLocaleLanguage', 't2.iso=t1.language_iso', 'left')
					->where('t2.file IS NOT NULL')
					->orderBy('t1.sort ASC')->findAll()->getData();
				
				$lp_arr = array();
				foreach ($locale_arr as $item)
				{
					$lp_arr[$item['id']."_"] = $item['file'];
				}
				$this->set('lp_arr', $locale_arr);
				$this->set('locale_str', pjAppController::jsonEncode($lp_arr));

				$bus_arr = pjBusModel::factory()
					->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->select(" t1.*, t2.content AS route")
					->where('t1.route_id', $arr['route_id'])
					->where('t1.id <>', $arr['id'])
					->orderBy("route asc")
					->findAll()
					->getData();
				$this->set('bus_arr', $bus_arr);
				
				$route_arr = pjRouteModel::factory()
					->select(" t1.*, t2.content as title")
					->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->where('t1.status', 'T')
					->find($arr['route_id'])
					->getData();
				$this->set('route_arr', $route_arr);
				
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('pjAdminBuses.js');
				$this->appendJs('index.php?controller=pjAdmin&action=pjActionMessages', PJ_INSTALL_URL, true);
			}
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionGetLocations()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			if (isset($_GET['route_id']) && (int) $_GET['route_id'] > 0)
			{
				$location_arr = pjRouteCityModel::factory()
					->select('t1.*, t2.content as name')
					->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.city_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->where('route_id', $_GET['route_id'])
					->orderBy("t1.order ASC")
					->findAll()->getData();
				$this->set('location_arr', $location_arr);
				
				if(isset($_GET['bus_id']))
				{
					$sl_arr = array(); 
					$_sl_arr = pjBusLocationModel::factory()
						->where('bus_id', $_GET['bus_id'])
						->findAll()
						->getData();
					foreach($_sl_arr as $k => $v)
					{
						$sl_arr[$v['location_id']] = $v;
					}
					$this->set('sl_arr', $sl_arr);
                }
                
                $this->set('is_edit', 0);
			}
		}
	}
	
	public function pjActionGetLocale()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			if (isset($_GET['locale']) && (int) $_GET['locale'] > 0)
			{
				pjAppController::setFields($_GET['locale']);
				
				$this->set('route_arr', pjRouteModel::factory()->select('t1.*, t2.content AS title')
					->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$_GET['locale']."'", 'inner')
					->where('t1.status', 'T')->orderBy('t2.content ASC')->findAll()->getData()
				);
			}
		}
	}
	
	public function pjActionGetLocaleTicket()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			if (isset($_GET['locale']) && (int) $_GET['locale'] > 0)
			{
				pjAppController::setFields($_GET['locale']);
				
				$this->set('ticket_arr', pjTicketModel::factory()->select('t1.*, t2.content AS title')
					->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$_GET['locale']."'", 'inner')
					->orderBy('t2.content ASC')->findAll()->getData()
				);
			}
		}
	}
	
	public function pjActionGetPriceGrid()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$arr = pjBusModel::factory()->find($_GET['bus_id'])->getData();
			
			$pjRouteCityModel = pjRouteCityModel::factory();

			$location_arr = $pjRouteCityModel
				->select('t1.*, t2.content as name')
				->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.city_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->where('route_id', $arr['route_id'])
				->orderBy("t1.order ASC")
				->findAll()
				->getData();
			
			
			$ticket_id = $_GET['ticket_id'];
			$location_id_arr = $pjRouteCityModel
				->reset()
				->where('t1.route_id', $arr['route_id'])
				->findAll()
				->getDataPair('city_id', 'city_id');
			$price_arr = array();
			if(!empty($location_id_arr))
			{
				$_price_arr = pjPriceModel::factory()
					->where('ticket_id', $ticket_id)
					->whereIn('from_location_id', $location_id_arr)
					->findAll()
					->getData();
				if(!empty($_price_arr))
				{
					foreach($_price_arr as $v)
					{
						$price_arr[$v['from_location_id'] . '_' . $v['to_location_id']] = $v['price'];
					}
				}
			}
			$this->set('price_arr', $price_arr);
			$this->set('ticket_id', $ticket_id);

			$this->set('arr', $arr);
			$this->set('location_arr', $location_arr);
		}
	}
	
	public function pjActionGetReturnPriceGrid()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$arr = pjBusModel::factory()->find($_GET['bus_id'])->getData();
				
			$pjRouteCityModel = pjRouteCityModel::factory();
				
			$location_arr = $pjRouteCityModel
				->select('t1.*, t2.content as name')
				->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.city_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->where('route_id', $arr['route_id'])
				->orderBy("t1.order ASC")
				->findAll()
				->getData();
				
				
			$ticket_id = $_GET['ticket_id'];
			$location_id_arr = $pjRouteCityModel
				->reset()
				->where('t1.route_id', $arr['route_id'])
				->findAll()
				->getDataPair('city_id', 'city_id');
			$price_arr = array();
			if(!empty($location_id_arr))
			{
				$_price_arr = pjPriceModel::factory()
					->where('ticket_id', $ticket_id)
					->whereIn('from_location_id', $location_id_arr)
					->where('is_return = "T"')
					->findAll()
					->getData();
				if(!empty($_price_arr))
				{
					foreach($_price_arr as $v)
					{
						$price_arr[$v['from_location_id'] . '_' . $v['to_location_id']] = $v['price'];
					}
				}
			}
			$this->set('return_price_arr', $price_arr);
			$this->set('ticket_id', $ticket_id);
			$this->set('arr', $arr);
			$this->set('location_arr', $location_arr);
		}
	}
	
	public function pjActionGetTickets()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$ticket_arr = array();
			if (isset($_GET['bus_id']) && (int) $_GET['bus_id'] > 0)
			{
				$ticket_arr = pjTicketModel::factory()->select('t1.*, t2.content as title')
								->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
								->where('bus_id', $_GET['bus_id'])->orderBy("t2.content ASC")->findAll()->getData();
			}
			$this->set('ticket_arr', $ticket_arr);
		}
	}
	
	public function pjActionCopyPrices()
	{
		$this->setAjax(true);
	
		if ($this->isXHR())
		{
			$response = array();
			
			$source_bus_id = $_POST['source_bus_id'];
			$source_ticket_id = $_POST['source_ticket_id'];
			$dst_bus_id = $_GET['bus_id'];
			$dst_ticket_id = $_GET['ticket_id'];
			
			$pjPriceModel = pjPriceModel::factory();
			$price_arr = $pjPriceModel->where('bus_id', $source_bus_id)->where('ticket_id', $source_ticket_id)->findAll()->getData();
			foreach($price_arr as $v)
			{
				$cnt = $pjPriceModel->reset()->where('bus_id', $dst_bus_id)->where('ticket_id', $dst_ticket_id)->where('from_location_id', $v['from_location_id'])->where('to_location_id', $v['to_location_id'])->findCount()->getData();
				$price = $v['price'];
				if($cnt == 0)
				{
					$data = array();
					$data['bus_id'] = $dst_bus_id;
					$data['ticket_id'] = $dst_ticket_id;
					$data['from_location_id'] = $v['from_location_id'];
					$data['to_location_id'] = $v['to_location_id'];
					$data['price'] = $price;
					$pjPriceModel->reset()->setAttributes($data)->insert();
				}else{
					$pjPriceModel->reset()
						->where('bus_id', $dst_bus_id)
						->where('ticket_id', $dst_ticket_id)
						->where('from_location_id', $v['from_location_id'])
						->where('to_location_id', $v['to_location_id'])
						->limit(1)
						->modifyAll(array('price' => $price));
				}
			}
			$response['code'] = 200;
			pjAppController::jsonResponse($response);
		}
	}
}
?>