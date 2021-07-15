<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
use ReallySimpleJWT\Token;

class pjApiBuses extends pjFrontMobile
{

	private $jwt_secret = 'maz!ReT423*&';
    /*
      * List buses when providing pickup_id, return_id and date
    */
    public function pjActionBuses() {
        $pickup_id = $_GET['pickup_id'];
        $return_id = $_GET['return_id'];
        $date = pjUtil::formatDate($_GET['date'], $this->option_arr['o_date_format']);
        $pjBusModel = pjBusModel::factory();
        $buses = array();
        if ($this->isBusReady() == true)
        {
            //get bus ids
            $bus_id_arr = $pjBusModel->getBusIds($date, $pickup_id, $return_id);
            $booking_period = array();
            $booked_data = array();
            if($bus_id_arr)
            {
                //get bus data
                $bus_list = $this->getBusList($pickup_id, $return_id, $bus_id_arr, $booking_period, $booked_data, $date, 'F');
                $booking_period = $bus_list['booking_period'];
                $bus_arr = $bus_list['bus_arr']; 
                foreach($bus_arr as $bus)
                {
                    $arr = array();
                    $arr['id'] = $bus['id'];
                    $arr['name'] = $bus['route'];
                    $arr['available_seats'] = $bus['seats_available'];

                    $pjBusTypeModel = pjBusTypeModel::factory ();
                    $bus_type_arr = $pjBusTypeModel->find ($bus['bus_type_id'])->getData ();
                    $total_seats = $bus_type_arr['seats_count'];
                    $arr['total_seat_count'] = $total_seats;

                    $arr['departure_time'] = $bus['f_departure_time'];
                    $arr['arrival_time'] = $bus['f_arrival_time'];
                    //get price
                    $pjPriceModel = pjPriceModel::factory();
                    $ticket_price_arr = $pjPriceModel->getTicketPrice($bus['id'], $pickup_id, $return_id, $_POST, $this->option_arr, $this->getLocaleId(), 'F');
                    $arr['ticket_price'] = $ticket_price_arr['ticket_arr'];
                    $arr['tax'] = $ticket_price_arr['tax'];

                    //remove html tags etc from the value
                    $strip_slashed_val = stripslashes($ticket_price_arr['tax_format']);
                    $quote_removed_val = str_replace('""','',$strip_slashed_val);
                    $strip_tagged_tax_format = strip_tags($quote_removed_val);
                    $arr['tax_format'] = $strip_tagged_tax_format;
                    array_push($buses, $arr);
                }
            }
        }
        if(count($buses) > 0){
            $response = array(
                'status' => 'true',
                'data' => $buses
            );  
        }else{
            $response = array(
                'status' => 'false',
                'message' => 'No buses found'
            ); 

        }
        pjAppController::jsonResponse($response);
    }

    /*
      * To check whether available or not when providing pickup_id, return_id and date
    */
    public function pjActionBusAvailability() {
       
        if($_GET['pickup_id'] != $_GET['return_id'])
        {
            $pickup_id = $_GET['pickup_id'];
            $return_id = $_GET['return_id'];
            $date = pjUtil::formatDate($_GET['date'], $this->option_arr['o_date_format']);
            $pjBusModel = pjBusModel::factory();
            $bus_id_arr = $pjBusModel->getBusIds($date, $pickup_id, $return_id);

            if(empty($bus_id_arr))
            {
                $response = array(
                    'status' => 'false',
                    'message' => 'Bus not available'
                );
            }else{
                $response = array(
                        'status' => 'true',
                        'message' => 'Bus available'
                    );
            }

            if(isset($_GET['is_return']) && ($_GET['is_return'] == 'T')){
                $pickup_id = $_GET['return_id'];
                $return_id = $_GET['pickup_id'];
                $date = pjUtil::formatDate($_GET['return_date'], $this->option_arr['o_date_format']);
                $return_bus_id_arr = $pjBusModel->getBusIds($date, $pickup_id, $return_id);

                if(empty($bus_id_arr) && empty($return_bus_id_arr))
                {
                    $response = array(
                        'status' => 'false',
                        'message' => 'Bus not available for both ways'
                    );
                }

                if(!empty($bus_id_arr) && empty($return_bus_id_arr))
                {
                    $response = array(
                        'status' => 'false',
                        'message' => 'Return bus not available'
                    );
                }elseif(empty($bus_id_arr) && !empty($return_bus_id_arr))
                {
                    $response = array(
                        'status' => 'false',
                        'message' => 'Return bus only available'
                    );
                }elseif(!empty($bus_id_arr) && !empty($return_bus_id_arr))
                {
                    $response = array(
                        'status' => 'true',
                        'message' => 'Bus available for round trip'
                    );
                }
            }
        }
        pjAppController::jsonResponse($response);
    }	

    /*
     * Get already booked seats of selected bus and date
     */
    public function pjActionBookedSeats(){
        $bus_id = $_GET['bus_id'];
        $pickup_id = $_GET['pickup_id'];
        $return_id = $_GET['return_id'];
        $booking_date = $_GET['date'];
        $date = pjUtil::formatDate($_GET['date'], $this->option_arr['o_date_format']);
        $option_arr = $this->option_arr;
        $response = array(
                    'status' => 'true',
                    'data' => array()
                );
        $booked_seats = array();
        $store = array(
                    'pickup_id' => $pickup_id,
                    'return_id' => $return_id,
                    'date' => $date,
                    'bus_id_arr' => array($bus_id),
                    'is_return' => 'F',
                    'booked_data' => $booked_data
                );
        $booking_period = array(); 
        $pjBusModel = pjBusModel::factory();
        $bus_id_arr = array(0 => $bus_id);

        if ($bus_id_arr) {
            $bus_list = $this->getBusList($pickup_id, $return_id, $bus_id_arr, $booking_period, $booked_data, $booking_date, 'F');
            
            if (isset($bus_list['booking_period'])) {
                $time = $bus_list['booking_period'];
                $store['booking_period'][$bus_id] = $time[$bus_id];

                $avail_arr = $this->getBusAvailability($bus_id, $store, $this->option_arr);
                $booked_seats = $avail_arr['booked_seat_arr'];
                if(count($booked_seats)){
                    $seat_id_array = array();
                    foreach($booked_seats as $k => $v){
                            array_push($seat_id_array, $v);
                        }

                    $selected_seats = pjSeatModel::factory()->select("name as seat_no")->whereIn('id', $seat_id_array)->findAll()->getData();
                    if(count($selected_seats))
                    {
                        foreach($selected_seats as $key => $seat){
                            array_push($response['data'], array('seat_no' => $seat['seat_no']));
                        }
                    }
                }
            }
        }
        pjAppController::jsonResponse($response);
    }

    /*
     *  List buses for current day, departing from the time the Agent access the page(this list is for setting delay time for buses by agent)
    */
    public function listBuses() {
        $todayDateTime = date('Y-m-d H:i');
        $today = date('Y-m-d');
        $pjBusModel = pjBusModel::factory()
                ->join('pjBusDelay', "t2.bus_id=t1.id", 'left outer')
                ->join('pjUser', "t3.id=t2.agent_id", 'left outer')
                ->join('pjMultiLang', "t4.model='pjRoute' AND t4.foreign_id=t1.route_id AND t4.field='title' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                ->where("(t1.end_date >= '$today')");
        $data = $pjBusModel->select("t1.id, t1.start_date, t1.end_date, t1.departure_time,
            t1.arrival_time, t4.content AS route, t2.delayed_by, t2.updated_dt, t3.name as agent_name, t3.email, t3.phone
            ")->findAll()->getData(); 
        $buses_without_delay = array();
        $buses_with_delay = array();
        if(count($data)){
           foreach($data as $key => $bus)
            {
                $departTimeArr = explode(':', $bus['departure_time']);
                $departDateTime = $today." ".$departTimeArr[0].":".$departTimeArr[1];//today's departure time
                $todayDateTimeStamp = strtotime($todayDateTime);
                $departDateTimeStamp = strtotime($departDateTime);

                $from = "";
                $to = "";
                if (strpos($bus['route'], '-') !== false) {
                    $routeArr = explode('-', $bus['route']);
                    $from = trim($routeArr[0]);
                    $to = trim($routeArr[1]);
                } else {
                    $routeArr = explode(' ', $bus['route']);
                    $from = trim($routeArr[0]);
                    $to = trim($routeArr[2]);
                }

                $arrivalTimeArr = explode(':', $bus['arrival_time']);

                $bus_arr = array(
                    'id' => $bus['id'],
                    'date' => date('d-m-Y'),
                    'from' => $from,
                    'to' => $to,
                    'time' => $departTimeArr[0].":".$departTimeArr[1] ."-". $arrivalTimeArr[0].":".$arrivalTimeArr[1],
                    'bus_name' => $bus['route']
                );
                if($departDateTimeStamp >= $todayDateTimeStamp){
                    if(isset($bus['delayed_by']) && ($bus['delayed_by'] != '')){
                        $updatedDtArr = explode(' ', $bus['updated_dt']);
                        if($updatedDtArr[0] == $today){//show only if there is delay set for current date
                            $delayedByArr = explode(':', $bus['delayed_by']);
                        
                            $selectedDepartTime = $departTimeArr[0].":".$departTimeArr[1];
                            $selectedArrivalTime = $arrivalTimeArr[0].":".$arrivalTimeArr[1];
                            $finalDepartTime = strtotime("+".$delayedByArr[0]." hours +".$delayedByArr[1]." minutes", strtotime($selectedDepartTime));
                            $finalDepartTime = date( "H:i", $finalDepartTime);

                            $finalArrivalTime = strtotime("+".$delayedByArr[0]." hours +".$delayedByArr[1]." minutes", strtotime($selectedArrivalTime));
                            $finalArrivalTime = date( "H:i", $finalArrivalTime);

                            $bus_arr['time'] = $finalDepartTime."-".$finalArrivalTime;

                            $bus_arr['delayed_by'] = ltrim($delayedByArr[0], "0").' hr '.$delayedByArr[1].'min';
                            $bus_arr['added_by_name'] = $bus['agent_name'];
                            $bus_arr['added_by_email'] = $bus['email'];
                            $bus_arr['added_by_phone'] = !empty($bus['phone']) ? $bus['phone'] : "null";
                            array_push($buses_with_delay, $bus_arr);

                        }
                    }else{
                        array_push($buses_without_delay, $bus_arr);
                    }
                }
            } 
        }
        $response = array(
            'status' => 'true',
            'data' => $buses_without_delay,
            'delay_data' => $buses_with_delay
        );
        pjAppController::jsonResponse($response);
    }

    /*
     *  Set or edit delay time for bus, by any agent user
    */
    public function setDelay() {
        $bus_id = trim($_POST['bus_id']);
        $agent_id = trim($_POST['agent_id']);
        $delay_time = trim($_POST['delay_time']);//hh:mm format
        $token = (isset($_POST['token']) && trim($_POST['token'])) ? trim($_POST['token']) : '';
        if(empty($bus_id) || empty($agent_id) || empty($delay_time) || empty($token)){
            $response = array(
                'status' => 'false',
                'message' => 'Please provide all required parameters'
           );
            pjAppController::jsonResponse($response);
        }
        //token validation check
        $token_validation_result = Token::validate($token, $this->jwt_secret);
        if($token_validation_result){
            $pjBusDelayModel = pjBusDelayModel::factory();
            $busDelayRes = $pjBusDelayModel
            ->select('id')
            ->where('bus_id', $bus_id)
            ->limit(1)
            ->findAll()
            ->getData();
            if((count($busDelayRes) > 0) && isset($busDelayRes[0]) && !empty($busDelayRes[0])){
                $busDelay = $busDelayRes[0];
                //update delay
                $pjBusDelayModel = pjBusDelayModel::factory();       
                $pjBusDelayModel->reset()->set('id', $busDelay['id'])->modify(
                    array(
                        'bus_id' => $bus_id,
                        'agent_id' => $agent_id,
                        'delayed_by' => $delay_time,
                        'updated_dt' => ':NOW()'
                    )
                );
            }else{
                //add delay
                $data = array(
                        'bus_id' => $bus_id,
                        'agent_id' => $agent_id,
                        'delayed_by' => $delay_time,
                        'created_dt' => ':NOW()',
                        'updated_dt' => ':NOW()'
                    );
                $id = pjBusDelayModel::factory($data)->insert()->getInsertId();
            }
            //send sms and email to the users who already booked this bus
            //pjApiBuses::notify($bus_id);// commented as the email and sms content is yet to be received from client
            $response = array(
                'status' => 'true',
                'message' => 'Delay time has been set'
            );
            pjAppController::jsonResponse($response);
        }else{
           $response = array(
                        'status' => 'false',
                        'message' => 'Invalid token, please login and set delay time'
                    );
            pjAppController::jsonResponse($response);
        }
    }

    /*
     * List available buses which has delay and those without delay to show in the availability list(created new api as the old api is live)
     */
    public function pjDelayedBuses() {
        $pickup_id = $_GET['pickup_id'];
        $return_id = $_GET['return_id'];
        $date = pjUtil::formatDate($_GET['date'], $this->option_arr['o_date_format']);
        $pjBusModel = pjBusModel::factory();
        $buses = array();
        if ($this->isBusReady() == true)
        {
            //get bus ids
            $pjBusModel = pjBusModel::factory();
            $bus_id_arr = $pjBusModel->getBusIds($date, $pickup_id, $return_id);
            $booking_period = array();
            $booked_data = array();
            if($bus_id_arr)
            {
                //get bus data
                $bus_list = $this->getBusList($pickup_id, $return_id, $bus_id_arr, $booking_period, $booked_data, $date, 'F');
                $booking_period = $bus_list['booking_period'];
                $bus_arr = $bus_list['bus_arr']; 
                foreach($bus_arr as $bus)
                {
                    $arr = array();
                    $arr['id'] = $bus['id'];
                    $arr['name'] = $bus['route'];

                    $arr['departure_time'] = $bus['f_departure_time'];
                    $arr['arrival_time'] = $bus['f_arrival_time'];

                    $delayData = pjBusDelayModel::factory()
                    ->where("(DATE(updated_dt) = CURDATE() and bus_id = '" . $bus['id'] . "')")
                    ->select("delayed_by")->findAll()->getData(); 
                    if(isset($delayData[0]) && isset($delayData[0]['delayed_by']) && ($delayData[0]['delayed_by'] != '')){

                        $busDelayInfo = $delayData[0];
                        $delayedByArr = explode(':', $busDelayInfo['delayed_by']);

                        $hourVal = ltrim($delayedByArr[0], "0");
                        if(intval($hourVal) > 1){
                            $arr['delayed_by'] =  $hourVal . " hours " . $delayedByArr[1] . " minutes";
                        }else{
                            $arr['delayed_by'] =  $hourVal . " hour " . $delayedByArr[1] . " minutes";
                        }

                        $departTimeArr = explode(':', $bus['f_departure_time']);
                        $departTime = $departTimeArr[0].":".$departTimeArr[1].":00";
                        $finalDepartTime = strtotime("+".$delayedByArr[0]." hours +".$delayedByArr[1]." minutes", strtotime($departTime));
                        $finalDepartTime = date( "H:i", $finalDepartTime);

                        $arrivalTimeArr = explode(':', $bus['f_arrival_time']);
                        $arrivalTime = $arrivalTimeArr[0].":".$arrivalTimeArr[1];
                        $finalArrivalTime = strtotime("+".$delayedByArr[0]." hours +".$delayedByArr[1]." minutes", strtotime($arrivalTime));
                        $finalArrivalTime = date( "H:i", $finalArrivalTime);

                        $arr['departure_time'] = $finalDepartTime;
                        $arr['arrival_time'] = $finalArrivalTime;
                    }
                    
                    $arr['available_seats'] = $bus['seats_available'];

                    $pjBusTypeModel = pjBusTypeModel::factory ();
                    $bus_type_arr = $pjBusTypeModel->find ($bus['bus_type_id'])->getData ();
                    $total_seats = $bus_type_arr['seats_count'];
                    $arr['total_seat_count'] = $total_seats;

                    //get price
                    $pjPriceModel = pjPriceModel::factory();
                    $ticket_price_arr = $pjPriceModel->getTicketPrice($bus['id'], $pickup_id, $return_id, $_POST, $this->option_arr, $this->getLocaleId(), 'F');
                    $arr['ticket_price'] = $ticket_price_arr['ticket_arr'];
                    $arr['tax'] = $ticket_price_arr['tax'];

                    //remove html tags etc from the value
                    $strip_slashed_val = stripslashes($ticket_price_arr['tax_format']);
                    $quote_removed_val = str_replace('""','',$strip_slashed_val);
                    $strip_tagged_tax_format = strip_tags($quote_removed_val);
                    $arr['tax_format'] = $strip_tagged_tax_format;
                    array_push($buses, $arr);
                }
            }
        }
        if(count($buses) > 0){
            $response = array(
                'status' => 'true',
                'data' => $buses
            );  
        }else{
            $response = array(
                'status' => 'false',
                'message' => 'No buses found'
            ); 

        }
        pjAppController::jsonResponse($response);
    }

    public function getDelayedBusIds($date, $pickup_id, $return_id)
    {
        $bus_id_arr = array();
        $day_of_week = strtolower(date('l', strtotime($date)));
        $arr = pjBusModel::factory()
        ->join('pjBusDelay', "t2.bus_id=t1.id", 'inner')
            ->where("(t1.start_date <= '$date' AND '$date' <= t1.end_date) AND (t1.recurring LIKE '%$day_of_week%') AND t1.id NOT IN (SELECT TSD.bus_id FROM `".pjBusDateModel::factory()->getTable()."` AS TSD WHERE TSD.`date` = '$date')")
            ->where("(t1.route_id IN(SELECT TRD.route_id FROM `".pjRouteDetailModel::factory()->getTable()."` AS TRD WHERE TRD.from_location_id = $pickup_id AND TRD.to_location_id = $return_id))")
            ->where("(t1.route_id IN(SELECT `TR`.id FROM `".pjRouteModel::factory()->getTable()."` AS `TR` WHERE `TR`.status='T'))")
            ->where("(DATE(t2.updated_dt) = CURDATE())")
            ->select("t1.id")
            //->debug(true)
            ->findAll()
            ->getData();
        
        $pjBusLocationModel = pjBusLocationModel::factory();
        foreach($arr as $k => $v)
        {
            $pickup_arr = $pjBusLocationModel->reset ()->where ( 'bus_id', $v['id'] )->where ( "location_id", $pickup_id )->limit ( 1 )->findAll ()->getData ();
            if (! empty ( $pickup_arr )) {
                $departure_dt = $date . ' ' . $pickup_arr [0] ['departure_time'];
                if($departure_dt >= date('Y-m-d H:i:s'))
                {
                    $bus_id_arr[] = $v['id'];
                }
            }
            
        }
        return $bus_id_arr;
    }

    public function notify($bus_id){
        $pjBookingModel = pjBookingModel::factory ();    
        $data = $pjBookingModel->reset()->select('t1.c_fname, t1.c_email, t1.c_phone, t1.booking_route')->where("(bus_id = '" . $bus_id . "' and user_id > 0)")
            ->findAll()
            ->getData();
        $delayData = pjBusDelayModel::factory()
                    ->where("(DATE(updated_dt) = CURDATE() and bus_id = '" . $bus_id . "')")
                    ->select("delayed_by")->findAll()->getData(); 
        if(isset($delayData[0]) && ($delayData[0]['delayed_by'] != '')){
            $busDelayInfo = $delayData[0];
            $delayedByArr = explode(':', $busDelayInfo['delayed_by']);
            $delayHour = $delayedByArr[0];
            $delayMinutes = $delayedByArr[1];
        }   
        if(count($data) && ($delayHour != '' || $delayMinutes != '')){
            foreach($data as $key => $row){
                $routeArr = explode(',', $row['booking_route']);
                $route = $routeArr[0];
                //send sms
                pjApiBuses::smsNotification($route, $row['c_phone'], $delayHour, $delayMinutes);
                //send email
                pjApiBuses::emailNotification($route, $row['c_email'], $row['c_fname'], $delayHour, $delayMinutes);
            }

        }
        return true;
    }

    public function smsNotification($route, $mobile, $delayHour, $delayMinutes){
        $sms_message = 'Bus you have booked in '.PJ_SITE_NAME.', $route will be delayed '.$delayHour." hours $delayMinutes minutes";
        $params = array(
                'text' => $sms_message,
                'type' => 'unicode',
                'key' => md5($this->option_arr['private_key'] . PJ_SALT)
        );
        $params['number'] = $mobile;
        $smsResult = $this->requestAction(array('controller' => 'pjSendSms', 'action' => 'pjActionSendSms', 'params' => $params), array('return'));
    }

    public function emailNotification($route, $email, $name, $delayHour, $delayMinutes){
        $Email = new pjEmail();
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
        $Email->setContentType('text/html');
        $message = "Hi ".$name.",<br/><br/>";
        $message .= 'Bus you have booked in '.PJ_SITE_NAME.', $route will be delayed '.$delayHour." hours $delayMinutes minutes";
        $message .= "Best Regards,<br/>";
        $message .= "mazhandufamilybus.com<br/>";

        $message = pjUtil::textToHtml($message);
        $subject = "Mazhandu Family Bus: Delay notification";
        $from_email = $this->option_arr['o_sender_email'];
        
        $Email->setTo($email)
        ->setFrom($from_email)
        ->setSubject($subject)
        ->send($message);
        
    }
    
   
}
?>