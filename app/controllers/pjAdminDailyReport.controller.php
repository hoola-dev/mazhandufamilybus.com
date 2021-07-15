<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjAdminDailyReport extends pjAdmin
{	
	public function pjActionIndex()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() || $this->isSuperAgent() || $this->isInvestor())
		{
            if (isset($_POST['report_date'])) {
                $report_date = pjUtil::formatDate($_POST['report_date'], $this->option_arr['o_date_format']);
            } else {
                $report_date = date('Y-m-d');
            }
            
            $pjUserModel = pjUserModel::factory();
            $pjCreditModel = pjCreditModel::factory();

            $pjBookingModel = pjBookingModel::factory()                
				->join('pjBus', "t2.id=t1.bus_id", 'left outer')
				->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
				->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                ->join('pjUser', "t6.id=t1.user_id", 'left outer');
			
			if ($this->isSuperAgent()) {
				$pjBookingModel->where('(user_id = '.$this->getUserId().' OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$this->getUserId().'))');	
            }

            $booking_arr = $pjBookingModel
				->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location,t6.name,t6.added_by,t6.agent_type')
                ->where('DATE(t1.created) = "'.$report_date.'"')
                ->where('t1.status','confirmed')
                ->where('user_id != 0')
                ->where('role_id',2)
                ->orderBy("created asc")
				->findAll()
                ->getData();

            $booking_ids = array();
            if ($booking_arr) {
                foreach ($booking_arr as $k=>$booking) {
                    $booking_ids[] = $booking['id'];
                    if ($this->isBusAdmin() && $booking['agent_type'] == 4) {
                        $added_by = $pjUserModel->reset()
                        ->find($booking['added_by'])
                        ->getData();

                        $booking_arr[$k]['name'] = $added_by['name'];
                    }
                }
            }            

            if ($booking_ids) {
                $sql = "SELECT t1.id,t1.name,t1.phone,t1.email,t1.added_by,t1.agent_type,t1.booking_total,t1.commission_percent,t1.booking_total*(t1.commission_percent/100) AS total_commission FROM 
                (
                    SELECT tb3.id,tb3.agent_type,tb3.added_by,tb3.name,tb3.phone,tb3.email,tb2.commission_percent,SUM(total) AS booking_total 
                    FROM  ".$pjBookingModel->getTable()." AS tb1
                    LEFT JOIN ".$pjCreditModel->getTable()." AS tb2 ON (tb2.user_id = tb1.user_id)  
                    LEFT JOIN ".$pjUserModel->getTable()." AS tb3 ON (tb3.id = tb1.user_id)              
                    WHERE tb1.id IN (".implode(',',$booking_ids).") 
                    GROUP BY tb1.user_id
                ) AS t1 ORDER BY booking_total DESC";

                $transaction_arr = $pjBookingModel->reset()->execute($sql)->getData();
                $transactions = array();
                if ($transaction_arr) {
                    foreach ($transaction_arr as $k=>$transaction) {
                        if ($this->isBusAdmin() && $transaction['agent_type'] == 4) {
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
            } else {
                $transactions = array();
            }

            $this->appendJs('jquery-ui-timepicker-addon.js', PJ_THIRD_PARTY_PATH . 'datetimepicker/');
            $this->appendCss('jquery-ui-timepicker-addon.css', PJ_THIRD_PARTY_PATH . 'datetimepicker/');
            $this->appendJs('pjAdminDailyReport.js');
                
            $this->set('booking_arr', $booking_arr);
            $this->set('transactions', $transactions);
            $this->set('option_arr', $this->option_arr);
            $this->set('report_date', $report_date);
		} else {
			$this->set('status', 2);
		}
    }	
    
    public function pjActionAgent()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() || $this->isInvestor())
		{
            if (isset($_POST['start_date'])) {
                $start_date = pjUtil::formatDate($_POST['start_date'], $this->option_arr['o_date_format']);
                $end_date = pjUtil::formatDate($_POST['end_date'], $this->option_arr['o_date_format']);
                if (!($_POST['end_date']) || $start_date >= $end_date) {
                    $end_date = $start_date;
                }
            } else {
                $start_date = date('Y-m-d');
                $end_date = date('Y-m-d');
            }

            $agent_id = (isset($_POST['agent_id'])) ? $_POST['agent_id'] : 0;

            $pjUserModel = pjUserModel::factory();
            $pjCreditModel = pjCreditModel::factory();

            $agents = $pjUserModel->select('t1.id,t1.name,t1.agent_type')
            ->where('t1.role_id', 2)
            ->where('t1.agent_type !=',4)
            ->orderBy("name")
            ->findAll()
            ->getData();;

            if ($agents && !$agent_id) {
                $agent_id = $agents[0]['id'];
            }

            $agent_detail = pjUserModel::factory()
                ->find($agent_id)
                ->getData();

            $name = ($agent_detail) ? $agent_detail['name'] : '';

            $pjBookingModel = pjBookingModel::factory()                
				->join('pjBus', "t2.id=t1.bus_id", 'left outer')
				->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
				->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                ->join('pjUser', "t6.id=t1.user_id", 'left outer');
			
			if ($agent_detail['agent_type'] == 2) {
				$pjBookingModel->where('(user_id = '.$agent_id.' OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$agent_id.'))');	
            } else {
                $pjBookingModel->where('user_id',$agent_id);
            }

            $booking_arr = $pjBookingModel
				->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location,t6.name,t6.added_by,t6.agent_type')
                ->where('DATE(t1.created) BETWEEN "'.$start_date.'" AND "'.$end_date.'"')
                ->where('t1.status','confirmed')
                ->orderBy("created asc")
				->findAll()
                ->getData(); 

            $this->appendJs('jquery-ui-timepicker-addon.js', PJ_THIRD_PARTY_PATH . 'datetimepicker/');
            $this->appendCss('jquery-ui-timepicker-addon.css', PJ_THIRD_PARTY_PATH . 'datetimepicker/');
            $this->appendJs('pjAdminDailyReport.js');
                
            $this->set('name', $name);
            $this->set('agents', $agents);
            $this->set('agent_id', $agent_id);
            $this->set('agent_detail', $agent_detail);
            $this->set('booking_arr', $booking_arr);
            $this->set('option_arr', $this->option_arr);
            $this->set('start_date', $start_date);
            $this->set('end_date', $end_date);
		} else {
			$this->set('status', 2);
		}
    }

    public function pjActionRoute()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() || $this->isInvestor())
		{
            if (isset($_POST['start_date'])) {
                $start_date = pjUtil::formatDate($_POST['start_date'], $this->option_arr['o_date_format']);
                $end_date = pjUtil::formatDate($_POST['end_date'], $this->option_arr['o_date_format']);
                if (!($_POST['end_date']) || $start_date >= $end_date) {
                    $end_date = $start_date;
                }
            } else {
                $start_date = date('Y-m-d');
                $end_date = date('Y-m-d');
            }

            $route_id = (isset($_POST['route_id'])) ? $_POST['route_id'] : 0;

            $routes = pjRouteModel::factory()
                ->select(" t1.id, t1.status, t2.content as title, t3.content as `from`, t4.content as `to`")
                ->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t1.id AND t3.field='from' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                ->join('pjMultiLang', "t4.model='pjRoute' AND t4.foreign_id=t1.id AND t4.field='to' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                ->orderBy("title")
				->findAll()
                ->getData();

            if ($routes && !$route_id) {
                $route_id = $routes[0]['id'];
            }

            $route_detail = pjRouteModel::factory()
            ->select(" t1.id, t1.status, t2.content as title, t3.content as `from`, t4.content as `to`")
            ->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
            ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t1.id AND t3.field='from' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
            ->join('pjMultiLang', "t4.model='pjRoute' AND t4.foreign_id=t1.id AND t4.field='to' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
            ->find($route_id)
            ->getData();

            $name = ($route_detail) ? $route_detail['title'] : '';

            $pjBusModel = pjBusModel::factory();

            $booking_arr = pjBookingModel::factory()   
                ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location,t6.name,t6.added_by,t6.agent_type')             
				->join('pjBus', "t2.id=t1.bus_id", 'left outer')
				->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
				->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                ->join('pjUser', "t6.id=t1.user_id", 'left outer')				
                ->where('DATE(t1.created) BETWEEN "'.$start_date.'" AND "'.$end_date.'"')
                ->where('bus_id IN (SELECT id FROM '.$pjBusModel->getTable().' WHERE route_id = '.$route_id.')')
                ->where('t1.status','confirmed')
                ->orderBy("created asc")
				->findAll()
                ->getData();             
            
            $this->appendJs('jquery-ui-timepicker-addon.js', PJ_THIRD_PARTY_PATH . 'datetimepicker/');
            $this->appendCss('jquery-ui-timepicker-addon.css', PJ_THIRD_PARTY_PATH . 'datetimepicker/');
            $this->appendJs('pjAdminDailyReport.js');
                
            $this->set('name', $name);
            $this->set('routes', $routes);
            $this->set('route_id', $route_id);
            $this->set('route_detail', $route_detail);
            $this->set('booking_arr', $booking_arr);
            $this->set('option_arr', $this->option_arr);
            $this->set('start_date', $start_date);
            $this->set('end_date', $end_date);
		} else {
			$this->set('status', 2);
		}
    }

    public function pjActionPaymentMethod()
	{
		$this->checkLogin();
		
		if ($this->isAdmin() || $this->isBusAdmin() || $this->isInvestor())
		{
            if (isset($_POST['start_date'])) {
                $start_date = pjUtil::formatDate($_POST['start_date'], $this->option_arr['o_date_format']);
                $end_date = pjUtil::formatDate($_POST['end_date'], $this->option_arr['o_date_format']);
                if (!($_POST['end_date']) || $start_date >= $end_date) {
                    $end_date = $start_date;
                }
            } else {
                $start_date = date('Y-m-d');
                $end_date = date('Y-m-d');
            }

            $payment_method = (isset($_POST['payment_method'])) ? $_POST['payment_method'] : '';

            $payment_methods = array();
            foreach (__('payment_methods', true, false) as $k => $v) {
                $payment_methods[$k] = $v;
            }

            if ($payment_methods && !$payment_method) {
                $payment_method = array_key_first($payment_methods);                               
            }   
            
            $name = (isset($payment_methods[$payment_method])) ? $payment_methods[$payment_method] : ''; 

            $booking_arr = pjBookingModel::factory()   
                ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location,t6.name,t6.added_by,t6.agent_type')             
				->join('pjBus', "t2.id=t1.bus_id", 'left outer')
				->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
				->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                ->join('pjUser', "t6.id=t1.user_id", 'left outer')				
                ->where('DATE(t1.created) BETWEEN "'.$start_date.'" AND "'.$end_date.'"')
                ->where('t1.status','confirmed')
                ->where('payment_method',$payment_method)
                ->orderBy("created asc")
				->findAll()
                ->getData();             
            
            $this->appendJs('jquery-ui-timepicker-addon.js', PJ_THIRD_PARTY_PATH . 'datetimepicker/');
            $this->appendCss('jquery-ui-timepicker-addon.css', PJ_THIRD_PARTY_PATH . 'datetimepicker/');
            $this->appendJs('pjAdminDailyReport.js');
                
            $this->set('name', $name);
            $this->set('payment_methods', $payment_methods);
            $this->set('payment_method', $payment_method);
            $this->set('booking_arr', $booking_arr);
            $this->set('option_arr', $this->option_arr);
            $this->set('start_date', $start_date);
            $this->set('end_date', $end_date);
		} else {
			$this->set('status', 2);
		}
    }
}
?>