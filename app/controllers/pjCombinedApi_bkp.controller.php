<?php
if (!defined("ROOT_PATH")) {
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjCombinedApi extends pjFront { 
    public function pjActionDeparture() { 
        $pjCityModel = pjCityModel::factory();
        $pjRouteDetailModel = pjRouteDetailModel::factory();
                   
        $from_location_arr = $pjCityModel
            ->reset()
            ->select('t1.*, t2.content as name')
            ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
            ->where("t1.id IN(SELECT TRD.from_location_id FROM `".$pjRouteDetailModel->getTable()."` AS TRD)")
            ->orderBy("t2.content ASC")
            ->findAll()
            ->getData();
        
        $cities = array();
        if ($from_location_arr) {
            foreach ($from_location_arr as $city) {
                if ($city['status'] == 'T') {
                    $cities[] = pjAppController::cleanData($city['name']);;
                }
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

        pjAppController::jsonResponse($data);
    }	

    public function pjActionDestination() { 
        $departure_city = (isset($_POST['departure_city'])) ? $_POST['departure_city'] : '';

        $pjCityModel = pjCityModel::factory();
        $pjRouteDetailModel = pjRouteDetailModel::factory();
        $pjMultiLangModel = pjMultiLangModel::factory();
                
        if (empty($departure_city)) {
            $to_location_arr = $pjCityModel
				->reset()
				->select('t1.*, t2.content as name')
				->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
				->where("t1.id IN(SELECT TRD.to_location_id FROM `".$pjRouteDetailModel->getTable()."` AS TRD)")
				->orderBy("t2.content ASC")
				->findAll()
				->getData();
        } else {
            $where = "WHERE TRD.from_location_id=(SELECT MTL.foreign_id FROM ".$pjMultiLangModel->getTable()." AS MTL WHERE MTL.model='pjCity' AND MTL.field='name' AND MTL.locale='".$this->getLocaleId()."' AND MTL.content = '".$departure_city."')";
            $to_location_arr = pjCityModel::factory()
                ->reset()
                ->select('t1.*, t2.content as name')
                ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                ->where("t1.id IN(SELECT TRD.to_location_id FROM `".$pjRouteDetailModel->getTable()."` AS TRD $where)")
                ->orderBy("t2.content ASC")
                ->findAll()
                ->getData();
        }
        
        $cities = array();
        if ($to_location_arr) {
            foreach ($to_location_arr as $city) {
                if ($city['status'] == 'T') {
                    $cities[] = pjAppController::cleanData($city['name']);
                }
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

        pjAppController::jsonResponse($data);
    }	
    
    public function pjActionTime() {
        $departure_city = (isset($_POST['departure_city']) && trim($_POST['departure_city'])) ? trim($_POST['departure_city']) : '';
        $destination_city = (isset($_POST['destination_city']) && trim($_POST['destination_city'])) ? trim($_POST['destination_city']) : '';
        $booking_date = (isset($_POST['booking_date']) && trim($_POST['booking_date'])) ? trim($_POST['booking_date']) : '';

        $pickup_id = '';
        $return_id = '';

        $pjCityModel = pjCityModel::factory();
        $pjRouteDetailModel = pjRouteDetailModel::factory();
        $pjMultiLangModel = pjMultiLangModel::factory();

        $where = "WHERE TRD.from_location_id=(SELECT MTL.foreign_id FROM ".$pjMultiLangModel->getTable()." AS MTL WHERE MTL.model='pjCity' AND MTL.field='name' AND MTL.locale='".$this->getLocaleId()."' AND MTL.content = '".$departure_city."')";
        $from_location_arr = pjCityModel::factory()
            ->reset()
            ->select('t1.*, t2.content as name')
            ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
            ->where("t1.id IN(SELECT TRD.from_location_id FROM `".$pjRouteDetailModel->getTable()."` AS TRD $where)")
            ->findAll()
            ->getData();

        if (count($from_location_arr)) {
            $pickup_id = $from_location_arr[0]['id'];

            $where = "WHERE TRD.from_location_id = ".$pickup_id." AND TRD.to_location_id = (SELECT MTL.foreign_id FROM ".$pjMultiLangModel->getTable()." AS MTL WHERE MTL.model='pjCity' AND MTL.field='name' AND MTL.locale='".$this->getLocaleId()."' AND MTL.content = '".$destination_city."')";
            $to_location_arr = pjCityModel::factory()
            ->reset()
            ->select('t1.*, t2.content as name')
            ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
            ->where("t1.id IN(SELECT TRD.to_location_id FROM `".$pjRouteDetailModel->getTable()."` AS TRD $where)")
            ->findAll()
            ->getData();

            if (count($from_location_arr)) {
                $return_id = $to_location_arr[0]['id'];
            }
        }

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
                        $pjTicketModel = pjTicketModel::factory();
                        $ticket_id_arr = $pjTicketModel->where('bus_id',$bus['id'])
                                ->limit(1)
                                ->findAll()
                                ->getData();

                        if (isset($ticket_id_arr[0]) && count($ticket_id_arr[0]) > 0) {
                            $ticket_id = $ticket_id_arr[0]['id'];
                        } else {
                            $ticket_id = '';
                        }       

                        $pjPriceModel = pjPriceModel::factory();
                        $ticket_price_arr = $pjPriceModel->select('*')
                             ->join('pjTicket', 't1.ticket_id = t2.id', 'left')
                             ->join('pjMultiLang', "t3.model='pjTicket' AND t3.foreign_id=t1.ticket_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                            ->where('t1.bus_id',$bus['id'])
                            ->where('ticket_id',$ticket_id)
                            ->where('from_location_id',$pickup_id)
                            ->where('to_location_id',$return_id)
                            ->where('is_return','F')
                            ->findAll()
                            ->getData();                            

                        if (isset($ticket_price_arr[0]) && count($ticket_price_arr[0]) > 0 && $ticket_price_arr[0]['price']) {
                            $ticket_price = number_format($ticket_price_arr[0]['price'], 2);
                            $ticket_type = $ticket_price_arr[0]['content'];
                        } else {
                            $ticket_price = '';
                            $ticket_type = '';
                        }

                        $location_array = array();
                        if ($bus['locations']) {
                            foreach ($bus['locations'] as $location) {
                                $location_array[] = array(
                                    'city' => pjAppController::cleanData($location['content']),
                                    'arrival_time' => pjAppController::cleanData($location['arrival_time']),
                                    'departure_time' => pjAppController::cleanData($location['departure_time'])
                                );
                            }
                        }
                        $time[] = array(
                            'bus_id' => pjAppController::cleanData($bus['id']),
                            'departure_time' => pjAppController::cleanData($bus['departure_time']),
                            'arrival_time' => pjAppController::cleanData($bus['arrival_time']),
                            'duration' => pjAppController::cleanData($bus['duration']),
                            'ticket_price' => pjAppController::cleanData($ticket_price),
                            'ticket_type' => pjAppController::cleanData($ticket_type),
                            'currency' => pjAppController::cleanData($this->option_arr['o_currency']),
                            'seats_available' => pjAppController::cleanData($bus['seats_available']),
                            'locations' => $location_array
                        );
                    }
                }
            }

            if ($time) {
                $data = array(
                    'success' => 1,
                    'departure_city_id' => pjAppController::cleanData($pickup_id),
                    'destination_city_id' => pjAppController::cleanData($return_id),
                    'booking_date' => pjAppController::cleanData($booking_date),
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

        pjAppController::jsonResponse($data);
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
            $seat_layout = '';

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
            $ticket_price_arr = $pjPriceModel->select('*')
                ->join('pjTicket', 't1.ticket_id = t2.id', 'left')
                ->join('pjMultiLang', "t3.model='pjTicket' AND t3.foreign_id=t1.ticket_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                ->where('t1.bus_id',$bus_id)
                ->where('ticket_id',$ticket_id)
                ->where('from_location_id',$pickup_id)
                ->where('to_location_id',$return_id)
                ->where('is_return','F')
                ->findAll()
                ->getData();

            if (isset($ticket_price_arr[0]) && count($ticket_price_arr[0]) > 0 && $ticket_price_arr[0]['price']) {
                $ticket_price = number_format($ticket_price_arr[0]['price'], 2);
                $ticket_type = $ticket_price_arr[0]['content'];
            } else {
                $ticket_price = '';
                $ticket_type = '';
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
                                        'seat_id' => pjAppController::cleanData($seat['id']),
                                        'seat_name' => pjAppController::cleanData($seat['name'])
                                    );
                                }
                            }
                        }

                        if (isset($bus_list['bus_arr'][0]['locations']) && $bus_list['bus_arr'][0]['locations']) {
                            foreach ($bus_list['bus_arr'][0]['locations'] as $location) {
                                $location_array[] = array(
                                    'city' => pjAppController::cleanData($location['content']),
                                    'arrival_time' => pjAppController::cleanData($location['arrival_time']),
                                    'departure_time' => pjAppController::cleanData($location['departure_time'])
                                );
                            }
                        }
                        $seat_layout = $bus_list['bus_arr'][0]['seat_layout'];
                    }
                }       
            }

            if ($available_seats) {
                $data = array(
                    'success' => 1,
                    'departure_city_id' => pjAppController::cleanData($pickup_id),
                    'destination_city_id' => pjAppController::cleanData($return_id),
                    'booking_date' => pjAppController::cleanData($booking_date),
                    'bus_id' => pjAppController::cleanData($bus_id),
                    'ticket_price' => pjAppController::cleanData($ticket_price),
                    'ticket_type' => pjAppController::cleanData($ticket_type),
                    'currency' => pjAppController::cleanData($this->option_arr['o_currency']),
                    'available_seats' => $available_seats,
                    'seat_layout' => $seat_layout,
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

        pjAppController::jsonResponse($data);
    }   

    public function pjActionPrice() {
        $pickup_id = (isset($_POST['departure_city_id']) && trim($_POST['departure_city_id'])) ? trim($_POST['departure_city_id']) : '';
        $return_id = (isset($_POST['destination_city_id']) && trim($_POST['destination_city_id'])) ? trim($_POST['destination_city_id']) : '';
        $booking_date = (isset($_POST['booking_date']) && trim($_POST['booking_date'])) ? trim($_POST['booking_date']) : '';
        $bus_id = (isset($_POST['bus_id']) && trim($_POST['bus_id'])) ? trim($_POST['bus_id']) : '';
        $seats = (isset($_POST['seat_ids']) && trim($_POST['seat_ids'])) ? trim($_POST['seat_ids']) : '';
        $seat_names = (isset($_POST['seat_names']) && trim($_POST['seat_names'])) ? trim($_POST['seat_names']) : '';

        if ($pickup_id && $return_id && $booking_date && $bus_id && $seats && $seat_names) {
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
                    'departure_city_id' => pjAppController::cleanData($pickup_id),
                    'destination_city_id' => pjAppController::cleanData($return_id),
                    'booking_date' => pjAppController::cleanData($booking_date),
                    'bus_id' => pjAppController::cleanData($bus_id),
                    'seats_count' => pjAppController::cleanData($seats_count),
                    'seat_ids' => pjAppController::cleanData($seats),
                    'seat_names' => pjAppController::cleanData($seat_names),
                    'ticket_price' => pjAppController::cleanData($final_ticket_price_arr['ticket_arr'][0]['price']),
                    'ticket_type' => pjAppController::cleanData($final_ticket_price_arr['ticket_arr'][0]['ticket']),
                    'tax' => pjAppController::cleanData($final_ticket_price_arr['tax']),
                    'total' => pjAppController::cleanData($final_ticket_price_arr['total']),
                    'currency' => pjAppController::cleanData($this->option_arr['o_currency'])
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

        pjAppController::jsonResponse($data);
    }   

    public function pjActionBooking() {
        $pickup_id = (isset($_POST['departure_city_id']) && trim($_POST['departure_city_id'])) ? trim($_POST['departure_city_id']) : '';
        $return_id = (isset($_POST['destination_city_id']) && trim($_POST['destination_city_id'])) ? trim($_POST['destination_city_id']) : '';
        $booking_date = (isset($_POST['booking_date']) && trim($_POST['booking_date'])) ? trim($_POST['booking_date']) : '';
        $bus_id = (isset($_POST['bus_id']) && trim($_POST['bus_id'])) ? trim($_POST['bus_id']) : '';
        $seats = (isset($_POST['seat_ids']) && trim($_POST['seat_ids'])) ? trim($_POST['seat_ids']) : '';
        $seat_names = (isset($_POST['seat_names']) && trim($_POST['seat_names'])) ? trim($_POST['seat_names']) : '';
        $title = (isset($_POST['title']) && trim($_POST['title'])) ? trim($_POST['title']) : '';
        $first_name = (isset($_POST['first_name']) && trim($_POST['first_name'])) ? trim($_POST['first_name']) : '';  
        $last_name = (isset($_POST['last_name']) && trim($_POST['last_name'])) ? trim($_POST['last_name']) : '';
        $phone = (isset($_POST['phone']) && trim($_POST['phone'])) ? trim($_POST['phone']) : '';
        $email = (isset($_POST['email']) && trim($_POST['email'])) ? trim($_POST['email']) : '';   

        if ($pickup_id && $return_id && $booking_date && $bus_id && $seats && $seat_names && $title && $first_name && $last_name && $phone && $email) {
            $seats_booked = explode(',',$seats);
            $seats_count = count($seats_booked);
            $selected_seats = implode('|',$seats_booked);

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
                    'c_title' => $title,
                    'c_fname' => $first_name,
                    'c_lname' => $last_name,
                    'c_phone_country' => '',
                    'c_phone' => $phone,
                    'c_email' => $email,
                    'c_company' => '',
                    'c_notes' => '',
                    'c_address' => '',
                    'c_city' => '',
                    'c_state' => '',
                    'c_zip' => '',
                    'c_country' => '',
                    'payment_method' => 'cash',
                    'agreement' => 'on',
                    'is_combined_api' => 1
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
                        'api_store' => $store,
                        'api_form' => $form
                    );
                    $result = $this->requestAction(array('controller' => 'pjFrontEnd', 'action' => 'pjActionSaveBooking', 'params' => $params), array('return'));
                    if ($result['code'] == 200) {
                        $final_ticket_price_arr = $pjPriceModel->getTicketPrice($bus_id, $pickup_id, $return_id, $booked_data, $this->option_arr, $this->getLocaleId(), 'F');

                        $booking_arr = $_SESSION['api_arr'];

                        $data = array(
                            'success' => 1,
                            'id' => pjAppController::cleanData($booking_arr['id']),
                            'departure_city_id' => pjAppController::cleanData($pickup_id),
                            'destination_city_id' => pjAppController::cleanData($return_id),
                            'booking_date' => pjAppController::cleanData($booking_date),
                            'bus_id' => pjAppController::cleanData($bus_id),
                            'booking_route' => pjAppController::cleanData($booking_arr['booking_route']),
                            'departure_time' => pjAppController::cleanData($booking_arr['booking_datetime']),
                            'arrival_time' => pjAppController::cleanData($booking_arr['stop_datetime']),
                            'route_title' => pjAppController::cleanData($booking_arr['route_title']),
                            'from_location' => pjAppController::cleanData($booking_arr['from_location']),
                            'to_location' => pjAppController::cleanData($booking_arr['to_location']),
                            'seat_ids' => pjAppController::cleanData($seats),
                            'seat_names' => pjAppController::cleanData($seat_names),
                            'title' => pjAppController::cleanData($title),
                            'first_name' => pjAppController::cleanData($first_name),
                            'last_name' => pjAppController::cleanData($last_name),
                            'phone' => pjAppController::cleanData($phone),
                            'email' => pjAppController::cleanData($email),
                            'uuid' => pjAppController::cleanData($booking_arr['uuid']),
                            'ticket_price' => pjAppController::cleanData($final_ticket_price_arr['ticket_arr'][0]['price']),
                            'ticket_type' => pjAppController::cleanData($final_ticket_price_arr['ticket_arr'][0]['ticket']),
                            'tax' => pjAppController::cleanData($final_ticket_price_arr['tax']),
                            'total' => pjAppController::cleanData($final_ticket_price_arr['total']),
                            'currency' => pjAppController::cleanData($this->option_arr['o_currency']),
                            'payment_method' => pjAppController::cleanData($booking_arr['payment_method']),
                            'status' => pjAppController::cleanData($booking_arr['status']),
                            'hold_time_hrs' => pjAppController::cleanData($this->option_arr['o_cash_min_hour'])
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

        pjAppController::jsonResponse($data);
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

        pjAppController::jsonResponse($data);
    }

    public function pjActionSetDate() {
        $params = $this->getParams();

        $post_params = array(
            'date_format' => $params['date_format'],
            'website_id' => PJ_COMBINED_API_WEBSITE_ID
        );
        $post_data = '';
        foreach($post_params as $k => $v) { 
            $post_data .= $k . '='.$v.'&'; 
        }
        $post_data = rtrim($post_data, '&');

        $url = PJ_COMBINED_API_URL.'/common/api/set_date';
        $ch = curl_init();

        curl_setopt($ch,CURLOPT_HTTPHEADER,array (
            "Accept: application/json"
        ));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $json_response = curl_exec($ch);
        if (curl_errno($ch)) {
            //$error_msg = curl_error($ch);
            //echo $error_msg;
        }

        curl_close($ch);
    }

    public function pjActionSetStatus() {
        $params = $this->getParams();

        $post_params = array(
            'uuid' => $params['uuid'],
            'status' => $params['status']
        );
        $post_data = '';
        foreach($post_params as $k => $v) { 
            $post_data .= $k . '='.$v.'&'; 
        }
        $post_data = rtrim($post_data, '&');

        $url = PJ_COMBINED_API_URL.'/common/api/set_status';
        $ch = curl_init();

        curl_setopt($ch,CURLOPT_HTTPHEADER,array (
            "Accept: application/json"
        ));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $json_response = curl_exec($ch);
        if (curl_errno($ch)) {
            //$error_msg = curl_error($ch);
            //echo $error_msg;
        }

        curl_close($ch);
    }
}
?>