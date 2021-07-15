<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
use ReallySimpleJWT\Token;
class pjApiBooking extends pjFront
{

	public $defaultStep = 'BusReservation_Step';
    public $defaultStore = 'BusReservation_Store';
    private $jwt_secret = 'maz!ReT423*&';
    private $agent_role = 2;

    public function pjActionAvailability() {
       
    $resp = array();
    $return_bus_id_arr = array();

    if($_GET['pickup_id'] != $_GET['return_id'])
    {
        $resp['code'] = 200;

        $pjBusModel = pjBusModel::factory();

        $pickup_id = $_GET['pickup_id'];
        $return_id = $_GET['return_id'];

        $date = pjUtil::formatDate($_GET['date'], $this->option_arr['o_date_format']);
        $this->_set('date', $_GET['date']);

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
            //pjAppController::jsonResponse($resp);
            //echo "test1";exit;
            return $resp;
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
                //pjAppController::jsonResponse($resp);
                return $resp;
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
            //echo "test2";exit;
            $resp['code'] = 200;
            //pjAppController::jsonResponse($resp);
            return $resp;
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
            //pjAppController::jsonResponse($resp);
            return $resp;
        }
    }
    //pjAppController::jsonResponse($resp);
    return $resp;
    }	

    protected function _get($key)
    {
        if ($this->_is($key))
        {
            return $_SESSION[$this->defaultStore][$key];
        }
        return false;
    }
    
    protected function _is($key)
    {
        return isset($_SESSION[$this->defaultStore]) && isset($_SESSION[$this->defaultStore][$key]);
    }
    
    protected function _set($key, $value)
    {
        $_SESSION[$this->defaultStore][$key] = $value;
        return $this;
    }
    
    /* if bus available, show number of seats and price*/
    public function pjActionSeats()
    {
        
        $pickup_id = $_GET['pickup_id'];
        $return_id = $_GET['return_id'];
        $data = array();
        $data['to']  = array();
        $data['come_back']  = array();
        $pjBusModel = pjBusModel::factory();
        $date = pjUtil::formatDate($_GET['date'], $this->option_arr['o_date_format']);

        if ($this->isBusReady() == true)
        {
            $booking_period = array();
            $booked_data = array();
            $bus_id_arr = $pjBusModel->getBusIds($date, $pickup_id, $return_id);
            if($bus_id_arr)
            {
                    
                $bus_list = $this->getBusList($pickup_id, $return_id, $bus_id_arr, $booking_period, $booked_data, $date, 'F');
                $booking_period = $bus_list['booking_period'];

                $bus_arr = $bus_list['bus_arr']; 
                $i = 0;
                $data['to'] = array();
                foreach($bus_arr as $bus)
                {
                     
                    $to_arr = array();
                    $to_arr['bus'] = $bus['route'];
                    $to_arr['available_seats'] = $bus['seats_available'];
                    //get amount
                    $pjPriceModel = pjPriceModel::factory();
                    if($bus['id'] > 0)
                    {
                        
                         $store_val = array('pickup_id' => $pickup_id, 'return_id' => $return_id, 'booking_period' => $booking_period);
                         $avail_arr = $this->getBusAvailability($bus['id'], $store_val, $this->option_arr);
                        // $booked_seat_arr = $avail_arr['booked_seat_arr'];
                         $bus_id = $bus['id'];
                         $option_arr = $this->option_arr;
                         $booked_seat_arr = pjBookingSeatModel::factory ()->select ( "DISTINCT seat_id" )->where ( "t1.booking_id IN(SELECT TB.id
                                                    FROM `" . pjBookingModel::factory ()->getTable () . "` AS TB
                                                    WHERE (TB.status='confirmed' OR (TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL " . $option_arr ['o_min_hour'] . " MINUTE))))
                            AND TB.bus_id = $bus_id AND TB.bus_departure_date = '$date')")->findAll ()->getData ();

                        
                         $seat_id_array = array();
                         if($booked_seat_arr){
                            foreach($booked_seat_arr as $k => $v){
                                array_push($seat_id_array, $v['seat_id']);
                            }
                         }
                         $selected_seats = array();
                        if(!empty($seat_id_array))
                        {
                            $selected_seats = pjSeatModel::factory()->select("name as id")->whereIn('id', $seat_id_array)->findAll()->getData ();
                        }
                        $to_arr['already_booked_seats'] = $selected_seats;

                         $ticket_price_arr = $pjPriceModel->getTicketPrice($bus['id'], $pickup_id, $return_id, $_POST, $this->option_arr, $this->getLocaleId(), 'F');
                         if($ticket_price_arr){
                            foreach($ticket_price_arr as $key => $val){
                                if($key == 'sub_total_format'){
                                    $strip_slashed_val = stripslashes($val);
                                    $quote_removed_val = str_replace('""','',$strip_slashed_val);
                                    $strip_tagged_val = strip_tags($quote_removed_val);
                                    $ticket_price_arr[$key] = $strip_tagged_val;
                                }
                                if($key == 'tax_format'){
                                    $strip_slashed_val = stripslashes($val);
                                    $quote_removed_val = str_replace('""','',$strip_slashed_val);
                                    $strip_tagged_val = strip_tags($quote_removed_val);
                                    $ticket_price_arr[$key] = $strip_tagged_val;
                                }
                                if($key == 'total_format'){
                                    $strip_slashed_val = stripslashes($val);
                                    $quote_removed_val = str_replace('""','',$strip_slashed_val);
                                    $strip_tagged_val = strip_tags($quote_removed_val);
                                    $ticket_price_arr[$key] = $strip_tagged_val;
                                }
                                if($key == 'deposit_format'){
                                    $strip_slashed_val = stripslashes($val);
                                    $quote_removed_val = str_replace('""','',$strip_slashed_val);
                                    $strip_tagged_val = strip_tags($quote_removed_val);
                                    $ticket_price_arr[$key] = $strip_tagged_val;
                                }
                                
                            }
                            
                         }

                         $to_arr['price'] = $ticket_price_arr;
                    }
                    array_push($data['to'], $to_arr);
                }

            }
            
            if(isset($_GET['is_return']) && ($_GET['is_return'] == 'T')){
                $return_bus_id_arr = $pjBusModel->getBusIds($date, $pickup_id, $return_id);  
                if($return_bus_id_arr)
                {
                   
                    $return_bus_list = $this->getBusList($pickup_id, $return_id, $return_bus_id_arr, $booking_period, $booked_data, $date, 'T');
                        
                    $booking_period = $return_bus_list['booking_period'];
                    $return_bus_arr = $return_bus_list['bus_arr'];
                    $data['come_back'] = array();
                    foreach($return_bus_arr as $return_bus)
                    {
                        $return_arr = array();
                        $return_arr['bus'] = $return_bus['route'];
                        $return_arr['available_seats'] = $return_bus['seats_available'];
                        $pjPriceModel = pjPriceModel::factory();
                        if($return_bus['id'] > 0){
                            $store_val = array('pickup_id' => $pickup_id, 'return_id' => $return_id, 'booking_period' => $booking_period);
                             $avail_arr = $this->getBusAvailability($return_bus['id'], $store_val, $this->option_arr);
                             //$booked_seat_arr = $avail_arr['booked_seat_arr'];
                             $return_bus_id = $return_bus['id'];
                             $option_arr = $this->option_arr;
                             $booked_seat_arr = pjBookingSeatModel::factory ()->select ( "DISTINCT seat_id" )->where ( "t1.booking_id IN(SELECT TB.id
                                                    FROM `" . pjBookingModel::factory ()->getTable () . "` AS TB
                                                    WHERE (TB.status='confirmed' OR (TB.status='pending' AND UNIX_TIMESTAMP(TB.created) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL " . $option_arr ['o_min_hour'] . " MINUTE))))
                            AND TB.bus_id = $return_bus_id AND TB.bus_departure_date = '$date')")->findAll ()->getData ();

                             $seat_id_array = array();
                            if($booked_seat_arr){
                                foreach($booked_seat_arr as $k => $v){
                                    array_push($seat_id_array, $v['seat_id']);
                                }
                            }
                            $selected_seats = array();
                            if(!empty($seat_id_array))
                            {
                                $selected_seats = pjSeatModel::factory()->select("name as id")->whereIn('id', $seat_id_array)->findAll()->getData ();
                            }
                            $return_arr['already_booked_seats'] = $selected_seats;

                            $ticket_price_arr = $pjPriceModel->getTicketPrice($return_bus['id'], $pickup_id, $return_id, $_POST, $this->option_arr, $this->getLocaleId(), 'T');
                            if($ticket_price_arr){
                            foreach($ticket_price_arr as $key => $val){
                                if($key == 'sub_total_format'){
                                    $strip_slashed_val = stripslashes($val);
                                    $quote_removed_val = str_replace('""','',$strip_slashed_val);
                                    $strip_tagged_val = strip_tags($quote_removed_val);
                                    $ticket_price_arr[$key] = $strip_tagged_val;
                                }
                                if($key == 'tax_format'){
                                    $strip_slashed_val = stripslashes($val);
                                    $quote_removed_val = str_replace('""','',$strip_slashed_val);
                                    $strip_tagged_val = strip_tags($quote_removed_val);
                                    $ticket_price_arr[$key] = $strip_tagged_val;
                                }
                                if($key == 'total_format'){
                                    $strip_slashed_val = stripslashes($val);
                                    $quote_removed_val = str_replace('""','',$strip_slashed_val);
                                    $strip_tagged_val = strip_tags($quote_removed_val);
                                    $ticket_price_arr[$key] = $strip_tagged_val;
                                }
                                if($key == 'deposit_format'){
                                    $strip_slashed_val = stripslashes($val);
                                    $quote_removed_val = str_replace('""','',$strip_slashed_val);
                                    $strip_tagged_val = strip_tags($quote_removed_val);
                                    $ticket_price_arr[$key] = $strip_tagged_val;
                                }
                                
                            }
                            
                         }
                            $return_arr['price'] = $ticket_price_arr;
                        }
                        array_push($data['come_back'], $return_arr);
                    }
                }
            }
            $response = array(
                //'status' => 'true',
                'data' => $data
            );    
            //pjAppController::jsonResponse($response);
        }else{

            $response = array();
            $response['data'] = array(
                //'status' => 'false',
                'message' => 'No data found'
            );  
            //pjAppController::jsonResponse($response);
        }
        return $response;
    }

    /*after final submission an api call which returns bus booked successfully*/
    public function pjActionConfirmation(){
        //when clicking confirm button user will get booking_id and code
        $booking_id = $_GET['booking_id'];
        $code = $_GET['code'];
        if(isset($code) && ($code == 200)){
            if($booking_id > 0){
                $response = array(
                    'status' => 'true',
                    'message' => 'Bus booked successfully'
                );
                pjAppController::jsonResponse($response);
 
            }
        }
        $response = array(
                'status' => 'false',
                'message' => 'Failed to book bus..'
            ); 
        pjAppController::jsonResponse($response);

    }
    /* public function pjActionSaveBooking(){
    

     }*/

     // Combine  Check bus availability api  and For available buses, show available seats and price api as a single API.
     public function pjActionBusData(){
        $resp_availability = $this->pjActionAvailability();
        $response_data = array();
        if(isset($resp_availability['code']) && ($resp_availability['code'] != 200)){
            $response = array(
                'status' => 'false',
                'code' => $resp_availability['code'],
                'message' => 'Bus not available'
            );
        }else{
            $seat_price_data = $this->pjActionSeats();
            $response = array(
                'status' => 'true',
                'code' => $resp_availability['code'],
                'data' => $seat_price_data['data']
            );
        }
         pjAppController::jsonResponse($response); 

     }

     public function pjActionBooking() {
        $pickup_id = (isset($_POST['departure_city_id']) && trim($_POST['departure_city_id'])) ? trim($_POST['departure_city_id']) : '';
        $return_id = (isset($_POST['destination_city_id']) && trim($_POST['destination_city_id'])) ? trim($_POST['destination_city_id']) : '';
        $booking_date = (isset($_POST['booking_date']) && trim($_POST['booking_date'])) ? trim($_POST['booking_date']) : '';
        $bus_id = (isset($_POST['bus_id']) && trim($_POST['bus_id'])) ? trim($_POST['bus_id']) : '';
        $seats = (isset($_POST['seat_ids']) && trim($_POST['seat_ids'])) ? trim($_POST['seat_ids']) : '';
        $title = (isset($_POST['title']) && trim($_POST['title'])) ? trim($_POST['title']) : '';
        $first_name = (isset($_POST['first_name']) && trim($_POST['first_name'])) ? trim($_POST['first_name']) : ''; 
        $last_name = (isset($_POST['last_name']) && trim($_POST['last_name'])) ? trim($_POST['last_name']) : '';
        $email = (isset($_POST['email']) && trim($_POST['email'])) ? trim($_POST['email']) : '';
        $phone = (isset($_POST['phone']) && trim($_POST['phone'])) ? trim($_POST['phone']) : '';
        $return_date = (isset($_POST['return_date']) && trim($_POST['return_date'])) ? trim($_POST['return_date']) : '';
        $is_return = (isset($_POST['is_return']) && trim($_POST['is_return'])) ? trim($_POST['is_return']) : 'F';
        $is_user = (isset($_POST['is_user']) && (trim($_POST['is_user']) == 'T')) ? 'T' : 'F';  
        $country_id = (isset($_POST['country_id']) && trim($_POST['country_id'])) ? trim($_POST['country_id']) : '';  
        $user_id = (isset($_POST['user_id']) && trim($_POST['user_id'])) ? trim($_POST['user_id']) : 0;

        $return_bus_id = (isset($_POST['return_bus_id']) && trim($_POST['return_bus_id'])) ? trim($_POST['return_bus_id']) : '';//for ret
        $return_seats = (isset($_POST['return_seat_ids']) && trim($_POST['return_seat_ids'])) ? trim($_POST['return_seat_ids']) : '';
        $payment_status = (isset($_POST['payment_status']) && trim($_POST['payment_status'])) ? trim($_POST['payment_status']) : '';
        $request_id = (isset($_POST['request_id']) && trim($_POST['request_id'])) ? trim($_POST['request_id']) : '';
        $total_price = (isset($_POST['total_price']) && trim($_POST['total_price'])) ? trim($_POST['total_price']) : 0; 
        

        if ($pickup_id && $return_id && $booking_date && $bus_id && $seats && $first_name && $last_name && $phone) {

            //check credit limit for agent booking
            $is_agent_booking = 0;
            if(isset($user_id) && ($user_id > 0)){
                $pjUserModelChk = pjUserModel::factory();
                $userCheckResult = $pjUserModelChk
                ->select('t1.role_id')
                ->join('pjRole', 't2.id=t1.role_id', 'left')
                ->where('t1.id', $user_id)
                ->limit(1)
                ->findAll()
                ->getData();
                $userChk = $userCheckResult[0];
                if($userChk['role_id'] == $this->agent_role){
                    $is_agent_booking = 1;
                    $user_credit = pjCreditModel::factory()
                    ->where('t1.user_id', $user_id)
                    ->findAll()
                    ->getData();
                    if (($total_price > 0) && (empty($user_credit) || ($user_credit[0]['credit'] < $total_price))) {
                        $response = array(
                            'status' => 'false',
                            'message' => "Booking could not be added since you do not have enough credit balance."
                        );
                       pjAppController::jsonResponse($response); 
                    }

                }
            }

            $seats = trim($seats);
            $seats = rtrim($seats, ',');
            $seats_booked = explode(',',$seats);
            $seats_count = count($seats_booked);
            //$selected_seats = implode('|',$seats_booked);

            $arr = pjBusModel::factory()->find($bus_id)->getData();
            $bus_type_id = $arr['bus_type_id'];
            $selected_seats_unique_ids = [];
            if(count($seats_booked)){
                foreach($seats_booked as $ky => $vl){
                    if(!empty($vl)){
                        $seat_name = $vl;
                        /*$selected_seats = pjSeatModel::factory()->select("id")
                        ->where("(name = '$seat_name' AND bus_type_id = '$bus_type_id')")
                        ->find()->getData ();*/
                         $pjSeatModel = pjSeatModel::factory();
                         $selected_seats = $pjSeatModel->where('bus_type_id',$bus_type_id)->where('name',$seat_name)->findAll()->getData();
                         if(isset($selected_seats[0]['id'])){
                            array_push($selected_seats_unique_ids, $selected_seats[0]['id']);
                         }

                    }
                    
                }
            }
            $selected_seats = implode('|', $selected_seats_unique_ids);
            
            //return seats
            if(isset($is_return) && ($is_return == 'T')){
                $return_seats = trim($return_seats);
                $return_seats = rtrim($return_seats, ',');
                $return_seats_booked = explode(',',$return_seats);
                $return_seats_count = count($return_seats_booked);
                $r_arr = pjBusModel::factory()->find($return_bus_id)->getData();
                $r_bus_type_id = $r_arr['bus_type_id'];
                $r_selected_seats_unique_ids = [];
                if(count($return_seats_booked)){
                    foreach($return_seats_booked as $ky => $vl){
                        if(!empty($vl)){
                            $r_seat_name = $vl;
                            /*$selected_seats = pjSeatModel::factory()->select("id")
                            ->where("(name = '$seat_name' AND bus_type_id = '$bus_type_id')")
                            ->find()->getData ();*/
                             $pjSeatModel = pjSeatModel::factory();
                             $r_selected_seats = $pjSeatModel->where('bus_type_id',$r_bus_type_id)->where('name',$r_seat_name)->findAll()->getData();
                             if(isset($r_selected_seats[0]['id'])){
                                array_push($r_selected_seats_unique_ids, $r_selected_seats[0]['id']);
                             }

                        }
                        
                    }
                }
                $r_selected_seats = implode('|', $r_selected_seats_unique_ids);

            }
            //return seats end

            $booked_data = array();
            $available_seats = array();

            $pjTicketModel = pjTicketModel::factory();
            $ticket_id_arr = $pjTicketModel->where('bus_id',$bus_id)
                    ->limit(1)
                    ->findAll()
                    ->getData();

            if (isset($ticket_id_arr[0]) && count($ticket_id_arr[0]) > 0) {
                $ticket_id = $ticket_id_arr[0]['id'];
            } else {
                $ticket_id = '';
            }       

            $pjPriceModel = pjPriceModel::factory();
            $ticket_price_arr = $pjPriceModel->where('bus_id',$bus_id)
                ->where('ticket_id',$ticket_id)
                ->where('from_location_id',$pickup_id)
                ->where('to_location_id',$return_id)
                ->where('is_return',$is_return)
                ->findAll()
                ->getData();

            if (isset($ticket_price_arr[0]) && count($ticket_price_arr[0]) > 0 && $ticket_price_arr[0]['price']) {
                $ticket_price = $this->option_arr['o_currency'].' '.number_format($ticket_price_arr[0]['price'], 2);
            } else {
                $ticket_price = '';
            }

            //for return ticket
            $pjTicketModel = pjTicketModel::factory();
            $r_ticket_id_arr = $pjTicketModel->where('bus_id',$return_bus_id)
                    ->limit(1)
                    ->findAll()
                    ->getData();

            if (isset($r_ticket_id_arr[0]) && count($r_ticket_id_arr[0]) > 0) {
                $r_ticket_id = $r_ticket_id_arr[0]['id'];
            } else {
                $r_ticket_id = '';
            }  

            $pjPriceModel = pjPriceModel::factory();
            $r_ticket_price_arr = $pjPriceModel->where('bus_id',$return_bus_id)
                ->where('ticket_id',$r_ticket_id)
                ->where('from_location_id',$return_id)
                ->where('to_location_id',$pickup_id)
                ->where('is_return',$is_return)
                ->findAll()
                ->getData();

            if (isset($r_ticket_price_arr[0]) && count($r_ticket_price_arr[0]) > 0 && $r_ticket_price_arr[0]['price']) {
                $r_ticket_price = $this->option_arr['o_currency'].' '.number_format($r_ticket_price_arr[0]['price'], 2);
            } else {
                $r_ticket_price = '';
            }
            //for return ticket ends
           
            if ($ticket_id) {
                 
                $booked_data = array(
                    'ticket_cnt_'.$ticket_id => $seats_count,
                    'selected_ticket' => $seats_count,
                    'selected_seats' => $selected_seats,
                    'bus_id' => $bus_id,
                    'has_map' => 'T'
                );

                if(isset($is_return) && ($is_return == 'T')){
                    $booked_data['return_bus_id'] = $return_bus_id;//for ret
                    $booked_data['return_selected_seats'] = $r_selected_seats;//for ret
                    $booked_data['return_ticket_cnt_'.$r_ticket_id] = $return_seats_count;//for ret
                    $booked_data['return_selected_ticket'] = $return_seats_count;//for ret
                }

                $store = array(
                    'pickup_id' => $pickup_id,
                    'return_id' => $return_id,
                    'date' => $booking_date,
                    'return_date' => $return_date,
                    'bus_id_arr' => array($bus_id),
                    'is_return' => $is_return,
                    'booked_data' => $booked_data,
                    'request_id' => $request_id
                );

                if(isset($is_return) && ($is_return == 'T')){
                    $store['return_bus_id_arr'] = array($return_bus_id);
                }

                $form = array(
                    'step_checkout' => 1,
                    'c_title' => $title,
                    'c_fname' => $first_name,
                    'c_lname' => $last_name,
                    'c_phone_country' => $country_id,
                    'c_phone' => $phone,
                    'c_email' => $email,
                    'c_company' => '',
                    'c_notes' => '',
                    'c_address' => '',
                    'c_city' => '',
                    'c_state' => '',
                    'c_zip' => '',
                    'c_country' => $country_id,
                    'payment_method' => 'cash',
                    'agreement' => 'on'
                );

                $date = pjUtil::formatDate($booking_date, $this->option_arr['o_date_format']);
                $booking_period = array();            

                $pjBusModel = pjBusModel::factory();
                $bus_id_arr = $pjBusModel->getBusIds($date, $pickup_id, $return_id);

                if ($bus_id_arr) {
                    $bus_list = $this->getBusList($pickup_id, $return_id, $bus_id_arr, $booking_period, $booked_data, $booking_date, 'F');
                    
                    if (isset($bus_list['booking_period'])) {
                        $time = $bus_list['booking_period'];
                        $store['booking_period'][$bus_id] = $time[$bus_id];

                        $avail_arr = $this->getBusAvailability($bus_id, $store, $this->option_arr);
                        $booked_seats = $avail_arr['booked_seat_arr'];
    
                        if(!empty($avail_arr['bus_type_arr'])) {
                            $all_seats = pjSeatModel::factory()->where('bus_type_id', $avail_arr['bus_type_arr']['id'])->findAll()->getData();
                        }else{
                            $all_seats = array();
                        }
                        if ($all_seats) {
                            foreach ($all_seats as $seat) {
                                if (!in_array($seat['id'],$booked_seats)) {
                                    $available_seats[] = array(
                                        'seat_id' => $seat['id'],
                                        'seat_name' => $seat['name']
                                    );
                                }
                            }
                        }
                    }
                } 


                //for return
                if(isset($is_return) && ($is_return == 'T')){
                    $formatted_return_date = pjUtil::formatDate($return_date, $this->option_arr['o_date_format']);
                        $r_booking_period = array();            

                        $pjBusModel = pjBusModel::factory();
                        $r_bus_id_arr = $pjBusModel->getBusIds($formatted_return_date, $return_id, $pickup_id);

                        if ($r_bus_id_arr) {
                            $r_bus_list = $this->getBusList($return_id, $pickup_id, $r_bus_id_arr, $r_booking_period, $booked_data, $return_date, 'F');


                            if (isset($r_bus_list['booking_period'])) {
                                $time = $r_bus_list['booking_period'];
                                $store['booking_period'][$return_bus_id] = $time[$return_bus_id];

                                $r_avail_arr = $this->getReturnBusAvailability($return_bus_id, $store, $this->option_arr);
                                $r_booked_seats = $r_avail_arr['booked_seat_arr'];
            
                                if(!empty($r_avail_arr['bus_type_arr'])) {
                                    $r_all_seats = pjSeatModel::factory()->where('bus_type_id', $r_avail_arr['bus_type_arr']['id'])->findAll()->getData();
                                }else{
                                    $r_all_seats = array();
                                }
                                if ($r_all_seats) {
                                    foreach ($r_all_seats as $r_seat) {
                                        if (!in_array($r_seat['id'],$r_booked_seats)) {
                                            $r_available_seats[] = array(
                                                'seat_id' => $r_seat['id'],
                                                'seat_name' => $r_seat['name']
                                            );
                                        }
                                    }
                                }
                            }
                        }

                }
                //for return ends

            }


            $data = array();
            if ($available_seats) {

                //$intersect = array_intersect($booked_seats, $seats_booked);
                $intersect = array_intersect($booked_seats, $selected_seats_unique_ids);

                //for return
                if(isset($is_return) && ($is_return == 'T') && (count($r_available_seats) == 0)){
                    $data = array(
                        'success' => 0,
                        'message' => 'Return ticket not available'
                    );
                    pjAppController::jsonResponse($data);
                }

                if(isset($is_return) && ($is_return == 'T')){
                    $r_intersect = array_intersect($r_booked_seats, $r_selected_seats_unique_ids);

                    if(count($r_intersect) > 0) {
                        $data = array(
                            'success' => 0,
                            'message' => 'Return ticket not available(already booked seats chosen)'
                        );
                        pjAppController::jsonResponse($data);
                    }
                }

                
                //for return ends

                if(count($intersect) > 0) {
                    $data = array(
                        'success' => 0,
                        'message' => 'Ticket not available(already booked seats chosen)'
                    );
                } else {
                    $params = array(
                        'is_api' => true,
                        'is_user' => $is_user,
                        'payment_status' => $payment_status,
                        'api_store' => $store,
                        'api_form' => $form,
                        'user_id' => $user_id

                    );


                    $result = $this->requestAction(array('controller' => 'pjFrontEnd', 'action' => 'pjActionSaveBooking', 'params' => $params), array('return'));
                    if ($result['code'] == 200) {
                        /*$data = array(
                            'success' => 1,
                            'departure_city_id' => $pickup_id,
                            'destination_city_id' => $return_id,
                            'booking_date' => $booking_date,
                            'bus_id' => $bus_id,
                            'ticket_price' => $ticket_price,
                            'seats' => $seats,
                            'name' => $first_name,
                            'phone' => $phone,
                            'message' => 'Booking successful'
                        ); */

                        //reduce credit limit of agent if booking status is confirmed and booking done by agent
                        if(isset($is_agent_booking) && ($is_agent_booking == 1) && isset($result['additional_data']['total_price']) && ($result['additional_data']['status'] == 'confirmed')){
                            $new_credit = $user_credit[0]['credit'] - $result['additional_data']['total_price'];
                            $res = pjCreditModel::factory()->whereIn('user_id', $user_id)->modifyAll(array(
                                'credit' => $new_credit));
                        }
                        
                        $return_data = array();
                        $return_data['booking_id'] = $result['booking_id'];
                        $return_data['user_id'] = $result['additional_data']['user_id'];
                        $return_data['unique_id'] = $result['additional_data']['unique_id'];
                        $return_data['travel_date'] = $booking_date;
                        $return_data_name = strip_tags($result['additional_data']['name']);

                        $return_data_name_arr = explode(",", $return_data_name);
                        $return_data['name'] = $return_data_name_arr[0];

                        $return_data['status'] = $result['additional_data']['status'];
                        $return_data['title'] = $title;
                        $return_data['person_firstname'] = $result['additional_data']['person_firstname'];
                        $return_data['person_lastname'] = $result['additional_data']['person_lastname'];
                        $return_data['person_phone'] = $result['additional_data']['person_phone'];
                        $return_data['person_email'] = $result['additional_data']['person_email'];
                        $return_data['from_location_name'] = $result['additional_data']['from_location_name'];
                        $return_data['to_location_name'] = $result['additional_data']['to_location_name'];
                        $return_data['total_price'] = $result['additional_data']['total_price'];
                        //$return_data['total_no_of_seatsbooked'] = $result['additional_data']['total_no_of_seatsbooked'];
                        $return_data['total_no_of_seatsbooked'] = $seats;

                        $return_data['departure_time'] = $result['additional_data']['departure_time'];
                        $return_data['arrival_time'] = $result['additional_data']['arrival_time'];
                        $return_data['payment_mode'] = $result['additional_data']['payment_mode'];

                        //for return trip in case of round trip
                        if(isset($is_return) && ($is_return == 'T')){
                            $return_data['r_booking_id'] = $result['additional_data']['r_booking_id'];
                            $return_data['r_unique_id'] = $result['additional_data']['r_unique_id'];
                            $return_data['return_date'] = $return_date;
                            
                            $return_data_r_name = strip_tags($result['additional_data']['r_name']);
                            $return_data_r_name_arr = explode(",", $return_data_r_name);
                            $return_data['r_name'] = $return_data_r_name_arr[0];
                            $return_data['r_status'] = $result['additional_data']['r_status'];
                            $return_data['r_from_location_name'] = $result['additional_data']['r_from_location_name'];
                            $return_data['r_to_location_name'] = $result['additional_data']['r_to_location_name'];
                            $return_data['r_total_price'] = $result['additional_data']['r_total_price'];
                            $return_data['r_total_no_of_seatsbooked'] = $return_seats;
                            $return_data['r_departure_time'] = $result['additional_data']['r_departure_time'];
                            $return_data['r_arrival_time'] = $result['additional_data']['r_arrival_time'];

                        }
                        //for return trip in case of round trip: ends


                        $data = array(
                            'status' => 'true',
                            'data' => $return_data,
                            'message' => 'Booking successful'
                        ); 
                    } else {
                        $data = array(
                            'status' => 'false',
                            'message' => 'Some technical problem occured'
                        ); 
                    }
                }
            } else{
                $data = array(
                        'success' => 0,
                        'message' => 'Ticket not availableee'
                    );
            }
        } else {
            $data = array(
                'status' => 'false',
                'message' => 'Need to pass all parameters'
            );
        }

        pjAppController::jsonResponse($data);
    }

    public function pjActionStatus(){
        $booking_id = (isset($_POST['booking_id']) && trim($_POST['booking_id'])) ? trim($_POST['booking_id']) : '';
        $agent_user_id = (isset($_POST['agent_user_id']) && trim($_POST['agent_user_id'])) ? trim($_POST['agent_user_id']) : '';
        $status = (isset($_POST['status']) && trim($_POST['status'])) ? trim($_POST['status']) : '';
        $token = (isset($_POST['token']) && trim($_POST['token'])) ? trim($_POST['token']) : '';

        //token validation check
        $token_validation_result = Token::validate($token, $this->jwt_secret);
        if($token_validation_result){
            //update status
            $booking_ids = trim($booking_id);
            $booking_ids = rtrim($booking_ids, ',');
            $booking_ids_arr = explode(',',$booking_ids);
            if(count($booking_ids_arr)){
                foreach($booking_ids_arr as $k => $id){
                    if($id > 0){

                        //reduce credit limit
                        $pjBookingModel = pjBookingModel::factory ();
                        $booking_details = $pjBookingModel->reset()->select('total,status')->find($id)->getData ();

                        $user_credit = pjCreditModel::factory()
                        ->where('t1.user_id', $agent_user_id)
                        ->findAll()
                        ->getData();
                        if (($status == 'confirmed') && ($booking_details['status'] != 'confirmed') && (empty($user_credit) || ($user_credit[0]['credit'] < $booking_details['total']))) {
                            $response = array(
                                'status' => 'false',
                                'message' => "Booking could not be confirmed since you do not have enough credit balance."
                            );
                           pjAppController::jsonResponse($response); 
                        }
                        $res = pjApiBooking::updateStatus($agent_user_id, $id, $status);
                        if($status == 'confirmed'){
                           $new_credit = $user_credit[0]['credit'] - $booking_details['total'];
                           $res = pjCreditModel::factory()->whereIn('user_id', $agent_user_id)->modifyAll(array('credit' => $new_credit));
                        }
                    }
                }
                $data = array('status' => $status);
                $resp = array(
                                'status' => 'true',
                                'data' => $data,
                                'message' => 'Status updated'
                            );
            }
             
        }else{
            $resp = array(
                            'status' => 'false',
                            'message' => 'Invalid token, please login and update status'
                        );
        }
       pjAppController::jsonResponse($resp);  
    }

    public function updateStatus($agent_user_id, $booking_id, $status){
        $pjBookingModel = pjBookingModel::factory();       
        $pjBookingModel->reset()->set('id', $booking_id)->modify(
            array(
                'status' => $status,
                'status_updated_by' => $agent_user_id,
                'status_updated_on' => date("Y-m-d H:i:s")
            )
        );
    }
    
   
}
?>