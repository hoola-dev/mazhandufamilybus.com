<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
use ReallySimpleJWT\Token;
class pjAdminApp extends pjFront
{
	private $jwt_secret = 'maz!ReT423*&';

	    /*
     * Api for agent login
     */
    public function pjActionLogin(){




        $email = trim($_GET['email']);
        $password = trim($_GET['password']);
        $pjUserModel = pjUserModel::factory();



  





        $user = $pjUserModel
            ->select('t1.*')
            ->where('t1.email', $email)
            ->where(sprintf("t1.password = AES_ENCRYPT('%s', '%s')", pjObject::escapeString($password), PJ_SALT))
            ->limit(1)
            ->findAll()
            ->getData();





        if(!empty($user[0])){
            $user = $user[0];
            $data = array();
            //generate jwt token for api authentication
            $userId = $user['id'];
            $expiration = time() + 86400;
            $issuer = 'mfb';
            try {
                $token = Token::create($userId, $this->jwt_secret, $expiration, $issuer);
            } catch (Exception $e) {
                $response = array(
                    'status' => 'false',
                    'message' => $e->getMessage()
                ); 
            }
            
            $data['token'] = $token;
            $data['agent_name'] = $user['name'];
            $sub_agent_code = $user['user_code'];
            $data['agent_id'] = $sub_agent_code;
            $data['agent_user_id'] = $user['id'];
            $response = array(
               'status' => 'true',
               'data' => $data
            );
        }else{
           $response = array(
                'status' => 'false',
                'message' => 'Invalid login'
           );    
        } 
        pjAppController::jsonResponse($response); 

    }




    public function pjActionRouteReport() {     

        $data = array();
		$return = pjRouteModel::factory()
				->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t1.id AND t3.field='from' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
				->join('pjMultiLang', "t4.model='pjRoute' AND t4.foreign_id=t1.id AND t4.field='to' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
				->select(" t1.id, t1.status, t2.content as title, t3.content as `from`, t4.content as `to`")
				->orderBy("title ASC")->findAll()->getData();

        if(isset($return[0]['id']) && !empty($return[0]['id'])){
            $data = $return;
            $status = true;  
        }else{
            $data = array(
                    'message' => 'No data found'
                );
            $status = false;  
        }
        $response = array(
            'status' => $status,
            'data' => $data
        );    
        pjAppController::jsonResponse($response);
    }	


	public function pjActionRouteView() {




			// $route = $_POST["route"];
            
            $pjUserModel = pjUserModel::factory();
			// $route_id = $_POST["route_id"];
			$route_id = 45;
			// $route_time_scale = $_POST["route_time_scale"];
			$route_time_scale = "uptodate";

			$user_id = 91;



				// 		$data = pjRouteModel::factory()
				// ->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				// ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t1.id AND t3.field='from' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
				// ->join('pjMultiLang', "t4.model='pjRoute' AND t4.foreign_id=t1.id AND t4.field='to' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
				// ->select(" t1.id, t1.status, t2.content as title, t3.content as `from`, t4.content as `to`")
				// ->where("t2.content = '$route'")->findAll()->getData();

				// foreach($data as $k => $v)
				// {
				// 	$route_id = $v['id'];
				// }





// this->checkLogin();
		
		// if ($this->isAdmin() || $this->isBusAdmin() || $this->isEditor() || $this->isInvestor())
		// {










			$this->setLayout('pjActionReport');
            
            $pjUserModel = pjUserModel::factory();

			$route_id = $route_id;
			
			$location_arr = pjRouteCityModel::factory()
				->select("t1.*, t2.content as name")
				->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.city_id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->where('route_id', $route_id)
				->orderBy("t1.order ASC")
				->findAll()->getData();
			$this->set('location_arr', $location_arr);
			
			$pjBusDateModel = pjBusDateModel::factory();
			$pjBookingModel = pjBookingModel::factory();
			$pjBookingTicketModel = pjBookingTicketModel::factory();
			
			$bus_arr = pjBusModel::factory()->where('route_id', $route_id)->findAll()->getData();
			
			$total_travels = 0;
			$total_bookings = 0;
			$total_tickets = 0;
			$total_income = 0;
			$route_trips = array();
			$timetable_arr = array();
			
			$bus_id_arr = array();
			$buses_not_operating_days_arr = array();
			$buses_not_operating_arr = array();
			
			$buses_total_bookings_arr = array();
			$buses_ticket_arr = array();
			$buses_income_arr = array();
			
			$buses_weekday_total_bookings_arr = array();
			$buses_weekday_ticket_arr = array();
			$buses_weekday_income_arr = array();

			foreach($bus_arr as $k => $v)
			{
				$bus_id_arr[] = $v['id'];
			}
			
			$days = __('days', true, false);
			$days[7] = $days[0];
			unset($days[0]);
			
			if(!empty($bus_id_arr))
			{
				$date_where_str = null;
				$booking_date_where_str = null;
				if($route_time_scale == 'period')
				{
					$start_date = pjUtil::formatDate($_POST['route_start_date'], $this->option_arr['o_date_format']);
					$end_date = pjUtil::formatDate($_POST['route_end_date'], $this->option_arr['o_date_format']);
					$date_where_str = "(t1.date BETWEEN '$start_date' AND '$end_date')";
					$booking_date_where_str = "(booking_date BETWEEN '$start_date' AND '$end_date')";
				}else{
					$end_date = date('Y-m-d');
					$date_where_str = "(t1.date <= '$end_date')";
					$booking_date_where_str = "(booking_date <= '$end_date')";
				}
				$temp_not_operating_days_arr = $pjBusDateModel->reset()->whereIn('t1.bus_id', $bus_id_arr)->where($date_where_str)->findAll()->getData();
				$temp_not_operating_arr = $pjBusDateModel->reset()->whereIn('t1.bus_id', $bus_id_arr)->where($date_where_str)->findAll()->getData();
				
				foreach($temp_not_operating_days_arr as $k => $v)
				{
					if(!isset($buses_not_operating_days_arr[$v['bus_id']]))
					{
						$buses_not_operating_days_arr[$v['bus_id']] = 1;
					}else{
						$buses_not_operating_days_arr[$v['bus_id']] += 1;
					}
				}
				foreach($temp_not_operating_arr as $k => $v)
				{
					$buses_not_operating_arr[$v['bus_id']][$v['date']] = $v['date'];
				}
				
				$pjBookingModel
					->reset()
					->select("COUNT(t1.id) AS cnt, t1.bus_id, DAYOFWEEK(booking_date) AS weekday")
					->where('t1.status', 'confirmed')
					->whereIn('t1.bus_id', $bus_id_arr)
                    ->where($booking_date_where_str);
                    
                if ($this->isEditor()) {
                    $pjBookingModel->where('(t1.user_id = '.$user_id.' OR t1.user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$user_id.'))');	
                }
                // if ($this->isBusAdmin()) {                    
                    $pjBookingModel->where('(t1.user_id = '.$user_id.' OR t1.user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$user_id.') OR t1.user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$user_id.'))  OR t1.user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id IN (1,3))))');	                    
                // }

				$temp_buses_total_bookings_arr = $pjBookingModel->groupBy("t1.bus_id, DAYOFWEEK(booking_date)")
					->findAll()->getData();
				
				$pjBookingTicketModel
					->reset()
					->select("SUM(qty) AS total_tickets, t2.bus_id, DAYOFWEEK(booking_date) AS weekday")
					->join("pjBooking", 't1.booking_id=t2.id', 'left')
					->where('t2.status', 'confirmed')
					->whereIn('t2.bus_id', $bus_id_arr)
                    ->where($booking_date_where_str);
                    
                if ($this->isEditor()) {
                    $pjBookingTicketModel->where('(t2.user_id = '.$user_id.' OR t2.user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$user_id.'))');	
                }
                // if ($this->isBusAdmin()) {                    
                    $pjBookingModel->where('(t2.user_id = '.$user_id.' OR t2.user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$user_id.') OR t2.user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$user_id.'))  OR t2.user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id IN (1,3))))');	                    
                // }

				$temp_buses_ticket_arr = $pjBookingTicketModel->groupBy("t2.bus_id, DAYOFWEEK(booking_date)")
					->findAll()->getData();
				
				$pjBookingModel
					->reset()
					->select("SUM(total) AS total_income, t1.bus_id, DAYOFWEEK(booking_date) AS weekday")
					->where('t1.status', 'confirmed')
					->whereIn('t1.bus_id', $bus_id_arr)
                    ->where($booking_date_where_str);

                if ($this->isEditor()) {
                    $pjBookingModel->where('(t1.user_id = '.$user_id.' OR t1.user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$user_id.'))');	
                } 
                // if ($this->isBusAdmin()) {                    
                    $pjBookingModel->where('(t1.user_id = '.$user_id.' OR t1.user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$user_id.') OR t1.user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$user_id.'))  OR t1.user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id IN (1,3))))');	                    
                // }  
                
				$temp_buses_income_arr = $pjBookingModel->groupBy("t1.bus_id, DAYOFWEEK(booking_date)")
					->findAll()->getData();
				
				$pjBookingModel
					->reset()
					->select("t1.bus_id, t1.pickup_id, t1.return_id, t1.total, (SELECT SUM(TBT.qty) FROM `".$pjBookingTicketModel->getTable()."` AS TBT WHERE TBT.booking_id = t1.id) as tickets")
					->where('t1.status', 'confirmed')
                    ->whereIn('t1.bus_id', $bus_id_arr)
                    ->where($booking_date_where_str);
                    
                if ($this->isEditor()) {
                    $pjBookingModel->where('(t1.user_id = '.$user_id.' OR t1.user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$user_id.'))');	
                } 
                // if ($this->isBusAdmin()) {                    
                    $pjBookingModel->where('(t1.user_id = '.$user_id.' OR t1.user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$user_id.') OR t1.user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$user_id.'))  OR t1.user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id = 2 AND agent_type = 3 AND added_by IN(SELECT id FROM '.$pjUserModel->getTable().' WHERE role_id IN (1,3))))');	                    
                // } 

				$temp_buses_route_strips_arr = $pjBookingModel->findAll()->getData();
				
				foreach($temp_buses_route_strips_arr as $v)
				{
					$route_trips['tickets'][$v['pickup_id'] . '-' . $v['return_id']][] = $v['tickets'];
					$route_trips['total'][$v['pickup_id'] . '-' . $v['return_id']][] = $v['total'];
				}
				
				$php_week_days = pjUtil::phpWeekDays();
				
				foreach($temp_buses_total_bookings_arr as $k => $v)
				{
					if(!isset($buses_total_bookings_arr[$v['bus_id']]))
					{
						$buses_total_bookings_arr[$v['bus_id']] = $v['cnt'];
					}else{
						$buses_total_bookings_arr[$v['bus_id']] += $v['cnt'];
					}
					$buses_weekday_total_bookings_arr[$v['bus_id']][$php_week_days[$v['weekday']]] = $v['cnt'];
				}
				foreach($temp_buses_ticket_arr as $k => $v)
				{
					if(!isset($buses_ticket_arr[$v['bus_id']]))
					{
						$buses_ticket_arr[$v['bus_id']] = $v['total_tickets'];
					}else{
						$buses_ticket_arr[$v['bus_id']] += $v['total_tickets'];
					}
					$buses_weekday_ticket_arr[$v['bus_id']][$php_week_days[$v['weekday']]] = $v['total_tickets'];
				}
				foreach($temp_buses_income_arr as $k => $v)
				{
					if(!isset($buses_income_arr[$v['bus_id']]))
					{
						$buses_income_arr[$v['bus_id']] = $v['total_income'];
					}else{
						$buses_income_arr[$v['bus_id']] += $v['total_income'];
					}
					$buses_weekday_income_arr[$v['bus_id']][$php_week_days[$v['weekday']]] = $v['total_income'];
				}
			}
			
			foreach($bus_arr as $k => $v)
			{
				$_total_travels = 0;
				$_total_bookings = 0;
				$_total_tickets = 0;
				$_total_income = 0;
								
				$start_date = $end_date = null;
				
				if($route_time_scale == 'period')
				{
					$_start_date = pjUtil::formatDate($_POST['route_start_date'], $this->option_arr['o_date_format']);
					$_end_date = pjUtil::formatDate($_POST['route_end_date'], $this->option_arr['o_date_format']);
					
					if(($_start_date >= $v['start_date'] && $_start_date <= $v['end_date']) && ($_end_date >= $v['start_date'] && $_end_date <= $v['end_date']))
					{
						$start_date = $_start_date;
						$end_date = $_end_date;
					}else if(($_start_date >= $v['start_date'] && $_start_date <= $v['end_date']) && ($_end_date > $v['end_date'])){
						$start_date = $_start_date;
						$end_date = $v['end_date'];
					}else if(($_end_date >= $v['start_date'] && $_end_date <= $v['end_date']) && ($_start_date < $v['start_date'])){
						$start_date = $v['start_date'];
						$end_date = $_end_date;
					}
				}else{
					$current_date = date('Y-m-d');
					if($current_date >= $v['start_date'] && $current_date <= $v['end_date'])
					{
						$start_date = $v['start_date'];
						$end_date = $current_date;
					}
				}

				if($start_date != null && $end_date != null)
				{
					if(empty($v['recurring']))
					{
						$weekday_arr = array();
						$number_of_days = pjUtil::calDays($start_date, $end_date);
						$_total_travels = $number_of_days - (isset($buses_not_operating_days_arr[$v['id']]) ? (int) $buses_not_operating_days_arr[$v['id']] : 0);
						$run_date = $start_date;
						while($run_date <= $end_date)
						{
							$number_day = strtolower(date("N", strtotime($run_date)));
							isset($timetable_arr[$number_day]['travels']) ? $timetable_arr[$number_day]['travels']++ : $timetable_arr[$number_day]['travels'] = 1;
							$run_date = date('Y-m-d', strtotime($run_date . '+1 days'));
							$weekday_arr[$number_day][] = $run_date;
						}
						foreach($days as $key => $val)
						{
							if(array_key_exists($key, $weekday_arr))
							{
								isset($timetable_arr[$key]['buses']) ? $timetable_arr[$key]['buses']++ : $timetable_arr[$key]['buses'] = 1;
							}
						}
					}else{
						$not_operating_arr = isset($buses_not_operating_arr[$v['id']]) ? $buses_not_operating_arr[$v['id']] : array();
						$recurring_arr = explode("|", $v['recurring']);
						$run_date = $start_date;
						while($run_date <= $end_date)
						{
							$week_day = strtolower(date("l", strtotime($run_date)));
							$number_day = strtolower(date("N", strtotime($run_date)));
							if(in_array($week_day, $recurring_arr) && !in_array($run_date, $not_operating_arr))
							{
								$_total_travels++;
								isset($timetable_arr[$number_day]['travels']) ? $timetable_arr[$number_day]['travels']++ : $timetable_arr[$number_day]['travels'] = 1;
							}
							$run_date = date('Y-m-d', strtotime($run_date . '+1 days'));
						}
						
						foreach($days as $key => $val)
						{
							if(in_array($val, $recurring_arr))
							{
								isset($timetable_arr[$key]['buses']) ? $timetable_arr[$key]['buses']++ : $timetable_arr[$key]['buses'] = 1;
							}
						}
					}
					
					$_total_bookings = isset($buses_total_bookings_arr[$v['id']]) ? $buses_total_bookings_arr[$v['id']] : 0;
					$_total_tickets = isset($buses_ticket_arr[$v['id']]) ? $buses_ticket_arr[$v['id']] : 0;
					$_total_income = isset($buses_income_arr[$v['id']]) ? $buses_income_arr[$v['id']] : 0;
					
				}
				
				foreach($days as $key => $val)
				{
					$cnt_bookings = isset($buses_weekday_total_bookings_arr[$v['id']][$key]) ? $buses_weekday_total_bookings_arr[$v['id']][$key] : 0;
					isset($timetable_arr[$key]['bookings']) ? $timetable_arr[$key]['bookings'] += $cnt_bookings : $timetable_arr[$key]['bookings'] = $cnt_bookings; 
					$weekday_total_tickets = isset($buses_weekday_ticket_arr[$v['id']][$key]) ? (int) $buses_weekday_ticket_arr[$v['id']][$key] : 0;
					isset($timetable_arr[$key]['tickets']) ? $timetable_arr[$key]['tickets'] += $weekday_total_tickets : $timetable_arr[$key]['tickets']= $weekday_total_tickets;
					$weekday_total_income = isset($buses_weekday_income_arr[$v['id']][$key]) ? (float) $buses_weekday_income_arr[$v['id']][$key] : 0;
					isset($timetable_arr[$key]['total']) ? $timetable_arr[$key]['total'] += $weekday_total_income : $timetable_arr[$key]['total'] = $weekday_total_income;
				}
				
				$total_travels += $_total_travels;
				$total_bookings += $_total_bookings;
				$total_tickets += $_total_tickets;
				$total_income += $_total_income;
				
				$bus_arr[$k]['total_travels'] = $_total_travels;
				$bus_arr[$k]['total_bookings'] = $_total_bookings;
				$bus_arr[$k]['total_tickets'] = $_total_tickets;
				$bus_arr[$k]['total_income'] = $_total_income;
			}
			// $this->set('bus_arr', $bus_arr);
			
			// $this->set('total_travels', $total_travels);
			// $this->set('total_bookings', $total_bookings);
			// $this->set('total_tickets', $total_tickets);
			// $this->set('total_income', $total_income);
			// $this->set('route_trips', $route_trips);
			// $this->set('timetable_arr', $timetable_arr);
			// $this->set('days', $days);
			
			// $this->appendJs('pjAdminReports.js');


									$response = array(
            'status' => "true",
            'location_arr' => $location_arr,
            'bus_arr' => $bus_arr,
            'total_travels' => $total_travels,
            'total_bookings' => $total_bookings,
            'total_income' => $total_income,
            'total_tickets' => $total_tickets,
            'timetable_arr' => $timetable_arr,
            'route_trips' => $route_trips,
            'days' => $days,
            
        	); 


			pjAppController::jsonResponse($response);

			return;



    }
    	
}