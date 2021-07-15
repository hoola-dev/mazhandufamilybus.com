<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjDummyBooking extends pjFront
{

     public function pjNames(){
        $nums = pjDummyBooking::pjNumbers();
        echo "<pre>";
        print_r($nums);
        exit;


        $pathName = __DIR__ ."/Names.txt";
        $file = fopen($pathName, "r");
        $namesArr = file($pathName, FILE_IGNORE_NEW_LINES);
        return $namesArr;
     }

     public function pjNumbers(){
        $pathName = __DIR__ ."/numbers.txt";
        $file = fopen($pathName, "r");
        $numbersArr = file($pathName, FILE_IGNORE_NEW_LINES);
        return $numbersArr;
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

        if ($pickup_id && $return_id && $booking_date && $bus_id && $seats && $first_name && $last_name && $phone) {
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


                    $result = $this->requestAction(array('controller' => 'pjDummyBook', 'action' => 'pjActionSaveBooking', 'params' => $params), array('return'));
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
   
}
?>