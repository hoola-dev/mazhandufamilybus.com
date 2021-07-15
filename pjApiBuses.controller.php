<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjApiBuses extends pjFront
{

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
                    $arr['total_seatss'] = $total_seats;

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
   
}
?>