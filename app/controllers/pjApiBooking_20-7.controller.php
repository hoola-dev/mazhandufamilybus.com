<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjApiBooking extends pjFront
{

	public $defaultStep = 'BusReservation_Step';
    public $defaultStore = 'BusReservation_Store';

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
        $data['return']  = array();
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
                         $booked_seat_arr = $avail_arr['booked_seat_arr'];
                         $to_arr['already_booked_seats'] = $booked_seat_arr;

                         $ticket_price_arr = $pjPriceModel->getTicketPrice($bus['id'], $pickup_id, $return_id, $_POST, $this->option_arr, $this->getLocaleId(), 'F');
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
                    $data['return'] = array();
                    foreach($return_bus_arr as $return_bus)
                    {
                        $return_arr = array();
                        $return_arr['bus'] = $return_bus['route'];
                        $return_arr['available_seats'] = $return_bus['seats_available'];
                        $pjPriceModel = pjPriceModel::factory();
                        if($return_bus['id'] > 0){
                            $store_val = array('pickup_id' => $pickup_id, 'return_id' => $return_id, 'booking_period' => $booking_period);
                             $avail_arr = $this->getBusAvailability($return_bus['id'], $store_val, $this->option_arr);
                             $booked_seat_arr = $avail_arr['booked_seat_arr'];
                             $return_arr['already_booked_seats'] = $booked_seat_arr;

                            $ticket_price_arr = $pjPriceModel->getTicketPrice($return_bus['id'], $pickup_id, $return_id, $_POST, $this->option_arr, $this->getLocaleId(), 'T');
                            $return_arr['price'] = $ticket_price_arr;
                        }
                        array_push($data['return'], $return_arr);
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
   
}
?>