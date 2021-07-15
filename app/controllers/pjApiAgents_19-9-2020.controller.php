<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
use ReallySimpleJWT\Token;

class pjApiAgents extends pjFront
{
    
    private $jwt_secret = 'maz!ReT423*&';
	public function pjActionAgents() {
        $district_id = (isset($_GET['district_id']) && !empty(trim($_GET['district_id']))) ? trim($_GET['district_id']) : 0;
        $districts = pjDistrictModel::factory()->orderBy('t1.dt_name ASC')->findAll()->getData();
        $data = array();
        if(isset($districts[0]['dt_id']) && !empty($districts[0]['dt_id'])){
            $district_id = (isset($_GET['district_id']) && !empty(trim($_GET['district_id']))) ? trim($_GET['district_id']) : $districts[0]['dt_id'];
            $data = pjUserModel::factory()
            ->select('id, name, email, phone, address, zipcode, district')
            ->where('role_id', 2)
            ->where('status','T')
            ->where('district',$district_id)
            ->limit(15)
            ->findAll()
            ->getData(); 
             if(!isset($data[0]['id'])){
                 $data = array(
                    'message' => 'No agents found'
                );
                $status = false; 
             }else{
                $status = true; 
             } 
             
        }else{
            $data = array(
                    'message' => 'No agents found'
                );
            $status = false;  
        }
        $response = array(
            'status' => $status,
            'data' => $data
        );    
        pjAppController::jsonResponse($response);
    }

    /*
     * Api for agent login
     */
    public function pjActionLogin(){
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $pjUserModel = pjUserModel::factory();
        $user = $pjUserModel
            ->select('t1.*,t2.role_name,t3.at_type')
            ->join('pjRole', 't2.id=t1.role_id', 'left')
            ->join('pjAgentType', 't3.at_id=t1.agent_type', 'left')
            ->where('t1.email', $email)
            ->where('t2.id', 2)
            ->where(sprintf("t1.password = AES_ENCRYPT('%s', '%s')", pjObject::escapeString($password), PJ_SALT))
            ->limit(1)
            ->findAll()
            ->getData();
        if(!empty($user[0])){
            $user = $user[0];
            $data = array();
            //generate jwt token for api authentication
            $userId = $user['id'];
            $expiration = time() + 3600;
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

    public function pjActionMyBookings(){
        $agent_user_id = (isset($_POST['agent_user_id']) && trim($_POST['agent_user_id'])) ? trim($_POST['agent_user_id']) : '';
        $token = (isset($_POST['token']) && trim($_POST['token'])) ? trim($_POST['token']) : '';
        $status = (isset($_POST['status']) && trim($_POST['status'])) ? trim($_POST['status']) : '';
        $status = (isset($status) && $status == 'all') ? '' : $status;

        //for search
        $srch = (isset($_POST['search']) && trim($_POST['search'])) ? trim($_POST['search']) : '';
        $from_date = (isset($_POST['from_date']) && trim($_POST['from_date'])) ? trim($_POST['from_date']) : '';

        if(empty($agent_user_id) || empty($token)){
            $resp = array(
                        'status' => 'false',
                        'message' => 'Please provide all parameters'
                    );
            pjAppController::jsonResponse($resp);  
        }

        //token validation check
        $token_validation_result = Token::validate($token, $this->jwt_secret);
        if($token_validation_result){
            //get selected agent's bookings
            $pjBookingModel = pjBookingModel::factory()                
                ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer');
            if (isset($status) && !empty($status) && in_array($status, array('confirmed','cancelled','pending')))
            {
                $pjBookingModel->where('t1.status', $status);
            }

            //for search
            if (isset($srch) && !empty($srch))
            {
                
                $pjBookingModel->where("(CONCAT(t1.c_fname,' ',t1.c_lname) = '$srch' OR
                  t1.c_fname like '%$srch%' OR t1.c_lname like '%$srch%' OR CONCAT(t1.c_fname,' ',t1.c_lname) LIKE '%$srch%' OR t1.c_phone LIKE '%$srch%' OR t1.uuid like '%$srch%')");
            }
            if (isset($from_date) && !empty($from_date))
            {
                $pjBookingModel->where("(DATE(t1.booking_datetime) >= '$from_date')");
            }
            

            $pjUserModel = pjUserModel::factory();
            $agentsBookings = $pjBookingModel->where('(user_id = '.$agent_user_id.' OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$agent_user_id.'))')
            ->orderBy("t1.id DESC")
            ->findAll()
            ->debug(true)
            ->getData();
            $respArr = array();
            if(count($agentsBookings)){
                foreach($agentsBookings as $key => $booking){
                    $arr = array();
                    $arr['booking_id'] = $agentsBookings[$key]['id'];
                    $arr['unique_id'] = $agentsBookings[$key]['uuid'];

                    //booked seats
                    $pjBookingSeatModel = pjBookingSeatModel::factory();
                    $seat_pair_arr = $pjBookingSeatModel->reset()->where('booking_id', $agentsBookings[$key]['id'])->findAll()->getDataPair("seat_id", 'seat_id');
                    $selected_seats = array();
                    $arr['booked_seat_numbers'] = '';
                    if(!empty($seat_pair_arr))
                    {
                        $selected_seats = pjSeatModel::factory()->whereIn('id', $seat_pair_arr)->findAll()->getDataPair("id", 'name');
                        $arr['booked_seat_numbers'] = join(", ", $selected_seats);
                    }

                    $arr['c_title'] = $agentsBookings[$key]['c_title'];
                    $arr['c_fname'] = $agentsBookings[$key]['c_fname'];
                    $arr['c_lname'] = $agentsBookings[$key]['c_lname'];
                    $arr['c_phone'] = $agentsBookings[$key]['c_phone'];
                    $arr['c_email'] = $agentsBookings[$key]['c_email'];

                    $booking_route = strip_tags($agentsBookings[$key]['booking_route']);
                    $bus_name_arr = explode(",",$booking_route);
                    $arr['bus_name'] = $bus_name_arr[0];

                    $bookingArr = explode("from",$booking_route);
                    $locFromTo = $bookingArr[1];
                    $locNameArr = explode("to",$locFromTo);
                    $arr['to_name'] = trim($locNameArr[0]);
                    $arr['from_name'] = trim($locNameArr[1]);

                    $arr['status'] = $agentsBookings[$key]['status'];
                    $arr['total_amount'] = $agentsBookings[$key]['total'];
                    $booking_datetime_arr = explode(" ", $agentsBookings[$key]['booking_datetime']);
                    $arr['booking_date'] = $booking_datetime_arr[0];
                    $arr['booking_time'] = $agentsBookings[$key]['booking_time'];
                    array_push($respArr, $arr);
                }
            }   
            $resp = array(
                        'status' => 'true',
                        'data' => $respArr
                    );
        }else{
            $resp = array(
                        'status' => 'false',
                        'message' => 'Invalid token, please login and check bookings'
                    );
        }
       pjAppController::jsonResponse($resp);  

    }

    public function pjActionOutBookings(){

        $agent_user_id = (isset($_POST['agent_user_id']) && trim($_POST['agent_user_id'])) ? trim($_POST['agent_user_id']) : '';
        $token = (isset($_POST['token']) && trim($_POST['token'])) ? trim($_POST['token']) : '';
        $status = (isset($_POST['status']) && trim($_POST['status'])) ? trim($_POST['status']) : '';
        $status = (isset($status) && $status == 'all') ? '' : $status;

        //for search
        $srch = (isset($_POST['search']) && trim($_POST['search'])) ? trim($_POST['search']) : '';
        $from_date = (isset($_POST['from_date']) && trim($_POST['from_date'])) ? trim($_POST['from_date']) : '';

        if(empty($agent_user_id) || empty($token)){
            $resp = array(
                        'status' => 'false',
                        'message' => 'Please provide all parameters'
                    );
            pjAppController::jsonResponse($resp);  
        }

        $token_validation_result = Token::validate($token, $this->jwt_secret);
        if($token_validation_result){
            $pjBookingModel = pjBookingModel::factory()
                    ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                    ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                    ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                    ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                    ->where('user_id',0);

             if (isset($status) && !empty($status) && in_array($status, array('confirmed','cancelled','pending')))
            {
                $pjBookingModel->where('t1.status', $status);
            } 

            //for search
            if (isset($srch) && !empty($srch))
            {
                
                $pjBookingModel->where("(CONCAT(t1.c_fname,' ',t1.c_lname) = '$srch' OR
                  t1.c_fname like '%$srch%' OR t1.c_lname like '%$srch%' OR CONCAT(t1.c_fname,' ',t1.c_lname) LIKE '%$srch%' OR t1.c_phone LIKE '%$srch%' OR t1.uuid like '%$srch%')");
            } 
            if (isset($from_date) && !empty($from_date))
            {
                $pjBookingModel->where("(DATE(t1.booking_datetime) >= '$from_date')");
            }  
        
            $data = $pjBookingModel
                        ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
                        ->orderBy("t1.id DESC")
                        ->findAll()
                        ->getData();
                     
            $respArr = array();
            foreach($data as $k => $v)
            {
                /*$route_details = '';
                $client_arr = array();
                if(!empty($v['c_fname']))
                {
                    $client_arr[] = $v['c_fname'];
                }
                if(!empty($v['c_lname']))
                {
                    $client_arr[] = $v['c_lname'];
                }
                $v['client'] = join(" ", $client_arr) . " " . $v['c_email'];*/
                /*$v['date_time'] = date($this->option_arr['o_date_format'] . ', ' . $this->option_arr['o_time_format'], strtotime($v['booking_datetime'])) . '<br/>' . date($this->option_arr['o_date_format'] . ', ' . $this->option_arr['o_time_format'], strtotime($v['stop_datetime']));
                if(date($this->option_arr['o_date_format'], strtotime($v['booking_datetime'])) == date($this->option_arr['o_date_format'], strtotime($v['stop_datetime'])))
                {
                    $v['date_time'] = date($this->option_arr['o_date_format'], strtotime($v['booking_datetime'])) . ' ' . date($this->option_arr['o_time_format'], strtotime($v['booking_datetime'])) . ' - ' . date($this->option_arr['o_time_format'], strtotime($v['stop_datetime']));
                }*/
                
                /*$route_details .= $v['route_title'];
                $route_details .= ', ' . date($this->option_arr['o_time_format'], strtotime($v['departure_time'])) . ' - ' . date($this->option_arr['o_time_format'], strtotime($v['arrival_time']));
                $route_details .= ' '  . mb_strtolower(__('lblFrom', true), 'UTF-8') . ' ' . $v['from_location'] . ' ' . mb_strtolower(__('lblTo', true), 'UTF-8') . ' ' . $v['to_location'];
                $v['route_details'] = $route_details;*/

                $arr = array();
                $arr['booking_id'] = $v['id'];
                $arr['unique_id'] = $v['uuid'];


                //booked seats
                $pjBookingSeatModel = pjBookingSeatModel::factory();
                $seat_pair_arr = $pjBookingSeatModel->reset()->where('booking_id', $v['id'])->findAll()->getDataPair("seat_id", 'seat_id');
                $selected_seats = array();
                $arr['booked_seat_numbers'] = '';
                if(!empty($seat_pair_arr))
                {
                    $selected_seats = pjSeatModel::factory()->whereIn('id', $seat_pair_arr)->findAll()->getDataPair("id", 'name');
                    $arr['booked_seat_numbers'] = join(", ", $selected_seats);
                }


                $arr['c_title'] = $v['c_title'];
                $arr['c_fname'] = $v['c_fname'];
                $arr['c_lname'] = $v['c_lname'];
                $arr['c_phone'] = $v['c_phone'];
                $arr['c_email'] = $v['c_email'];

                $booking_route = strip_tags($v['booking_route']);
                $bus_name_arr = explode(",",$booking_route);
                $arr['bus_name'] = $bus_name_arr[0];

                $bookingArr = explode("from",$booking_route);
                $locFromTo = $bookingArr[1];
                $locNameArr = explode("to",$locFromTo);
                $arr['to_name'] = trim($locNameArr[0]);
                $arr['from_name'] = trim($locNameArr[1]);

                $arr['status'] = $v['status'];
                $arr['total_amount'] = $v['total'];
                $booking_datetime_arr = explode(" ", $v['booking_datetime']);
                $arr['booking_date'] = $booking_datetime_arr[0];
                $arr['booking_time'] = $v['booking_time'];
                array_push($respArr, $arr);

                //$data[$k] = $v;
            }

            if(count($data)){
                $resp = array(
                            'status' => 'true',
                            //'data' => $data
                            'data' => $respArr
                        );
            }else{
                $resp = array(
                            'status' => 'false',
                            'message' => 'No outside bookings found'
                        );
            }

        }else{
            $resp = array(
                        'status' => 'false',
                        'message' => 'Invalid token, please login and check outside bookings'
                    );
        }

        
        
        pjAppController::jsonResponse($resp); 
    }

    public function pjActionMyRevenue(){
        $agent_user_id = (isset($_POST['agent_user_id']) && trim($_POST['agent_user_id'])) ? trim($_POST['agent_user_id']) : '';
        $token = (isset($_POST['token']) && trim($_POST['token'])) ? trim($_POST['token']) : '';
        $data = array();

        if(empty($agent_user_id) || empty($token)){
            $resp = array(
                        'status' => 'false',
                        'message' => 'Please provide all parameters'
                    );
            pjAppController::jsonResponse($resp);  
        }

        $token_validation_result = Token::validate($token, $this->jwt_secret);
        if($token_validation_result){
            $pjUserModel = pjUserModel::factory();
            $pjCommissionModel = pjCommissionModel::factory();
            $data_object = $pjCommissionModel->select('t1.*,t1.cm_added_on,t2.name')
                        ->join('pjUser', 't2.id=t1.cm_added_by', 'left');
            $data_object->where('(cm_user_id = '.$agent_user_id.' OR cm_user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$agent_user_id.'))');    
            $data_object->where('cm_user_id',$agent_user_id);
            $data = $data_object->orderBy("cm_added_on DESC")->findAll()->getData();

            $total_amount = 0;
            if(count($data) > 0){
                foreach($data as $dta){
                    $total_amount += $dta['cm_credit_added'];
                }
            }

            $user_credit = pjCreditModel::factory()
                    ->where('t1.user_id', $agent_user_id)
                    ->findAll()
                    ->getData();

            $user_credit_balance = (!empty($user_credit)) ? $user_credit[0]['credit'] : 0;
            $user_total_commission = (!empty($user_credit)) ? $user_credit[0]['total_commission'] : 0;
            $total_amount += $user_total_commission;

            $balance_percentage = ($user_credit_balance/$total_amount) * 100;
            $balance_percentage = round($balance_percentage,2);


            if(count($data)){
                $resp = array(
                            'status' => 'true',
                            'data' => $data,
                            'credit' => $user_credit_balance,
                            'total_allocated_credit' => $total_amount,
                            'balance_percentage' => $balance_percentage
                        );
            }else{
                $resp = array(
                            'status' => 'false',
                            'message' => 'No revenue found'
                        );
            }
        }else{
            $resp = array(
                        'status' => 'false',
                        'message' => 'Invalid token, please login and check your revenue'
                    );
        }
        pjAppController::jsonResponse($resp);

    }

    //api for stress testing
    public function pjActionTest(){
        $status = (isset($_POST['status']) && trim($_POST['status'])) ? trim($_POST['status']) : '';
        $status = (isset($status) && $status == 'all') ? '' : $status;

        //for search
        $srch = (isset($_POST['search']) && trim($_POST['search'])) ? trim($_POST['search']) : '';
        $from_date = (isset($_POST['from_date']) && trim($_POST['from_date'])) ? trim($_POST['from_date']) : '';
        
        $pjBookingModel = pjDummyBookingModel::factory()
                ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                ->where('user_id',0);

         if (isset($status) && !empty($status) && in_array($status, array('confirmed','cancelled','pending')))
        {
            $pjBookingModel->where('t1.status', $status);
        } 
        //for search
        if (isset($srch) && !empty($srch))
        {
            
            $pjBookingModel->where("(CONCAT(t1.c_fname,' ',t1.c_lname) = '$srch' OR
              t1.c_fname like '%$srch%' OR t1.c_lname like '%$srch%' OR CONCAT(t1.c_fname,' ',t1.c_lname) LIKE '%$srch%' OR t1.c_phone LIKE '%$srch%' OR t1.uuid like '%$srch%')");
        } 
        if (isset($from_date) && !empty($from_date))
        {
            $pjBookingModel->where("(DATE(t1.booking_datetime) >= '$from_date')");
        }  
        $data = $pjBookingModel
                    ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
                    ->orderBy("t1.id DESC")
                    ->findAll()
                    ->getData();
        $respArr = array();
        foreach($data as $k => $v)
        {
            $arr = array();
            $arr['booking_id'] = $v['id'];
            $arr['unique_id'] = $v['uuid'];
            //booked seats
            $pjBookingSeatModel = pjBookingSeatTestModel::factory();
            $seat_pair_arr = $pjBookingSeatModel->reset()->where('booking_id', $v['id'])->findAll()->getDataPair("seat_id", 'seat_id');
            $selected_seats = array();
            $arr['booked_seat_numbers'] = '';
            if(!empty($seat_pair_arr))
            {
                $selected_seats = pjSeatModel::factory()->whereIn('id', $seat_pair_arr)->findAll()->getDataPair("id", 'name');
                $arr['booked_seat_numbers'] = join(", ", $selected_seats);
            }
            $arr['c_title'] = $v['c_title'];
            $arr['c_fname'] = $v['c_fname'];
            $arr['c_lname'] = $v['c_lname'];
            $arr['c_phone'] = $v['c_phone'];
            $arr['c_email'] = $v['c_email'];
            $booking_route = strip_tags($v['booking_route']);
            $bus_name_arr = explode(",",$booking_route);
            $arr['bus_name'] = $bus_name_arr[0];
            $bookingArr = explode("from",$booking_route);
            $locFromTo = $bookingArr[1];
            $locNameArr = explode("to",$locFromTo);
            $arr['to_name'] = trim($locNameArr[0]);
            $arr['from_name'] = trim($locNameArr[1]);
            $arr['status'] = $v['status'];
            $arr['total_amount'] = $v['total'];
            $booking_datetime_arr = explode(" ", $v['booking_datetime']);
            $arr['booking_date'] = $booking_datetime_arr[0];
            $arr['booking_time'] = $v['booking_time'];
            array_push($respArr, $arr);
        }
        if(count($data)){
            $resp = array(
                        'status' => 'true',
                        //'data' => $data
                        'data' => $respArr
                    );
        }else{
            $resp = array(
                        'status' => 'false',
                        'message' => 'No outside bookings found'
                    );
        }
        
        pjAppController::jsonResponse($resp); 
    }

    //api for revenue details
    public function pjGetCreditData(){
        $agent_user_id = (isset($_POST['agent_user_id']) && trim($_POST['agent_user_id'])) ? trim($_POST['agent_user_id']) : '';
        $token = (isset($_POST['token']) && trim($_POST['token'])) ? trim($_POST['token']) : '';
        $data = array();
        if(empty($agent_user_id) || empty($token)){
            $resp = array(
                        'status' => 'false',
                        'message' => 'Please provide all parameters'
                    );
            pjAppController::jsonResponse($resp);  
        }

        $token_validation_result = Token::validate($token, $this->jwt_secret);
        if($token_validation_result){
            $pjUserModel = pjUserModel::factory();
            $pjCommissionModel = pjCommissionModel::factory();
            $data_object = $pjCommissionModel->select('t1.*')
                        ->join('pjUser', 't2.id=t1.cm_added_by', 'left');
            $data_object->where('(cm_user_id = '.$agent_user_id.' OR cm_user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$agent_user_id.'))');    
            $data_object->where('cm_user_id',$agent_user_id);
            $result = $data_object->findAll()->getData();

            $total_amount = 0;
            if(count($result) > 0){
                foreach($result as $res){
                    $total_amount += $res['cm_credit_added'];
                }
            }


            $user_credit = pjCreditModel::factory()
                    ->where('t1.user_id', $agent_user_id)
                    ->findAll()
                    ->getData();

            $user_credit_balance = (!empty($user_credit)) ? $user_credit[0]['credit'] : 0;
            $user_total_commission = (!empty($user_credit)) ? $user_credit[0]['total_commission'] : 0;
            $total_amount += $user_total_commission;
            $balance_percentage = ($user_credit_balance/$total_amount) * 100;
            $balance_percentage = round($balance_percentage,2);

            $user_commission_percent = (!empty($user_credit)) ? $user_credit[0]['commission_percent'] : 0;

            $user = $pjUserModel
            ->select('t1.*')
            ->where('t1.id', $agent_user_id)
            ->limit(1)
            ->findAll()
            ->getData();
            $userData = $user[0];

            //new bookings today
            $current_date = date('Y-m-d');
            $pjBookingModel = pjBookingModel::factory();
            $cnt_today_bookings_model = $pjBookingModel->where("t1.created LIKE '%$current_date%'");
            $cnt_today_bookings_model->where('t1.user_id = '.$agent_user_id);
            $cnt_today_new_bookings = $cnt_today_bookings_model->findCount()->getData();

            $data['agent_name'] = $userData['name'];
            $data['agent_email'] = $userData['email'];
            $data['agent_phone'] = $userData['phone'];
            $data['agent_user_code'] = $userData['user_code'];

            $data['total_allocated_credit'] = strval($total_amount);
            $data['available_credit_balance'] = $user_credit_balance;
            $data['balance_percentage'] = $balance_percentage;

            $data['commission_percentage'] = $user_commission_percent;
            $data['total_commission'] = $user_total_commission;
            $data['today_new_bookings_count'] = $cnt_today_new_bookings;

            $cnt_today_departure = 0;
            $date = $current_date;
            $next_buses_arr = array();
            $pjBusModel = pjBusModel::factory();
            $pjRouteModel = pjRouteModel::factory();
            $cnt_routes = $pjRouteModel->findCount()->getData();
            $cnt_buses = $pjBusModel->findCount()->getData();
            $next_3_months = strtotime('+3 month', strtotime($date));
            $weekday = strtolower(date('l'));
            if($cnt_buses > 0)
            {
                while(count($next_buses_arr) < 5 && strtotime($date) < $next_3_months)
                {
                    $bus_arr = $pjBusModel
                    ->reset()
                    ->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                    ->select("t1.*, t2.content AS route,
                                (SELECT COUNT(TB.id) 
                                    FROM `".$pjBookingModel->getTable()."` AS TB 
                                    WHERE (
                                        TB.bus_id=t1.id AND 
                                        TB.booking_date='$date' AND 
                                        (TB.user_id = ".$agent_user_id." OR TB.user_id IN (SELECT id FROM ".$pjUserModel->getTable()." WHERE added_by = ".$agent_user_id."))
                                    )
                                )AS total_bookings,
                                (SELECT SUM(TBT.qty) 
                                    FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT 
                                    WHERE TBT.booking_id IN 
                                            (SELECT TB1.id 
                                            FROM `".$pjBookingModel->getTable()."` AS TB1
                                            WHERE TB1.bus_id=t1.id AND TB1.booking_date='$date' AND (TB1.user_id = ".$agent_user_id." OR TB1.user_id IN (SELECT id FROM ".$pjUserModel->getTable()." WHERE added_by = ".$agent_user_id.")))) AS total_tickets")
                    ->where("(t1.start_date <= '$date' AND t1.end_date >= '$date')")
                    ->orderBy("departure_time ASC")
                    ->findAll()
                    ->getData();  
                    foreach($bus_arr as $v)
                    {
                        if(empty($v['recurring']))
                        {
                            if($date == $current_date)
                            {
                                $cnt_today_departure++;
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

            $data['buses_to_depart_today'] = strval($cnt_today_departure);
            $data['routes_count'] = $cnt_routes;
            $data['buses_count'] = $cnt_buses;
            if(count($data)){
                $resp = array(
                            'status' => 'true',
                            'data' => $data
                        );
            }else{
                $resp = array(
                            'status' => 'false',
                            'message' => 'No revenue data found'
                        );
            }
        }else{
            $resp = array(
                        'status' => 'false',
                        'message' => 'Invalid token, please login and check your revenue'
                    );
        }
        pjAppController::jsonResponse($resp);

    }

    
}
?>