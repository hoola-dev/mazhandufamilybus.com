<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAdmin extends pjAppController
{
	public $defaultUser = 'admin_user';
	
	public $requireLogin = true;
	
	public function __construct($requireLogin=null)
	{
		$this->setLayout('pjActionAdmin');
		
		if (!is_null($requireLogin) && is_bool($requireLogin))
		{
			$this->requireLogin = $requireLogin;
		}
		
		if ($this->requireLogin)
		{
			if (!$this->isLoged() && !in_array(@$_GET['action'], array('pjActionLogin', 'pjActionForgot', 'pjActionPreview')))
			{
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin");
			}
		}
	}
	
	public function beforeRender()
	{
		
	}

	public function pjActionIndex()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isEditor() || $this->isBusAdmin() || $this->isInvestor())
		{
			$pjBookingModel = pjBookingModel::factory();
			$pjBusModel = pjBusModel::factory();
            $pjRouteModel = pjRouteModel::factory();
            $pjUserModel = pjUserModel::factory();
            $pjMultiLangModel = pjMultiLangModel::factory();
            $pjCreditModel = pjCreditModel::factory();
		
            $current_date = date('Y-m-d');
			$weekday = strtolower(date('l'));
            
            if ($this->isEditor()) {
                $agent_arr = pjUserModel::factory()
                ->find($this->getUserId())
                ->getData();

                $this->set('user_code', $agent_arr['user_code']);

                $user_credit = pjCreditModel::factory()
                    ->where('t1.user_id', $this->getUserId())
                    ->findAll()
                    ->getData();

                $user_credit_balance = (!empty($user_credit)) ? $user_credit[0]['credit'] : 0;
                $user_commission_percent = (!empty($user_credit)) ? $user_credit[0]['commission_percent'] : 0;
                $user_total_commission = (!empty($user_credit)) ? $user_credit[0]['total_commission'] : 0;
                
                $this->set('user_credit_balance', $user_credit_balance);
                $this->set('user_commission_percent', $user_commission_percent);
                $this->set('user_total_commission', $user_total_commission);
            }
                    
			$cnt_today_bookings_model = $pjBookingModel->where("t1.created LIKE '%$current_date%' AND t1.status='confirmed'");
			//if ($this->isEditor() || $this->isBusAdmin()) {
            if ($this->isEditor()) {
				$cnt_today_bookings_model->where('t1.user_id = '.$this->getUserId());	
            }
            if ($this->isBusAdmin()) {
				//$cnt_today_bookings_model->orWhere('(t1.user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
			}
			$cnt_today_bookings = $cnt_today_bookings_model->findCount()->getData();
            $this->set('cnt_today_bookings', $cnt_today_bookings);
            
            $cnt_api_booking_model = $pjBookingModel->reset()->where("t1.created LIKE '%$current_date%' AND t1.is_combined_api = 1 AND t1.status='confirmed'");
            $cnt_api_booking = $cnt_api_booking_model->findCount()->getData();
            $this->set('cnt_api_booking', $cnt_api_booking);

            $cnt_web_booking_model = $pjBookingModel->reset()->where("t1.created LIKE '%$current_date%' AND t1.status='confirmed' AND (t1.is_combined_api = 0 || t1.is_combined_api IS NULL)");
            $cnt_web_booking = $cnt_web_booking_model->findCount()->getData();
            $this->set('cnt_web_booking', $cnt_web_booking);

            $cnt_agent_booking_model = $pjBookingModel->reset()->where("t1.created LIKE '%$current_date%' AND t1.status='confirmed' AND t1.user_id <> 0");
            $cnt_agent_booking = $cnt_agent_booking_model->findCount()->getData();
            $this->set('cnt_agent_booking', $cnt_agent_booking);
                      
			$cnt_today_departure = 0;
			$next_buses_arr = array();
            $date = $current_date;

            $sql = 'SELECT t1.bus_id,t3.content AS route,count(t1.bus_id) AS total_bookings,SUM(t1.total) AS total_amount
            FROM '.$pjBookingModel->getTable().' AS t1
            LEFT JOIN '.$pjBusModel->getTable().' AS t2 ON (t2.id = t1.bus_id)
            LEFT JOIN '.$pjMultiLangModel->getTable().' AS t3 ON (t3.model="pjRoute" AND t3.foreign_id=t2.route_id AND t3.field="title" AND t3.locale="'.$this->getLocaleId().'")
            WHERE t1.created LIKE "%'.$current_date.'%"  AND t1.status="confirmed"
            GROUP BY t2.route_id
            ORDER BY route';

            $route_today_arr = $pjBookingModel->reset()->execute($sql)->getData();
            $this->set('route_today_arr', $route_today_arr);

            $sql = 'SELECT SUM(t1.total) AS total_amount
            FROM '.$pjBookingModel->getTable().' AS t1            
            WHERE t1.created LIKE "%'.$current_date.'%" AND t1.status="confirmed"';            

            $route_total_arr = $pjBookingModel->reset()->execute($sql)->getData();
            $route_total = (isset($route_total_arr[0]['total_amount'])) ? $route_total_arr[0]['total_amount'] : 0;
            $this->set('route_total', $route_total);

            $sql = "SELECT t1.id,t1.name,t1.phone,t1.email,t1.added_by,t1.agent_type,t1.booking_total,t1.commission_percent,t1.booking_total*(t1.commission_percent/100) AS total_commission FROM 
            (
                SELECT tb3.id,tb3.agent_type,tb3.added_by,tb3.name,tb3.phone,tb3.email,tb2.commission_percent,SUM(total) AS booking_total 
                FROM  ".$pjBookingModel->getTable()." AS tb1
                LEFT JOIN ".$pjCreditModel->getTable()." AS tb2 ON (tb2.user_id = tb1.user_id)  
                LEFT JOIN ".$pjUserModel->getTable()." AS tb3 ON (tb3.id = tb1.user_id)              
                WHERE tb1.created LIKE '%$current_date%' AND
                tb1.status='confirmed' AND
                tb1.user_id <> 0 AND
                tb3.role_id = 2
                GROUP BY tb1.user_id
            ) AS t1 ORDER BY booking_total DESC";

            $transaction_arr = $pjBookingModel->reset()->execute($sql)->getData();
            $transactions = array();
            if ($transaction_arr) {
                foreach ($transaction_arr as $k=>$transaction) {
                    if ($transaction['agent_type'] == 4) {
                        $added_by = $pjUserModel->reset()
                        ->find($transaction['added_by'])
                        ->getData();

                        if (isset($transactions[$added_by['id']])) {
                            $transactions[$added_by['id']]['booking_total'] += $transaction['booking_total'];
                            $transactions[$added_by['id']]['total_commission'] += $transaction['total_commission'];
                        } else {
                            $transaction['name'] = $added_by['name'];
                            $transaction['phone'] = $added_by['phone'];
                            $transaction['email'] = $added_by['email'];
                            $transactions[$added_by['id']] = $transaction;
                        }                        
                    } else {
                        $transactions[$transaction['id']] = $transaction;
                    }
                }
            }
            $this->set('agent_bookings', $transactions);
			
			$cnt_routes = $pjRouteModel->findCount()->getData();
			$cnt_buses = $pjBusModel->findCount()->getData();
			
			$next_3_months = strtotime('+3 month', strtotime($date));
            
            $today_bus_ids = array();

			if($cnt_buses > 0)
			{
				while(count($next_buses_arr) < 5 && strtotime($date) < $next_3_months)
				{
					if ($this->isAdmin() || $this->isBusAdmin()) {
						$bus_arr = $pjBusModel
							->reset()
							->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
							->select("t1.*, t2.content AS route,
										(SELECT COUNT(TB.id) 
											FROM `".$pjBookingModel->getTable()."` AS TB 
											WHERE TB.bus_id=t1.id AND TB.booking_date='$date') AS total_bookings,
										(SELECT SUM(TBT.qty) 
											FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT 
											WHERE TBT.booking_id IN 
													(SELECT TB1.id 
													FROM `".$pjBookingModel->getTable()."` AS TB1
													WHERE TB1.bus_id=t1.id AND TB1.booking_date='$date')) AS total_tickets")
							->where("(t1.start_date <= '$date' AND t1.end_date >= '$date')")
							->orderBy("departure_time ASC")
							->findAll()
							->getData();
					} else {
						$bus_arr = $pjBusModel
							->reset()
							->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
							->select("t1.*, t2.content AS route,
										(SELECT COUNT(TB.id) 
											FROM `".$pjBookingModel->getTable()."` AS TB 
                                            WHERE (
                                                TB.bus_id=t1.id AND 
                                                TB.booking_date='$date' AND 
                                                (TB.user_id = ".$this->getUserId()." OR TB.user_id IN (SELECT id FROM ".$pjUserModel->getTable()." WHERE added_by = ".$this->getUserId()."))
                                            )
                                        )AS total_bookings,
										(SELECT SUM(TBT.qty) 
											FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT 
											WHERE TBT.booking_id IN 
													(SELECT TB1.id 
													FROM `".$pjBookingModel->getTable()."` AS TB1
													WHERE TB1.bus_id=t1.id AND TB1.booking_date='$date' AND (TB1.user_id = ".$this->getUserId()." OR TB1.user_id IN (SELECT id FROM ".$pjUserModel->getTable()." WHERE added_by = ".$this->getUserId().")))) AS total_tickets")
							->where("(t1.start_date <= '$date' AND t1.end_date >= '$date')")
                            ->orderBy("departure_time ASC")
							->findAll()
							->getData();	
                    }

					foreach($bus_arr as $v)
					{
						if(empty($v['recurring']))
						{
							if($date == $current_date)
							{
                                $cnt_today_departure++;
                                $today_bus_ids[] = $v['id'];
							}
							if(count($next_buses_arr) < 5 && strtotime(date('Y-m-d H:i:s')) <= strtotime($date . ' ' . $v['departure_time']))
							{
								$v['departure_date'] = $date;
								$next_buses_arr[] = $v;
							}
						}else{
							if(in_array($weekday, explode("|", $v['recurring'])))
							{
								if($date == $current_date)
								{
                                    $cnt_today_departure++;
                                    $today_bus_ids[] = $v['id'];
								}
								if(count($next_buses_arr) < 5 && strtotime(date('Y-m-d H:i:s')) <= strtotime($date . ' ' . $v['departure_time']))
								{
									$v['departure_date'] = $date;
									$next_buses_arr[] = $v;
								}
							}
						}
					}
					$date = date('Y-m-d', strtotime($date . ' + 1 day'));
				}
			}
			$this->set('cnt_today_departure', $cnt_today_departure);
			
			$this->set('cnt_routes', $cnt_routes);
            $this->set('cnt_buses', $cnt_buses);
            
            $bus_today_arr = array();
            $total_bus_amount = 0;
            if ($today_bus_ids) {
                $sql1 = 'SELECT t4.bus_id,count(t4.bus_id) AS total_bookings,SUM(t4.total) AS total_amount
                FROM '.$pjBookingModel->getTable().' AS t4   
                WHERE t4.created LIKE "%'.$current_date.'%"
                AND t4.status="confirmed"
                AND t4.bus_id IN ('.implode(',',$today_bus_ids).')
                GROUP BY t4.bus_id';

                $sql = 'SELECT t1.id,t1.departure_time,t1.arrival_time,t2.content AS route,t3.*             
                FROM '.$pjBusModel->getTable().' AS t1
                LEFT JOIN '.$pjMultiLangModel->getTable().' AS t2 ON (t2.model="pjRoute" AND t2.foreign_id=t1.route_id AND t2.field="title" AND t2.locale="'.$this->getLocaleId().'")
                LEFT JOIN ('.$sql1.') AS t3 ON t3.bus_id = t1.id
                WHERE t1.id IN ('.implode(',',$today_bus_ids).')
                ORDER BY total_bookings DESC,content ASC';

                $bus_today_arr = $pjBookingModel->reset()->execute($sql)->getData();  
                
                $sql = 'SELECT SUM(total_amount) AS total_bus FROM
                    ('.$sql1.') AS t1';

                $bus_today_total_arr = $pjBookingModel->reset()->execute($sql)->getData(); 
                $total_bus_amount = $bus_today_total_arr[0]['total_bus'];
            }

            $this->set('bus_today_arr', $bus_today_arr);
            $this->set('total_bus_amount', $total_bus_amount);
			
			if ($this->isAdmin() || $this->isBusAdmin()) {
				$latest_bookings = $pjBookingModel
					->reset()
					->select("t1.*, (SELECT SUM(TBT.qty) 
									FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT 
									WHERE TBT.booking_id=t1.id)
								AS tickets, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location")
					->join('pjBus', "t2.id=t1.bus_id", 'left outer')
					->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
					->limit(5)
					->orderBy('t1.created DESC')
					->findAll()->getData();
			} else {
				$latest_bookings = $pjBookingModel
					->reset()
					->select("t1.*, (SELECT SUM(TBT.qty) 
									FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT 
									WHERE TBT.booking_id=t1.id)
								AS tickets, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location")
					->join('pjBus', "t2.id=t1.bus_id", 'left outer')
					->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
					->where('t1.user_id = '.$this->getUserId().' OR t1.user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().')')
					->limit(5)
					->orderBy('t1.created DESC')
					->findAll()->getData();	
			}
			
			$this->set('latest_bookings', $latest_bookings);
			$this->set('next_buses_arr', $next_buses_arr);
			
			$this->set('cnt_bookings', $pjBookingModel->reset()->findCount()->getData());
			$this->set('cnt_confirmed_bookings', $pjBookingModel->reset()->where('t1.status', 'confirmed')->findCount()->getData());
			
			if ($this->isAdmin() || $this->isBusAdmin()) {
				$sold_tickets = pjBookingTicketModel::factory()
					->select("SUM(t1.qty) AS tickets")
					->where("t1.booking_id IN (SELECT TB.id FROM `".$pjBookingModel->getTable()."` AS TB WHERE TB.status='confirmed')")
					->findAll()->getData();
			} else {
				$sold_tickets = pjBookingTicketModel::factory()
					->select("SUM(t1.qty) AS tickets")
					->where("t1.booking_id IN (SELECT TB.id FROM `".$pjBookingModel->getTable()."` AS TB WHERE TB.status='confirmed' AND (TB.user_id = ".$this->getUserId()." OR TB.user_id IN (SELECT id FROM ".$pjUserModel->getTable()." WHERE added_by = ".$this->getUserId().")))")
                    ->findAll()->getData();	
			}
			$this->set('sold_tickets', $sold_tickets);

			if ($this->isAdmin() || $this->isBusAdmin()) {
				$total_revenue = pjBookingModel::factory()
					->select("SUM(t1.total) AS revenue")
					->where('t1.status', 'confirmed')
					->findAll()->getData();
			} else {
				$total_revenue = pjBookingModel::factory()
					->select("SUM(t1.total) AS revenue")
					->where('t1.status', 'confirmed')
					->where('(t1.user_id = '.$this->getUserId().' OR t1.user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))')
					->findAll()->getData();	
			}
			$this->set('total_revenue', $total_revenue);
						
			$backup_arr = pjAppController::getBackupInfo();
			$this->set('backup_arr', $backup_arr);
			
			if ($this->isAdmin() || $this->isBusAdmin()) {
				$overlapping_seats = pjBookingSeatModel::factory()
					->join("pjBooking", "t1.booking_id = t2.id", 'inner')
					->select("DISTINCT (GROUP_CONCAT( CONCAT_WS(':', t2.id, t2.uuid)SEPARATOR '~:~' )) AS uuid")
					->where("UNIX_TIMESTAMP(t2.booking_datetime) >= UNIX_TIMESTAMP(NOW())")
					->groupBy("t1.seat_id, t1.start_location_id, t1.end_location_id, t2.booking_date, t2.bus_id")
					->having("count(t1.booking_id) > 1")
					->findAll()
					->toArray('uuid', '~:~')
					->getData();
			} else {
				$overlapping_seats = pjBookingSeatModel::factory()
					->join("pjBooking", "t1.booking_id = t2.id AND (t2.user_id = ".$this->getUserId()." OR (t2.user_id IN (SELECT id FROM ".$pjUserModel->getTable()." WHERE added_by = ".$this->getUserId().")))", 'inner')
					->select("DISTINCT (GROUP_CONCAT( CONCAT_WS(':', t2.id, t2.uuid)SEPARATOR '~:~' )) AS uuid")
					->where("UNIX_TIMESTAMP(t2.booking_datetime) >= UNIX_TIMESTAMP(NOW())")
					->groupBy("t1.seat_id, t1.start_location_id, t1.end_location_id, t2.booking_date, t2.bus_id")
					->having("count(t1.booking_id) > 1")
					->findAll()
					->toArray('uuid', '~:~')
					->getData();	
			}
			$this->set('overlapping_seats', $overlapping_seats);	
		} else {
			$this->set('status', 2);
		}
	}
	
	public function pjActionForgot()
	{
		$this->setLayout('pjActionAdminLogin');
		
		if (isset($_POST['forgot_user']))
		{
			if (!isset($_POST['forgot_email']) || !pjValidation::pjActionNotEmpty($_POST['forgot_email']) || !pjValidation::pjActionEmail($_POST['forgot_email']))
			{
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionForgot&err=AA10");
			}
			$pjUserModel = pjUserModel::factory();
			$user = $pjUserModel
				->where('t1.email', $_POST['forgot_email'])
				->limit(1)
				->findAll()
				->getData();
				
			if (count($user) != 1)
			{
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionForgot&err=AA10");
			} else {
				$user = $user[0];
				
                $Email = new pjEmail();

                $option = pjOptionModel::factory()
				->where('t1.key', 'o_sender_email')
				->limit(1)
				->findAll()
                ->getData();

                $from_email = $this->getAdminEmail();
                if(isset($option[0]['value']) && !empty($option[0]['value'])) {
                    $from_email = $option[0]['value'];
                }

				$Email
					->setTo($user['email'])
					->setFrom($from_email)
					->setSubject(__('emailForgotSubject', true));
				
				if ($this->option_arr['o_send_email'] == 'smtp')
				{
					$Email
						->setTransport('smtp')
						->setSmtpHost($this->option_arr['o_smtp_host'])
						->setSmtpPort($this->option_arr['o_smtp_port'])
						->setSmtpUser($this->option_arr['o_smtp_user'])
						->setSmtpPass($this->option_arr['o_smtp_pass'])
						->setSender($this->option_arr['o_smtp_user'])
					;
				}
				
				$body = str_replace(
					array('{Name}', '{Password}'),
					array($user['name'], $user['password']),
					__('emailForgotBody', true)
				);

				if ($Email->send($body))
				{
					$err = "AA11";
				} else {
					$err = "AA12";
				}
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionForgot&err=$err");
			}
		} else {
			$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
			$this->appendJs('pjAdmin.js');
		}
	}
	
	public function pjActionMessages()
	{
		$this->setAjax(true);
		header("Content-Type: text/javascript; charset=utf-8");
	}
	
	public function pjActionLogin()
	{
		$this->setLayout('pjActionAdminLogin');
		
		if (isset($_POST['login_user']))
		{
			if (!isset($_POST['login_email']) || !isset($_POST['login_password']) ||
				!pjValidation::pjActionNotEmpty($_POST['login_email']) ||
				!pjValidation::pjActionNotEmpty($_POST['login_password']) ||
				!pjValidation::pjActionEmail($_POST['login_email']))
			{
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin&err=4");
			}
			$pjUserModel = pjUserModel::factory();

            $user = $pjUserModel
                ->select('t1.*,t2.role_name,t3.at_type')
                ->join('pjRole', 't2.id=t1.role_id', 'left')
                ->join('pjAgentType', 't3.at_id=t1.agent_type', 'left')
				->where('t1.email', $_POST['login_email'])
				->where(sprintf("t1.password = AES_ENCRYPT('%s', '%s')", pjObject::escapeString($_POST['login_password']), PJ_SALT))
				->limit(1)
				->findAll()
				->getData();

			if (count($user) != 1)
			{
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin&err=1");
			} else {
				$user = $user[0];
				unset($user['password']);
															
				if (!in_array($user['role_id'], array(1,2,3,4,6,10)))
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin&err=2");
				}
				
				if (/*$user['role_id'] == 3 && */$user['is_active'] == 'F')
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin&err=2");
				}
				
				if ($user['status'] != 'T')
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin&err=3");
				}
				
                $last_login = date("Y-m-d H:i:s");
    			$_SESSION[$this->defaultUser] = $user;
    			
    			$data = array();
    			$data['last_login'] = $last_login;
    			$pjUserModel->reset()->setAttributes(array('id' => $user['id']))->modify($data);

    			if ($this->isAdmin() || $this->isEditor() || $this->isBusAdmin() || $this->isInvestor())
    			{
	    			pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionIndex");
                }
                if ($this->isPolice() || $this->isIntelligenceService())
    			{
	    			pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdminSchedule&action=pjActionIndex");
    			}
			}
		} else {
			$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
			$this->appendJs('pjAdmin.js');
		}
	}
	
	public function pjActionLogout()
	{
		if ($this->isLoged())
        {
        	unset($_SESSION[$this->defaultUser]);
        }
       	pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionLogin");
	}
	
	public function pjActionProfile()
	{
		$this->checkLogin();
		
		//if (!$this->isAdmin())
		//{
			if (isset($_POST['profile_update']))
			{
				$pjUserModel = pjUserModel::factory();
				$arr = $pjUserModel->find($this->getUserId())->getData();
				$data = array();
				$data['role_id'] = $arr['role_id'];
				$data['status'] = $arr['status'];
				$post = array_merge($_POST, $data);
				if (!$pjUserModel->validates($post))
				{
					pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionProfile&err=AA14");
				}
                $pjUserModel->set('id', $this->getUserId())->modify($post);                
                
                $_SESSION['admin_user']['email'] = $_POST['email'];
				pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionProfile&err=AA13");
			} else {
				$this->set('arr', pjUserModel::factory()->find($this->getUserId())->getData());
				$this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
				$this->appendJs('pjAdmin.js');
			}
		//} else {
		//	$this->set('status', 2);
		//}
    }
    
    public function pjActionGetCreditPassword()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() || $this->isSuperAgent())
		{
            $is_reset = false;
            $is_sent = false;
            $option_arr = $this->option_arr;
            if (isset($_POST['reset_password'])) {
                $is_reset = true;
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

                $email = ($_SESSION['admin_user']['email']) ? $_SESSION['admin_user']['email'] : '';
                $phone = ($_SESSION['admin_user']['phone']) ? $_SESSION['admin_user']['phone'] : '';

                $user_detail = $pjUserModel = pjUserModel::factory()
                ->where('t1.id =',$this->getUserId())
                ->select ('AES_DECRYPT(t1.credit_password,"'.PJ_SALT.'") AS credit_password')
                ->findAll()
                ->getData();

                if (!$user_detail[0]['credit_password']) {
                    $new_password = '';
                    for($i = 0; $i < 6; $i++) {
                        $new_password .= mt_rand(0, 9);
                    }

                    $result = pjUserModel::factory()->where('id', $this->getUserId())->modifyAll(array(
                        'credit_password' => $new_password
                    ));
                } else {
                    $result = true;
                    $new_password = $user_detail[0]['credit_password'];
                }

                $text = 'Your Current Credit Password with '.PJ_SITE_NAME.' is '.$new_password;
                $message = pjUtil::textToHtml($text);
                $subject = 'Current Credit Password';

                $from_email = $this->getAdminEmail();
                if(!empty($option_arr['o_sender_email'])) {
                    $from_email = $option_arr['o_sender_email'];
                }

                $is_sent = false;
                if ($result) {
                    if ($email) {
                        $result = $Email
                            ->setTo($email)
                            ->setFrom($from_email)
                            ->setSubject($subject)
                            ->send($message);
                            
                        $is_sent = true;
                    }

                    if ($phone) {
                        $params = array(
                            'text' => $text,
                            'type' => 'unicode',
                            'key' => md5($option_arr['private_key'] . PJ_SALT),
                            'number' => $phone
                        );

                        $this->requestAction(array('controller' => 'pjSendSms', 'action' => 'pjActionSendSms', 'params' => $params), array('return'));

                        $is_sent = true;
                    }
                }
            }
            
            $this->set('arr', pjUserModel::factory()->find($this->getUserId())->getData());
            $this->set('is_reset', $is_reset);
            $this->set('is_sent', $is_sent);
            $this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
            $this->appendJs('pjAdmin.js?'.PJ_CSS_JS_VERSION);
		} else {
			$this->set('status', 2);
		}
	}

    public function pjActionCreditPassword()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() || $this->isSuperAgent())
		{
            $is_reset = false;
            $is_sent = false;
            $option_arr = $this->option_arr;
			if (isset($_POST['password_update']))
			{
                $current_password = trim($_POST['current_password']);
                $new_password = trim($_POST['new_password']);

                $pjUserModel = pjUserModel::factory();
                $arr = $pjUserModel->find($this->getUserId())->getData();
                
                if (!$new_password) {
                    pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionCreditPassword&err=ACP01");
                } else if ($arr['credit_password'] && !$current_password) {
                    pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionCreditPassword&err=ACP02");
                } else {
                    if ($arr['credit_password']) {
                        $user = pjUserModel::factory()
                        ->where('t1.id', $this->getUserId())
                        ->where(sprintf("t1.credit_password = AES_ENCRYPT('%s', '%s')", pjObject::escapeString($_POST['current_password']), PJ_SALT))
                        ->limit(1)
                        ->findAll()
                        ->getData();

                        if (count($user) != 1) {
                            pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionCreditPassword&err=ACP02");
                        }
                    }

                    $result = pjUserModel::factory()->where('id', $this->getUserId())->modifyAll(array(
                        'credit_password' => $new_password
                    ));

                    pjUtil::redirect($_SERVER['PHP_SELF'] . "?controller=pjAdmin&action=pjActionCreditPassword&err=ACP03");
                    exit;
                }	
			} 
            
            $this->set('arr', pjUserModel::factory()->find($this->getUserId())->getData());
            $this->set('is_reset', $is_reset);
            $this->set('is_sent', $is_sent);
            $this->appendJs('jquery.validate.min.js', PJ_THIRD_PARTY_PATH . 'validate/');
            $this->appendJs('pjAdmin.js?'.PJ_CSS_JS_VERSION);
		} else {
			$this->set('status', 2);
		}
	}
}
?>