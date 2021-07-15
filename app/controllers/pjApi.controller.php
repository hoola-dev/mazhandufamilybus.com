<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjApi extends pjFront
{
    public function pjActionDateFormat() {     
        $data['date_format'] = $this->option_arr['o_date_format'];

        echo json_encode($data);
        exit;
    }	

	public function pjActionDeparture() {
        $departure = (isset($_POST['departure_city']) && trim($_POST['departure_city'])) ? trim($_POST['departure_city']) : '';

        if ($departure) {
            $pjMultiLang = pjMultiLangModel::factory();
            $cities_array = $pjMultiLang
                    ->reset()
                    ->select('t1.*')	
                    ->join('pjCity', "t2.id = t1.foreign_id AND t2.status='T'", 'left outer')			
                    ->where("model='pjCity' AND locale=1 AND soundex(content) LIKE soundex('$departure')")
                    ->orderBy("content ASC")
                    ->findAll()
                    ->getData();
            
            
            $cities = array();
            if ($cities_array) {
                foreach ($cities_array as $city) {
                    $cities[] = array(                    
                        'id' => $city['foreign_id'],
                        'name' => $city['content']
                    );
                }
            }

            if ($cities) {
                $data = array(
                    'success' => 1,
                    'cities' => $cities
                );
            } else {
                $data = array(
                    'success' => 0,
                    'message' => 'No data found'
                );
            }
        } else {
            $data = array(
                'success' => 0,
                'message' => 'Need to pass the required parameter'
            );
        }

        echo json_encode($data);
        exit;
    }	
    
    public function pjActionDestination() {
        $destination = (isset($_POST['destination_city']) && trim($_POST['destination_city'])) ? trim($_POST['destination_city']) : '';

        if ($destination) {
            $pjMultiLang = pjMultiLangModel::factory();
            $cities_array = $pjMultiLang
                    ->reset()
                    ->select('t1.*')	
                    ->join('pjCity', "t2.id = t1.foreign_id AND t2.status='T'", 'left outer')			
                    ->where("model='pjCity' AND locale=1 AND soundex(content) LIKE soundex('$destination')")
                    ->orderBy("content ASC")
                    ->findAll()
                    ->getData();
            
            
            $cities = array();
            if ($cities_array) {
                foreach ($cities_array as $city) {
                    $cities[] = array(                    
                        'id' => $city['foreign_id'],
                        'name' => $city['content']
                    );
                }
            }
            
            if ($cities) {
                $data = array(
                    'success' => 1,
                    'cities' => $cities
                );
            } else {
                $data = array(
                    'success' => 0,
                    'message' => 'No data found'
                );
            }
        } else {
            $data = array(
                'success' => 0,
                'message' => 'Need to pass the required parameter'
            );
        }

        echo json_encode($data);
        exit;
    }
    
    public function pjActionTime() {
        $pickup_id = (isset($_POST['departure_city_id']) && trim($_POST['departure_city_id'])) ? trim($_POST['departure_city_id']) : '';
        $return_id = (isset($_POST['destination_city_id']) && trim($_POST['destination_city_id'])) ? trim($_POST['destination_city_id']) : '';
        $booking_date = (isset($_POST['booking_date']) && trim($_POST['booking_date'])) ? trim($_POST['booking_date']) : '';
        
        if ($pickup_id && $return_id && $booking_date) {
            $date = pjUtil::formatDate($booking_date, $this->option_arr['o_date_format']);
            $booking_period = array();
            $booked_data = array();

            $time = array();

            $pjBusModel = pjBusModel::factory();
            $bus_id_arr = $pjBusModel->getBusIds($date, $pickup_id, $return_id);

            if ($bus_id_arr) {
                $bus_list = $this->getBusList($pickup_id, $return_id, $bus_id_arr, $booking_period, $booked_data, $booking_date, 'F');

                if ($bus_list && $bus_list['bus_arr']) {
                    foreach ($bus_list['bus_arr'] as $bus) {
                        $location_array = array();
                        foreach ($bus['locations'] as $location) {
                            $location_array[] = array(
                                'city' => $location['content'],
                                'arrival_time' => $location['arrival_time'],
                                'departure_time' => $location['departure_time']                                
                            );
                        }
                        $time[] = array(
                            'bus_id' => $bus['id'],
                            'departure_time' => $bus['departure_time'],
                            'arrival_time' => $bus['arrival_time'],
                            'duration' => $bus['duration'],
                            'seats_available' => $bus['seats_available'],
                            'locations' => $location_array
                        );
                    }
                }
            }

            if ($time) {
                $data = array(
                    'success' => 1,
                    'departure_city_id' => $pickup_id,
                    'destination_city_id' => $return_id,
                    'booking_date' => $booking_date,
                    'time' => $time
                );
            } else {
                $data = array(
                    'success' => 0,
                    'message' => 'No data found'
                );
            }
        } else {
            $data = array(
                'success' => 0,
                'message' => 'Need to pass all parameters'
            );
        }

        echo json_encode($data);
        exit;
    }

    public function pjActionSeats() {
        $pickup_id = (isset($_POST['departure_city_id']) && trim($_POST['departure_city_id'])) ? trim($_POST['departure_city_id']) : '';
        $return_id = (isset($_POST['destination_city_id']) && trim($_POST['destination_city_id'])) ? trim($_POST['destination_city_id']) : '';
        $booking_date = (isset($_POST['booking_date']) && trim($_POST['booking_date'])) ? trim($_POST['booking_date']) : '';
        $bus_id = (isset($_POST['bus_id']) && trim($_POST['bus_id'])) ? trim($_POST['bus_id']) : '';

        if ($pickup_id && $return_id && $booking_date && $bus_id) {
            $booked_data = array();
            $available_seats = array();
            $location_array = array();

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
                ->where('is_return','F')
                ->findAll()
                ->getData();

            if (isset($ticket_price_arr[0]) && count($ticket_price_arr[0]) > 0 && $ticket_price_arr[0]['price']) {
                $ticket_price = $this->option_arr['o_currency'].' '.number_format($ticket_price_arr[0]['price'], 2);
            } else {
                $ticket_price = '';
            }
            
            if ($ticket_id && $ticket_price) {
                $store['pickup_id'] = $pickup_id;
                $store['return_id'] = $return_id;

                $date = pjUtil::formatDate($booking_date, $this->option_arr['o_date_format']);
                $booking_period = array();
                $booked_data = array();     

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

                    if (isset($bus_list['bus_arr'][0]['locations']) && $bus_list['bus_arr'][0]['locations']) {
                        foreach ($bus_list['bus_arr'][0]['locations'] as $location) {
                            $location_array[] = array(
                                'city' => $location['content'],
                                'arrival_time' => $location['arrival_time'],
                                'departure_time' => $location['departure_time']
                            );
                        }
                    }
                }       
            }

            if ($available_seats) {
                $data = array(
                    'success' => 1,
                    'departure_city_id' => $pickup_id,
                    'destination_city_id' => $return_id,
                    'booking_date' => $booking_date,
                    'bus_id' => $bus_id,
                    'ticket_price' => $ticket_price,
                    'available_seats' => $available_seats,
                    'locations' => $location_array
                );
            } else {
                $data = array(
                    'success' => 0,
                    'message' => 'No data found'
                );
            }
        } else {
            $data = array(
                'success' => 0,
                'message' => 'Need to pass all parameters'
            );
        }

        echo json_encode($data);
        exit;
    }   

    public function pjActionPrice() {
        $pickup_id = (isset($_POST['departure_city_id']) && trim($_POST['departure_city_id'])) ? trim($_POST['departure_city_id']) : '';
        $return_id = (isset($_POST['destination_city_id']) && trim($_POST['destination_city_id'])) ? trim($_POST['destination_city_id']) : '';
        $booking_date = (isset($_POST['booking_date']) && trim($_POST['booking_date'])) ? trim($_POST['booking_date']) : '';
        $bus_id = (isset($_POST['bus_id']) && trim($_POST['bus_id'])) ? trim($_POST['bus_id']) : '';
        $seats = (isset($_POST['seat_ids']) && trim($_POST['seat_ids'])) ? trim($_POST['seat_ids']) : '';

        if ($pickup_id && $return_id && $booking_date && $bus_id && $seats) {
            $seats_booked = explode(',',$seats);
            $seats_count = count($seats_booked);
            $selected_seats = implode('|',$seats_booked);

            $final_ticket_price_arr = array();

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
                ->where('is_return','F')
                ->findAll()
                ->getData();

            if (isset($ticket_price_arr[0]) && count($ticket_price_arr[0]) > 0 && $ticket_price_arr[0]['price']) {
                $ticket_price = $this->option_arr['o_currency'].' '.number_format($ticket_price_arr[0]['price'], 2);
            } else {
                $ticket_price = '';
            }
            
            if ($ticket_id && $ticket_price) {
                $booked_data = array(
                    'ticket_cnt_'.$ticket_id => $seats_count,
                    'selected_ticket' => $seats_count,
                    'selected_seats' => $selected_seats,
                    'bus_id' => $bus_id,
                    'has_map' => 'T'
                );

                $final_ticket_price_arr = $pjPriceModel->getTicketPrice($bus_id, $pickup_id, $return_id, $booked_data, $this->option_arr, $this->getLocaleId(), 'F');
            }

            if (isset($final_ticket_price_arr['ticket_arr']) && $final_ticket_price_arr['ticket_arr']) {
                $data = array(
                    'success' => 1,
                    'departure_city_id' => $pickup_id,
                    'destination_city_id' => $return_id,
                    'booking_date' => $booking_date,
                    'bus_id' => $bus_id,
                    'seats_count' => $seats_count,
                    'seat_ids' => $seats,
                    'ticket_price' => $final_ticket_price_arr['ticket_arr'][0]['price'],
                    'ticket_type' => $final_ticket_price_arr['ticket_arr'][0]['ticket'],
                    'tax' => $final_ticket_price_arr['tax'],
                    'total' => $final_ticket_price_arr['total'],
                    'currency' => $this->option_arr['o_currency']
                );
            } else {
                $data = array(
                    'success' => 0,
                    'message' => 'No data found'
                );
            }
        } else {
            $data = array(
                'success' => 0,
                'message' => 'Need to pass all parameters'
            );
        }

        echo json_encode($data);
        exit;
    }   

    public function pjActionBooking() {
        $pickup_id = (isset($_POST['departure_city_id']) && trim($_POST['departure_city_id'])) ? trim($_POST['departure_city_id']) : '';
        $return_id = (isset($_POST['destination_city_id']) && trim($_POST['destination_city_id'])) ? trim($_POST['destination_city_id']) : '';
        $booking_date = (isset($_POST['booking_date']) && trim($_POST['booking_date'])) ? trim($_POST['booking_date']) : '';
        $bus_id = (isset($_POST['bus_id']) && trim($_POST['bus_id'])) ? trim($_POST['bus_id']) : '';
        $seats = (isset($_POST['seat_ids']) && trim($_POST['seat_ids'])) ? trim($_POST['seat_ids']) : '';
        $first_last_name = (isset($_POST['name']) && trim($_POST['name'])) ? trim($_POST['name']) : '';  
        $phone = (isset($_POST['phone']) && trim($_POST['phone'])) ? trim($_POST['phone']) : '';    

        if ($pickup_id && $return_id && $booking_date && $bus_id && $seats && $first_last_name && $phone) {
            $seats_booked = explode(',',$seats);
            $seats_count = count($seats_booked);
            $selected_seats = implode('|',$seats_booked);
            $name = explode(' ',$first_last_name);
            $first_name = $name[0];
            $last_name = '';
            foreach ($name as $k=>$v) {
                if ($k == 0) {
                    continue;
                }
                $last_name.= $v.' ';
            }
            $last_name = trim($last_name);

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
                ->where('is_return','F')
                ->findAll()
                ->getData();

            if (isset($ticket_price_arr[0]) && count($ticket_price_arr[0]) > 0 && $ticket_price_arr[0]['price']) {
                $ticket_price = $this->option_arr['o_currency'].' '.number_format($ticket_price_arr[0]['price'], 2);
            } else {
                $ticket_price = '';
            }

            if ($ticket_id && $ticket_price) {
                $booked_data = array(
                    'ticket_cnt_'.$ticket_id => $seats_count,
                    'selected_ticket' => $seats_count,
                    'selected_seats' => $selected_seats,
                    'bus_id' => $bus_id,
                    'has_map' => 'T'
                );

                $store = array(
                    'pickup_id' => $pickup_id,
                    'return_id' => $return_id,
                    'date' => $booking_date,
                    'bus_id_arr' => array($bus_id),
                    'is_return' => 'F',
                    'booked_data' => $booked_data
                );

                $form = array(
                    'step_checkout' => 1,
                    'c_title' => '',
                    'c_fname' => $first_name,
                    'c_lname' => $last_name,
                    'c_phone_country' => '',
                    'c_phone' => $phone,
                    'c_email' => '',
                    'c_company' => '',
                    'c_notes' => '',
                    'c_address' => '',
                    'c_city' => '',
                    'c_state' => '',
                    'c_zip' => '',
                    'c_country' => '',
                    'payment_method' => 'cash',
                    'agreement' => 'on',
                    'is_api' => 1
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
            }

            $data = array();
            if ($available_seats) {
                $intersect = array_intersect($booked_seats, $seats_booked);
                if(!empty($intersect)) {
                    $data = array(
                        'success' => 0,
                        'message' => 'Ticket not available'
                    );
                } else {
                    $params = array(
                        'is_api' => true,
                        'is_combined_api' => false,
                        'api_store' => $store,
                        'api_form' => $form
                    );
                    $result = $this->requestAction(array('controller' => 'pjFrontEnd', 'action' => 'pjActionSaveBooking', 'params' => $params), array('return'));
                    if ($result['code'] == 200) {
                        $final_ticket_price_arr = $pjPriceModel->getTicketPrice($bus_id, $pickup_id, $return_id, $booked_data, $this->option_arr, $this->getLocaleId(), 'F');

                        $booking_arr = $_SESSION['api_arr'];
                        
                        $data = array(
                            'success' => 1,
                            'departure_city_id' => $pickup_id,
                            'destination_city_id' => $return_id,
                            'booking_date' => $booking_date,
                            'bus_id' => $bus_id,
                            'booking_route' => $booking_arr['booking_route'],
                            'departure_time' => $booking_arr['booking_datetime'],
                            'arrival_time' => $booking_arr['stop_datetime'],
                            'route_title' => $booking_arr['route_title'],
                            'from_location' => $booking_arr['from_location'],
                            'to_location' => $booking_arr['to_location'],
                            'seats' => $seats,
                            'name' => $first_last_name,
                            'phone' => $phone,
                            'uuid' => $booking_arr['uuid'],
                            'ticket_price' => $final_ticket_price_arr['ticket_arr'][0]['price'],
                            'ticket_type' => $final_ticket_price_arr['ticket_arr'][0]['ticket'],
                            'tax' => $final_ticket_price_arr['tax'],
                            'total' => $final_ticket_price_arr['total'],
                            'currency' => $this->option_arr['o_currency'],
                            'payment_method' => $booking_arr['payment_method'],
                            'status' => $booking_arr['status'],
                            'hold_time_hrs' => $this->option_arr['o_cash_min_hour'],
                            'message' => (isset($_SESSION['api_message'])) ? $_SESSION['api_message'] : 'Booking success'
                        ); 
                    } else {
                        $data = array(
                            'success' => 0,
                            'message' => 'Some technical problem occured'
                        ); 
                    }
                }
            } else {
                $data = array(
                    'success' => 0,
                    'message' => 'Ticket not available'
                );
            }
        } else {
            $data = array(
                'success' => 0,
                'message' => 'Need to pass all parameters'
            );
        }

        echo json_encode($data);
        exit;
    }

    public function pjActionTicket() {
        $phone = (isset($_POST['phone']) && trim($_POST['phone'])) ? trim($_POST['phone']) : ''; 

        if ($phone) {
            $params = array(
                'is_api' => true,
                'phone' => $phone
            );
            $data = $this->requestAction(array('controller' => 'pjFrontEnd', 'action' => 'pjSendTicket', 'params' => $params), array('return'));
        } else {
            $data = array(
                'success' => 0,
                'message' => 'Need to pass the required parameter'
            );
        }

        echo json_encode($data);
        exit;
    }
}
?>