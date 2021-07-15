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
        $is_same_site_return = (isset($_POST['is_same_site_return']) && trim($_POST['is_same_site_return'])) ? trim($_POST['is_same_site_return']) : 0;

        // $departure_city = 'Chingola';
        // $destination_city = 'Kabwe';
        // $booking_date = '11/11/2020';

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
                        $bus_type_arr = pjBusTypeModel::factory ()->find ( $bus ['bus_type_id'] )->getData ();
                        $seats_count = $bus_type_arr['seats_count'];

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
                             ->join('pjBus', "t4.id=t1.bus_id", 'left outer')
                            ->where('t1.bus_id',$bus['id'])
                            ->where('ticket_id',$ticket_id)
                            ->where('from_location_id',$pickup_id)
                            ->where('to_location_id',$return_id)
                            ->where('is_return','F')
                            ->findAll()
                            ->getData(); 

                        $discount = 0;

                        if (isset($ticket_price_arr[0]) && count($ticket_price_arr[0]) > 0 && $ticket_price_arr[0]['price']) {
                            $actual_ticket_price = $ticket_price_arr[0]['price'];  
                                                      
                            if ($is_same_site_return == 1) {  
                                $discount = $ticket_price_arr[0]['discount'];                              
                                $ticket_price = $actual_ticket_price - ($actual_ticket_price * $discount / 100);
                            } else {
                                $ticket_price = $actual_ticket_price;
                            }

                            $ticket_price = number_format($ticket_price, 2);
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
                            'return_discount' => $discount,
                            'app_discount' => pjAppController::cleanData($this->option_arr['o_api_discount']),
                            'seats_available' => pjAppController::cleanData($seats_count),
                            'seats_booking_available' => pjAppController::cleanData($bus['seats_available']),
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
        $is_same_site_return = (isset($_POST['is_same_site_return']) && trim($_POST['is_same_site_return'])) ? trim($_POST['is_same_site_return']) : 0;

        // $pickup_id = 1;
        // $return_id = 3;
        // $bus_id = 15;
        // $booking_date = '11/11/2020';

        //  $locations = $this->getBusLocations($pickup_id, $return_id, $bus_id, '2020-11-11');
        //  echo "<pre>";print_r($locations);echo "</pre>";exit;

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
                ->join('pjBus', "t4.id=t1.bus_id", 'left outer')
                ->where('t1.bus_id',$bus_id)
                ->where('ticket_id',$ticket_id)
                ->where('from_location_id',$pickup_id)
                ->where('to_location_id',$return_id)
                ->where('is_return','F')
                ->findAll()
                ->getData();

            $discount = 0;

            if (isset($ticket_price_arr[0]) && count($ticket_price_arr[0]) > 0 && $ticket_price_arr[0]['price']) {
                $actual_ticket_price = $ticket_price_arr[0]['price'];  
                
                if ($is_same_site_return == 1) {
                    $discount = $ticket_price_arr[0]['discount'];
                    $ticket_price = $actual_ticket_price - ($actual_ticket_price * $discount / 100);
                } else {
                    $ticket_price = $actual_ticket_price;
                }
                
                $ticket_price = number_format($ticket_price, 2);
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
                    'return_discount' => $discount,
                    'app_discount' => pjAppController::cleanData($this->option_arr['o_api_discount']),
                    'available_seats' => $available_seats,
                    'seat_layout' => $seat_layout,
                    'max_seats' => pjAppController::cleanData($this->option_arr['o_max_seats']),
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
        $is_same_site_return = (isset($_POST['is_same_site_return']) && trim($_POST['is_same_site_return'])) ? trim($_POST['is_same_site_return']) : 0;

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
            $ticket_price_arr = $pjPriceModel->select('*')
                ->where('bus_id',$bus_id)
                ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                ->join('pjMultiLang', "t3.model='pjTicket' AND t3.foreign_id=t1.ticket_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                ->where('ticket_id',$ticket_id)
                ->where('from_location_id',$pickup_id)
                ->where('to_location_id',$return_id)
                ->where('is_return','F')
                ->findAll()
                ->getData();

            $discount = 0;

            if (isset($ticket_price_arr[0]) && count($ticket_price_arr[0]) > 0 && $ticket_price_arr[0]['price']) {
                $actual_ticket_price = $ticket_price_arr[0]['price']; 
                
                if ($is_same_site_return == 1) {
                    $discount = $ticket_price_arr[0]['discount'];
                    $original_ticket_price = $actual_ticket_price - ($actual_ticket_price * $discount / 100);
                } else {
                    $original_ticket_price = $actual_ticket_price;
                }
                $ticket_price = $this->option_arr['o_currency'].' '.number_format($original_ticket_price, 2);
                $ticket_type = $ticket_price_arr[0]['content'];
            } else {
                $ticket_price = '';
                $ticket_type = '';
                $original_ticket_price = 0;                
            }
            
            if ($ticket_id && $ticket_price) {
                $key_name = ($is_same_site_return == 1) ? 'return_ticket_cnt_'.$ticket_id : 'ticket_cnt_'.$ticket_id;
                $booked_data = array(
                    $key_name => $seats_count,
                    'selected_ticket' => $seats_count,
                    'selected_seats' => $selected_seats,
                    'bus_id' => $bus_id,
                    'has_map' => 'T'
                );                  

                $is_return = ($is_same_site_return == 1) ? 'T' : 'F';
                $final_ticket_price_arr = $pjPriceModel->getTicketPrice($bus_id, $pickup_id, $return_id, $booked_data, $this->option_arr, $this->getLocaleId(), $is_return);
            }

            if (isset($final_ticket_price_arr['ticket_arr']) && $final_ticket_price_arr['ticket_arr']) {
                $actual_total = $final_ticket_price_arr['total']; 

                if ($actual_total <= $this->option_arr['o_api_discount']) {
                    $actual_total = 0;
                } else {
                    $actual_total = $actual_total - $this->option_arr['o_api_discount'];
                }

                $data = array(
                    'success' => 1,
                    'departure_city_id' => pjAppController::cleanData($pickup_id),
                    'destination_city_id' => pjAppController::cleanData($return_id),
                    'booking_date' => pjAppController::cleanData($booking_date),
                    'bus_id' => pjAppController::cleanData($bus_id),
                    'seats_count' => pjAppController::cleanData($seats_count),
                    'seat_ids' => pjAppController::cleanData($seats),
                    'seat_names' => pjAppController::cleanData($seat_names),
                    'ticket_price' => pjAppController::cleanData($original_ticket_price),
                    'ticket_type' => pjAppController::cleanData($final_ticket_price_arr['ticket_arr'][0]['ticket']),
                    'tax' => pjAppController::cleanData($final_ticket_price_arr['tax']),
                    'total' => pjAppController::cleanData($actual_total),
                    'currency' => pjAppController::cleanData($this->option_arr['o_currency']),
                    'return_discount' => $discount,
                    'app_discount' => pjAppController::cleanData($this->option_arr['o_api_discount'])
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
        $payment_type = (isset($_POST['payment_type']) && trim($_POST['payment_type'])) ? trim($_POST['payment_type']) : '';  
        $is_same_site_return = (isset($_POST['is_same_site_return']) && trim($_POST['is_same_site_return'])) ? trim($_POST['is_same_site_return']) : 0;

        $is_email = ($payment_type == 'flutterwave' && !$email) ? 0 : 1;

        if ($pickup_id && $return_id && $booking_date && $bus_id && $seats && $seat_names && $title && $first_name && $last_name && $phone && $payment_type && $is_email) {
            $seats_booked = explode(',',$seats);
            $seats_count = count($seats_booked);
            $selected_seats = implode('|',$seats_booked);

            $booked_data = array();
            $available_seats = array();
            $duration = '';

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
                ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                ->join('pjMultiLang', "t3.model='pjTicket' AND t3.foreign_id=t1.ticket_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                ->where('bus_id',$bus_id)
                ->where('ticket_id',$ticket_id)
                ->where('from_location_id',$pickup_id)
                ->where('to_location_id',$return_id)
                ->where('is_return','F')
                ->findAll()
                ->getData();

            $discount = 0;

            if (isset($ticket_price_arr[0]) && count($ticket_price_arr[0]) > 0 && $ticket_price_arr[0]['price']) {
                $actual_ticket_price = $ticket_price_arr[0]['price']; 
                
                if ($is_same_site_return == 1) {
                    $discount = $ticket_price_arr[0]['discount'];
                    $original_ticket_price = $actual_ticket_price - ($actual_ticket_price * $discount / 100);
                } else {
                    $original_ticket_price = $actual_ticket_price;
                }
                $ticket_price = $this->option_arr['o_currency'].' '.number_format($original_ticket_price, 2);
                $ticket_type = $ticket_price_arr[0]['content'];                
            } else {
                $ticket_price = '';
                $ticket_type = '';
                $original_ticket_price = 0;                
            }

            if ($ticket_id && $ticket_price) {
                $key_name = ($is_same_site_return == 1) ? 'return_ticket_cnt_'.$ticket_id : 'ticket_cnt_'.$ticket_id;
                $booked_data = array(
                    $key_name => $seats_count,
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
                    'payment_method' => $payment_type,
                    'agreement' => 'on',
                    'is_combined_api' => 1
                );

                $date = pjUtil::formatDate($booking_date, $this->option_arr['o_date_format']);
                $booking_period = array();            

                $pjBusModel = pjBusModel::factory();
                $bus_id_arr = $pjBusModel->getBusIds($date, $pickup_id, $return_id);    

                if ($bus_id_arr && in_array($bus_id,$bus_id_arr)) {
                    $bus_list = $this->getBusList($pickup_id, $return_id, array($bus_id), $booking_period, $booked_data, $booking_date, 'F');
                    
                    if (isset($bus_list['booking_period'])) {
                        $duration = $bus_list['bus_arr'][0]['duration'];
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
                        'message' => 'We are sorry, seat you selected not available'
                    );
                } else {
                    $params = array(
                        'is_api' => true,                        
                        'api_store' => $store,
                        'api_form' => $form,
                        'payment_method' => $payment_type,
                        'is_same_site_return' => $is_same_site_return
                    );
                    $result = $this->requestAction(array('controller' => 'pjFrontEnd', 'action' => 'pjActionSaveBooking', 'params' => $params), array('return'));
                    if ($result['code'] == 200) {
                        $is_return = ($is_same_site_return == 1) ? 'T' : 'F'; 
                        $final_ticket_price_arr = $pjPriceModel->getTicketPrice($bus_id, $pickup_id, $return_id, $booked_data, $this->option_arr, $this->getLocaleId(), $is_return);

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
                            'duration' => pjAppController::cleanData($duration),
                            'seat_ids' => pjAppController::cleanData($seats),
                            'seat_names' => pjAppController::cleanData($seat_names),
                            'title' => pjAppController::cleanData($title),
                            'first_name' => pjAppController::cleanData($first_name),
                            'last_name' => pjAppController::cleanData($last_name),
                            'phone' => pjAppController::cleanData($phone),
                            'email' => pjAppController::cleanData($email),
                            'uuid' => pjAppController::cleanData($booking_arr['uuid']),
                            'ticket_price' => pjAppController::cleanData($original_ticket_price),                            'ticket_type' => pjAppController::cleanData($final_ticket_price_arr['ticket_arr'][0]['ticket']),
                            'tax' => pjAppController::cleanData($booking_arr['tax']),
                            'total' => pjAppController::cleanData($booking_arr['total']),
                            'currency' => pjAppController::cleanData($this->option_arr['o_currency']),
                            'return_discount' => $discount,
                            'app_discount' => pjAppController::cleanData($this->option_arr['o_api_discount']),
                            'payment_method' => pjAppController::cleanData($booking_arr['payment_method']),
                            'status' => pjAppController::cleanData($booking_arr['status']),
                            'hold_time_hrs' => pjAppController::cleanData($this->option_arr['o_cash_min_hour'])
                        ); 
                    } else {
                        $data = array(
                            'success' => 0,
                            'message' => 'We are sorry, seat you selected not available'
                        ); 
                    }
                }
            } else {
                $data = array(
                    'success' => 0,
                    'message' => 'We are sorry, seat you selected not available'
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

    public function pjActionAgent() { 
        $phone = (isset($_POST['phone'])) ? $_POST['phone'] : '';
        $district = (isset($_POST['district'])) ? $_POST['district'] : '';

        $agents_object = pjUserModel::factory()
        ->select('t1.id,t1.phone,t1.email,t1.name,t1.address,t1.user_code,t2.dt_name,t3.credit')
        ->join('pjDistrict', 't2.dt_id = t1.district', 'left')
        ->join('pjCredit', 't3.user_id = t1.id', 'left outer')
        ->where('role_id',2)
        ->where('mobile_app_status',1)
        ->where('status','T');

        if ($phone) {
            $agents_object->where('phone',$phone);
        }
        if ($district) {
            $agents_object->where('dt_name',$district);
        }
        $agents = $agents_object->orderBy("t2.dt_name ASC")
        ->findAll()
        ->getData();

        $agents_arr = array();
        if (count($agents)) {
            foreach ($agents as $agent) {
                $agents_arr[] = array(
                    'phone' => pjAppController::cleanData($agent['phone']),
                    'district' => pjAppController::cleanData($agent['dt_name']),
                    'email' => pjAppController::cleanData($agent['email']),
                    'name' => pjAppController::cleanData($agent['name']),
                    'address' => pjAppController::cleanData($agent['address']),
                    'user_id' => pjAppController::cleanData($agent['id']),
                    'user_code' => pjAppController::cleanData($agent['user_code']),
                    'credit_balance' => pjAppController::cleanData($agent['credit']),
                );
            }
            $data = array(
                'success' => 1,
                'agents' => $agents_arr
            );
        }

        pjAppController::jsonResponse($data);
    }

    public function pjActionUpComingBooking() {
        $phone = (isset($_POST['phone']) && trim($_POST['phone'])) ? trim($_POST['phone']) : ''; 

        if ($phone) {
            $upcoming = array();

            $booking_phone = pjBookingModel::factory()
            ->where('c_phone',$phone)
            ->where('booking_date >=',date('Y-m-d'))
            ->orderBy("booking_date ASC")
            ->findAll()
            ->getData();
            if (count($booking_phone)) {
                foreach ($booking_phone as $booking) {
                    $pjBookingModel = pjBookingModel::factory();
    
                    $booking_arr = $pjBookingModel
                        ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
                        ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                        ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                        ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                        ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                        ->find($booking['id'])
                        ->getData();
                        
                    $booking_arr['tickets'] = pjBookingTicketModel::factory()
                        ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                        ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                        ->select('t1.*, t2.content as title')
                        ->where('booking_id', $booking['id'])
                        ->findAll()
                        ->getData();

                    $price_tbl = pjPriceModel::factory()->getTable();
        
                    $pjBookingTicketModel = pjBookingTicketModel::factory();
                    $tickets = $pjBookingTicketModel
                        ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                        ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                        ->select('t1.*, t2.content as title, (SELECT TP.price FROM `'.$price_tbl.'` AS TP WHERE TP.ticket_id = t1.ticket_id AND TP.bus_id = '.$booking_arr['bus_id'].' AND TP.from_location_id = '.$booking_arr['pickup_id'].' AND TP.to_location_id= '.$booking_arr['return_id']. ' AND is_return = "F" LIMIT 1) as price')
                        ->where('booking_id', $booking_arr['id'])
                        ->findAll()->getData();
        
                    $ticket_arr = $tickets[0];
                    $ticket_arr['currency'] = $this->option_arr['o_currency'];

                    $seats = '';
                    $seat_ids = '';
                    $booked_seat_id_arr = pjBookingSeatModel::factory()
                        ->select("DISTINCT (seat_id)")
                        ->where('booking_id', $booking_arr['id'])
                        ->findAll()
                        ->getDataPair('seat_id', 'seat_id');

                    if(!empty($booked_seat_id_arr))
                    {
                        $selected_seat_arr = pjSeatModel::factory()->whereIn('t1.id', $booked_seat_id_arr)->findAll()->getDataPair('id', 'name');
                        $seats = join(", ", $selected_seat_arr);
                        $seat_ids = join(", ", $booked_seat_id_arr);
                    }
                    $ticket_arr['seats'] = $seats;
                    $ticket_arr['seat_ids'] = $seat_ids;

                    $booking_arr['tickets'] = $ticket_arr;
                    
                    $pjCityModel = pjCityModel::factory();
                    $pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($booking_arr['pickup_id'])->getData();
                    $to_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($booking_arr['return_id'])->getData();
                    $booking_arr['from_location'] = $pickup_location['name'];
                    $booking_arr['to_location'] = $to_location['name'];	

                    $bus_id = $booking_arr['bus_id'];
                    $bus_id_arr = array($bus_id);
                    $bus_list = $this->getBusList($booking_arr['pickup_id'], $booking_arr['return_id'], $bus_id_arr, array(), array(), $booking_arr['booking_date'], 'F');

                    $duration = (isset($bus_list['bus_arr'][0]['duration'])) ? $bus_list['bus_arr'][0]['duration'] : '';

                    $upcoming[] = array(                                                      
                        'booking_date' => pjAppController::cleanData($booking_arr['booking_date']),
                        'booking_route' => pjAppController::cleanData($booking_arr['booking_route']),
                        'departure_time' => pjAppController::cleanData($booking_arr['booking_datetime']),
                        'arrival_time' => pjAppController::cleanData($booking_arr['stop_datetime']),
                        'route_title' => pjAppController::cleanData($booking_arr['route_title']),
                        'from_location' => pjAppController::cleanData($booking_arr['from_location']),
                        'to_location' => pjAppController::cleanData($booking_arr['to_location']),
                        'duration' => pjAppController::cleanData($duration),
                        'seat_names' => pjAppController::cleanData($booking_arr['tickets']['seats']),
                        'title' => pjAppController::cleanData($booking_arr['c_title']),
                        'first_name' => pjAppController::cleanData($booking_arr['c_fname']),
                        'last_name' => pjAppController::cleanData($booking_arr['c_lname']),
                        'phone' => pjAppController::cleanData($booking_arr['c_phone']),
                        'email' => pjAppController::cleanData($booking_arr['c_email']),
                        'uuid' => pjAppController::cleanData($booking_arr['uuid']),
                        'ticket_type' => pjAppController::cleanData($booking_arr['tickets']['title']),
                        'total' => pjAppController::cleanData($booking_arr['total']),
                        'currency' => pjAppController::cleanData($booking_arr['tickets']['currency']),
                        'payment_method' => pjAppController::cleanData($booking_arr['payment_method']),
                        'status' => pjAppController::cleanData($booking_arr['status']),
                        'departure_city_id' => pjAppController::cleanData($booking_arr['pickup_id']),
                        'destination_city_id' => pjAppController::cleanData($booking_arr['return_id']),
                        'bus_id' => pjAppController::cleanData($booking_arr['bus_id']),
                        'ticket_price' => pjAppController::cleanData($booking_arr['tickets']['price']),
                        'seat_ids' => pjAppController::cleanData($booking_arr['tickets']['seat_ids']),
                        'tax' => pjAppController::cleanData($booking_arr['tax']),
                        'hold_time_hrs' => pjAppController::cleanData($this->option_arr['o_cash_min_hour'])                        
                    ); 
                }
                $data = array(
                    'success' => 1,  
                    'booking' => (!empty($upcoming)) ? $upcoming : null
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

        pjAppController::jsonResponse($data);
    }	

    public function pjActionArchivedBooking() {
        $phone = (isset($_POST['phone']) && trim($_POST['phone'])) ? trim($_POST['phone']) : ''; 

        if ($phone) {
            $archived = array();

            $booking_phone = pjBookingModel::factory()
            ->where('c_phone',$phone)
            ->where('booking_date <',date('Y-m-d'))
            ->orderBy("booking_date ASC")
            ->findAll()
            ->getData();
            if (count($booking_phone)) {
                foreach ($booking_phone as $booking) {
                    $pjBookingModel = pjBookingModel::factory();
    
                    $booking_arr = $pjBookingModel
                        ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
                        ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                        ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                        ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                        ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                        ->find($booking['id'])
                        ->getData();
                        
                    $booking_arr['tickets'] = pjBookingTicketModel::factory()
                        ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                        ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                        ->select('t1.*, t2.content as title')
                        ->where('booking_id', $booking['id'])
                        ->findAll()
                        ->getData();

                    $price_tbl = pjPriceModel::factory()->getTable();
        
                    $pjBookingTicketModel = pjBookingTicketModel::factory();
                    $tickets = $pjBookingTicketModel
                        ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                        ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                        ->select('t1.*, t2.content as title, (SELECT TP.price FROM `'.$price_tbl.'` AS TP WHERE TP.ticket_id = t1.ticket_id AND TP.bus_id = '.$booking_arr['bus_id'].' AND TP.from_location_id = '.$booking_arr['pickup_id'].' AND TP.to_location_id= '.$booking_arr['return_id']. ' AND is_return = "F" LIMIT 1) as price')
                        ->where('booking_id', $booking_arr['id'])
                        ->findAll()->getData();
        
                    $ticket_arr = $tickets[0];
                    $ticket_arr['currency'] = $this->option_arr['o_currency'];

                    $seats = '';
                    $seat_ids = '';
                    $booked_seat_id_arr = pjBookingSeatModel::factory()
                        ->select("DISTINCT (seat_id)")
                        ->where('booking_id', $booking_arr['id'])
                        ->findAll()
                        ->getDataPair('seat_id', 'seat_id');

                    if(!empty($booked_seat_id_arr))
                    {
                        $selected_seat_arr = pjSeatModel::factory()->whereIn('t1.id', $booked_seat_id_arr)->findAll()->getDataPair('id', 'name');
                        $seats = join(", ", $selected_seat_arr);
                        $seat_ids = join(", ", $booked_seat_id_arr);
                    }
                    $ticket_arr['seats'] = $seats;
                    $ticket_arr['seat_ids'] = $seat_ids;

                    $booking_arr['tickets'] = $ticket_arr;
                    
                    $pjCityModel = pjCityModel::factory();
                    $pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($booking_arr['pickup_id'])->getData();
                    $to_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($booking_arr['return_id'])->getData();
                    $booking_arr['from_location'] = $pickup_location['name'];
                    $booking_arr['to_location'] = $to_location['name'];	

                    $bus_id = $booking_arr['bus_id'];
                    $bus_id_arr = array($bus_id);
                    $bus_list = $this->getBusList($booking_arr['pickup_id'], $booking_arr['return_id'], $bus_id_arr, array(), array(), $booking_arr['booking_date'], 'F');

                    $duration = (isset($bus_list['bus_arr'][0]['duration'])) ? $bus_list['bus_arr'][0]['duration'] : '';

                    $archived[] = array(                                                      
                        'booking_date' => pjAppController::cleanData($booking_arr['booking_date']),
                        'booking_route' => pjAppController::cleanData($booking_arr['booking_route']),
                        'departure_time' => pjAppController::cleanData($booking_arr['booking_datetime']),
                        'arrival_time' => pjAppController::cleanData($booking_arr['stop_datetime']),
                        'route_title' => pjAppController::cleanData($booking_arr['route_title']),
                        'from_location' => pjAppController::cleanData($booking_arr['from_location']),
                        'to_location' => pjAppController::cleanData($booking_arr['to_location']),
                        'seat_names' => pjAppController::cleanData($booking_arr['tickets']['seats']),
                        'duration' => pjAppController::cleanData($duration),
                        'title' => pjAppController::cleanData($booking_arr['c_title']),
                        'first_name' => pjAppController::cleanData($booking_arr['c_fname']),
                        'last_name' => pjAppController::cleanData($booking_arr['c_lname']),
                        'phone' => pjAppController::cleanData($booking_arr['c_phone']),
                        'email' => pjAppController::cleanData($booking_arr['c_email']),
                        'uuid' => pjAppController::cleanData($booking_arr['uuid']),
                        'ticket_type' => pjAppController::cleanData($booking_arr['tickets']['title']),
                        'total' => pjAppController::cleanData($booking_arr['total']),
                        'currency' => pjAppController::cleanData($booking_arr['tickets']['currency']),
                        'payment_method' => pjAppController::cleanData($booking_arr['payment_method']),
                        'status' => pjAppController::cleanData($booking_arr['status']),
                        'departure_city_id' => pjAppController::cleanData($booking_arr['pickup_id']),
                        'destination_city_id' => pjAppController::cleanData($booking_arr['return_id']),
                        'bus_id' => pjAppController::cleanData($booking_arr['bus_id']),
                        'ticket_price' => pjAppController::cleanData($booking_arr['tickets']['price']),
                        'seat_ids' => pjAppController::cleanData($booking_arr['tickets']['seat_ids']),
                        'tax' => pjAppController::cleanData($booking_arr['tax']),
                        'hold_time_hrs' => pjAppController::cleanData($this->option_arr['o_cash_min_hour'])
                    ); 
                }
                $data = array(
                    'success' => 1,  
                    'booking' => (!empty($archived)) ? $archived : null
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

        pjAppController::jsonResponse($data);
    }
    
    public function pjActionTrip() {
        $uuid = (isset($_POST['uuid']) && trim($_POST['uuid'])) ? trim($_POST['uuid']) : ''; 

        if ($uuid) {
            $booking_array = pjBookingModel::factory()
            ->where('uuid',$uuid)
            ->findAll()
            ->getData();

            if (count($booking_array)) {
                $booking = $booking_array[0];
                $pjBookingModel = pjBookingModel::factory();

                $booking_arr = $pjBookingModel
                    ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
                    ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                    ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                    ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                    ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                    ->find($booking['id'])
                    ->getData();
                    
                $booking_arr['tickets'] = pjBookingTicketModel::factory()
                    ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                    ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                    ->select('t1.*, t2.content as title')
                    ->where('booking_id', $booking['id'])
                    ->findAll()
                    ->getData();

                $price_tbl = pjPriceModel::factory()->getTable();
    
                $pjBookingTicketModel = pjBookingTicketModel::factory();
                $tickets = $pjBookingTicketModel
                    ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                    ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                    ->select('t1.*, t2.content as title, (SELECT TP.price FROM `'.$price_tbl.'` AS TP WHERE TP.ticket_id = t1.ticket_id AND TP.bus_id = '.$booking_arr['bus_id'].' AND TP.from_location_id = '.$booking_arr['pickup_id'].' AND TP.to_location_id= '.$booking_arr['return_id']. ' AND is_return = "F" LIMIT 1) as price')
                    ->where('booking_id', $booking_arr['id'])
                    ->findAll()->getData();
    
                $ticket_arr = $tickets[0];
                $ticket_arr['currency'] = $this->option_arr['o_currency'];

                $seats = '';
                $seat_ids = '';
                $booked_seat_id_arr = pjBookingSeatModel::factory()
                    ->select("DISTINCT (seat_id)")
                    ->where('booking_id', $booking_arr['id'])
                    ->findAll()
                    ->getDataPair('seat_id', 'seat_id');

                if(!empty($booked_seat_id_arr))
                {
                    $selected_seat_arr = pjSeatModel::factory()->whereIn('t1.id', $booked_seat_id_arr)->findAll()->getDataPair('id', 'name');
                    $seats = join(", ", $selected_seat_arr);
                    $seat_ids = join(", ", $booked_seat_id_arr);
                }
                $ticket_arr['seats'] = $seats;
                $ticket_arr['seat_ids'] = $seat_ids;

                $booking_arr['tickets'] = $ticket_arr;
                
                $pjCityModel = pjCityModel::factory();
                $pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($booking_arr['pickup_id'])->getData();
                $to_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($booking_arr['return_id'])->getData();
                $booking_arr['from_location'] = $pickup_location['name'];
                $booking_arr['to_location'] = $to_location['name'];	

                $bus_id = $booking_arr['bus_id'];
                $bus_id_arr = array($bus_id);
                $bus_list = $this->getBusList($booking_arr['pickup_id'], $booking_arr['return_id'], $bus_id_arr, array(), array(), $booking_arr['booking_date'], 'F');

                $duration = (isset($bus_list['bus_arr'][0]['duration'])) ? $bus_list['bus_arr'][0]['duration'] : '';

                $trip = array(                                                      
                    'booking_date' => pjAppController::cleanData($booking_arr['booking_date']),
                    'booking_route' => pjAppController::cleanData($booking_arr['booking_route']),
                    'departure_time' => pjAppController::cleanData($booking_arr['booking_datetime']),
                    'arrival_time' => pjAppController::cleanData($booking_arr['stop_datetime']),
                    'route_title' => pjAppController::cleanData($booking_arr['route_title']),
                    'from_location' => pjAppController::cleanData($booking_arr['from_location']),
                    'to_location' => pjAppController::cleanData($booking_arr['to_location']),
                    'duration' => pjAppController::cleanData($duration),
                    'seat_names' => pjAppController::cleanData($booking_arr['tickets']['seats']),
                    'title' => pjAppController::cleanData($booking_arr['c_title']),
                    'first_name' => pjAppController::cleanData($booking_arr['c_fname']),
                    'last_name' => pjAppController::cleanData($booking_arr['c_lname']),
                    'phone' => pjAppController::cleanData($booking_arr['c_phone']),
                    'email' => pjAppController::cleanData($booking_arr['c_email']),
                    'uuid' => pjAppController::cleanData($booking_arr['uuid']),
                    'ticket_type' => pjAppController::cleanData($booking_arr['tickets']['title']),
                    'total' => pjAppController::cleanData($booking_arr['total']),
                    'currency' => pjAppController::cleanData($booking_arr['tickets']['currency']),
                    'payment_method' => pjAppController::cleanData($booking_arr['payment_method']),
                    'status' => pjAppController::cleanData($booking_arr['status']),
                    'departure_city_id' => pjAppController::cleanData($booking_arr['pickup_id']),
                    'destination_city_id' => pjAppController::cleanData($booking_arr['return_id']),
                    'bus_id' => pjAppController::cleanData($booking_arr['bus_id']),
                    'ticket_price' => pjAppController::cleanData($booking_arr['tickets']['price']),
                    'seat_ids' => pjAppController::cleanData($booking_arr['tickets']['seat_ids']),
                    'tax' => pjAppController::cleanData($booking_arr['tax']),
                    'hold_time_hrs' => pjAppController::cleanData($this->option_arr['o_cash_min_hour'])
                ); 
                $data = array(
                    'success' => 1,  
                    'booking' => $trip
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

        pjAppController::jsonResponse($data);
    }

    public function pjActionFlutterwaveResponse() {
        $uuid = (isset($_POST['uuid']) && trim($_POST['uuid'])) ? trim($_POST['uuid']) : ''; 
        $status = (isset($_POST['status']) && trim($_POST['status'])) ? trim($_POST['status']) : '';
        $txn_id = (isset($_POST['txn_id']) && trim($_POST['txn_id'])) ? trim($_POST['txn_id']) : '';

        if ($uuid && $status && $txn_id) {            
            $booking_array = pjBookingModel::factory()
            ->where('uuid',$uuid)
            ->findAll()
            ->getData();

            if (count($booking_array)) {
                $id = $booking_array[0]['id'];

                pjBookingModel::factory()
                    ->where('uuid',$uuid)
                    ->modifyAll(array('status' => $status,'txn_id' => $txn_id,'processed_on' => ':NOW()'));
              
                $arr =  pjBookingModel::factory()->select ( 't1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location' )->join ( 'pjBus', "t2.id=t1.bus_id", 'left outer' )->join ( 'pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='" . $this->getLocaleId () . "'", 'left outer' )->find ( $id )->getData ();
   			    $tickets = pjBookingTicketModel::factory()->join ( 'pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjTicket', "t3.id=t1.ticket_id", 'left' )->select ( 't1.*, t2.content as title' )->where ( 'booking_id', $arr ['id'] )->findAll ()->getData ();
                $arr ['tickets'] = $tickets;

                if ($status == 'confirmed') {
                    $booking = $booking_array[0];
                    $pjBookingModel = pjBookingModel::factory();

                    pjBookingPaymentModel::factory()
                        ->where('booking_id', $booking['id'])
                        ->where('payment_type', 'online')
                        ->modifyAll(array('status' => 'paid'));
        
                        $booking_arr = $pjBookingModel
                            ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
                            ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                            ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                            ->find($booking['id'])
                            ->getData();
                            
                        $booking_arr['tickets'] = pjBookingTicketModel::factory()
                            ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                            ->select('t1.*, t2.content as title')
                            ->where('booking_id', $booking['id'])
                            ->findAll()
                            ->getData();

                        $price_tbl = pjPriceModel::factory()->getTable();
            
                        $pjBookingTicketModel = pjBookingTicketModel::factory();
                        $tickets = $pjBookingTicketModel
                            ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                            ->select('t1.*, t2.content as title, (SELECT TP.price FROM `'.$price_tbl.'` AS TP WHERE TP.ticket_id = t1.ticket_id AND TP.bus_id = '.$booking_arr['bus_id'].' AND TP.from_location_id = '.$booking_arr['pickup_id'].' AND TP.to_location_id= '.$booking_arr['return_id']. ' AND is_return = "F" LIMIT 1) as price')
                            ->where('booking_id', $booking_arr['id'])
                            ->findAll()->getData();
            
                        $ticket_arr = $tickets[0];
                        $ticket_arr['currency'] = $this->option_arr['o_currency'];

                        $seats = '';
                        $booked_seat_id_arr = pjBookingSeatModel::factory()
                            ->select("DISTINCT (seat_id)")
                            ->where('booking_id', $booking_arr['id'])
                            ->findAll()
                            ->getDataPair('seat_id', 'seat_id');

                        if(!empty($booked_seat_id_arr))
                        {
                            $selected_seat_arr = pjSeatModel::factory()->whereIn('t1.id', $booked_seat_id_arr)->findAll()->getDataPair('id', 'name');
                            $seats = join(", ", $selected_seat_arr);
                        }
                        $ticket_arr['seats'] = $seats;

                        $booking_arr['tickets'] = $ticket_arr;
                        
                        $pjCityModel = pjCityModel::factory();
                        $pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($booking_arr['pickup_id'])->getData();
                        $to_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($booking_arr['return_id'])->getData();
                        $booking_arr['from_location'] = $pickup_location['name'];
                        $booking_arr['to_location'] = $to_location['name'];	

                        $trip = array(                                                      
                            'booking_date' => pjAppController::cleanData($booking_arr['booking_date']),
                            'booking_route' => pjAppController::cleanData($booking_arr['booking_route']),
                            'departure_time' => pjAppController::cleanData($booking_arr['booking_datetime']),
                            'arrival_time' => pjAppController::cleanData($booking_arr['stop_datetime']),
                            'route_title' => pjAppController::cleanData($booking_arr['route_title']),
                            'from_location' => pjAppController::cleanData($booking_arr['from_location']),
                            'to_location' => pjAppController::cleanData($booking_arr['to_location']),
                            'seat_names' => pjAppController::cleanData($booking_arr['tickets']['seats']),
                            'title' => pjAppController::cleanData($booking_arr['c_title']),
                            'first_name' => pjAppController::cleanData($booking_arr['c_fname']),
                            'last_name' => pjAppController::cleanData($booking_arr['c_lname']),
                            'phone' => pjAppController::cleanData($booking_arr['c_phone']),
                            'email' => pjAppController::cleanData($booking_arr['c_email']),
                            'uuid' => pjAppController::cleanData($booking_arr['uuid']),
                            'ticket_type' => pjAppController::cleanData($booking_arr['tickets']['title']),
                            'total' => pjAppController::cleanData($booking_arr['total']),
                            'currency' => pjAppController::cleanData($booking_arr['tickets']['currency']),
                            'payment_method' => pjAppController::cleanData($booking_arr['payment_method']),
                            'status' => pjAppController::cleanData($booking_arr['status'])
                        ); 
                    $data = array(
                        'success' => 1,  
                        'message' => null,
                        'booking' => $trip
                    );

                    $message_status = 'confirm';
                } else {
                    $data = array(
                        'success' => 0,  
                        'message' => 'Your payment failed and so your booking get cancelled'
                    );

                    $message_status = 'cancel';
                }
                $pjFrontEnd = new pjFrontEnd(); 
                $pjFrontEnd->pjActionConfirmSend( $this->option_arr, $arr, PJ_SALT, $message_status);                
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

        pjAppController::jsonResponse($data);
    }

    public function pjActionCgrateResponse() {
        $uuid = (isset($_POST['uuid']) && trim($_POST['uuid'])) ? trim($_POST['uuid']) : ''; 
        $status = (isset($_POST['status']) && trim($_POST['status'])) ? trim($_POST['status']) : '';
        $txn_id = (isset($_POST['txn_id']) && trim($_POST['txn_id'])) ? trim($_POST['txn_id']) : '';

        if ($uuid && $status && $txn_id) {            
            $booking_array = pjBookingModel::factory()
            ->where('uuid',$uuid)
            ->findAll()
            ->getData();

            if (count($booking_array)) {
                $id = $booking_array[0]['id'];

                pjBookingModel::factory()
                    ->where('uuid',$uuid)
                    ->modifyAll(array('status' => $status,'txn_id' => $txn_id,'processed_on' => ':NOW()'));
              
                $arr =  pjBookingModel::factory()->select ( 't1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location' )->join ( 'pjBus', "t2.id=t1.bus_id", 'left outer' )->join ( 'pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='" . $this->getLocaleId () . "'", 'left outer' )->find ( $id )->getData ();
   			    $tickets = pjBookingTicketModel::factory()->join ( 'pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjTicket', "t3.id=t1.ticket_id", 'left' )->select ( 't1.*, t2.content as title' )->where ( 'booking_id', $arr ['id'] )->findAll ()->getData ();
                $arr ['tickets'] = $tickets;

                if ($status == 'confirmed') {
                    $booking = $booking_array[0];
                    $pjBookingModel = pjBookingModel::factory();

                    pjBookingPaymentModel::factory()
                        ->where('booking_id', $booking['id'])
                        ->where('payment_type', 'online')
                        ->modifyAll(array('status' => 'paid'));
        
                        $booking_arr = $pjBookingModel
                            ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
                            ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                            ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                            ->find($booking['id'])
                            ->getData();
                            
                        $booking_arr['tickets'] = pjBookingTicketModel::factory()
                            ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                            ->select('t1.*, t2.content as title')
                            ->where('booking_id', $booking['id'])
                            ->findAll()
                            ->getData();

                        $price_tbl = pjPriceModel::factory()->getTable();
            
                        $pjBookingTicketModel = pjBookingTicketModel::factory();
                        $tickets = $pjBookingTicketModel
                            ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                            ->select('t1.*, t2.content as title, (SELECT TP.price FROM `'.$price_tbl.'` AS TP WHERE TP.ticket_id = t1.ticket_id AND TP.bus_id = '.$booking_arr['bus_id'].' AND TP.from_location_id = '.$booking_arr['pickup_id'].' AND TP.to_location_id= '.$booking_arr['return_id']. ' AND is_return = "F" LIMIT 1) as price')
                            ->where('booking_id', $booking_arr['id'])
                            ->findAll()->getData();
            
                        $ticket_arr = $tickets[0];
                        $ticket_arr['currency'] = $this->option_arr['o_currency'];

                        $seats = '';
                        $booked_seat_id_arr = pjBookingSeatModel::factory()
                            ->select("DISTINCT (seat_id)")
                            ->where('booking_id', $booking_arr['id'])
                            ->findAll()
                            ->getDataPair('seat_id', 'seat_id');

                        if(!empty($booked_seat_id_arr))
                        {
                            $selected_seat_arr = pjSeatModel::factory()->whereIn('t1.id', $booked_seat_id_arr)->findAll()->getDataPair('id', 'name');
                            $seats = join(", ", $selected_seat_arr);
                        }
                        $ticket_arr['seats'] = $seats;

                        $booking_arr['tickets'] = $ticket_arr;
                        
                        $pjCityModel = pjCityModel::factory();
                        $pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($booking_arr['pickup_id'])->getData();
                        $to_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($booking_arr['return_id'])->getData();
                        $booking_arr['from_location'] = $pickup_location['name'];
                        $booking_arr['to_location'] = $to_location['name'];	

                        $trip = array(                                                      
                            'booking_date' => pjAppController::cleanData($booking_arr['booking_date']),
                            'booking_route' => pjAppController::cleanData($booking_arr['booking_route']),
                            'departure_time' => pjAppController::cleanData($booking_arr['booking_datetime']),
                            'arrival_time' => pjAppController::cleanData($booking_arr['stop_datetime']),
                            'route_title' => pjAppController::cleanData($booking_arr['route_title']),
                            'from_location' => pjAppController::cleanData($booking_arr['from_location']),
                            'to_location' => pjAppController::cleanData($booking_arr['to_location']),
                            'seat_names' => pjAppController::cleanData($booking_arr['tickets']['seats']),
                            'title' => pjAppController::cleanData($booking_arr['c_title']),
                            'first_name' => pjAppController::cleanData($booking_arr['c_fname']),
                            'last_name' => pjAppController::cleanData($booking_arr['c_lname']),
                            'phone' => pjAppController::cleanData($booking_arr['c_phone']),
                            'email' => pjAppController::cleanData($booking_arr['c_email']),
                            'uuid' => pjAppController::cleanData($booking_arr['uuid']),
                            'ticket_type' => pjAppController::cleanData($booking_arr['tickets']['title']),
                            'total' => pjAppController::cleanData($booking_arr['total']),
                            'currency' => pjAppController::cleanData($booking_arr['tickets']['currency']),
                            'payment_method' => pjAppController::cleanData($booking_arr['payment_method']),
                            'status' => pjAppController::cleanData($booking_arr['status'])
                        ); 
                    $data = array(
                        'success' => 1,  
                        'message' => null,
                        'booking' => $trip
                    );

                    $message_status = 'confirm';
                } else {
                    $data = array(
                        'success' => 0,  
                        'message' => 'Your payment failed and so your booking get cancelled'
                    );

                    $message_status = 'cancel';
                }
                $pjFrontEnd = new pjFrontEnd(); 
                $pjFrontEnd->pjActionConfirmSend( $this->option_arr, $arr, PJ_SALT, $message_status);                
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

        pjAppController::jsonResponse($data);
    }

    public function pjActionCgrate() {
        $uuid = (isset($_POST['uuid']) && trim($_POST['uuid'])) ? trim($_POST['uuid']) : ''; 
        $status = (isset($_POST['status']) && trim($_POST['status'])) ? trim($_POST['status']) : '';

        if ($uuid && $status) {            
            $booking_array = pjBookingModel::factory()
            ->where('uuid',$uuid)
            ->findAll()
            ->getData();

            if (count($booking_array)) {
                $id = $booking_array[0]['id'];

                pjBookingModel::factory()
                    ->where('uuid',$uuid)
                    ->modifyAll(array('status' => $status,'processed_on' => ':NOW()'));
              
                $arr =  pjBookingModel::factory()->select ( 't1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location' )->join ( 'pjBus', "t2.id=t1.bus_id", 'left outer' )->join ( 'pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='" . $this->getLocaleId () . "'", 'left outer' )->find ( $id )->getData ();
   			    $tickets = pjBookingTicketModel::factory()->join ( 'pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjTicket', "t3.id=t1.ticket_id", 'left' )->select ( 't1.*, t2.content as title' )->where ( 'booking_id', $arr ['id'] )->findAll ()->getData ();
                $arr ['tickets'] = $tickets;

                if ($status == 'confirmed') {
                    $booking = $booking_array[0];
                    $pjBookingModel = pjBookingModel::factory();

                    pjBookingPaymentModel::factory()
                        ->where('booking_id', $booking['id'])
                        ->where('payment_type', 'online')
                        ->modifyAll(array('status' => 'paid'));
        
                        $booking_arr = $pjBookingModel
                            ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
                            ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                            ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                            ->find($booking['id'])
                            ->getData();
                            
                        $booking_arr['tickets'] = pjBookingTicketModel::factory()
                            ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                            ->select('t1.*, t2.content as title')
                            ->where('booking_id', $booking['id'])
                            ->findAll()
                            ->getData();

                        $price_tbl = pjPriceModel::factory()->getTable();
            
                        $pjBookingTicketModel = pjBookingTicketModel::factory();
                        $tickets = $pjBookingTicketModel
                            ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                            ->select('t1.*, t2.content as title, (SELECT TP.price FROM `'.$price_tbl.'` AS TP WHERE TP.ticket_id = t1.ticket_id AND TP.bus_id = '.$booking_arr['bus_id'].' AND TP.from_location_id = '.$booking_arr['pickup_id'].' AND TP.to_location_id= '.$booking_arr['return_id']. ' AND is_return = "F" LIMIT 1) as price')
                            ->where('booking_id', $booking_arr['id'])
                            ->findAll()->getData();
            
                        $ticket_arr = $tickets[0];
                        $ticket_arr['currency'] = $this->option_arr['o_currency'];

                        $seats = '';
                        $booked_seat_id_arr = pjBookingSeatModel::factory()
                            ->select("DISTINCT (seat_id)")
                            ->where('booking_id', $booking_arr['id'])
                            ->findAll()
                            ->getDataPair('seat_id', 'seat_id');

                        if(!empty($booked_seat_id_arr))
                        {
                            $selected_seat_arr = pjSeatModel::factory()->whereIn('t1.id', $booked_seat_id_arr)->findAll()->getDataPair('id', 'name');
                            $seats = join(", ", $selected_seat_arr);
                        }
                        $ticket_arr['seats'] = $seats;

                        $booking_arr['tickets'] = $ticket_arr;
                        
                        $pjCityModel = pjCityModel::factory();
                        $pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($booking_arr['pickup_id'])->getData();
                        $to_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($booking_arr['return_id'])->getData();
                        $booking_arr['from_location'] = $pickup_location['name'];
                        $booking_arr['to_location'] = $to_location['name'];	

                        $trip = array(                                                      
                            'booking_date' => pjAppController::cleanData($booking_arr['booking_date']),
                            'booking_route' => pjAppController::cleanData($booking_arr['booking_route']),
                            'departure_time' => pjAppController::cleanData($booking_arr['booking_datetime']),
                            'arrival_time' => pjAppController::cleanData($booking_arr['stop_datetime']),
                            'route_title' => pjAppController::cleanData($booking_arr['route_title']),
                            'from_location' => pjAppController::cleanData($booking_arr['from_location']),
                            'to_location' => pjAppController::cleanData($booking_arr['to_location']),
                            'seat_names' => pjAppController::cleanData($booking_arr['tickets']['seats']),
                            'title' => pjAppController::cleanData($booking_arr['c_title']),
                            'first_name' => pjAppController::cleanData($booking_arr['c_fname']),
                            'last_name' => pjAppController::cleanData($booking_arr['c_lname']),
                            'phone' => pjAppController::cleanData($booking_arr['c_phone']),
                            'email' => pjAppController::cleanData($booking_arr['c_email']),
                            'uuid' => pjAppController::cleanData($booking_arr['uuid']),
                            'ticket_type' => pjAppController::cleanData($booking_arr['tickets']['title']),
                            'total' => pjAppController::cleanData($booking_arr['total']),
                            'currency' => pjAppController::cleanData($booking_arr['tickets']['currency']),
                            'payment_method' => pjAppController::cleanData($booking_arr['payment_method']),
                            'status' => pjAppController::cleanData($booking_arr['status'])
                        ); 
                    $data = array(
                        'success' => 1,  
                        'message' => null,
                        'booking' => $trip
                    );

                    $message_status = 'confirm';

                    $pjFrontEnd = new pjFrontEnd(); 
                    $pjFrontEnd->pjActionConfirmSend( $this->option_arr, $arr, PJ_SALT, $message_status);  
                } else {
                    $data = array(
                        'success' => 0,  
                        'message' => 'Your payment failed and so your booking get cancelled'
                    );

                    $message_status = 'cancel';
                }                              
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

    public function pjActionMyBookings(){
        $phone = (isset($_POST['phone']) && trim($_POST['phone'])) ? trim($_POST['phone']) : '';
        $status = (isset($_POST['status']) && trim($_POST['status'])) ? trim($_POST['status']) : '';

        if($phone){
            $agent_detail = pjUserModel::factory()
                        ->select('t1.id')
                        ->where('role_id',2)
                        ->where('phone',$phone)
                        ->limit(1,0)
                        ->findAll()
                        ->getData();

            if (isset($agent_detail[0]) && $agent_detail[0]) {
                $agent_user_id = $agent_detail[0]['id'];

                $pjBookingModel = pjBookingModel::factory()                
                    ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                    ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                    ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                    ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer');
                if (isset($status) && !empty($status) && in_array($status, array('confirmed','cancelled','pending')))
                {
                    $pjBookingModel->where('t1.status', $status);
                }

                $pjUserModel = pjUserModel::factory();
                $agentsBookings = $pjBookingModel->where('(user_id = '.$agent_user_id.' OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$agent_user_id.'))')
                ->orderBy("t1.id DESC")
                ->findAll()
                ->getData();
                $respArr = array();
                if(count($agentsBookings)){
                    foreach($agentsBookings as $key => $booking){
                        $arr = array();
                        $arr['uuid'] = pjAppController::cleanData($agentsBookings[$key]['uuid']);
                        $arr['title'] = pjAppController::cleanData($agentsBookings[$key]['c_title']);
                        $arr['first_name'] = pjAppController::cleanData($agentsBookings[$key]['c_fname']);
                        $arr['last_name'] = pjAppController::cleanData($agentsBookings[$key]['c_lname']);
                        $arr['phone'] = pjAppController::cleanData($agentsBookings[$key]['c_phone']);
                        $arr['email'] = pjAppController::cleanData($agentsBookings[$key]['c_email']);

                        $booking_route = strip_tags($agentsBookings[$key]['booking_route']);
                        $bus_name_arr = explode(",",$booking_route);
                        $arr['bus_name'] = pjAppController::cleanData($bus_name_arr[0]);

                        $bookingArr = explode("from",$booking_route);
                        $locFromTo = $bookingArr[1];
                        $locNameArr = explode("to",$locFromTo);
                        $arr['from_location'] = pjAppController::cleanData($locNameArr[1]);
                        $arr['to_location'] = pjAppController::cleanData($locNameArr[0]);                        

                        $arr['status'] = pjAppController::cleanData($agentsBookings[$key]['status']);
                        $arr['total'] = pjAppController::cleanData($agentsBookings[$key]['total']);
                        $booking_datetime_arr = explode(" ", $agentsBookings[$key]['booking_datetime']);
                        $arr['booking_date'] = pjAppController::cleanData($booking_datetime_arr[0]);
                        $arr['booking_time'] = pjAppController::cleanData($agentsBookings[$key]['booking_time']);
                        
                        array_push($respArr, $arr);
                    }
                }   
                $data = array(
                    'success' => 1,
                    'message' => '',
                    'bookings' => $respArr
                );
            } else {
                $data = array(
                    'success' => 0,
                    'message' => 'No agent found'
                ); 
            }
        } else {
            $data = array(
                'success' => 0,
                'message' => 'Need to pass the required parameter'
            );
        }
       pjAppController::jsonResponse($data);  
    }

    public function pjActionOutsideBookings(){
        $pjBookingModel = pjBookingModel::factory()                
            ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
            ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
            ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
            ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
            ->where('user_id', 0)
            ->where('payment_method', 'cash');
            if (isset($status) && !empty($status) && in_array($status, array('confirmed','cancelled','pending')))
        {
            $pjBookingModel->where('t1.status', $status);
        }
        
        $bookings = $pjBookingModel->orderBy("t1.id DESC")
        ->findAll()
        ->getData();

        $respArr = array();
        if(count($bookings)){
            foreach($bookings as $key => $booking){
                $arr = array();
                $arr['uuid'] = pjAppController::cleanData($bookings[$key]['uuid']);
                $arr['title'] = pjAppController::cleanData($bookings[$key]['c_title']);
                $arr['first_name'] = pjAppController::cleanData($bookings[$key]['c_fname']);
                $arr['last_name'] = pjAppController::cleanData($bookings[$key]['c_lname']);
                $arr['phone'] = pjAppController::cleanData($bookings[$key]['c_phone']);
                $arr['email'] = pjAppController::cleanData($bookings[$key]['c_email']);

                $booking_route = strip_tags($bookings[$key]['booking_route']);
                $bus_name_arr = explode(",",$booking_route);
                $arr['bus_name'] = pjAppController::cleanData($bus_name_arr[0]);

                $bookingArr = explode("from",$booking_route);
                $locFromTo = $bookingArr[1];
                $locNameArr = explode("to",$locFromTo);
                $arr['from_location'] = pjAppController::cleanData($locNameArr[1]);
                $arr['to_location'] = pjAppController::cleanData($locNameArr[0]);                        

                $arr['status'] = pjAppController::cleanData($bookings[$key]['status']);
                $arr['total'] = pjAppController::cleanData($bookings[$key]['total']);
                $booking_datetime_arr = explode(" ", $bookings[$key]['booking_datetime']);
                $arr['booking_date'] = pjAppController::cleanData($booking_datetime_arr[0]);
                $arr['booking_time'] = pjAppController::cleanData($bookings[$key]['booking_time']);
                $arr['payment_method'] = pjAppController::cleanData($bookings[$key]['payment_method']);
                
                array_push($respArr, $arr);
            }
        }   
        $data = array(
            'success' => 1,
            'message' => '',
            'bookings' => $respArr
        );

       pjAppController::jsonResponse($data);  
    }

    public function pjActionFutureBookings(){
        $pjBookingModel = pjBookingModel::factory()                
            ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
            ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
            ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
            ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
            ->where('booking_date >= CURRENT_DATE');

            if (isset($status) && !empty($status) && in_array($status, array('confirmed','cancelled','pending')))
        {
            $pjBookingModel->where('t1.status', $status);
        }
        
        $bookings = $pjBookingModel->orderBy("t1.id DESC")
        ->findAll()
        ->getData();

        $respArr = array();
        if(count($bookings)){
            foreach($bookings as $key => $booking){
                $arr = array();
                $arr['uuid'] = pjAppController::cleanData($bookings[$key]['uuid']);
                $arr['title'] = pjAppController::cleanData($bookings[$key]['c_title']);
                $arr['first_name'] = pjAppController::cleanData($bookings[$key]['c_fname']);
                $arr['last_name'] = pjAppController::cleanData($bookings[$key]['c_lname']);
                $arr['phone'] = pjAppController::cleanData($bookings[$key]['c_phone']);
                $arr['email'] = pjAppController::cleanData($bookings[$key]['c_email']);

                $booking_route = strip_tags($bookings[$key]['booking_route']);
                $bus_name_arr = explode(",",$booking_route);
                $arr['bus_name'] = pjAppController::cleanData($bus_name_arr[0]);

                $bookingArr = explode("from",$booking_route);
                $locFromTo = $bookingArr[1];
                $locNameArr = explode("to",$locFromTo);
                $arr['from_location'] = pjAppController::cleanData($locNameArr[1]);
                $arr['to_location'] = pjAppController::cleanData($locNameArr[0]);                        

                $arr['status'] = pjAppController::cleanData($bookings[$key]['status']);
                $arr['total'] = pjAppController::cleanData($bookings[$key]['total']);
                $booking_datetime_arr = explode(" ", $bookings[$key]['booking_datetime']);
                $arr['booking_date'] = pjAppController::cleanData($booking_datetime_arr[0]);
                $arr['booking_time'] = pjAppController::cleanData($bookings[$key]['booking_time']);
                $arr['payment_method'] = pjAppController::cleanData($bookings[$key]['payment_method']);
                
                array_push($respArr, $arr);
            }
        }   
        $data = array(
            'success' => 1,
            'message' => '',
            'bookings' => $respArr
        );

       pjAppController::jsonResponse($data);  
    }

    public function pjActionSettings(){        
        $data = array(
            'success' => 1,
            'message' => '',
            'max_seats' => pjAppController::cleanData($this->option_arr['o_max_seats']),
            'wallet_id' => pjAppController::cleanData('M0'.PJ_COMBINED_API_WEBSITE_ID.PJ_LIPILA_WALLET_IDENTIFIER),
            'wallet_key' => pjAppController::cleanData(PJ_LIPILA_WALLET_KEY.'P0'.PJ_COMBINED_API_WEBSITE_ID),
            'flutterwave_public_key' => pjAppController::cleanData($this->option_arr['o_flutterwave_public_key']),
            'flutterwave_secret_key' => pjAppController::cleanData($this->option_arr['o_flutterwave_secret_key']),
            'flutterwave_encryption_key' => pjAppController::cleanData($this->option_arr['o_flutterwave_encryption_key']),
            'flutterwave_subaccount' => pjAppController::cleanData(PJ_FLUTTERWAVE_SUB_ACCOUNT)
        );

       pjAppController::jsonResponse($data);  
    }

    public function pjActionAgentConfirmed(){
        $user_id = (isset($_POST['user_id']) && trim($_POST['user_id'])) ? trim($_POST['user_id']) : '';

        if($user_id){
            $agent_detail = pjUserModel::factory()
                        ->select('t1.id,t1.name,t1.phone')
                        ->where('role_id',2)
                        ->where('id',$user_id)
                        ->limit(1,0)
                        ->findAll()
                        ->getData();

            if (isset($agent_detail[0]) && $agent_detail[0]) {
                $agent_user_id = $agent_detail[0]['id'];

                $pjBookingModel = pjBookingModel::factory()                
                    ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                    ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                    ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                    ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                    ->where('t1.status', 'confirmed');                    

                $pjUserModel = pjUserModel::factory();
                $agentsBookings = $pjBookingModel->where('(user_id = '.$agent_user_id.' OR user_id IN (SELECT id FROM '.$pjUserModel->getTable().' WHERE added_by = '.$agent_user_id.'))')
                ->orderBy("t1.id DESC")
                ->findAll()
                ->getData();
                $respArr = array();
                if(count($agentsBookings)){
                    foreach($agentsBookings as $key => $booking){
                        $arr = array();
                        $arr['uuid'] = pjAppController::cleanData($agentsBookings[$key]['uuid']);
                        $arr['title'] = pjAppController::cleanData($agentsBookings[$key]['c_title']);
                        $arr['first_name'] = pjAppController::cleanData($agentsBookings[$key]['c_fname']);
                        $arr['last_name'] = pjAppController::cleanData($agentsBookings[$key]['c_lname']);
                        $arr['phone'] = pjAppController::cleanData($agentsBookings[$key]['c_phone']);
                        $arr['email'] = pjAppController::cleanData($agentsBookings[$key]['c_email']);

                        $booking_route = strip_tags($agentsBookings[$key]['booking_route']);
                        $bus_name_arr = explode(",",$booking_route);
                        $arr['bus_name'] = pjAppController::cleanData($bus_name_arr[0]);

                        $bookingArr = explode("from",$booking_route);
                        $locFromTo = $bookingArr[1];
                        $locNameArr = explode("to",$locFromTo);
                        $arr['from_location'] = pjAppController::cleanData($locNameArr[1]);
                        $arr['to_location'] = pjAppController::cleanData($locNameArr[0]);                        

                        $arr['status'] = pjAppController::cleanData($agentsBookings[$key]['status']);
                        $arr['total'] = pjAppController::cleanData($agentsBookings[$key]['total']);
                        $booking_datetime_arr = explode(" ", $agentsBookings[$key]['booking_datetime']);
                        $arr['booking_date'] = pjAppController::cleanData($booking_datetime_arr[0]);
                        $arr['booking_time'] = pjAppController::cleanData($agentsBookings[$key]['booking_time']);
                        
                        array_push($respArr, $arr);
                    }
                }   
                $data = array(
                    'success' => 1,
                    'message' => '',
                    'user_id' => pjAppController::cleanData($agent_detail[0]['id']),
                    'name' => pjAppController::cleanData($agent_detail[0]['name']),
                    'phone' => pjAppController::cleanData($agent_detail[0]['phone']),
                    'bookings' => $respArr
                );
            } else {
                $data = array(
                    'success' => 0,
                    'message' => 'No agent found'
                ); 
            }
        } else {
            $data = array(
                'success' => 0,
                'message' => 'Need to pass the required parameter'
            );
        }
       pjAppController::jsonResponse($data);  
    }

    public function pjActionConfirm(){
        $user_id = (isset($_POST['user_id']) && trim($_POST['user_id'])) ? trim($_POST['user_id']) : '';
        $uuid = (isset($_POST['uuid']) && trim($_POST['uuid'])) ? trim($_POST['uuid']) : '';

        if($user_id && $uuid){
            $agent_detail = pjUserModel::factory()
                        ->select('t1.id,t1.name,t1.phone')
                        ->where('role_id',2)
                        ->where('id',$user_id)
                        ->limit(1,0)
                        ->findAll()
                        ->getData();

            if (isset($agent_detail[0]) && $agent_detail[0]) {
                $booking_array = pjBookingModel::factory()
                ->where('uuid',$uuid)
                ->findAll()
                ->getData();

                if (count($booking_array)) {
                    $booking = $booking_array[0];     
                    $bookig_date_f = date($this->option_arr['o_date_format'],strtotime($booking['booking_date']));               

                    if ($booking['status'] == 'confirmed') {
                        $data = array(
                            'success' => 0,
                            'message' => 'Ticket already confirmed'
                        ); 
                    } else {
                        $user_credit = pjCreditModel::factory()
                        ->where('t1.user_id', $user_id)
                        ->findAll()
                        ->getData();

                        $user_current_credit = (empty($user_credit)) ? 0 : $user_credit[0]['credit'];
                        if ((empty($user_credit) || $user_current_credit < $booking['total'])) {
                            $data = array(
                                'success' => 0,
                                'message' => 'In-sufficient Credit'
                            ); 
                        } else {
                            $agent_user_id = $agent_detail[0]['id'];                                      
                            
                            $pjBookingModel = pjBookingModel::factory();

                            $booking_arr = $pjBookingModel
                                ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
                                ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                                ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                                ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                                ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                                ->find($booking['id'])
                                ->getData();
                                
                            $booking_arr['tickets'] = pjBookingTicketModel::factory()
                                ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                                ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                                ->select('t1.*, t2.content as title')
                                ->where('booking_id', $booking['id'])
                                ->findAll()
                                ->getData();

                            $price_tbl = pjPriceModel::factory()->getTable();
                
                            $pjBookingTicketModel = pjBookingTicketModel::factory();
                            $tickets = $pjBookingTicketModel
                                ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                                ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                                ->select('t1.*, t2.content as title, (SELECT TP.price FROM `'.$price_tbl.'` AS TP WHERE TP.ticket_id = t1.ticket_id AND TP.bus_id = '.$booking_arr['bus_id'].' AND TP.from_location_id = '.$booking_arr['pickup_id'].' AND TP.to_location_id= '.$booking_arr['return_id']. ' AND is_return = "F" LIMIT 1) as price')
                                ->where('booking_id', $booking_arr['id'])
                                ->findAll()->getData();
                
                            $ticket_arr = $tickets[0];
                            $ticket_arr['currency'] = $this->option_arr['o_currency'];

                            $seats = '';
                            $seat_ids = '';
                            $booked_seat_id_arr = pjBookingSeatModel::factory()
                                ->select("DISTINCT (seat_id)")
                                ->where('booking_id', $booking_arr['id'])
                                ->findAll()
                                ->getDataPair('seat_id', 'seat_id');

                            if(!empty($booked_seat_id_arr))
                            {
                                $selected_seat_arr = pjSeatModel::factory()->whereIn('t1.id', $booked_seat_id_arr)->findAll()->getDataPair('id', 'name');
                                $seats = join(", ", $selected_seat_arr);
                                $seat_ids = join(", ", $booked_seat_id_arr);
                            }
                            $ticket_arr['seats'] = $seats;
                            $ticket_arr['seat_ids'] = $seat_ids;

                            $booking_arr['tickets'] = $ticket_arr;
                            
                            $pjCityModel = pjCityModel::factory();
                            $pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($booking_arr['pickup_id'])->getData();
                            $to_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($booking_arr['return_id'])->getData();
                            $booking_arr['from_location'] = $pickup_location['name'];
                            $booking_arr['to_location'] = $to_location['name'];	

                            $bus_id = $booking_arr['bus_id'];
                            $bus_id_arr = array($bus_id);
                            $bus_list = $this->getBusList($booking_arr['pickup_id'], $booking_arr['return_id'], $bus_id_arr, array(), array(), $bookig_date_f, 'F');

                            $duration = (isset($bus_list['bus_arr'][0]['duration'])) ? $bus_list['bus_arr'][0]['duration'] : '';

                            $store['pickup_id'] = $pickup_id = $booking['pickup_id'];
                            $store['return_id'] = $return_id = $booking['return_id'];
                            $store['date'] = $booking_date = $bookig_date_f;
                            $store['this_booking_id'] = $booking['id'];
                            $bus_id = $booking['bus_id'];
                            $seats_booked = $booked_seat_id_arr;
                            $booking_period = $bus_list['booking_period'];
                            $booked_data = array();

                            $bus_list = $this->getBusList($pickup_id, $return_id, array($bus_id), $booking_period, $booked_data, $booking_date, 'F');
                            $time = $bus_list['booking_period'];
                            $store['booking_period'][$bus_id] = $time[$bus_id];

                            $avail_arr = $this->getBusAvailability($bus_id, $store, $this->option_arr);
                            $booked_seats = $avail_arr['booked_seat_arr'];                    

                            $intersect = array_intersect($booked_seats, $seats_booked);

                            if(!empty($intersect)) {
                                $data = array(
                                    'success' => 0,
                                    'message' => 'No ticket available'
                                );  
                            } else {
                                $result = pjBookingModel::factory()->whereIn('uuid', $uuid)->modifyAll(array(
                                    'user_id' => $user_id,
                                    'status' => 'confirmed',
                                    'updated_by' => $user_id,
                                    'updated_on' => date("Y-m-d H:i:s")
                                ));  
                                
                                $new_credit = $user_current_credit - $booking['total'];                    
                                $result = pjCreditModel::factory()->whereIn('user_id', $user_id)->modifyAll(array(
                                    'credit' => $new_credit
                                ));	

                                $send_arr = pjBookingModel::factory ()
                                ->select ( 't1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location' )
                                ->join ( 'pjBus', "t2.id=t1.bus_id", 'left outer' )
                                ->join ( 'pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='" . $this->getLocaleId () . "'", 'left outer' )
                                ->join ( 'pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='" . $this->getLocaleId () . "'", 'left outer' )
                                ->join ( 'pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='" . $this->getLocaleId () . "'", 'left outer' )
                                ->find ($booking['id'])
                                ->getData ();

                                $send_tickets = pjBookingTicketModel::factory ()
                                ->join ( 'pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )
                                ->join ( 'pjTicket', "t3.id=t1.ticket_id", 'left' )->select ( 't1.*, t2.content as title' )
                                ->where ( 'booking_id', $send_arr ['id'] )
                                ->findAll ()
                                ->getData ();
                            
                                $send_arr['tickets'] = $send_tickets;                    

                                $pjFrontEnd = new pjFrontEnd(); 
                                $pjFrontEnd->pjActionConfirmSend( $this->option_arr, $send_arr, PJ_SALT, 'confirm');                    

                                $data = array(
                                    'success' => 1,
                                    'message' => '',
                                    'user_id' => pjAppController::cleanData($agent_detail[0]['id']),
                                    'agent_name' => pjAppController::cleanData($agent_detail[0]['name']),
                                    'agent_phone' => pjAppController::cleanData($agent_detail[0]['phone']),
                                    'booking_date' => pjAppController::cleanData($booking_arr['booking_date']),
                                    'booking_route' => pjAppController::cleanData($booking_arr['booking_route']),
                                    'departure_time' => pjAppController::cleanData($booking_arr['booking_datetime']),
                                    'arrival_time' => pjAppController::cleanData($booking_arr['stop_datetime']),
                                    'route_title' => pjAppController::cleanData($booking_arr['route_title']),
                                    'from_location' => pjAppController::cleanData($booking_arr['from_location']),
                                    'to_location' => pjAppController::cleanData($booking_arr['to_location']),
                                    'duration' => pjAppController::cleanData($duration),
                                    'seat_names' => pjAppController::cleanData($booking_arr['tickets']['seats']),
                                    'title' => pjAppController::cleanData($booking_arr['c_title']),
                                    'first_name' => pjAppController::cleanData($booking_arr['c_fname']),
                                    'last_name' => pjAppController::cleanData($booking_arr['c_lname']),
                                    'phone' => pjAppController::cleanData($booking_arr['c_phone']),
                                    'email' => pjAppController::cleanData($booking_arr['c_email']),
                                    'uuid' => pjAppController::cleanData($booking_arr['uuid']),
                                    'ticket_type' => pjAppController::cleanData($booking_arr['tickets']['title']),
                                    'total' => pjAppController::cleanData($booking_arr['total']),
                                    'currency' => pjAppController::cleanData($booking_arr['tickets']['currency']),
                                    'payment_method' => pjAppController::cleanData($booking_arr['payment_method']),
                                    'status' => pjAppController::cleanData($booking_arr['status']),
                                    'departure_city_id' => pjAppController::cleanData($booking_arr['pickup_id']),
                                    'destination_city_id' => pjAppController::cleanData($booking_arr['return_id']),
                                    'bus_id' => pjAppController::cleanData($booking_arr['bus_id']),
                                    'ticket_price' => pjAppController::cleanData($booking_arr['tickets']['price']),
                                    'seat_ids' => pjAppController::cleanData($booking_arr['tickets']['seat_ids']),
                                    'tax' => pjAppController::cleanData($booking_arr['tax']),
                                    'hold_time_hrs' => pjAppController::cleanData($this->option_arr['o_cash_min_hour'])
                                );
                            }
                        }
                    }
                } else {
                    $data = array(
                        'success' => 0,
                        'message' => 'No booking found'
                    );  
                }
            } else {
                $data = array(
                    'success' => 0,
                    'message' => 'No agent found'
                ); 
            }
        } else {
            $data = array(
                'success' => 0,
                'message' => 'Need to pass the required parameter'
            );
        }
       pjAppController::jsonResponse($data);  
    }

    public function pjActionNews(){  
        $news = pjNewsModel::factory()
            ->where('t1.nw_is_active =',1)
            ->findAll()->getData();

        $respArr = array();
        if(count($news)){
            foreach($news as $key => $nw){
                $arr = array();
                $arr['title'] = pjAppController::cleanData($nw['nw_title']);
                $arr['date'] = pjAppController::cleanData(date('F d',strtotime($nw['nw_date'])));
                $arr['added_on'] = pjAppController::cleanData($nw['nw_added_on']);
                $arr['link'] = pjAppController::cleanData($nw['nw_link']);
                $arr['description'] = pjAppController::cleanData($nw['nw_description']);   
                $arr['image']  = pjAppController::cleanData(PJ_INSTALL_URL.'app/uploads/news/'.$nw['nw_image']);             
                
                array_push($respArr, $arr);
            }
        }   
        $data = array(
            'success' => 1,
            'message' => '',
            'news' => $respArr
        );

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

    public function pjActionCancel() {	
        $uuid = (isset($_POST['uuid']) && trim($_POST['uuid'])) ? trim($_POST['uuid']) : ''; 
        $no_message = (isset($_POST['no_message']) && trim($_POST['no_message'])) ? trim($_POST['no_message']) : 0; 

        $pjFrontEnd = new pjFrontEnd();
        $pjBookingModel = pjBookingModel::factory();
		
		if ($uuid) {
			$booking_arr1 = pjBookingModel::factory()
				->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
				->join('pjBus', "t2.id=t1.bus_id", 'left outer')
				->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
				->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
				->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                ->where('t1.uuid',$uuid)
                ->findAll()
                ->getData();

			if (isset($booking_arr1[0]) && count($booking_arr1[0]) > 0) {
                $booking_arr = $booking_arr1[0];
				$sql = "UPDATE `".$pjBookingModel->getTable()."` SET status = 'cancelled' WHERE `id` = ".$booking_arr['id'];
				
				$pjBookingModel->reset()->execute($sql);

				$booking_arr['tickets'] = pjBookingTicketModel::factory()
					->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
					->join('pjTicket', "t3.id=t1.ticket_id", 'left')
					->select('t1.*, t2.content as title')
					->where('booking_id', $booking_arr['id'])
					->findAll()
                    ->getData();
                    
                $refund_message = '';
                if ($booking_arr['txn_id'] && $booking_arr['payment_method'] == 'flutterwave') {
                    $refund_status = $pjFrontEnd->pjActionFlutterwaveRefund($booking_arr['txn_id'],$booking_arr['total']);
                    $refund_message = ($refund_status) ? '&p=1&r=1' : '&p=1&r=0';
                }                  
                 
                if ($no_message != 1) {
                    $pjFrontEnd->pjActionConfirmSend($this->option_arr, $booking_arr, PJ_SALT, 'cancel');
                }
                
                $data = array(
                    'success' => 1,
                    'message' => 'Your booking successfully cancelled'
                );
			} else {
                $data = array(
                    'success' => 0,
                    'message' => 'No booking found'
                );
            }
		} else {
            $data = array(
                'success' => 0,
                'message' => 'Need to pass the required parameter'
            );
        }

        pjAppController::jsonResponse($data);  
    }
    
    public function pjActionCancelInsurance() {        
        $params = $this->getParams();

        $booking_arr1 = pjBookingModel::factory()
				->select('t1.*')				
                ->where('t1.uuid',$params['uuid'])
                ->findAll()
                ->getData();

        if (isset($booking_arr1[0]) && count($booking_arr1[0]) > 0) {
            $booking_arr = $booking_arr1[0];

            $post_params = array(
                'uuid' => $params['uuid'],
                'website_id' => PJ_COMBINED_API_WEBSITE_ID
            );
            $post_data = '';
            foreach($post_params as $k => $v) { 
                $post_data .= $k . '='.$v.'&'; 
            }
            $post_data = rtrim($post_data, '&');

            $url = PJ_COMBINED_API_URL.'/cancelinsurance';
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

            $response = json_decode($json_response,true);

            if ($response['success'] == 1 && $response['is_insurance'] == 1 && $response['is_cancelled'] == 1) {
                if(!empty($booking_arr['c_phone'])) {
                    $message = 'Your insurance has been cancelled as the tickets on booking ID - '.$params['uuid'].' is cancelled.';
                    $params_sms = array(
                            'text' => $message,
                            'type' => 'unicode',
                            'key' => md5($this->option_arr['private_key'] . PJ_SALT),
                            'number' => $booking_arr['c_phone']
                    );                
                    
                    $this->requestAction(array('controller' => 'pjSendSms', 'action' => 'pjActionSendSms', 'params' => $params_sms), array('return'));
                }

                if ($booking_arr['c_email']) {
                    $Email = new pjEmail();
                    if ($option_arr['o_send_email'] == 'smtp') {
                        $Email
                            ->setTransport('smtp')
                            ->setSmtpHost($option_arr['o_smtp_host'])
                            ->setSmtpPort($option_arr['o_smtp_port'])
                            ->setSmtpUser($option_arr['o_smtp_user'])
                            ->setSmtpPass($option_arr['o_smtp_pass'])
                            ->setSender($option_arr['o_smtp_user'])
                        ;
                    }
                    $Email->setContentType('text/html');
                    
                    $admin_email = $this->getAdminEmail();
                    $from_email = $admin_email;
                    if(!empty($this->option_arr['o_sender_email'])) {
                        $from_email = $option_arr['o_sender_email'];
                    }

                    $message = 'Your insurance has been cancelled as the tickets on booking ID - '.$params['uuid'].' is cancelled.';
                    
                    $Email
                        ->setTo($booking_arr['c_email'])
                        ->setFrom($from_email)
                        ->setSubject('Insurance Cancelled')
                        ->send(pjUtil::textToHtml($message));
                }
            }          
        }
    }

    public function pjActionDashboard(){  
        $user_id = (isset($_POST['user_id']) && trim($_POST['user_id'])) ? trim($_POST['user_id']) : '';

        $pjBookingModel = pjBookingModel::factory();
        $pjBusModel = pjBusModel::factory();
        $pjRouteModel = pjRouteModel::factory();
        $pjUserModel = pjUserModel::factory();
        $pjMultiLangModel = pjMultiLangModel::factory();
        $pjCreditModel = pjCreditModel::factory();
    
        $current_date = date('Y-m-d');
        $weekday = strtolower(date('l'));

        $cnt_today_bookings_model = $pjBookingModel->where("t1.created LIKE '%$current_date%' AND t1.status='confirmed'");
        if ($user_id) {
            $cnt_today_bookings_model->where('t1.user_id = '.$user_id);	
        }
        $cnt_today_bookings = $cnt_today_bookings_model->findCount()->getData();

        $cnt_api_booking_model = $pjBookingModel->reset()->where("t1.created LIKE '%$current_date%' AND t1.is_combined_api = 1 AND t1.status='confirmed'");
        $cnt_api_booking = $cnt_api_booking_model->findCount()->getData();

        $cnt_web_booking_model = $pjBookingModel->reset()->where("t1.created LIKE '%$current_date%' AND t1.status='confirmed' AND (t1.is_combined_api = 0 || t1.is_combined_api IS NULL)");
        $cnt_web_booking = $cnt_web_booking_model->findCount()->getData();

        $cnt_agent_booking_model = $pjBookingModel->reset()->where("t1.created LIKE '%$current_date%' AND t1.status='confirmed' AND t1.user_id <> 0");
        $cnt_agent_booking = $cnt_agent_booking_model->findCount()->getData();

        $cnt_today_departure = 0;
        $next_buses_arr = array();
        $date = $current_date;

        $sql = 'SELECT t1.bus_id,t3.content AS route,count(t1.bus_id) AS total_bookings,SUM(t1.total) AS total_amount
        FROM '.$pjBookingModel->getTable().' AS t1
        LEFT JOIN '.$pjBusModel->getTable().' AS t2 ON (t2.id = t1.bus_id)
        LEFT JOIN '.$pjMultiLangModel->getTable().' AS t3 ON (t3.model="pjRoute" AND t3.foreign_id=t2.route_id AND t3.field="title" AND t3.locale="'.$this->getLocaleId().'")
        WHERE t1.created LIKE "%'.$current_date.'%"  AND t1.status="confirmed"
        GROUP BY t2.route_id
        ORDER BY route';

        $route_today_arr = $pjBookingModel->reset()->execute($sql)->getData();
        $route_bookings = array();
        foreach ($route_today_arr as $route) {
            $route_bookings = array(
                'route' => $route['route'],
                'total_bookings' => $route['total_bookings'],
                'total_amount' => round($route['total_amount'], 2)
            );
        }

        $sql = 'SELECT SUM(t1.total) AS total_amount
        FROM '.$pjBookingModel->getTable().' AS t1            
        WHERE t1.created LIKE "%'.$current_date.'%" AND t1.status="confirmed"';        

        $route_total_arr = $pjBookingModel->reset()->execute($sql)->getData();
        $route_total = (isset($route_total_arr[0]['total_amount'])) ? $route_total_arr[0]['total_amount'] : 0;
            
        $sql = "SELECT t1.id,t1.name,t1.phone,t1.email,t1.added_by,t1.agent_type,t1.booking_total,t1.commission_percent,t1.booking_total*(t1.commission_percent/100) AS total_commission FROM 
        (
            SELECT tb3.id,tb3.agent_type,tb3.added_by,tb3.name,tb3.phone,tb3.email,tb2.commission_percent,SUM(total) AS booking_total 
            FROM  ".$pjBookingModel->getTable()." AS tb1
            LEFT JOIN ".$pjCreditModel->getTable()." AS tb2 ON (tb2.user_id = tb1.user_id)  
            LEFT JOIN ".$pjUserModel->getTable()." AS tb3 ON (tb3.id = tb1.user_id)              
            WHERE tb1.created LIKE '%$current_date%' AND
            tb1.status='confirmed' AND
            tb1.user_id <> 0 AND
            tb3.role_id = 2
            GROUP BY tb1.user_id
        ) AS t1 ORDER BY booking_total DESC";

        $transaction_arr = $pjBookingModel->reset()->execute($sql)->getData();
        $transactions = array();
        if ($transaction_arr) {
            foreach ($transaction_arr as $k=>$transaction) {
                if ($transaction['agent_type'] == 4) {
                    $added_by = $pjUserModel->reset()
                    ->find($transaction['added_by'])
                    ->getData();

                    if (isset($transactions[$added_by['id']])) {
                        $transactions[$added_by['id']]['booking_total'] += round($transaction['booking_total'],2);
                        $transactions[$added_by['id']]['total_commission'] += round($transaction['total_commission'],2);
                    } else {
                        $transaction['name'] = $added_by['name'];
                        $transaction['phone'] = $added_by['phone'];
                        $transaction['email'] = $added_by['email'];
                        $transaction['booking_total'] = round($transaction['booking_total'],2);
                        $transaction['total_commission'] = round($transaction['total_commission'],2);
                        $transactions[$added_by['id']] = $transaction;
                    }                        
                } else {
                    $transaction['booking_total'] = round($transaction['booking_total'],2);
                    $transaction['total_commission'] = round($transaction['total_commission'],2);
                    $transactions[$transaction['id']] = $transaction;
                }
            }
        }

        $cnt_routes = $pjRouteModel->findCount()->getData();
        $cnt_buses = $pjBusModel->findCount()->getData();
        
        $next_3_months = strtotime('+3 month', strtotime($date));
        
        $today_bus_ids = array();

        if($cnt_buses > 0)
        {
            while(count($next_buses_arr) < 5 && strtotime($date) < $next_3_months)
            {
                if (empty($user_id)) {
                    $bus_arr = $pjBusModel
                        ->reset()
                        ->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                        ->select("t1.*, t2.content AS route,
                                    (SELECT COUNT(TB.id) 
                                        FROM `".$pjBookingModel->getTable()."` AS TB 
                                        WHERE TB.bus_id=t1.id AND TB.booking_date='$date') AS total_bookings,
                                    (SELECT SUM(TBT.qty) 
                                        FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT 
                                        WHERE TBT.booking_id IN 
                                                (SELECT TB1.id 
                                                FROM `".$pjBookingModel->getTable()."` AS TB1
                                                WHERE TB1.bus_id=t1.id AND TB1.booking_date='$date')) AS total_tickets")
                        ->where("(t1.start_date <= '$date' AND t1.end_date >= '$date')")
                        ->orderBy("departure_time ASC")
                        ->findAll()
                        ->getData();
                } else {
                    $bus_arr = $pjBusModel
                        ->reset()
                        ->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.route_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                        ->select("t1.*, t2.content AS route,
                                    (SELECT COUNT(TB.id) 
                                        FROM `".$pjBookingModel->getTable()."` AS TB 
                                        WHERE (
                                            TB.bus_id=t1.id AND 
                                            TB.booking_date='$date' AND 
                                            (TB.user_id = ".$user_id." OR TB.user_id IN (SELECT id FROM ".$pjUserModel->getTable()." WHERE added_by = ".$user_id."))
                                        )
                                    )AS total_bookings,
                                    (SELECT SUM(TBT.qty) 
                                        FROM `".pjBookingTicketModel::factory()->getTable()."` AS TBT 
                                        WHERE TBT.booking_id IN 
                                                (SELECT TB1.id 
                                                FROM `".$pjBookingModel->getTable()."` AS TB1
                                                WHERE TB1.bus_id=t1.id AND TB1.booking_date='$date' AND (TB1.user_id = ".$user_id." OR TB1.user_id IN (SELECT id FROM ".$pjUserModel->getTable()." WHERE added_by = ".$user_id.")))) AS total_tickets")
                        ->where("(t1.start_date <= '$date' AND t1.end_date >= '$date')")
                        ->orderBy("departure_time ASC")
                        ->findAll()
                        ->getData();	
                }

                foreach($bus_arr as $v)
                {
                    if(empty($v['recurring']))
                    {
                        if($date == $current_date)
                        {
                            $today_bus_ids[] = $v['id'];
                        }                        
                    }else{
                        if(in_array($weekday, explode("|", $v['recurring'])))
                        {
                            if($date == $current_date)
                            {
                                $today_bus_ids[] = $v['id'];
                            }
                        }
                    }
                }
                $date = date('Y-m-d', strtotime($date . ' + 1 day'));
            }
        }

        $bus_today_arr = array();
        $total_bus_amount = 0;
        if ($today_bus_ids) {
            $sql1 = 'SELECT t4.bus_id,count(t4.bus_id) AS total_bookings,SUM(t4.total) AS total_amount
            FROM '.$pjBookingModel->getTable().' AS t4   
            WHERE t4.created LIKE "%'.$current_date.'%"
            AND t4.status="confirmed"
            AND t4.bus_id IN ('.implode(',',$today_bus_ids).')
            GROUP BY t4.bus_id';

            $sql = 'SELECT t1.id,t1.departure_time,t1.arrival_time,t2.content AS route,t3.*             
            FROM '.$pjBusModel->getTable().' AS t1
            LEFT JOIN '.$pjMultiLangModel->getTable().' AS t2 ON (t2.model="pjRoute" AND t2.foreign_id=t1.route_id AND t2.field="title" AND t2.locale="'.$this->getLocaleId().'")
            LEFT JOIN ('.$sql1.') AS t3 ON t3.bus_id = t1.id
            WHERE t1.id IN ('.implode(',',$today_bus_ids).')
            ORDER BY total_bookings DESC,content ASC';

            $bus_today_arr = $pjBookingModel->reset()->execute($sql)->getData();  
            
            $sql = 'SELECT SUM(total_amount) AS total_bus FROM
                ('.$sql1.') AS t1';

            $bus_today_total_arr = $pjBookingModel->reset()->execute($sql)->getData(); 
            $total_bus_amount = round($bus_today_total_arr[0]['total_bus'],2);
        }

        $today_bus_bookings = array();
        if ($bus_today_arr) {            
            foreach ($bus_today_arr as $bus) {
                $departure_time = pjUtil::formatTime ($bus['departure_time'], 'H:i', $this->option_arr['o_time_format']);
                $arrival_time = pjUtil::formatTime ($bus['arrival_time'], 'H:i', $this->option_arr['o_time_format']);

                $today_bus_bookings[] = array(
                    'bus' => $bus['route'].' '.$departure_time.' - '.$arrival_time,
                    'total_bookings' => ($bus['total_bookings']) ? $bus['total_bookings'] : 0,
                    'total_amount' => round($bus['total_amount'], 2)
                );
            }
        }

        $data = array(
            'success' => 1,
            'message' => '',
            'currency' => $this->option_arr['o_currency'],
            'total_bookings' => $cnt_today_bookings,
            'total_app_bookings' => $cnt_api_booking,
            'total_web_bookings' => $cnt_web_booking,
            'total_agent_bookings' => $cnt_agent_booking,
            'route_bookings' => $route_bookings,
            'route_total' => $route_total,
            'agent_bookings' => $transactions,
            'bus_bookings' => $today_bus_bookings,
            'bus_total' => $total_bus_amount
        );

        pjAppController::jsonResponse($data);  
    }

    public function pjActionDailyReport(){  
        $report_date = (isset($_POST['date']) && trim($_POST['date'])) ? trim($_POST['date']) : date('Y-m-d');

        $pjUserModel = pjUserModel::factory();
        $pjCreditModel = pjCreditModel::factory();

        $pjBookingModel = pjBookingModel::factory()                
            ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
            ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
            ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
            ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
            ->join('pjUser', "t6.id=t1.user_id", 'left outer');

        $booking_arr = $pjBookingModel
            ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location,t6.name,t6.added_by,t6.agent_type')
            ->where('DATE(t1.created) = "'.$report_date.'"')
            ->where('t1.status','confirmed')
            ->where('user_id != 0')
            ->where('role_id',2)
            ->orderBy("created asc")
            ->findAll()
            ->getData();

        $bookings_created = array();
        if ($booking_arr) {
            foreach ($booking_arr as $v) {
                $created = date($this->option_arr['o_date_format'] . ', ' . $this->option_arr['o_time_format'], strtotime($v['created']));
                $date_time = date($this->option_arr['o_date_format'] . ', ' . $this->option_arr['o_time_format'], strtotime($v['booking_datetime'])) . '<br/>' . date($this->option_arr['o_date_format'] . ', ' . $this->option_arr['o_time_format'], strtotime($v['stop_datetime']));
                if(date($this->option_arr['o_date_format'], strtotime($v['booking_datetime'])) == date($this->option_arr['o_date_format'], strtotime($v['stop_datetime'])))
                {
                    $date_time = date($this->option_arr['o_date_format'], strtotime($v['booking_datetime'])) . '<br/>' . date($this->option_arr['o_time_format'], strtotime($v['booking_datetime'])) . ' - ' . date($this->option_arr['o_time_format'], strtotime($v['stop_datetime']));
                }
                
                $route_details = $v['route_title'];
                $route_details .= ', ' . date($this->option_arr['o_time_format'], strtotime($v['departure_time'])) . ' - ' . date($this->option_arr['o_time_format'], strtotime($v['arrival_time']));
                $route_details .= '<br/>'  . mb_strtolower(__('lblFrom', true), 'UTF-8') . ' ' . $v['from_location'] . ' ' . mb_strtolower(__('lblTo', true), 'UTF-8') . ' ' . $v['to_location'];

                $bookings_created[] = array(
                    'created' => $created,
                    'agent' => $v['name'],
                    'customer' => $v['c_fname'].' '.$v['c_lname'],
                    'departure_arrival' => $date_time,
                    'route' => $route_details,
                    'total' => number_format($v['total'], 2),
                    'status' => $v['status']
                );
            }
        }

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

        $agent_transaction = array();
        if ($transactions) {
            foreach($transactions as $v) {
                $agent_transaction[] = array(
                    'agent' => $v['name'],
                    'phone' => $v['phone'],
                    'email' => $v['email'],
                    'total_amount' => number_format($v['booking_total'], 2),
                    'total_commission' => number_format($v['total_commission'], 2)
                );
            }
        }

        $data = array(
            'success' => 1,
            'message' => '',
            'currency' => $this->option_arr['o_currency'],
            'bookings_created' => $bookings_created,
            'agent_transaction' => $agent_transaction
        );

        pjAppController::jsonResponse($data);
    }

    public function pjActionAgentReport(){  
        $agent_id = (isset($_POST['agent_id']) && trim($_POST['agent_id'])) ? trim($_POST['agent_id']) : 0;
        $start_date = (isset($_POST['start_date']) && trim($_POST['start_date'])) ? trim($_POST['start_date']) : '';
        if ($start_date) {
            $end_date = (isset($_POST['end_date']) && trim($_POST['end_date'])) ? trim($_POST['end_date']) : '';
            if (!$end_date || $start_date >= $end_date) {
                $end_date = $start_date;
            }
        } else {
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d');
        }

        $pjUserModel = pjUserModel::factory();
        $pjCreditModel = pjCreditModel::factory();        

        $agent_detail = pjUserModel::factory()
            ->find($agent_id)
            ->getData();

        if ($agent_detail) {
            $name = $agent_detail['name'];

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

            $total = 0;
            $total_confirmed = 0;
            $total_app = 0;
            $total_web = 0;
            $agent_bookings = array();
            foreach($booking_arr as $v) { 
                if ($v['is_combined_api'] == 1) {
                    $channel = 'App';
                    $total_app++;
                } else {
                    $channel = 'Web';
                    $total_web++;
                }
                $total += round($v['total'], 2);
                if ($v['status'] == 'confirmed') {
                    $total_confirmed += round($v['total'], 2);
                }
                $created = date($this->option_arr['o_date_format'] . ', ' . $this->option_arr['o_time_format'], strtotime($v['created']));
                $date_time = date($this->option_arr['o_date_format'] . ', ' . $this->option_arr['o_time_format'], strtotime($v['booking_datetime']));                                        
                $route_details = $v['route_title'];
                $route_details .= ', ' . date($this->option_arr['o_time_format'], strtotime($v['departure_time']));
                $route_details .= '<br/>'  . mb_strtolower(__('lblFrom', true), 'UTF-8') . ' ' . $v['from_location'] . ' ' . mb_strtolower(__('lblTo', true), 'UTF-8') . ' ' . $v['to_location'];
                
                $v['route_details'] = $route_details;

                $agent_bookings[] = array(
                    'channel' => $channel,
                    'created' => $created,
                    'date_time' => $date_time,
                    'booking_id' => $v['uuid'],
                    'route' => $route_details,
                    'status' => $v['status'],
                    'amount' => number_format($v['total'], 2)
                );
            }

            $data = array(
                'success' => 1,
                'message' => '',
                'start_date' => $start_date,
                'end_date' => $end_date,
                'currency' => $this->option_arr['o_currency'],
                'app_bookings' => $total_app,
                'web_bookings' => $total_web,
                'total_amount' => number_format($total, 2),
                'agent_bookings' => $agent_bookings
            );
        } else {
            $data = array(
                'success' => 0,
                'message' => 'No agent found'
            );
        }

        pjAppController::jsonResponse($data);
    }

    public function pjActionRoutes(){  
        $route_arr = pjRouteModel::factory()
            ->select(" t1.id, t1.status, t2.content as title, t3.content as `from`, t4.content as `to`")
            ->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
            ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t1.id AND t3.field='from' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
            ->join('pjMultiLang', "t4.model='pjRoute' AND t4.foreign_id=t1.id AND t4.field='to' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
            ->orderBy("title")
            ->findAll()
            ->getData();

        $routes = array();
        if ($route_arr) {
            foreach ($route_arr as $route) { 
                $routes[] = array(
                    'id' => $route['id'],
                    'name' => $route['title']
                );                
            }
        }

        $data = array(
            'success' => 1,
            'message' => '',
            'routes' => $routes
        );

        pjAppController::jsonResponse($data);
    }

    public function pjActionRouteReport(){  
        $route_id = (isset($_POST['route_id']) && trim($_POST['route_id'])) ? trim($_POST['route_id']) : 0;
        $start_date = (isset($_POST['start_date']) && trim($_POST['start_date'])) ? trim($_POST['start_date']) : '';
        if ($start_date) {
            $end_date = (isset($_POST['end_date']) && trim($_POST['end_date'])) ? trim($_POST['end_date']) : '';
            if (!$end_date || $start_date >= $end_date) {
                $end_date = $start_date;
            }
        } else {
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d');
        }

         $route_detail = pjRouteModel::factory()
         ->select(" t1.id, t1.status, t2.content as title, t3.content as `from`, t4.content as `to`")
         ->join('pjMultiLang', "t2.model='pjRoute' AND t2.foreign_id=t1.id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
         ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t1.id AND t3.field='from' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
         ->join('pjMultiLang', "t4.model='pjRoute' AND t4.foreign_id=t1.id AND t4.field='to' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
         ->find($route_id)
         ->getData();

        if ($route_detail) {
            $name = $route_detail['title'];

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

            $total = 0;
            $total_confirmed = 0;
            $total_app = 0;
            $total_web = 0;
            $total_agent = 0;
            $route_bookings = array();
            foreach($booking_arr as $v) { 
                if ($v['user_id'] > 0) {
                    $channel = 'Agent';
                    $total_agent++;
                } else if ($v['is_combined_api'] == 1) {
                    $channel = 'App';
                    $total_app++;
                } else {
                    $channel = 'Web';
                    $total_web++;
                }
                $total += round($v['total'], 2);
                if ($v['status'] == 'confirmed') {
                    $total_confirmed += round($v['total'], 2);
                }
                $created = date($this->option_arr['o_date_format'] . ', ' . $this->option_arr['o_time_format'], strtotime($v['created']));
                $date_time = date($this->option_arr['o_date_format'] . ', ' . $this->option_arr['o_time_format'], strtotime($v['booking_datetime']));

                $route_bookings[] = array(
                    'channel' => $channel,
                    'created' => $created,
                    'date_time' => $date_time,
                    'booking_id' => $v['uuid'],
                    'status' => $v['status'],
                    'amount' => number_format($v['total'], 2)
                );
            }

            $data = array(
                'success' => 1,
                'message' => '',
                'start_date' => $start_date,
                'end_date' => $end_date,
                'currency' => $this->option_arr['o_currency'],
                'app_bookings' => $total_app,
                'web_bookings' => $total_web,
                'agent_bookings' => $total_agent,
                'total_amount' => number_format($total, 2),
                'route_bookings' => $route_bookings
            );
        } else {
            $data = array(
                'success' => 0,
                'message' => 'No route found'
            );
        }

        pjAppController::jsonResponse($data);
    }

    public function pjActionPaymentMethods(){  
        $payment_methods = array();
        foreach (__('payment_methods', true, false) as $k => $v) {
            $payment_methods[] = array(
                'code' => $k,
                'name' => $v
            );
        }

        $data = array(
            'success' => 1,
            'message' => '',
            'payment_methods' => $payment_methods
        );

        pjAppController::jsonResponse($data);
    }

    public function pjActionPaymentMethodReport(){  
        $payment_method = (isset($_POST['payment_method']) && trim($_POST['payment_method'])) ? trim($_POST['payment_method']) : 0;
        $start_date = (isset($_POST['start_date']) && trim($_POST['start_date'])) ? trim($_POST['start_date']) : '';
        if ($start_date) {
            $end_date = (isset($_POST['end_date']) && trim($_POST['end_date'])) ? trim($_POST['end_date']) : '';
            if (!$end_date || $start_date >= $end_date) {
                $end_date = $start_date;
            }
        } else {
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d');
        }

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

        $total = 0;
        $total_confirmed = 0;
        $total_app = 0;
        $total_web = 0;
        $total_agent = 0;
        $payment_method_bookings = array();
        foreach($booking_arr as $v) { 
            if ($v['user_id'] > 0) {
                $channel = 'Agent';
                $total_agent++;
            } else if ($v['is_combined_api'] == 1) {
                $channel = 'App';
                $total_app++;
            } else {
                $channel = 'Web';
                $total_web++;
            }
            $total += round($v['total'], 2);
            if ($v['status'] == 'confirmed') {
                $total_confirmed += round($v['total'], 2);
            }
            $created = date($this->option_arr['o_date_format'] . ', ' . $this->option_arr['o_time_format'], strtotime($v['created']));
            $date_time = date($this->option_arr['o_date_format'] . ', ' . $this->option_arr['o_time_format'], strtotime($v['booking_datetime']));

            $payment_method_bookings[] = array(
                'channel' => $channel,
                'created' => $created,
                'date_time' => $date_time,
                'booking_id' => $v['uuid'],
                'status' => $v['status'],
                'amount' => number_format($v['total'], 2)
            );
        }

        $data = array(
            'success' => 1,
            'message' => '',
            'start_date' => $start_date,
            'end_date' => $end_date,
            'currency' => $this->option_arr['o_currency'],
            'app_bookings' => $total_app,
            'web_bookings' => $total_web,
            'agent_bookings' => $total_agent,
            'total_amount' => number_format($total, 2),
            'payment_method_bookings' => $payment_method_bookings
        );

        pjAppController::jsonResponse($data);
    }

    public function pjActionIsSuperAdmin(){  
        $phone = (isset($_POST['phone']) && trim($_POST['phone'])) ? trim($_POST['phone']) : 0;

        $user = pjUserModel::factory()
            ->where('phone',$phone)
            ->where('role_id',1)
            ->findAll()
            ->getData();

        $is_super_admin = ($user && count($user)) ? 'yes' : 'no';

        $data = array(
            'success' => 1,
            'message' => '',
            'is_super_admin' => $is_super_admin
        );

        pjAppController::jsonResponse($data);
    }

    public function pjActionIsBusAdmin(){  
        $phone = (isset($_POST['phone']) && trim($_POST['phone'])) ? trim($_POST['phone']) : 0;

        $user = pjUserModel::factory()
            ->where('phone',$phone)
            ->where('role_id',3)
            ->findAll()
            ->getData();

        $is_bus_admin = ($user && count($user)) ? 'yes' : 'no';

        $data = array(
            'success' => 1,
            'message' => '',
            'is_bus_admin' => $is_bus_admin
        );

        pjAppController::jsonResponse($data);
    }

    public function pjActionLipilaResponse() {
        $uuid = (isset($_POST['uuid']) && trim($_POST['uuid'])) ? trim($_POST['uuid']) : ''; 
        $status = (isset($_POST['status']) && trim($_POST['status'])) ? trim($_POST['status']) : '';
        $txn_id = (isset($_POST['txn_id']) && trim($_POST['txn_id'])) ? trim($_POST['txn_id']) : '';

        if ($uuid && $status && $txn_id) {            
            $booking_array = pjBookingModel::factory()
            ->where('uuid',$uuid)
            ->findAll()
            ->getData();

            if (count($booking_array)) {
                $id = $booking_array[0]['id'];

                pjBookingModel::factory()
                    ->where('uuid',$uuid)
                    ->modifyAll(array('status' => $status,'txn_id' => $txn_id,'processed_on' => ':NOW()'));
              
                $arr =  pjBookingModel::factory()->select ( 't1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location' )->join ( 'pjBus', "t2.id=t1.bus_id", 'left outer' )->join ( 'pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='" . $this->getLocaleId () . "'", 'left outer' )->find ( $id )->getData ();
   			    $tickets = pjBookingTicketModel::factory()->join ( 'pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjTicket', "t3.id=t1.ticket_id", 'left' )->select ( 't1.*, t2.content as title' )->where ( 'booking_id', $arr ['id'] )->findAll ()->getData ();
                $arr ['tickets'] = $tickets;

                if ($status == 'confirmed') {
                    $booking = $booking_array[0];
                    $pjBookingModel = pjBookingModel::factory();

                    pjBookingPaymentModel::factory()
                        ->where('booking_id', $booking['id'])
                        ->where('payment_type', 'online')
                        ->modifyAll(array('status' => 'paid'));
        
                        $booking_arr = $pjBookingModel
                            ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
                            ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                            ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                            ->find($booking['id'])
                            ->getData();
                            
                        $booking_arr['tickets'] = pjBookingTicketModel::factory()
                            ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                            ->select('t1.*, t2.content as title')
                            ->where('booking_id', $booking['id'])
                            ->findAll()
                            ->getData();

                        $price_tbl = pjPriceModel::factory()->getTable();
            
                        $pjBookingTicketModel = pjBookingTicketModel::factory();
                        $tickets = $pjBookingTicketModel
                            ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                            ->select('t1.*, t2.content as title, (SELECT TP.price FROM `'.$price_tbl.'` AS TP WHERE TP.ticket_id = t1.ticket_id AND TP.bus_id = '.$booking_arr['bus_id'].' AND TP.from_location_id = '.$booking_arr['pickup_id'].' AND TP.to_location_id= '.$booking_arr['return_id']. ' AND is_return = "F" LIMIT 1) as price')
                            ->where('booking_id', $booking_arr['id'])
                            ->findAll()->getData();
            
                        $ticket_arr = $tickets[0];
                        $ticket_arr['currency'] = $this->option_arr['o_currency'];

                        $seats = '';
                        $booked_seat_id_arr = pjBookingSeatModel::factory()
                            ->select("DISTINCT (seat_id)")
                            ->where('booking_id', $booking_arr['id'])
                            ->findAll()
                            ->getDataPair('seat_id', 'seat_id');

                        if(!empty($booked_seat_id_arr))
                        {
                            $selected_seat_arr = pjSeatModel::factory()->whereIn('t1.id', $booked_seat_id_arr)->findAll()->getDataPair('id', 'name');
                            $seats = join(", ", $selected_seat_arr);
                        }
                        $ticket_arr['seats'] = $seats;

                        $booking_arr['tickets'] = $ticket_arr;
                        
                        $pjCityModel = pjCityModel::factory();
                        $pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($booking_arr['pickup_id'])->getData();
                        $to_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($booking_arr['return_id'])->getData();
                        $booking_arr['from_location'] = $pickup_location['name'];
                        $booking_arr['to_location'] = $to_location['name'];	

                        $trip = array(                                                      
                            'booking_date' => pjAppController::cleanData($booking_arr['booking_date']),
                            'booking_route' => pjAppController::cleanData($booking_arr['booking_route']),
                            'departure_time' => pjAppController::cleanData($booking_arr['booking_datetime']),
                            'arrival_time' => pjAppController::cleanData($booking_arr['stop_datetime']),
                            'route_title' => pjAppController::cleanData($booking_arr['route_title']),
                            'from_location' => pjAppController::cleanData($booking_arr['from_location']),
                            'to_location' => pjAppController::cleanData($booking_arr['to_location']),
                            'seat_names' => pjAppController::cleanData($booking_arr['tickets']['seats']),
                            'title' => pjAppController::cleanData($booking_arr['c_title']),
                            'first_name' => pjAppController::cleanData($booking_arr['c_fname']),
                            'last_name' => pjAppController::cleanData($booking_arr['c_lname']),
                            'phone' => pjAppController::cleanData($booking_arr['c_phone']),
                            'email' => pjAppController::cleanData($booking_arr['c_email']),
                            'uuid' => pjAppController::cleanData($booking_arr['uuid']),
                            'ticket_type' => pjAppController::cleanData($booking_arr['tickets']['title']),
                            'total' => pjAppController::cleanData($booking_arr['total']),
                            'currency' => pjAppController::cleanData($booking_arr['tickets']['currency']),
                            'payment_method' => pjAppController::cleanData($booking_arr['payment_method']),
                            'status' => pjAppController::cleanData($booking_arr['status'])
                        ); 
                    $data = array(
                        'success' => 1,  
                        'message' => null,
                        'booking' => $trip
                    );

                    $message_status = 'confirm';
                } else {
                    $data = array(
                        'success' => 0,  
                        'message' => 'Your payment failed and so your booking get cancelled'
                    );

                    $message_status = 'cancel';
                }
                $pjFrontEnd = new pjFrontEnd(); 
                $pjFrontEnd->pjActionConfirmSend( $this->option_arr, $arr, PJ_SALT, $message_status);                
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

        pjAppController::jsonResponse($data);
    }

    public function pjActionMTNResponse() {
        $uuid = (isset($_POST['uuid']) && trim($_POST['uuid'])) ? trim($_POST['uuid']) : ''; 
        $status = (isset($_POST['status']) && trim($_POST['status'])) ? trim($_POST['status']) : '';
        $txn_id = (isset($_POST['txn_id']) && trim($_POST['txn_id'])) ? trim($_POST['txn_id']) : '';

        if ($uuid && $status) {            
            $booking_array = pjBookingModel::factory()
            ->where('uuid',$uuid)
            ->findAll()
            ->getData();

            if (count($booking_array)) {
                $id = $booking_array[0]['id'];

                pjBookingModel::factory()
                    ->where('uuid',$uuid)
                    ->modifyAll(array('status' => $status,'txn_id' => $txn_id,'processed_on' => ':NOW()'));
              
                $arr =  pjBookingModel::factory()->select ( 't1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location' )->join ( 'pjBus', "t2.id=t1.bus_id", 'left outer' )->join ( 'pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='" . $this->getLocaleId () . "'", 'left outer' )->find ( $id )->getData ();
   			    $tickets = pjBookingTicketModel::factory()->join ( 'pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='" . $this->getLocaleId () . "'", 'left outer' )->join ( 'pjTicket', "t3.id=t1.ticket_id", 'left' )->select ( 't1.*, t2.content as title' )->where ( 'booking_id', $arr ['id'] )->findAll ()->getData ();
                $arr ['tickets'] = $tickets;

                if ($status == 'confirmed') {
                    $booking = $booking_array[0];
                    $pjBookingModel = pjBookingModel::factory();

                    pjBookingPaymentModel::factory()
                        ->where('booking_id', $booking['id'])
                        ->where('payment_type', 'online')
                        ->modifyAll(array('status' => 'paid'));
        
                        $booking_arr = $pjBookingModel
                            ->select('t1.*, t2.departure_time, t2.arrival_time, t3.content as route_title, t4.content as from_location, t5.content as to_location')
                            ->join('pjBus', "t2.id=t1.bus_id", 'left outer')
                            ->join('pjMultiLang', "t3.model='pjRoute' AND t3.foreign_id=t2.route_id AND t3.field='title' AND t3.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjMultiLang', "t4.model='pjCity' AND t4.foreign_id=t1.pickup_id AND t4.field='name' AND t4.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjMultiLang', "t5.model='pjCity' AND t5.foreign_id=t1.return_id AND t5.field='name' AND t5.locale='".$this->getLocaleId()."'", 'left outer')
                            ->find($booking['id'])
                            ->getData();
                            
                        $booking_arr['tickets'] = pjBookingTicketModel::factory()
                            ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                            ->select('t1.*, t2.content as title')
                            ->where('booking_id', $booking['id'])
                            ->findAll()
                            ->getData();

                        $price_tbl = pjPriceModel::factory()->getTable();
            
                        $pjBookingTicketModel = pjBookingTicketModel::factory();
                        $tickets = $pjBookingTicketModel
                            ->join('pjMultiLang', "t2.model='pjTicket' AND t2.foreign_id=t1.ticket_id AND t2.field='title' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                            ->join('pjTicket', "t3.id=t1.ticket_id", 'left')
                            ->select('t1.*, t2.content as title, (SELECT TP.price FROM `'.$price_tbl.'` AS TP WHERE TP.ticket_id = t1.ticket_id AND TP.bus_id = '.$booking_arr['bus_id'].' AND TP.from_location_id = '.$booking_arr['pickup_id'].' AND TP.to_location_id= '.$booking_arr['return_id']. ' AND is_return = "F" LIMIT 1) as price')
                            ->where('booking_id', $booking_arr['id'])
                            ->findAll()->getData();
            
                        $ticket_arr = $tickets[0];
                        $ticket_arr['currency'] = $this->option_arr['o_currency'];

                        $seats = '';
                        $booked_seat_id_arr = pjBookingSeatModel::factory()
                            ->select("DISTINCT (seat_id)")
                            ->where('booking_id', $booking_arr['id'])
                            ->findAll()
                            ->getDataPair('seat_id', 'seat_id');

                        if(!empty($booked_seat_id_arr))
                        {
                            $selected_seat_arr = pjSeatModel::factory()->whereIn('t1.id', $booked_seat_id_arr)->findAll()->getDataPair('id', 'name');
                            $seats = join(", ", $selected_seat_arr);
                        }
                        $ticket_arr['seats'] = $seats;

                        $booking_arr['tickets'] = $ticket_arr;
                        
                        $pjCityModel = pjCityModel::factory();
                        $pickup_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($booking_arr['pickup_id'])->getData();
                        $to_location = $pjCityModel->reset()->select('t1.*, t2.content as name')->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')->find($booking_arr['return_id'])->getData();
                        $booking_arr['from_location'] = $pickup_location['name'];
                        $booking_arr['to_location'] = $to_location['name'];	

                        $trip = array(                                                      
                            'booking_date' => pjAppController::cleanData($booking_arr['booking_date']),
                            'booking_route' => pjAppController::cleanData($booking_arr['booking_route']),
                            'departure_time' => pjAppController::cleanData($booking_arr['booking_datetime']),
                            'arrival_time' => pjAppController::cleanData($booking_arr['stop_datetime']),
                            'route_title' => pjAppController::cleanData($booking_arr['route_title']),
                            'from_location' => pjAppController::cleanData($booking_arr['from_location']),
                            'to_location' => pjAppController::cleanData($booking_arr['to_location']),
                            'seat_names' => pjAppController::cleanData($booking_arr['tickets']['seats']),
                            'title' => pjAppController::cleanData($booking_arr['c_title']),
                            'first_name' => pjAppController::cleanData($booking_arr['c_fname']),
                            'last_name' => pjAppController::cleanData($booking_arr['c_lname']),
                            'phone' => pjAppController::cleanData($booking_arr['c_phone']),
                            'email' => pjAppController::cleanData($booking_arr['c_email']),
                            'uuid' => pjAppController::cleanData($booking_arr['uuid']),
                            'ticket_type' => pjAppController::cleanData($booking_arr['tickets']['title']),
                            'total' => pjAppController::cleanData($booking_arr['total']),
                            'currency' => pjAppController::cleanData($booking_arr['tickets']['currency']),
                            'payment_method' => pjAppController::cleanData($booking_arr['payment_method']),
                            'status' => pjAppController::cleanData($booking_arr['status'])
                        ); 
                    $data = array(
                        'success' => 1,  
                        'message' => null,
                        'booking' => $trip
                    );

                    $message_status = 'confirm';
                } else {
                    $data = array(
                        'success' => 0,  
                        'message' => 'Your payment failed and so your booking get cancelled'
                    );

                    $message_status = 'cancel';
                }
                $pjFrontEnd = new pjFrontEnd(); 
                $pjFrontEnd->pjActionConfirmSend( $this->option_arr, $arr, PJ_SALT, $message_status);                
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

        pjAppController::jsonResponse($data);
    }
}
?>