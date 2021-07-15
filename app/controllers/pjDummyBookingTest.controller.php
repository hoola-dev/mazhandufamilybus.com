<?php
/*error_reporting(E_ALL);
ini_set("display_errors", 1);*/
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
class pjDummyBookingTest extends pjFront
{

     public function pjNames(){
        $pathName = __DIR__ ."/Names.txt";
        $file = fopen($pathName, "r");
        $namesArr = file($pathName, FILE_IGNORE_NEW_LINES);
        return $namesArr;
     }

     public function pjMobNumbers(){
        $pathName = __DIR__ ."/numbers.txt";
        $file = fopen($pathName, "r");
        $numbersArr = file($pathName, FILE_IGNORE_NEW_LINES);
        return $numbersArr;
     }

     public function pjGetSource() {
        $pjCityModel = pjCityModel::factory();
        $pjRouteDetailModel = pjRouteDetailModel::factory();
        $source_location_arr = $pjCityModel
                ->reset()
                ->select('t1.id, t2.content as name')
                ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                ->where("t1.id IN(SELECT TRD.from_location_id FROM `".$pjRouteDetailModel->getTable()."` AS TRD)")
                ->orderBy("t2.content ASC")
                ->findAll()
                ->getData();
        return $source_location_arr;
    }

    public function pjGetDestination($source) {
        $where = '';
        $destination_location_arr = array();
        if(isset($source) && !empty($source)){
            $pjCityModel = pjCityModel::factory();
            $pjRouteDetailModel = pjRouteDetailModel::factory();
            $where = "WHERE TRD.from_location_id=" . $source;
            $destination_location_arr = $pjCityModel
                ->reset()
                ->select('t1.*, t2.content as name')
                ->join('pjMultiLang', "t2.model='pjCity' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
                ->where("t1.id IN(SELECT TRD.to_location_id FROM `".$pjRouteDetailModel->getTable()."` AS TRD $where)")
                ->orderBy("t2.content ASC")
                ->findAll()
                ->getData();       
        }
        return $destination_location_arr;
    }

    public function pjGetBusAvailability($pickup_id, $return_id, $is_return, $date, $return_date) {
       
        if($pickup_id != $return_id)
        {
            $date = pjUtil::formatDate($date, $this->option_arr['o_date_format']);
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

            if(isset($is_return) && ($is_return == 'T')){
                $pickup_id_val = $pickup_id;
                $pickup_id = $return_id;
                $return_id = $pickup_id_val;
                $date = pjUtil::formatDate($return_date, $this->option_arr['o_date_format']);
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
        return $response;
    }

    public function pjGetDate($startDate,$endDate){
        $days = round((strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24));
        $n = rand(0,$days);
        return date("Y-m-d",strtotime("$startDate + $n days"));  
    }

    public function pjGetReturnDate($date){
        $n = 2;
        return date("Y-m-d",strtotime("$date + $n days")); 
    }

    public function pjGetCountries() {
        $data = array();
        $countries_arr = pjCountryModel::factory()
            ->select('t1.id, t2.content AS name,t1.phone_code')
            ->join('pjMultiLang', "t2.model='pjCountry' AND t2.foreign_id=t1.id AND t2.field='name' AND t2.locale='".$this->getLocaleId()."'", 'left outer')
            ->orderBy('`name` ASC')->findAll()->getData();

        if(isset($countries_arr[0]['id']) && !empty($countries_arr[0]['id'])){
            $aDefaultOption=array("id"=>"248",
                                  "name" => "Zambia",
                                  "phone_code" => "+260"
                              );
            array_unshift($countries_arr,$aDefaultOption);
            $data = $countries_arr;
        }else{
            $data = array();
        }
        return $data;
    }

    public function pjActionSeats($pickup_id,$return_id,$date, $is_return)
    {
        $data = array();
        $data['to']  = array();
        $data['come_back']  = array();
        $pjBusModel = pjBusModel::factory();
        $date = pjUtil::formatDate($date, $this->option_arr['o_date_format']);

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
                   $to_arr['id'] = $bus['id'];
                    $to_arr['bus'] = $bus['route'];
                    $to_arr['available_seats'] = $bus['seats_available'];
                    $pjPriceModel = pjPriceModel::factory();
                    if($bus['id'] > 0)
                    {
                        
                         $store_val = array('pickup_id' => $pickup_id, 'return_id' => $return_id, 'booking_period' => $booking_period);
                         $avail_arr = $this->getBusAvailability($bus['id'], $store_val, $this->option_arr);
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
            
            if(isset($is_return) && ($is_return == 'T')){
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
                             $return_bus_id = $return_bus['id'];
                             $option_arr = $this->option_arr;
                             $booked_seat_arr = pjBookingSeatModel::factory ()->select ( "DISTINCT seat_id" )->where ( "t1.booking_id IN(SELECT TB.id
                                                    FROM `" . pjDummyBookingModel::factory ()->getTable () . "` AS TB
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
                'data' => $data
            );    
        }else{

            $response = array();
            $response['data'] = array(
                //'status' => 'false',
                'message' => 'No data found'
            );  
        }
        return $response;
    }

    public function pjActionBusData($pickup_id, $return_id,$is_return,$date, $return_date){
        $resp_availability = $this->pjActionAvailability($pickup_id, $return_id,$is_return,$date, $return_date);
        $response_data = array();
        if(isset($resp_availability['code']) && ($resp_availability['code'] != 200)){
            $response = array(
                'status' => 'false',
                'code' => $resp_availability['code'],
                'message' => 'Bus not available'
            );
        }else{
            $seat_price_data = $this->pjActionSeats($pickup_id, $return_id,$date,$is_return);
            $response = array(
                'status' => 'true',
                'code' => $resp_availability['code'],
                'data' => $seat_price_data['data']
            );
        }
         return $response; 

     }

     public function pjActionAvailability($pickup_id, $return_id,$is_return, $date, $return_date) {
       
    $resp = array();
    $return_bus_id_arr = array();

    if($pickup_id != $return_id)
    {
        $resp['code'] = 200;
        $pjBusModel = pjBusModel::factory();
        $date = pjUtil::formatDate($date, $this->option_arr['o_date_format']);
        $this->_set('date', $date);

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
            
            return $resp;
        }

        if (isset($is_return) && $is_return == 'T')
        {
            $pickup_id_value =  $pickup_id;
            $pickup_id = $return_id;
            $return_id = $pickup_id_value;
                
            $date = pjUtil::formatDate($return_date, $this->option_arr['o_date_format']);
            $return_bus_id_arr = $pjBusModel->getBusIds($date, $pickup_id, $return_id);
            if(!isset($_GET['final_check'])) {
                $this->_set('return_date', $return_date);   
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
            $this->_set('pickup_id', $pickup_id);
            $this->_set('return_id', $return_id);
            $this->_set('bus_id_arr', $bus_id_arr);
            $this->_set('is_return', $is_return);
            if (isset($is_return) && $is_return == 'T')
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
            return $resp;
        }
    }
    return $resp;
    }

     public function pjActionBookingTest() {
        $sources = pjDummyBookingTest::pjGetSource();
        $arrValues = array('T','F');
        $names = pjDummyBookingTest::pjNames();
        $mobileNumbers = pjDummyBookingTest::pjMobNumbers();
        $countryArr = pjDummyBookingTest::pjGetCountries();
        $c = 0;

        for($i=0;$i<=1000;$i++){

            //echo $i."<br/>";

            $sourceKey = array_rand($sources); 

            //echo "sourceKey".$sourceKey."<br/>";
            $sourceItem = $sources[$sourceKey];
            //$pickup_id = $sourceItem['id'];
            $pickup_id = 1;

            //echo "pickup_id".$pickup_id."<br/>";

            $destinations = pjDummyBookingTest::pjGetDestination($pickup_id);


            $destKey = array_rand($destinations); 
            $destItem = $destinations[$destKey];
            //$return_id = $destItem['id'];
            $return_id = 5;

            $dt = date_create(date('Y-m-d'));
            date_add($dt, date_interval_create_from_date_string('12 days'));
            $dtVal = date_format($dt, 'd/m/Y');

            date_add($dt, date_interval_create_from_date_string('12 days'));
            $dtVal2 = date_format($dt, 'd/m/Y');

            $startDate = $dtVal;
            //$endDate = date('d/m/Y', strtotime('+12 day'));
            $endDate =  $dtVal2;

            //echo $startDate.'  '.$endDate;
            //exit;

            /*$booking_date = pjDummyBookingTest::pjGetDate($startDate,$endDate);
            $return_date = pjDummyBookingTest::pjGetReturnDate($booking_date);*/

            $booking_date = $startDate;
            $return_date = $endDate;

            $keyVal = array_rand($arrValues);
            //$is_return = $arrValues[$keyVal];
            $is_return = 'F';

            $res = pjDummyBookingTest::pjGetBusAvailability($pickup_id, $return_id, $is_return, $booking_date, $return_date);

            /*echo $pickup_id.'  '.$return_id.'  '.$is_return. '  '.$booking_date.'  '.$return_date."<br/>";

             echo "<pre>";
                    print_r($res);
                    exit;*/


            if($res['status'] == 'true'){//bus available

               

                $busRes = pjDummyBookingTest::pjActionBusData($pickup_id, $return_id,$is_return,$booking_date, $return_date);
                 if($busRes['status'] == 'true'){//bus details available
                    $busInfo = $busRes['data']['to'];

                    //print_r($busInfo);
                    //$busInfo[0]['id'];
                    //exit;
                    //take bus id
                    //take return bus id
                    $bus_id = $busInfo[0]['id'];
                    $return_bus_id = 0;
                }



            

            $seats =  rand(1,45);
            $return_seats = '';
            if($is_return == 'T'){
               $return_seats =  rand(1,45);
               if($i%2 == 0){
                $seats .=  ','.rand(1,45);
                $return_seats .=  ','.rand(1,45);
               }
            }

            $title = 'mr';

            $keyVal = array_rand($names); 
            $name = $names[$keyVal];
            $f_l_names = explode(" ", $name);
            $first_name = $f_l_names[0];
            $last_name = $f_l_names[1];

            $mkeyVal = array_rand($mobileNumbers); 
            $phone = $mobileNumbers[$mkeyVal];

            $providerValues = array('gmail','yopmail','yahoo');
            $keyVal = array_rand($providerValues); 
            $provider = $providerValues[$keyVal];
            $email = $first_name.".".$last_name."@".$provider.".com";

            $is_user = 'F';  

            $keyVal = array_rand($countryArr); 
            $countryItem = $countryArr[$keyVal];
            $country_id = $countryItem['id'];

            $user_id = 0;
            $payment_status = '';
            $request_id = ''; 

            /*echo "pickup_id: ".$pickup_id."<br/>";
            echo "return_id: ".$return_id."<br/>";
            echo "bus_id: ".$bus_id."<br/>";
            echo "return_bus_id: ".$return_bus_id."<br/>";
            echo "is_return: ".$is_return."<br/>";
            echo "startDate: ".$startDate."<br/>";
            echo "endDate: ".$endDate."<br/>";
            echo "booking_date: ".$booking_date."<br/>";
            echo "return_date: ".$return_date."<br/>";
            echo "seats: ".$seats."<br/>";
            echo "return_seats: ".$return_seats."<br/>";
            echo "title: ".$title."<br/>";
            echo "first_name: ".$first_name."<br/>";
            echo "last_name: ".$last_name."<br/>";
            echo "phone: ".$phone."<br/>";
            echo "email: ".$email."<br/>";
            echo "is_user: ".$is_user."<br/>";
            echo "country_id: ".$country_id."<br/>";
            echo "user_id: ".$user_id."<br/>";
            echo "payment_status: ".$payment_status."<br/>";
            echo "request_id: ".$request_id."<br/>";

            exit;*/

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
                            $c++; 
                        } 
                    }
                } 

         }//empty checking
        }//bus availability
        }//for

        echo "$c bookings created";
        exit;
    }//function
   
}
?>